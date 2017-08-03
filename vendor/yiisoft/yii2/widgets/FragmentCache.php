<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\base\Widget;
use yii\caching\Cache;
use yii\caching\Dependency;
use yii\di\Instance;

/**
 * FragmentCache is used by [[\yii\base\View]] to provide caching of page fragments.
 *
 * @property string|false $cachedContent The cached content. False is returned if valid content is not found
 * in the cache. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FragmentCache extends Widget
{
    /**
     * @var Cache|array|string the cache object or the application component ID of the cache object.
     * After the FragmentCache object is created, if you want to change this property,
     * you should only assign it with a cache object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     * 数据缓存实例
     */
    public $cache = 'cache';
    /**
     * @var int number of seconds that the data can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire.
     * 过期时间
     */
    public $duration = 60;
    /**
     * @var array|Dependency the dependency that the cached content depends on.
     * This can be either a [[Dependency]] object or a configuration array for creating the dependency object.
     * For example,
     *
     * ```php
     * [
     *     'class' => 'yii\caching\DbDependency',
     *     'sql' => 'SELECT MAX(updated_at) FROM post',
     * ]
     * ```
     *
     * would make the output cache depends on the last modified time of all posts.
     * If any post has its modification time changed, the cached content would be invalidated.
     * 缓存依赖
     */
    public $dependency;
    /**
     * @var array list of factors that would cause the variation of the content being cached.
     * Each factor is a string representing a variation (e.g. the language, a GET parameter).
     * The following variation setting will cause the content to be cached in different versions
     * according to the current application language:
     *
     * ```php
     * [
     *     Yii::$app->language,
     * ]
     * ```
     * 参与缓存key的生成
     */
    public $variations;
    /**
     * @var bool whether to enable the fragment cache. You may use this property to turn on and off
     * the fragment cache according to specific setting (e.g. enable fragment cache only for GET requests).
     * 是否开启缓存
     */
    public $enabled = true;
    /**
     * @var array a list of placeholders for embedding dynamic contents. This property
     * is used internally to implement the content caching feature. Do not modify it.
     * 动态内容只会被$stack中第一个添加进去的片段缓存实例处理 也就是最外层的缓存
     */
    public $dynamicPlaceholders;

    /*
     * 依靠ob_start() ob_get_clean 来支持嵌套的缓冲区;
     * controller中获取view时会在view对象中使用ob_start开启缓冲区
     * 片段缓存时在view页面中开启ob_start 导致ob_start嵌套
     * ob_get_clean 会获取离ob_start最近的缓冲区内容并清理掉当前缓冲区来 这样就可以去掉一层嵌套
     * \yii\base\View beginCache()
     *      调用begin()的时候会把缓存对象的实例放入$stack中
     *      获取缓存结果
     *      调用end 获取缓存内容 + 动态内容 写入缓存 echo给页面
     *
     * 因为是可以有多个片段缓存 并且是两阶段操作 所以需要在$stack中保留实例 begin放进去 end生成缓存删除
     * 缓存不存在是需要调用end进行生成 要根据具体的缓存对象调用run函数
     * 缓存存在调用end调用run(),把内容echo给页面
     *
     * $this->getView()->cacheStack[] = $this;
     * $stack仅仅是与片段缓存对象相关 是一个页面的一部分
     * 而动态内容是整个页面都可以存在的
     */

    /**
     * Initializes the FragmentCache object.
     */
    public function init()
    {
        parent::init();

        $this->cache = $this->enabled ? Instance::ensure($this->cache, Cache::className()) : null;

        if ($this->cache instanceof Cache && $this->getCachedContent() === false) {
            $this->getView()->cacheStack[] = $this;
            ob_start();  //缓存中没有内容 开启ob_start 在run() 中ob_get_clean中获取缓存内容
            ob_implicit_flush(false);
        }
    }

    /**
     * Marks the end of content to be cached.
     * Content displayed before this method call and after [[init()]]
     * will be captured and saved in cache.
     * This method does nothing if valid content is already found in cache.
     */
    public function run()
    {
        if (($content = $this->getCachedContent()) !== false) {
            echo $content;
        } elseif ($this->cache instanceof Cache) {
            array_pop($this->getView()->cacheStack); //动态内容只会在最后一个缓存对象中处理  两个片段缓存只能嵌套 不能独立 否则动态内容会出错

            $content = ob_get_clean();
            if ($content === false || $content === '') {
                return;
            }
            if (is_array($this->dependency)) {
                $this->dependency = Yii::createObject($this->dependency);
            }
            //这里是连着动态内容一起进行缓存
            $data = [$content, $this->dynamicPlaceholders];
            $this->cache->set($this->calculateKey(), $data, $this->duration, $this->dependency);

            //执行动态内容替换掉
            if (empty($this->getView()->cacheStack) && !empty($this->dynamicPlaceholders)) {
                $content = $this->updateDynamicContent($content, $this->dynamicPlaceholders);
            }
            echo $content;
        }
    }

    /**
     * @var string|bool the cached content. False if the content is not cached.
     */
    private $_content;

    /**
     * Returns the cached content if available.
     * @return string|false the cached content. False is returned if valid content is not found in the cache.
     * 获取缓存内容
     */
    public function getCachedContent()
    {
        if ($this->_content !== null) {
            return $this->_content;
        }

        $this->_content = false;

        if (!($this->cache instanceof Cache)) {
            return $this->_content;
        }

        $key = $this->calculateKey(); //生成缓存key
        $data = $this->cache->get($key); //获取缓存内容
        if (!is_array($data) || count($data) !== 2) {
            return $this->_content;  //这里就返回false了 走run()方法生成缓存
        }

        //没有动态内容
        list ($this->_content, $placeholders) = $data;
        if (!is_array($placeholders) || count($placeholders) === 0) {
            return $this->_content;
        }

        //cacheStack 在init中只有获取不到缓存的时候才会放进去
        if (empty($this->getView()->cacheStack)) {
            // outermost cache: replace placeholder with dynamic content
            $this->_content = $this->updateDynamicContent($this->_content, $placeholders);
        }

        //嵌套片段 如果内层缓存没过期 外层过期 外层缓存要重新生成动态内容
        foreach ($placeholders as $name => $statements) {
            $this->getView()->addDynamicPlaceholder($name, $statements);
        }

        return $this->_content;
    }

    /**
     * Replaces placeholders in content by results of evaluated dynamic statements.
     *
     * @param string $content
     * @param array $placeholders
     * @return string final content
     * 替换掉动态内容
     */
    protected function updateDynamicContent($content, $placeholders)
    {
        foreach ($placeholders as $name => $statements) {
            $placeholders[$name] = $this->getView()->evaluateDynamicContent($statements);
        }

        return strtr($content, $placeholders);
    }

    /**
     * Generates a unique key used for storing the content in cache.
     * The key generated depends on both [[id]] and [[variations]].
     * @return mixed a valid cache key
     * 生成缓存key
     */
    protected function calculateKey()
    {
        $factors = [__CLASS__, $this->getId()];
        if (is_array($this->variations)) {
            foreach ($this->variations as $factor) {
                $factors[] = $factor;
            }
        }

        return $factors;
    }
}
