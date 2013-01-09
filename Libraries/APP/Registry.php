<?php
/**
 * @category   APP
 * @package    App_Registry
 * @copyright  Copyright (c) 1998 - 2009 Tencent. (http://www.qq.com)
 * @version    $Id: Registry.php v1.0 2009-2-25 0:08:04 tomsui $
 */
class APP_Registry
{
    /**
     * the data registered inside.
     * @var string
     * @static
     */
    static $_data = array();

    /**
     * setter method, basically same as offsetSet().
     *
     * This method can be called statically.
     *
     * @param string $key The location in the ArrayObject in which to store
     *   the value.
     * @param mixed $value The object to store in the ArrayObject.
     * @return void
     * @static
     */
    public static function set($key, $value)
    {
        self::$_data[$key] = $value;
    }

    /**
     * getter method, basically same as offsetGet().
     *
     * This method can be called statically.
     *
     * @param string $key - get the value associated with $key
     * @return mixed
     * @throws APP_Exception if no entry is registerd for $key.
     */
    public static function get($key)
    {
        if(!isset(self::$_data[$key]))
        {
            require_once LIB_PATH . '/APP/Exception.php';
            throw new APP_Exception("No entry is registered for key '$key'");
        }
        return self::$_data[$key];
    }

    /**
     * Returns TRUE if the $index is a named value in the registry,
     * or FALSE if $index was not found in the registry.
     *
     * @param  string $index
     * @return boolean
     * @static
     */
    public static function isRegistered($key)
    {
        return isset(self::$_data[$key]) ? true : false;
    }

    /**
     * Get all registed data
     *
     * @return array
     * @static
     */
    public static function getAllRegistedData()
    {
        return self::$_data;
    }
}

