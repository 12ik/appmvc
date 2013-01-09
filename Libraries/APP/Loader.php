<?php
/**
 * @category   APP
 * @package    APP_Loader
 * @version    $Id: Loader.php v1.0 2009-2-25 0:08:04 tomsui $
 */
class APP_Loader
{
    /**
     * Load a class
     *
     * Load a APP class file. If the class already exists in memory, return
     * directly.
     *
     * @static
     * @param  string $class String name of the class to load.
     * @param  array  $config  OPTIONAL; an array with adapter parameters.
     * @return void
     * @throws APP_Exception
     */
    public static function loadClass($class)
    {
        if (class_exists($class, false) || interface_exists($class, false)) {
            return;
        }

        $file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        self::_securityCheck($file);

        require($file) ;
    }

    /**
     * Autoload switch
     *
     * if switch opens, APP class can be used without loading previously
     *
     * @static
     * @param boolean $use  to set wheather Autoload switcher in use or not.
     * @return void
     */
    public static function Autoload($use = true)
    {
        if($use){
            spl_autoload_register(array('APP_Loader', 'loadClass'));
        }else{
            spl_autoload_unregister(array('APP_Loader', 'loadClass'));
        }

    }

    /**
     * Security Check
     *
     * File name of APP classs can just contain alpha,digits,backslash(/),
     * slash(\),underline(_),period(.) and dash(-). If contains other irregular
     * charactor, an APP_Exception is thrown.
     *
     * @static
     * @param boolean $filename  the filename string to be check.
     * @return void
     * @throws APP_Exception
     */
    protected static function _securityCheck($filename)
    {
        if (preg_match('/[^a-z0-9\\/\\\\_.-]/i', $filename)) {
            require_once 'APP/Exception.php';
            throw new APP_Exception('Security check: Illegal character in filename');
        }
    }
}

