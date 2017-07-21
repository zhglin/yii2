<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * Dependency is the base class for cache dependency classes.
 *
 * Child classes should override its [[generateDependencyData()]] for generating
 * the actual dependency data.
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Dependency extends \yii\base\Object
{
    /**
     * @var mixed the dependency data that is saved in cache and later is compared with the
     * latest dependency data.
     * 保存的依赖值
     */
    public $data;
    /**
     * @var bool whether this dependency is reusable or not. True value means that dependent
     * data for this cache dependency will be generated only once per request. This allows you
     * to use the same cache dependency for multiple separate cache calls while generating the same
     * page without an overhead of re-evaluating dependency data each time. Defaults to false.
     *
     * $dependency = new \yii\caching\FileDependency(['fileName'=>'yanying.txt']);
     * $cache->add('file_key','hello world',3000,$dependency);
     * $cache->add('file_key2','hello world2',3000,$dependency);
     * 如果为true $dependency不会多次执行generateDependencyData 来获取$data 一个请求进行多次设置比较有用
     * 生成相同的缓存时 使用相同的缓存依赖
     * 可以用di注入一个缓存依赖
     */
    public $reusable = false;

    /**
     * @var array static storage of cached data for reusable dependencies.
     */
    private static $_reusableData = [];


    /**
     * Evaluates the dependency by generating and saving the data related with dependency.
     * This method is invoked by cache before writing data into it.
     * @param Cache $cache the cache component that is currently evaluating this dependency
     * 获取依赖的值
     */
    public function evaluateDependency($cache)
    {
        if ($this->reusable) {
            $hash = $this->generateReusableHash();
            if (!array_key_exists($hash, self::$_reusableData)) {
                self::$_reusableData[$hash] = $this->generateDependencyData($cache);
            }
            $this->data = self::$_reusableData[$hash];
        } else {
            $this->data = $this->generateDependencyData($cache);
        }
    }

    /**
     * Returns a value indicating whether the dependency has changed.
     * @deprecated since version 2.0.11. Will be removed in version 2.1. Use [[isChanged()]] instead.
     */
    public function getHasChanged($cache)
    {
        return $this->isChanged($cache);
    }

    /**
     * Checks whether the dependency is changed
     * @param Cache $cache the cache component that is currently evaluating this dependency
     * @return bool whether the dependency has changed.
     * @since 2.0.11
     * 如果依赖的值有变动 缓存失效
     */
    public function isChanged($cache)
    {
        if ($this->reusable) {
            $hash = $this->generateReusableHash();
            if (!array_key_exists($hash, self::$_reusableData)) {
                self::$_reusableData[$hash] = $this->generateDependencyData($cache);
            }
            $data = self::$_reusableData[$hash];
        } else {
            $data = $this->generateDependencyData($cache);
        }
        return $data !== $this->data;
    }

    /**
     * Resets all cached data for reusable dependencies.
     */
    public static function resetReusableData()
    {
        self::$_reusableData = [];
    }

    /**
     * Generates a unique hash that can be used for retrieving reusable dependency data.
     * @return string a unique hash value for this cache dependency.
     * @see reusable
     * data值的不同导致sha1的结果不同
     */
    protected function generateReusableHash()
    {
        $data = $this->data;
        $this->data = null;  // https://github.com/yiisoft/yii2/issues/3052
        $key = sha1(serialize($this));
        $this->data = $data;
        return $key;
    }

    /**
     * Generates the data needed to determine if dependency is changed.
     * Derived classes should override this method to generate the actual dependency data.
     * @param Cache $cache the cache component that is currently evaluating this dependency
     * @return mixed the data needed to determine if dependency has been changed.
     * 获取依赖的值
     */
    abstract protected function generateDependencyData($cache);
}
