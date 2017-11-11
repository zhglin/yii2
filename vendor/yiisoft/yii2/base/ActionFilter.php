<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ActionFilter is the base class for action filters.
 *
 * An action filter will participate in the action execution workflow by responding to
 * the `beforeAction` and `afterAction` events triggered by modules and controllers.
 *
 * Check implementation of [[\yii\filters\AccessControl]], [[\yii\filters\PageCache]] and [[\yii\filters\HttpCache]] as examples on how to use it.
 *
 * For more details and usage information on ActionFilter, see the [guide article on filters](guide:structure-filters).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActionFilter extends Behavior
{
    /**
     * @var array list of action IDs that this filter should apply to. If this property is not set,
     * then the filter applies to all actions, unless they are listed in [[except]].
     * If an action ID appears in both [[only]] and [[except]], this filter will NOT apply to it.
     *
     * Note that if the filter is attached to a module, the action IDs should also include child module IDs (if any)
     * and controller IDs.
     *
     * Since version 2.0.9 action IDs can be specified as wildcards, e.g. `site/*`.
     *
     * @see except
     * action在$only中的才进行过滤
     */
    public $only;
    /**
     * @var array list of action IDs that this filter should not apply to.
     * @see only
     * action 不在 $except中的才进行过滤
     */
    public $except = [];

    /*
     * 访问控制
     * 所有的actionFilter都是注册EVENT_BEFORE_ACTION事件
     * actionId都存在$only  $except 不进行过滤
     */

    /*
     * actionFilter是在controller中的Controller::EVENT_BEFORE_ACTION, Controller::EVENT_AFTER_ACTION中加入处理流程
     * 可以通过$only $except进行action的过滤(是否触发事件)
     * attach默认加载的是Controller::EVENT_BEFORE_ACTION 通过beforeFilter的返回值确定是否加载Controller::EVENT_AFTER_ACTION
     */

    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        $this->owner = $owner;
        $owner->on(Controller::EVENT_BEFORE_ACTION, [$this, 'beforeFilter']);
    }

    /**
     * @inheritdoc
     */
    public function detach()
    {
        if ($this->owner) {
            $this->owner->off(Controller::EVENT_BEFORE_ACTION, [$this, 'beforeFilter']);
            $this->owner->off(Controller::EVENT_AFTER_ACTION, [$this, 'afterFilter']);
            $this->owner = null;
        }
    }

    /**
     * @param ActionEvent $event
     * Controller.php beforeAction $event是ActionEvent
     */
    public function beforeFilter($event)
    {
        if (!$this->isActive($event->action)) {
            return;
        }

        $event->isValid = $this->beforeAction($event->action); //是否启用本次过滤
        if ($event->isValid) { //是否注册afterFilter
            // call afterFilter only if beforeFilter succeeds
            // beforeFilter and afterFilter should be properly nested
            $this->owner->on(Controller::EVENT_AFTER_ACTION, [$this, 'afterFilter'], null, false);
        } else {
            $event->handled = true;
        }
    }

    /**
     * @param ActionEvent $event
     */
    public function afterFilter($event)
    {
        $event->result = $this->afterAction($event->action, $event->result);
        $this->owner->off(Controller::EVENT_AFTER_ACTION, [$this, 'afterFilter']);
    }

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param Action $action the action to be executed.
     * @return bool whether the action should continue to be executed.
     * 具体的beforeFilter处理函数
     */
    public function beforeAction($action)
    {
        return true;
    }

    /**
     * This method is invoked right after an action is executed.
     * You may override this method to do some postprocessing for the action.
     * @param Action $action the action just executed.
     * @param mixed $result the action execution result
     * @return mixed the processed action result.
     */
    public function afterAction($action, $result)
    {
        return $result;
    }

    /**
     * Returns an action ID by converting [[Action::$uniqueId]] into an ID relative to the module
     * @param Action $action
     * @return string
     * @since 2.0.7
     */
    protected function getActionId($action)
    {
        //owner可能是一个module实例
        if ($this->owner instanceof Module) {
            $mid = $this->owner->getUniqueId();
            $id = $action->getUniqueId();
            if ($mid !== '' && strpos($id, $mid) === 0) {
                $id = substr($id, strlen($mid) + 1);
            }
        } else {
            $id = $action->id;
        }

        return $id;
    }

    /**
     * Returns a value indicating whether the filter is active for the given action.
     * @param Action $action the action being filtered
     * @return bool whether the filter is active for the given action.
     */
    protected function isActive($action)
    {
        //获取当前请求的action名
        $id = $this->getActionId($action);

        if (empty($this->only)) {
            $onlyMatch = true;
        } else {
            $onlyMatch = false;
            foreach ($this->only as $pattern) {
                if (fnmatch($pattern, $id)) { //正则匹配
                    $onlyMatch = true;
                    break;
                }
            }
        }

        $exceptMatch = false;
        foreach ($this->except as $pattern) {
            if (fnmatch($pattern, $id)) {
                $exceptMatch = true;
                break;
            }
        }

        return !$exceptMatch && $onlyMatch;
    }
}
