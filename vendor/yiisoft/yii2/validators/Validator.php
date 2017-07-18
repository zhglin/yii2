<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\Component;
use yii\base\NotSupportedException;

/**
 * Validator is the base class for all validators.
 *
 * Child classes should override the [[validateValue()]] and/or [[validateAttribute()]] methods to provide the actual
 * logic of performing data validation. Child classes may also override [[clientValidateAttribute()]]
 * to provide client-side validation support.
 *
 * Validator declares a set of [[builtInValidators|built-in validators]] which can
 * be referenced using short names. They are listed as follows:
 *
 * - `boolean`: [[BooleanValidator]]
 * - `captcha`: [[\yii\captcha\CaptchaValidator]]
 * - `compare`: [[CompareValidator]]
 * - `date`: [[DateValidator]]
 * - `datetime`: [[DateValidator]]
 * - `time`: [[DateValidator]]
 * - `default`: [[DefaultValueValidator]]
 * - `double`: [[NumberValidator]]
 * - `each`: [[EachValidator]]
 * - `email`: [[EmailValidator]]
 * - `exist`: [[ExistValidator]]
 * - `file`: [[FileValidator]]
 * - `filter`: [[FilterValidator]]
 * - `image`: [[ImageValidator]]
 * - `in`: [[RangeValidator]]
 * - `integer`: [[NumberValidator]]
 * - `match`: [[RegularExpressionValidator]]
 * - `required`: [[RequiredValidator]]
 * - `safe`: [[SafeValidator]]
 * - `string`: [[StringValidator]]
 * - `trim`: [[FilterValidator]]
 * - `unique`: [[UniqueValidator]]
 * - `url`: [[UrlValidator]]
 * - `ip`: [[IpValidator]]
 *
 * For more details and usage information on Validator, see the [guide article on validators](guide:input-validation).
 *
 * @property array $attributeNames Attribute names. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Validator extends Component
{
    /**
     * @var array list of built-in validators (name => class or configuration)
     */
    public static $builtInValidators = [
        'boolean' => 'yii\validators\BooleanValidator',
        'captcha' => 'yii\captcha\CaptchaValidator',
        'compare' => 'yii\validators\CompareValidator',
        'date' => 'yii\validators\DateValidator',
        'datetime' => [
            'class' => 'yii\validators\DateValidator',
            'type' => DateValidator::TYPE_DATETIME,
        ],
        'time' => [
            'class' => 'yii\validators\DateValidator',
            'type' => DateValidator::TYPE_TIME,
        ],
        'default' => 'yii\validators\DefaultValueValidator',
        'double' => 'yii\validators\NumberValidator',
        'each' => 'yii\validators\EachValidator',
        'email' => 'yii\validators\EmailValidator',
        'exist' => 'yii\validators\ExistValidator',
        'file' => 'yii\validators\FileValidator',
        'filter' => 'yii\validators\FilterValidator',
        'image' => 'yii\validators\ImageValidator',
        'in' => 'yii\validators\RangeValidator',
        'integer' => [
            'class' => 'yii\validators\NumberValidator',
            'integerOnly' => true,
        ],
        'match' => 'yii\validators\RegularExpressionValidator',
        'number' => 'yii\validators\NumberValidator',
        'required' => 'yii\validators\RequiredValidator',
        'safe' => 'yii\validators\SafeValidator',
        'string' => 'yii\validators\StringValidator',
        'trim' => [
            'class' => 'yii\validators\FilterValidator',
            'filter' => 'trim',
            'skipOnArray' => true,
        ],
        'unique' => 'yii\validators\UniqueValidator',
        'url' => 'yii\validators\UrlValidator',
        'ip' => 'yii\validators\IpValidator',
    ];
    /**
     * @var array|string attributes to be validated by this validator. For multiple attributes,
     * please specify them as an array; for single attribute, you may use either a string or an array.
     * 需要验证的属性
     */
    public $attributes = [];
    /**
     * @var string the user-defined error message. It may contain the following placeholders which
     * will be replaced accordingly by the validator:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     *
     * Note that some validators may introduce other properties for error messages used when specific
     * validation conditions are not met. Please refer to individual class API documentation for details
     * about these properties. By convention, this property represents the primary error message
     * used when the most important validation condition is not met.
     */
    public $message;
    /**
     * @var array|string scenarios that the validator can be applied to. For multiple scenarios,
     * please specify them as an array; for single scenario, you may use either a string or an array.
     * 场景  on 和 except中如果存在同一个scenario 忽略except
     */
    public $on = [];
    /**
     * @var array|string scenarios that the validator should not be applied to. For multiple scenarios,
     * please specify them as an array; for single scenario, you may use either a string or an array.
     * except的场景
     */
    public $except = [];
    /**
     * @var bool whether this validation rule should be skipped if the attribute being validated
     * already has some validation error according to some previous rules. Defaults to true.
     * 如果此attribute在别的验证器里验证过 并且出错 如果$skipOnError为true 就跳过当前的验证
     * 一个属性有多个验证规则
     */
    public $skipOnError = true;
    /**
     * @var bool whether this validation rule should be skipped if the attribute value
     * is null or an empty string.
     * 如果$isEmpty判断为空 并且 $skipOnEmpty为true这跳过此attribute的验证
     */
    public $skipOnEmpty = true;
    /**
     * @var bool whether to enable client-side validation for this validator.
     * The actual client-side validation is done via the JavaScript code returned
     * by [[clientValidateAttribute()]]. If that method returns null, even if this property
     * is true, no client-side validation will be done by this validator.
     */
    public $enableClientValidation = true;
    /**
     * @var callable a PHP callable that replaces the default implementation of [[isEmpty()]].
     * If not set, [[isEmpty()]] will be used to check if a value is empty. The signature
     * of the callable should be `function ($value)` which returns a boolean indicating
     * whether the value is empty.
     * 是否为空的判定规则  回调函数 'isEmpty' => function ($value) {return empty($value)};
     */
    public $isEmpty;
    /**
     * @var callable a PHP callable whose return value determines whether this validator should be applied.
     * The signature of the callable should be `function ($model, $attribute)`, where `$model` and `$attribute`
     * refer to the model and the attribute currently being validated. The callable should return a boolean value.
     *
     * This property is mainly provided to support conditional validation on the server-side.
     * If this property is not set, this validator will be always applied on the server-side.
     *
     * The following example will enable the validator only when the country currently selected is USA:
     *
     * ```php
     * function ($model) {
     *     return $model->country == Country::USA;
     * }
     * ```
     *
     * @see whenClient
     * 回调函数只有此函数为true 才会使用当前的验证器进行验证
     */
    public $when;
    /**
     * @var string a JavaScript function name whose return value determines whether this validator should be applied
     * on the client-side. The signature of the function should be `function (attribute, value)`, where
     * `attribute` is an object describing the attribute being validated (see [[clientValidateAttribute()]])
     * and `value` the current value of the attribute.
     *
     * This property is mainly provided to support conditional validation on the client-side.
     * If this property is not set, this validator will be always applied on the client-side.
     *
     * The following example will enable the validator only when the country currently selected is USA:
     *
     * ```javascript
     * function (attribute, value) {
     *     return $('#country').val() === 'USA';
     * }
     * ```
     *
     * @see when
     */
    public $whenClient;


    /**
     * Creates a validator object.
     * @param string|\Closure $type the validator type. This can be either:
     *  * a built-in validator name listed in [[builtInValidators]];
     *  * a method name of the model class;
     *  * an anonymous function;
     *  * a validator class name.
     * @param \yii\base\Model $model the data model to be validated.
     * @param array|string $attributes list of attributes to be validated. This can be either an array of
     * the attribute names or a string of comma-separated attribute names.
     * @param array $params initial values to be applied to the validator properties.
     * @return Validator the validator
     */
    public static function createValidator($type, $model, $attributes, $params = [])
    {
        //需要验证的属性名
        $params['attributes'] = $attributes;

        //Closure匿名函数 或者是 $model中的函数  使用InlineValidator验证器
        if ($type instanceof \Closure || $model->hasMethod($type)) {
            // method-based validator
            $params['class'] = __NAMESPACE__ . '\InlineValidator';
            $params['method'] = $type;
        } else {
            //内建的验证器
            if (isset(static::$builtInValidators[$type])) {
                $type = static::$builtInValidators[$type];
            }
            //扩展的  [class=> value]
            if (is_array($type)) {
                $params = array_merge($type, $params);
            } else {
                $params['class'] = $type;
            }
        }

        return Yii::createObject($params);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        //这里的attributes on except 都是createValidator中的 $params
        parent::init();
        $this->attributes = (array) $this->attributes;
        $this->on = (array) $this->on;
        $this->except = (array) $this->except;
    }

    /**
     * Validates the specified object.
     * @param \yii\base\Model $model the data model being validated
     * @param array|null $attributes the list of attributes to be validated.
     * Note that if an attribute is not associated with the validator - it will be
     * ignored. If this parameter is null, every attribute listed in [[attributes]] will be validated.
     * Validator是所有验证器类的父类
     * 此函数是验证之前的通用处理
     */
    public function validateAttributes($model, $attributes = null)
    {
        //获取需要此验证器需要进行验证的attribute
        if (is_array($attributes)) {
            $newAttributes = [];
            foreach ($attributes as $attribute) {
                if (in_array($attribute, $this->getAttributeNames(), true)) {
                    $newAttributes[] = $attribute;
                }
            }
            $attributes = $newAttributes;
        } else {
            $attributes = $this->getAttributeNames();
        }

        //是否能跳过
        foreach ($attributes as $attribute) {
            $skip = $this->skipOnError && $model->hasErrors($attribute)
                || $this->skipOnEmpty && $this->isEmpty($model->$attribute);
            if (!$skip) {
                if ($this->when === null || call_user_func($this->when, $model, $attribute)) {
                    $this->validateAttribute($model, $attribute);
                }
            }
        }
    }

    /**
     * Validates a single attribute.
     * Child classes must implement this method to provide the actual validation logic.
     * @param \yii\base\Model $model the data model to be validated
     * @param string $attribute the name of the attribute to be validated.
     * 具体的验证方法
     * 可以通过model进行单一的属性验证
     * 不通过在model中填写rules规则
     * SluggableBehavior.php validateSlug
     */
    public function validateAttribute($model, $attribute)
    {
        $result = $this->validateValue($model->$attribute);
        if (!empty($result)) {
            $this->addError($model, $attribute, $result[0], $result[1]);
        }
    }

    /**
     * Validates a given value.
     * You may use this method to validate a value out of the context of a data model.
     * @param mixed $value the data value to be validated.
     * @param string $error the error message to be returned, if the validation fails.
     * @return bool whether the data is valid.
     * 不通过model进行单独的属性验证
     *  $email = 'test@example.com';
     *  $validator = new yii\validators\EmailValidator();
     *  $validator->validate($email, $error)
     */
    public function validate($value, &$error = null)
    {
        $result = $this->validateValue($value);
        if (empty($result)) {
            return true;
        }

        list($message, $params) = $result;
        $params['attribute'] = Yii::t('yii', 'the input value');
        if (is_array($value)) {
            $params['value'] = 'array()';
        } elseif (is_object($value)) {
            $params['value'] = 'object';
        } else {
            $params['value'] = $value;
        }
        $error = $this->formatMessage($message, $params);

        return false;
    }

    /**
     * Validates a value.
     * A validator class can implement this method to support data validation out of the context of a data model.
     * @param mixed $value the data value to be validated.
     * @return array|null the error message and the parameters to be inserted into the error message.
     * Null should be returned if the data is valid.
     * @throws NotSupportedException if the validator does not supporting data validation without a model
     * 单独验证器
     * 需要具体的验证器 也就是此对象的子类
     */
    protected function validateValue($value)
    {
        throw new NotSupportedException(get_class($this) . ' does not support validateValue().');
    }

    /**
     * Returns the JavaScript needed for performing client-side validation.
     *
     * Calls [[getClientOptions()]] to generate options array for client-side validation.
     *
     * You may override this method to return the JavaScript validation code if
     * the validator can support client-side validation.
     *
     * The following JavaScript variables are predefined and can be used in the validation code:
     *
     * - `attribute`: an object describing the the attribute being validated.
     * - `value`: the value being validated.
     * - `messages`: an array used to hold the validation error messages for the attribute.
     * - `deferred`: an array used to hold deferred objects for asynchronous validation
     * - `$form`: a jQuery object containing the form element
     *
     * The `attribute` object contains the following properties:
     * - `id`: a unique ID identifying the attribute (e.g. "loginform-username") in the form
     * - `name`: attribute name or expression (e.g. "[0]content" for tabular input)
     * - `container`: the jQuery selector of the container of the input field
     * - `input`: the jQuery selector of the input field under the context of the form
     * - `error`: the jQuery selector of the error tag under the context of the container
     * - `status`: status of the input field, 0: empty, not entered before, 1: validated, 2: pending validation, 3: validating
     *
     * @param \yii\base\Model $model the data model being validated
     * @param string $attribute the name of the attribute to be validated.
     * @param \yii\web\View $view the view object that is going to be used to render views or view files
     * containing a model form with this validator applied.
     * @return string|null the client-side validation script. Null if the validator does not support
     * client-side validation.
     * @see getClientOptions()
     * @see \yii\widgets\ActiveForm::enableClientValidation
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        return null;
    }

    /**
     * Returns the client-side validation options.
     * This method is usually called from [[clientValidateAttribute()]]. You may override this method to modify options
     * that will be passed to the client-side validation.
     * @param \yii\base\Model $model the model being validated
     * @param string $attribute the attribute name being validated
     * @return array the client-side validation options
     * @since 2.0.11
     */
    public function getClientOptions($model, $attribute)
    {
        return [];
    }

    /**
     * Returns a value indicating whether the validator is active for the given scenario and attribute.
     *
     * A validator is active if
     *
     * - the validator's `on` property is empty, or
     * - the validator's `on` property contains the specified scenario
     *
     * @param string $scenario scenario name
     * @return bool whether the validator applies to the specified scenario.
     * 是否符合当前的验证器规则 scenario不在except中 并且(on为空或者scenario在on中
     */
    public function isActive($scenario)
    {
        return !in_array($scenario, $this->except, true) && (empty($this->on) || in_array($scenario, $this->on, true));
    }

    /**
     * Adds an error about the specified attribute to the model object.
     * This is a helper method that performs message selection and internationalization.
     * @param \yii\base\Model $model the data model being validated
     * @param string $attribute the attribute being validated
     * @param string $message the error message
     * @param array $params values for the placeholders in the error message
     */
    public function addError($model, $attribute, $message, $params = [])
    {
        $params['attribute'] = $model->getAttributeLabel($attribute);
        if (!isset($params['value'])) {
            $value = $model->$attribute;
            if (is_array($value)) {
                $params['value'] = 'array()';
            } elseif (is_object($value) && !method_exists($value, '__toString')) {
                $params['value'] = '(object)';
            } else {
                $params['value'] = $value;
            }
        }
        $model->addError($attribute, $this->formatMessage($message, $params));
    }

    /**
     * Checks if the given value is empty.
     * A value is considered empty if it is null, an empty array, or an empty string.
     * Note that this method is different from PHP empty(). It will return false when the value is 0.
     * @param mixed $value the value to be checked
     * @return bool whether the value is empty
     * 验证是否属性为空
     */
    public function isEmpty($value)
    {
        if ($this->isEmpty !== null) {
            return call_user_func($this->isEmpty, $value);
        } else {
            return $value === null || $value === [] || $value === '';
        }
    }

    /**
     * Formats a mesage using the I18N, or simple strtr if `\Yii::$app` is not available.
     * @param string $message
     * @param array $params
     * @since 2.0.12
     * @return string
     */
    protected function formatMessage($message, $params)
    {
        if (Yii::$app !== null) {
            return \Yii::$app->getI18n()->format($message, $params, Yii::$app->language);
        }

        $placeholders = [];
        foreach ((array) $params as $name => $value) {
            $placeholders['{' . $name . '}'] = $value;
        }

        return ($placeholders === []) ? $message : strtr($message, $placeholders);
    }

    /**
     * Returns cleaned attribute names without the `!` character at the beginning
     * @return array attribute names.
     * @since 2.0.12
     */
    public function getAttributeNames()
    {
        return array_map(function($attribute) {
            return ltrim($attribute, '!');
        }, $this->attributes);
    }
}
