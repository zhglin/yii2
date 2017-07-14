<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Object;

/**
 * UrlRule represents a rule used by [[UrlManager]] for parsing and generating URLs.
 *
 * To define your own URL parsing and creation logic you can extend from this class
 * and add it to [[UrlManager::rules]] like this:
 *
 * ```php
 * 'rules' => [
 *     ['class' => 'MyUrlRule', 'pattern' => '...', 'route' => 'site/index', ...],
 *     // ...
 * ]
 * ```
 *
 * @property null|int $createUrlStatus Status of the URL creation after the last [[createUrl()]] call. `null`
 * if rule does not provide info about create status. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UrlRule extends Object implements UrlRuleInterface
{
    /**
     * Set [[mode]] with this value to mark that this rule is for URL parsing only
     * 不能反向生成
     */
    const PARSING_ONLY = 1;
    /**
     * Set [[mode]] with this value to mark that this rule is for URL creation only
     * 只能用于生成  不进行url匹配
     */
    const CREATION_ONLY = 2;
    /**
     * Represents the successful URL generation by last [[createUrl()]] call.
     * @see $createStatus
     * @since 2.0.12
     * 匹配成功
     */
    const CREATE_STATUS_SUCCESS = 0;
    /**
     * Represents the unsuccessful URL generation by last [[createUrl()]] call, because rule does not support
     * creating URLs.
     * @see $createStatus
     * @since 2.0.12
     * 不能反向生成
     */
    const CREATE_STATUS_PARSING_ONLY = 1;
    /**
     * Represents the unsuccessful URL generation by last [[createUrl()]] call, because of mismatched route.
     * @see $createStatus
     * @since 2.0.12
     * 不匹配route
     */
    const CREATE_STATUS_ROUTE_MISMATCH = 2;
    /**
     * Represents the unsuccessful URL generation by last [[createUrl()]] call, because of mismatched
     * or missing parameters.
     * @see $createStatus
     * @since 2.0.12
     * 不匹配参数
     */
    const CREATE_STATUS_PARAMS_MISMATCH = 4;

    /**
     * @var string the name of this rule. If not set, it will use [[pattern]] as the name.
     */
    public $name;
    /**
     * On the rule initialization, the [[pattern]] matching parameters names will be replaced with [[placeholders]].
     * @var string the pattern used to parse and create the path info part of a URL.
     * @see host
     * @see placeholders
     * 请求的模式  经过处理后最终会把参数名去掉 只保留参数的匹配规则 用来正向匹配
     */
    public $pattern;
    /**
     * @var string the pattern used to parse and create the host info part of a URL (e.g. `http://example.com`).
     * @see pattern
     * 用于匹配和生成其他的url
     */
    public $host;
    /**
     * @var string the route to the controller action
     * 路由
     */
    public $route;
    /**
     * @var array the default GET parameters (name => value) that this rule provides.
     * When this rule is used to parse the incoming request, the values declared in this property
     * will be injected into $_GET.
     * 默认值
     */
    public $defaults = [];
    /**
     * @var string the URL suffix used for this rule.
     * For example, ".html" can be used so that the URL looks like pointing to a static HTML page.
     * If not set, the value of [[UrlManager::suffix]] will be used.
     * url后缀
     */
    public $suffix;
    /**
     * @var string|array the HTTP verb (e.g. GET, POST, DELETE) that this rule should match.
     * Use array to represent multiple verbs that this rule may match.
     * If this property is not set, the rule can match any verb.
     * Note that this property is only used when parsing a request. It is ignored for URL creation.
     * 限制的http 的 method
     */
    public $verb;
    /**
     * @var int a value indicating if this rule should be used for both request parsing and URL creation,
     * parsing only, or creation only.
     * If not set or 0, it means the rule is both request parsing and URL creation.
     * If it is [[PARSING_ONLY]], the rule is for request parsing only.
     * If it is [[CREATION_ONLY]], the rule is for URL creation only.
     * 设置模式 只用来正向解析 或者 反向生成
     */
    public $mode;
    /**
     * @var bool a value indicating if parameters should be url encoded.
     */
    public $encodeParams = true;
    /**
     * @var UrlNormalizer|array|false|null the configuration for [[UrlNormalizer]] used by this rule.
     * If `null`, [[UrlManager::normalizer]] will be used, if `false`, normalization will be skipped
     * for this rule.
     * @since 2.0.10
     */
    public $normalizer;

    /**
     * @var int|null status of the URL creation after the last [[createUrl()]] call.
     * @since 2.0.12
     * 创建url的结果
     */
    protected $createStatus;
    /**
     * @var array list of placeholders for matching parameters names. Used in [[parseRequest()]], [[createUrl()]].
     * On the rule initialization, the [[pattern]] parameters names will be replaced with placeholders.
     * This array contains relations between the original parameters names and their placeholders.
     * The array keys are the placeholders and the values are the original names.
     *
     * @see parseRequest()
     * @see createUrl()
     * @since 2.0.7
     * 经过hash后的参数名
     */
    protected $placeholders = [];

    /**
     * @var string the template for generating a new URL. This is derived from [[pattern]] and is used in generating URL.
     * pattern的模板 里面不含有参数匹配规则 只有参数名 controller/<name1>/<name2>/action 生成url时直接替换掉<name>
     */
    private $_template;
    /**
     * @var string the regex for matching the route part. This is used in generating URL.
     * 生成时用来匹配route的pattern  格式与$pattern相同 去掉名称只保留参数的匹配规则
     */
    private $_routeRule;
    /**
     * @var array list of regex for matching parameters. This is used in generating URL.
     * 用于生成的匹配规则 Url::to(['post/view', 'id' => 100]);   名称 => 匹配规则
     * 保存的是pattern中 不在route中的参数 有默认值的也会放在里面
     */
    private $_paramRules = [];
    /**
     * @var array list of parameters used in the route.
     * route中的参数值的名称以及值
     * array(2) {["controller"]=>"<controller>", ["action"]=>"<action>" }
     */
    private $_routeParams = [];


    /**
     * @return string
     * @since 2.0.11
     */
    public function __toString()
    {
        $str = '';
        if ($this->verb !== null) {
            $str .= implode(',', $this->verb) . ' ';
        }
        if ($this->host !== null && strrpos($this->name, $this->host) === false) {
            $str .= $this->host . '/';
        }
        $str .= $this->name;

        if ($str === '') {
            return '/';
        }
        return $str;
    }

    /**
     * Initializes this rule.
     */
    public function init()
    {
        if ($this->pattern === null) {
            throw new InvalidConfigException('UrlRule::pattern must be set.');
        }
        if ($this->route === null) {
            throw new InvalidConfigException('UrlRule::route must be set.');
        }
        if (is_array($this->normalizer)) {
            $normalizerConfig = array_merge(['class' => UrlNormalizer::className()], $this->normalizer);
            $this->normalizer = Yii::createObject($normalizerConfig);
        }
        if ($this->normalizer !== null && $this->normalizer !== false && !$this->normalizer instanceof UrlNormalizer) {
            throw new InvalidConfigException('Invalid config for UrlRule::normalizer.');
        }
        //http method 转成大写
        if ($this->verb !== null) {
            if (is_array($this->verb)) {
                foreach ($this->verb as $i => $verb) {
                    $this->verb[$i] = strtoupper($verb);
                }
            } else {
                $this->verb = [strtoupper($this->verb)];
            }
        }
        //当前 路由 的名称
        if ($this->name === null) {
            $this->name = $this->pattern;
        }
        $this->preparePattern();
    }

    /**
     * Process [[$pattern]] on rule initialization.
     */
    private function preparePattern()
    {
        $this->pattern = $this->trimSlashes($this->pattern);
        $this->route = trim($this->route, '/');

        if ($this->host !== null) {
            $this->host = rtrim($this->host, '/');
            $this->pattern = rtrim($this->host . '/' . $this->pattern, '/');
        } elseif ($this->pattern === '') {
            $this->_template = '';
            $this->pattern = '#^$#u';

            return;                                                   //'aaahttp://zhlxin.com/<controller:\w+>/<action:\w+>';
        } elseif (($pos = strpos($this->pattern, '://')) !== false) { //如果含有://说明含有http://域名
            if (($pos2 = strpos($this->pattern, '/', $pos + 3)) !== false) { //从://后面如果有／
                $this->host = substr($this->pattern, 0, $pos2); //截取出来作为host
            } else {
                $this->host = $this->pattern;
            }
        } elseif (strpos($this->pattern, '//') === 0) {     //如果pattern是以//开头  这种pattern用来生成的时候可以指定http或者https BaseUrl.php (ensureScheme)
            if (($pos2 = strpos($this->pattern, '/', 2)) !== false) { //并且//后面的字符里面含有／
                $this->host = substr($this->pattern, 0, $pos2); // host即为//到／的字符串
            } else {
                $this->host = $this->pattern;
            }
        } else {
            $this->pattern = '/' . $this->pattern . '/';
        }

        /*
         * pattern  '<controller:[\w-]+>/<id:\d+>'
         * route    '<controller>/view',
         * 如果route中包含< 说明route中有参数 需要从patter中获取
         * /<([\w._-]+)>/ 匹配<>中的字符
         *
         * _routeParams中保留着参数的名称 以及参数的匹配模式
         */
        if (strpos($this->route, '<') !== false && preg_match_all('/<([\w._-]+)>/', $this->route, $matches)) {
            foreach ($matches[1] as $name) {
                $this->_routeParams[$name] = "<$name>";
            }
        }

        $this->translatePattern(true);
    }

    /**
     * Prepares [[$pattern]] on rule initialization - replace parameter names by placeholders.
     *
     * @param bool $allowAppendSlash Defines position of slash in the param pattern in [[$pattern]].
     * If `false` slash will be placed at the beginning of param pattern. If `true` slash position will be detected
     * depending on non-optional pattern part.
     */
    private function translatePattern($allowAppendSlash)
    {
        $tr = [
            '.' => '\\.',
            '*' => '\\*',
            '$' => '\\$',
            '[' => '\\[',
            ']' => '\\]',
            '(' => '\\(',
            ')' => '\\)',
        ];

        $tr2 = [];
        $requiredPatternPart = $this->pattern;
        $oldOffset = 0;
        /*
         * /<([\w._-]+):?([^>]+)?>/
         * <controller:\w+>/<action:\w+>
         * 匹配出来参数的名称 以及对应的匹配规则
         * array(3){[0]=>{[0]=>"<controller:\w+>"[1]=>int(18)}
                    [1]=>{[0]=>"controller"     [1]=>int(19)}
                    [2]=>{[0]=>"\w+"            [1]=>int(30)}
                  }
         * '[^\/]+'  匹配除了／的任意字符
         */
        if (preg_match_all('/<([\w._-]+):?([^>]+)?>/', $this->pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            $appendSlash = false;
            foreach ($matches as $match) {
                $name = $match[1][0]; //当前参数的名称
                $pattern = isset($match[2][0]) ? $match[2][0] : '[^\/]+'; //当前参数的匹配规则
                $placeholder = 'a' . hash('crc32b', $name); // placeholder must begin with a letter
                $this->placeholders[$placeholder] = $name;  //对name进行hash
                if (array_key_exists($name, $this->defaults)) { //此参数有默认值
                    $length = strlen($match[0][0]); //匹配出来的字符串长度
                    $offset = $match[0][1]; //从第几个字符开始匹配到的 下标从1开始
                    $requiredPatternPart = str_replace("/{$match[0][0]}/", '//', $requiredPatternPart);//'posts/<page:\d+>/<tag>' 转为 posts//<tag>
                    /*
                     * (?P<name>pattern) 命名捕获
                     * $tr["/<page>"] = "(/(?P<hash(name)>\d+))?";
                     */
                    if (
                        $allowAppendSlash
                        && ($appendSlash || $offset === 1)
                        && (($offset - $oldOffset) === 1)
                        && isset($this->pattern[$offset + $length])
                        && $this->pattern[$offset + $length] === '/'
                        && isset($this->pattern[$offset + $length + 1]) //匹配出来的在 开头 的位置并且后面还有 后面加／
                    ) {
                        // if pattern starts from optional params, put slash at the end of param pattern
                        // @see https://github.com/yiisoft/yii2/issues/13086
                        $appendSlash = true;
                        $tr["<$name>/"] = "((?P<$placeholder>$pattern)/)?";
                    } elseif (   //匹配出来的在 中间位置 前面加／
                        $offset > 1
                        && $this->pattern[$offset - 1] === '/'
                        && (!isset($this->pattern[$offset + $length]) || $this->pattern[$offset + $length] === '/')
                    ) {
                        $appendSlash = false;
                        $tr["/<$name>"] = "(/(?P<$placeholder>$pattern))?";
                    } //不管在开始位置还是中间位置 都放到tr中
                    $tr["<$name>"] = "(?P<$placeholder>$pattern)?";
                    $oldOffset = $offset + $length;
                } else { //此参数 不含有默认值
                    $appendSlash = false;
                    $tr["<$name>"] = "(?P<$placeholder>$pattern)";
                }

                //如果_routeParams中含有同样的 参数名称
                if (isset($this->_routeParams[$name])) {
                    $tr2["<$name>"] = "(?P<$placeholder>$pattern)";
                } else {
                    //不在route中的参数值
                    $this->_paramRules[$name] = $pattern === '[^\/]+' ? '' : "#^$pattern$#u";
                }
            }
        }
        /*
         * 'pattern' => '<page:\d+>/<tag>',
           'route' => 'post/index',
           'defaults' => ['page' => 1, 'tag' => ''],
         */
        // we have only optional params in route - ensure slash position on param patterns 全是可选的参数
        // 以默认值开头的pattern 不再按照默认值的方式进行处理也就是不在正则中加上／
        /*如果没有此判断处理
         * 'pattern' => '<page:\d+>/<tag>',
         * 'defaults' => ['page' => 1, 'tag' => ''],
         * 对于此url 生成的pattern为 #^((?P<a140ab620>\d+)/)?(?P<a0389b783>[^\/]+)?$#u
         * 对于 ／tag 这种url会匹配不出来 因为是以／开头 正则是要求\d+开头
         *
         * 这步处理的pattern结果为 #^(?P<a140ab620>\d+)?(/(?P<a0389b783>[^\/]+))?$#u
         */
        if ($allowAppendSlash && trim($requiredPatternPart, '/') === '') {
            //$this->pattern = #^(?P<a140ab620>\d+)?(/(?P<a0389b783>[^\/]+))?$#u
            $this->translatePattern(false);
            return;
        }

        // /posts/<page>/<tag>/lin/  _template是去掉参数的匹配规则之后的字符 去掉参数的匹配规则
        $this->_template = preg_replace('/<([\w._-]+):?([^>]+)?>/', '<$1>', $this->pattern);
        // 有默认值 "#^posts(/(?P<a140ab620>\d+))?(/(?P<a0389b783>[^\/]+))?/lin$#u" 转换
        // 无默认值 "#^posts/(?P<a140ab620>\d+)/(?P<a0389b783>[^\/]+)/lin$#u"
        // tr中包含有默认值 与 无默认值的参数名 以及匹配规则 有默认值的匹配规则只是多了／
        $this->pattern = '#^' . trim(strtr($this->_template, $tr), '/') . '$#u';
        // if host starts with relative scheme, then insert pattern to match any
        if (strpos($this->host, '//') === 0) {
            // #^[\w]+://posts(/(?P<a140ab620>\d+))?(/(?P<a0389b783>[^\/]+))?/lin$#u 插入对协议的匹配 http https ftp ..
            $this->pattern = substr_replace($this->pattern, '[\w]+://', 2, 0);
        }
//echo $this->pattern;die;
        if (!empty($this->_routeParams)) {
            //与_template意义相同
            $this->_routeRule = '#^' . strtr($this->route, $tr2) . '$#u';
        }

    }

    /**
     * @param UrlManager $manager the URL manager
     * @return UrlNormalizer|null
     * @since 2.0.10
     */
    protected function getNormalizer($manager)
    {
        if ($this->normalizer === null) {
            return $manager->normalizer;
        } else {
            return $this->normalizer;
        }
    }

    /**
     * @param UrlManager $manager the URL manager
     * @return bool
     * @since 2.0.10
     */
    protected function hasNormalizer($manager)
    {
        return $this->getNormalizer($manager) instanceof UrlNormalizer;
    }

    /**
     * Parses the given request and returns the corresponding route and parameters.
     * @param UrlManager $manager the URL manager
     * @param Request $request the request component
     * @return array|bool the parsing result. The route and the parameters are returned as an array.
     * If `false`, it means this rule cannot be used to parse this path info.
     */
    public function parseRequest($manager, $request)
    {
        //是否只能反向生成
        if ($this->mode === self::CREATION_ONLY) {
            return false;
        }

        //是否匹配http method
        if (!empty($this->verb) && !in_array($request->getMethod(), $this->verb, true)) {
            return false;
        }

        $suffix = (string)($this->suffix === null ? $manager->suffix : $this->suffix);
        $pathInfo = $request->getPathInfo();
        $normalized = false;
        if ($this->hasNormalizer($manager)) {
            $pathInfo = $this->getNormalizer($manager)->normalizePathInfo($pathInfo, $suffix, $normalized);
        }

        //验证后缀是否相等
        if ($suffix !== '' && $pathInfo !== '') {
            $n = strlen($suffix);
            if (substr_compare($pathInfo, $suffix, -$n, $n) === 0) {
                $pathInfo = substr($pathInfo, 0, -$n); //pathInfo去掉后缀
                if ($pathInfo === '') {
                    // suffix alone is not allowed
                    return false;
                }
            } else {
                return false;
            }
        }

        if ($this->host !== null) {
            //pathInfo转成当前的完整路径
            $pathInfo = strtolower($request->getHostInfo()) . ($pathInfo === '' ? '' : '/' . $pathInfo);
        }

        //能否匹配上
        if (!preg_match($this->pattern, $pathInfo, $matches)) {
            return false;
        }

        /*
         * array(5) {   [0]=>"site/index"   ["a4cf2669a"]=>"site"
         *              [1]=>"site"         ["a47cc8c92"]=>"index"
         *              [2]=>"index"
         *          }
         * 把经过hash的参数名转换回来
         */
        $matches = $this->substitutePlaceholderNames($matches);

        //没有匹配出来的参数 从defaults中赋值
        foreach ($this->defaults as $name => $value) {
            if (!isset($matches[$name]) || $matches[$name] === '') {
                $matches[$name] = $value;
            }
        }

        /*
         * 根据pattern的匹配结果 对route中的参数进行赋值
         */
        $params = $this->defaults;
        $tr = []; // <name> => value
        foreach ($matches as $name => $value) {
            if (isset($this->_routeParams[$name])) {  //route 中有参数值
                $tr[$this->_routeParams[$name]] = $value;
                unset($params[$name]);
            } elseif (isset($this->_paramRules[$name])) { //pattern 中有参数 返回
                $params[$name] = $value;
            }
        }
        // #^(?P<a4cf2669a>\w+)/(?P<a47cc8c92>\w+)$#u
        // <controller>/<action>
        if ($this->_routeRule !== null) {
            $route = strtr($this->route, $tr); // 把route中的参数换成参数值
        } else {
            $route = $this->route;
        }

        Yii::trace("Request parsed with URL rule: {$this->name}", __METHOD__);

        if ($normalized) {
            // pathInfo was changed by normalizer - we need also normalize route
            return $this->getNormalizer($manager)->normalizeRoute([$route, $params]);
        } else {
            return [$route, $params];
        }
    }

    /**
     * Creates a URL according to the given route and parameters.
     * @param UrlManager $manager the URL manager
     * @param string $route the route. It should not have slashes at the beginning or the end.
     * @param array $params the parameters
     * @return string|bool the created URL, or `false` if this rule cannot be used for creating this URL.
     */
    public function createUrl($manager, $route, $params)
    {
        //只允许解析 不允许生成
        if ($this->mode === self::PARSING_ONLY) {
            $this->createStatus = self::CREATE_STATUS_PARSING_ONLY;
            return false;
        }

        //替换掉_template中的<name> 使<name>变成具体的值 结果即为url
        $tr = [];

        // match the route part first
        /*
         *  如果route中含有参数 并且能匹配上
         *      如果参数具有默认值 并且与$params中的值相同 生成的时候忽略掉
         *      否则以$params中的值进行生成
         */
        if ($route !== $this->route) { //此路由规则中含有参数
            if ($this->_routeRule !== null && preg_match($this->_routeRule, $route, $matches)) {
                $matches = $this->substitutePlaceholderNames($matches);
                foreach ($this->_routeParams as $name => $token) {
                    if (isset($this->defaults[$name]) && strcmp($this->defaults[$name], $matches[$name]) === 0) { //如果此参数有默认值 并且与匹配的值相同
                        $tr[$token] = '';
                    } else {
                        $tr[$token] = $matches[$name]; //<controller> => $matches[$name]
                    }
                }
            } else {
                $this->createStatus = self::CREATE_STATUS_ROUTE_MISMATCH;
                return false;
            }
        }

        // match default params
        // if a default param is not in the route pattern, its value must also be matched
        /*
         *  [
         *      'pattern' => '<controller>/index1/<page:\d+>',
         *      'route' => '<controller>/index',
         *      'defaults' => ['controller' => 'zhl', 'page' => 1],
         *  ],
         *
         *  [
         *      'pattern' => '<controller>/index2/',
         *      'route' => '<controller>/index',
         *      'defaults' => ['controller' => 'zhl1'],
         *  ],
         *
         *  如果此条路由规则有默认值 并且想匹配到此规则
         *  那么$params中 也要传默认值的参数 (''除外) 如果默认值与$params中的值相同 替换为''
         *  否则可能多条规则都可以匹配成功
         */
        foreach ($this->defaults as $name => $value) {
            if (isset($this->_routeParams[$name])) { //route中有默认值 不处理 已经在route里面处理过了
                continue;
            }

            if (!isset($params[$name])) { //$params中没有给默认值的参数
                // allow omit empty optional params  可以忽略空的可选参数  默认值为''生成是可以不传此值
                // @see https://github.com/yiisoft/yii2/issues/10970
                if (in_array($name, $this->placeholders) && strcmp($value, '') === 0) { //params中参数名存在在pattern参数中 并且值为''
                    $params[$name] = '';
                } else {
                    $this->createStatus = self::CREATE_STATUS_PARAMS_MISMATCH;
                    return false;
                }
            }

            //params[$name] 与默认值 相等  如果在_paramRules中从$params中删掉$name 防止下面匹配_paramRules中重复处理
            //如果不相等 此处不处理 接着匹配_paramRules
            if (strcmp($params[$name], $value) === 0) { // strcmp will do string conversion automatically
                unset($params[$name]);
                if (isset($this->_paramRules[$name])) {
                    $tr["<$name>"] = '';
                }
            } elseif (!isset($this->_paramRules[$name])) { //默认值不在_paramRules里面 default中的值 多于pattern中的参数值 不匹配
                $this->createStatus = self::CREATE_STATUS_PARAMS_MISMATCH;
                return false;
            }
        }

        // match params in the pattern
        /*
         * 匹配_paramRules中的参数规则
         *  如果匹配不上规则 并且不在defaults中 匹配失败
         */
        foreach ($this->_paramRules as $name => $rule) {
            //$Params[$name] 能匹配上参数的规则
            if (isset($params[$name]) && !is_array($params[$name]) && ($rule === '' || preg_match($rule, $params[$name]))) {
                $tr["<$name>"] = $this->encodeParams ? urlencode($params[$name]) : $params[$name];
                unset($params[$name]);
            }
            /*
             * [
             *      'pattern' => 'post/<page:\d+>/<tag>',
             *      'route' => 'post/index1',
             *      'defaults' => ['page' => ''],
             *      'host' => "http://<lang:en|fr>.example.com",
             *  ],
             * Url::to(['post/index1', 'tag' => 'a', 'lang' => 'en'])
             * 因为生成的时候没有传page并且page为'' 上面在匹配defaults时为params[page]赋值为'' 来进行_paramRules的匹配
             * 但是page的值是匹配不了\d+的规则的 这时如果在defaults中 算匹配成功
             */
            elseif (!isset($this->defaults[$name]) || isset($params[$name])) {
                $this->createStatus = self::CREATE_STATUS_PARAMS_MISMATCH;
                return false;
            }
        }

        $url = $this->trimSlashes(strtr($this->_template, $tr));
        //如果有参数有默认值 并且生成的与默认值相同 生成的结果回去掉默认值 但是会留下多余的／
        // 例如/post//a
        //需要preg_replace把多个//替换成1个
        if ($this->host !== null) {
            $pos = strpos($url, '/', 8);
            if ($pos !== false) {
                $url = substr($url, 0, $pos) . preg_replace('#/+#', '/', substr($url, $pos));
            }
        } elseif (strpos($url, '//') !== false) {
            $url = preg_replace('#/+#', '/', trim($url, '/'));
        }

        //如果有后缀加上后缀
        if ($url !== '') {
            $url .= ($this->suffix === null ? $manager->suffix : $this->suffix);
        }

        //匹配完default 与 _paramRules 处理剩下的 $params
        if (!empty($params) && ($query = http_build_query($params)) !== '') {
            $url .= '?' . $query;
        }

        $this->createStatus = self::CREATE_STATUS_SUCCESS;
        return $url;
    }

    /**
     * Returns status of the URL creation after the last [[createUrl()]] call.
     *
     * @return null|int Status of the URL creation after the last [[createUrl()]] call. `null` if rule does not provide
     * info about create status.
     * @see $createStatus
     * @since 2.0.12
     */
    public function getCreateUrlStatus() {
        return $this->createStatus;
    }

    /**
     * Returns list of regex for matching parameter.
     * @return array parameter keys and regexp rules.
     *
     * @since 2.0.6
     */
    protected function getParamRules()
    {
        return $this->_paramRules;
    }

    /**
     * Iterates over [[placeholders]] and checks whether each placeholder exists as a key in $matches array.
     * When found - replaces this placeholder key with a appropriate name of matching parameter.
     * Used in [[parseRequest()]], [[createUrl()]].
     *
     * @param array $matches result of `preg_match()` call
     * @return array input array with replaced placeholder keys
     * @see placeholders
     * @since 2.0.7
     */
    protected function substitutePlaceholderNames(array $matches)
    {
        foreach ($this->placeholders as $placeholder => $name) {
            if (isset($matches[$placeholder])) {
                $matches[$name] = $matches[$placeholder];
                unset($matches[$placeholder]);
            }
        }
        return $matches;
    }

    /**
     * Trim slashes in passed string. If string begins with '//', two slashes are left as is
     * in the beginning of a string.
     *
     * @param string $string
     * @return string
     * 如果pattern是以两个//开头 就保留//说明有host 否则去掉/
     */
    private function trimSlashes($string)
    {
        if (strpos($string, '//') === 0) {
            return '//' . trim($string, '/');
        }
        return trim($string, '/');
    }
}
