<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * MemCacheServer represents the configuration data for a single memcache or memcached server.
 *
 * See [PHP manual](http://php.net/manual/en/memcache.addserver.php) for detailed explanation
 * of each configuration property.
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MemCacheServer extends \yii\base\Object
{
    /**
     * @var string memcache server hostname or IP address
     */
    public $host;
    /**
     * @var int memcache server port
     */
    public $port = 11211;
    /**
     * @var int probability of using this server among all servers.
     * 为此服务器创建的桶的数量，用来控制此服务器被选中的权重，单个服务器被选中的概率是相对于所有服务器weight总和而言的。
     */
    public $weight = 1;
    /**
     * @var bool whether to use a persistent connection. This is used by memcache only.
     * 控制是否使用持久化连接。默认TRUE。
     */
    public $persistent = true;
    /**
     * @var int timeout in milliseconds which will be used for connecting to the server.
     * This is used by memcache only. For old versions of memcache that only support specifying
     * timeout in seconds this will be rounded up to full seconds.
     * 连接持续（超时）时间（单位秒），默认值1秒，修改此值之前请三思，过长的连接持续时间可能会导致失去所有的缓存优势。
     */
    public $timeout = 1000;
    /**
     * @var int how often a failed server will be retried (in seconds). This is used by memcache only.
     * 服务器连接失败时重试的间隔时间，默认值15秒。如果此参数设置为-1表示不重试。此参数和persistent参数在扩展以 dl()函数动态加载的时候无效。
     */
    public $retryInterval = 15;
    /**
     * @var bool if the server should be flagged as online upon a failure. This is used by memcache only.
     * 控制此服务器是否可以被标记为在线状态。设置此参数值为FALSE并且retry_interval参数 设置为-1时允许将失败的服务器保留在一个池中以免影响key的分配算法。
     * 对于这个服务器的请求会进行故障转移或者立即失败，
     * 这受限于memcache.allow_failover参数的设置。该参数默认TRUE，表明允许进行故障转移。
     */
    public $status = true;
    /**
     * @var \Closure this callback function will run upon encountering an error.
     * The callback is run before fail over is attempted. The function takes two parameters,
     * the [[host]] and the [[port]] of the failed server.
     * This is used by memcache only.
     * 允许用户指定一个运行时发生错误后的回调函数。回调函数会在故障转移之前运行。回调函数会接受到两个参数，分别是失败主机的 主机名和端口号。
     */
    public $failureCallback;
}
