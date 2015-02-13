<?php

namespace Overtrue\Wechat\Utils;

abstract class MagicAccess
{
    /**
     * 允许设置的属性名称
     *
     * @var array
     */
    protected $properties = array();

    /**
     * 基础属性
     *
     * @var array
     */
    protected $baseProperties = array('from', 'to', 'to_group', 'to_all', 'staff');

    /**
     * 方法名转换缓存
     *
     * @var array
     */
    static protected $snakeCache = array();


    /**
     * 设置属性
     *
     * @param string $attribute
     * @param string $value
     *
     * @return Overtrue\Wechat\Message
     */
    public function with($attribute, $value)
    {
        if (!is_scalar($value)) {
            throw new InvalidArgumentException("属性值只能为标量");
        }

        $attribute = $this->snake($attribute);

        if (!in_array($attribute, array_merge($this->baseProperties, $this->properties))) {
            throw new InvalidArgumentException("不存在的属性‘{$attribute}’");
        }

        $this->attributes[$attribute] = $value;

        return $this;
    }

    /**
     * 生成数组
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * 调用不存在的方法
     *
     * @param string $method
     * @param array  $args
     *
     * @return Overtrue\Wechat\Message
     */
    public function __call($method, $args)
    {
        if (stripos($method, 'with') === 0) {
            $method = substr($method, 4);
        }

        return $this->with($method, array_shift($args));
    }

    /**
     * 魔术读取
     *
     * @param string $property
     */
    public function __get($property)
    {
        return !isset($this->attributes[$property]) ? null : $this->attributes[$property];
    }

    /**
     * 魔术写入
     *
     * @param string $property
     * @param mixed  $value
     */
    public function __set($property, $value)
    {
        return $this->with($property, $value);
    }

    /**
     * 转换为下划线模式字符串
     *
     * @param string $value
     * @param string $delimiter
     *
     * @return string
     */
    protected function snake($value, $delimiter = '_')
    {
        $key = $value . $delimiter;

        if (isset(static::$snakeCache[$key])) {
            return static::$snakeCache[$key];
        }

        if (!ctype_lower($value)) {
            $value = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1' . $delimiter, $value));
        }

        return static::$snakeCache[$key] = $value;
    }
}