<?php

/**
 * Cache is an abstract class for all cache classes.
 * @package core
 * @subpackage cache
 * @author: zhlupeng@gmail.com
 */
abstract class Cache
{
	protected $_last_id = array();
	protected $_last_namespace = array();
	protected static $_instance_list = array();
	const DEFAULT_NAMESPACE = '';
 /**
  * Cache lifetime (in seconds)
  *
  * @var int $lifeTime
  */
  protected $lifeTime = 86400 ;

 /**
  * Timestamp of the last valid cache
  *
  * @var int $refreshTime
  */
  protected $refreshTime;

 /**
  * Gets the cache content for a given id and namespace.
  *
  * @param  string  The cache id
  * @param  string  The name of the cache namespace
  * @param  boolean If set to true, the cache validity won't be tested
  *
  * @return string  The data of the cache (or null if no cache available)
  */
  abstract public function get($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false);

  /**
   * Returns true if there is a cache for the given id and namespace.
   *
   * @param  string  The cache id
   * @param  string  The name of the cache namespace
   * @param  boolean If set to true, the cache validity won't be tested
   *
   * @return boolean true if the cache exists, false otherwise
   */
  abstract public function has($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false);

 /**
  * Saves some data in the cache.
  *
  * @param string The cache id
  * @param string The name of the cache namespace
  * @param string The data to put in cache
  *
  * @return boolean true if no problem
  */
  abstract public function set($id, $namespace = self::DEFAULT_NAMESPACE, $data);

 /**
  * Removes a content from the cache.
  *
  * @param string The cache id
  * @param string The name of the cache namespace
  *
  * @return boolean true if no problem
  */
  abstract public function remove($id, $namespace = self::DEFAULT_NAMESPACE);

 /**
  * Cleans the cache.
  *
  * If no namespace is specified all cache content will be destroyed
  * else only cache contents of the specified namespace will be destroyed.
  *
  * @param string The name of the cache namespace
  *
  * @return boolean true if no problem
  */
  abstract public function clean($namespace = null, $mode = 'all');

 /**
  * Sets a new life time.
  *
  * @param int The new life time (in seconds)
  */
  public function setLifeTime($newLifeTime)
  {
    $this->lifeTime = $newLifeTime;
    $this->refreshTime = time() - $newLifeTime;
	return $this;
  }

  /**
   * Returns the current life time.
   *
   * @return int The current life time (in seconds)
   */
  public function getLifeTime()
  {
    return $this->lifeTime;
  }

  public function getInstance($class = FileCache, $param = null){
	  $key = $class . serialize($param);
	  if(self::$_instance_list[$key]) 
		  return self::$_instance_list[$key];
	  else{
			$obj = new $class($param);
			if(! is_a($obj,Cache)){
				throw new Exception($class. ' is not a cache class');
				return null;
			}
			$obj->setLifeTime(600);
			self::$_instance_list[$key] = $obj;
			return $obj;
	  }
  }

  public function startCache($id,$namespace = self::DEFAULT_NAMESPACE, $auto = false ){
	  $this->_last_id[] = $id;
	  $this->_last_namespace[] = $namespace;
	  $reload = $_GET['ala_reload_page'];

	  if(! $reload && $this->has($id,$namespace,$auto)){
	  	echo $this->get($id, $namespace,$auto);
	    return false;
	  }
	  else{
		  ob_start();
		  return true;
	  }
  }
  public function endCache(){
	  $reload = $_GET['ala_reload_page'];
	  $id = array_pop($this->_last_id);
	  $namespace = array_pop($this->_last_namespace);

	  if( $reload  || ! $this->has($id, $namespace, true)){
		  $data = ob_get_contents();
		  $this->set($id, $namespace,$data);
		  ob_end_flush();
	  }
  }

 /**
  * Returns the cache last modification time.
  *
  * @return int The last modification time
  */
  abstract public function lastModified($id, $namespace = self::DEFAULT_NAMESPACE);
}
