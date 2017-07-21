<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * ChainedDependency represents a dependency which is composed of a list of other dependencies.
 *
 * When [[dependOnAll]] is true, if any of the dependencies has changed, this dependency is
 * considered changed; When [[dependOnAll]] is false, if one of the dependencies has NOT changed,
 * this dependency is considered NOT changed.
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ChainedDependency extends Dependency
{
    /**
     * @var Dependency[] list of dependencies that this dependency is composed of.
     * Each array element must be a dependency object.
     */
    public $dependencies = [];
    /**
     * @var bool whether this dependency is depending on every dependency in [[dependencies]].
     * Defaults to true, meaning if any of the dependencies has changed, this dependency is considered changed.
     * When it is set false, it means if one of the dependencies has NOT changed, this dependency
     * is considered NOT changed.
     * true 有一个依赖对象的值有变动 就算失效
     * false 有一个依赖对象值没变 就不算失效
     */
    public $dependOnAll = true;

    /*
     * $dependency = new \yii\caching\FileDependency(['fileName'=>'yanying.txt']);
     * $dependency2 = new \yii\caching\DbDependency(['sql'=>'']);
     * ChainedDependency = new \yii\caching\ChainedDependency(['dependencies'=>[$dependency,$dependency2]]);
     * $cache->add('file_key','hello world',3000,ChainedDependency);
     * 多个依赖
     * $dependencies 每个依赖的具体实例
     */

    /**
     * Evaluates the dependency by generating and saving the data related with dependency.
     * @param Cache $cache the cache component that is currently evaluating this dependency
     * 为每个依赖对象获取依赖值 data
     */
    public function evaluateDependency($cache)
    {
        foreach ($this->dependencies as $dependency) {
            $dependency->evaluateDependency($cache);
        }
    }

    /**
     * Generates the data needed to determine if dependency has been changed.
     * This method does nothing in this class.
     * @param Cache $cache the cache component that is currently evaluating this dependency
     * @return mixed the data needed to determine if dependency has been changed.
     * 聚合对象 不需要单个对象的生成方法
     */
    protected function generateDependencyData($cache)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function isChanged($cache)
    {
        foreach ($this->dependencies as $dependency) {
            if ($this->dependOnAll && $dependency->isChanged($cache)) {
                return true;
            } elseif (!$this->dependOnAll && !$dependency->isChanged($cache)) {
                return false;
            }
        }
        return !$this->dependOnAll;
    }
}
