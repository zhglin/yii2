<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * DummyCache is a placeholder cache component.
 *
 * DummyCache does not cache anything. It is provided so that one can always configure
 * a 'cache' application component and save the check of existence of `\Yii::$app->cache`.
 * By replacing DummyCache with some other cache component, one can quickly switch from
 * non-caching mode to caching mode.
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DummyCache extends Cache
{
    /*
     * 这个组件的目的是为了简化那些需要查询缓存有效性的代码。 例如，在开发中如果服务器没有实际的缓存支持，用它配置 一个缓存组件。
     * 一个真正的缓存服务启用后，可以再切换为使用相应的缓存组件。
     * 两种条件下你都可以使用同样的代码 Yii::$app->cache->get($key) 尝试从缓存中取回数据而不用担心 Yii::$app->cache 可能是 null
     */

    /**
     * Retrieves a value from cache with a specified key.
     * This is the implementation of the method declared in the parent class.
     * @param string $key a unique key identifying the cached value
     * @return mixed|false the value stored in cache, false if the value is not in the cache or expired.
     */
    protected function getValue($key)
    {
        return false;
    }

    /**
     * Stores a value identified by a key in cache.
     * This is the implementation of the method declared in the parent class.
     *
     * @param string $key the key identifying the value to be cached
     * @param mixed $value the value to be cached
     * @param int $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return bool true if the value is successfully stored into cache, false otherwise
     */
    protected function setValue($key, $value, $duration)
    {
        return true;
    }

    /**
     * Stores a value identified by a key into cache if the cache does not contain this key.
     * This is the implementation of the method declared in the parent class.
     * @param string $key the key identifying the value to be cached
     * @param mixed $value the value to be cached
     * @param int $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return bool true if the value is successfully stored into cache, false otherwise
     */
    protected function addValue($key, $value, $duration)
    {
        return true;
    }

    /**
     * Deletes a value with the specified key from cache
     * This is the implementation of the method declared in the parent class.
     * @param string $key the key of the value to be deleted
     * @return bool if no error happens during deletion
     */
    protected function deleteValue($key)
    {
        return true;
    }

    /**
     * Deletes all values from cache.
     * This is the implementation of the method declared in the parent class.
     * @return bool whether the flush operation was successful.
     */
    protected function flushValues()
    {
        return true;
    }
}
