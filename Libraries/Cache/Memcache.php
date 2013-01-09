<?php
/*
 * 调用memcache
 * */
require_once 'Interface.php';

class Cache_Memcache implements Cache_Interface
{
   private static $instance;
   private $config = array();
   private $memcache;
   public function __construct($config) 
   {
       $this->config = $config;
       $this->memcache = &new Memcache();
       $this->memcache->connect($config['host'], $config['port']);
   }
   
   public static function singleton($config) 
   {
       $key = $config['host']."_".$config['port'];
       if (!isset(self::$instance))
       {
           self::$instance = array();
       }
       if (!isset(self::$instance[$key])) {
           $c = __CLASS__;
           self::$instance[$key] = new $c($config);
       }
       return self::$instance[$key];
   }
   
   public function add($key,$var,$expire=600)
   {
       return $this->memcache->add($key, $var, false, $expire);
   }
   
   public function delete($key)
   {
        return $this->memcache->delete($key,3);
   }
   
   public function get($key)
   {
   		if(isset($_GET['ala_reload_page'])){
   			return false;
   		}
       $value = &$this->memcache->get($key);
       return $value;
   }
   
   public function replace($key,$var,$expire=600)
   {
       return $this->memcache->replace($key, $var, false, $expire);
   }
   
   public function set($key,$var,$expire=600)
   {
       return $this->memcache->set($key, $var, false, $expire);
   }
   
   public function __destruct()
   {
       $this->memcache->close();
   }
}