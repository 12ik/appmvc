<?php
/**
 * 
 * @package core
 * @subpackage cache
 * @author: zhlupeng@gmail.com
 */
include_once(dirname(__FILE__).'/Cache.php');
class AlaMemCache extends Cache{
	private $ip;
	private $port;
	private $time_out ;
	private $mc;
	private $last_has_data;
	private $last_has_key;

	const DEFAULT_NAMESPACE = '';
	static private  $mem= array();

	public function __construct($ip, $port){
		$this->ip = $ip;
		$this->port = $port;
		$this->time_out = 3;
		if(empty(self::$mem[$ip.$port])){
			$this->mc = new Memcache;
			self::$mem[$ip.$port] =  $this->mc;
			for ($mc_i=0;$mc_i<10;$mc_i++) {
				if($this->mc->connect($this->ip,$this->port,$this->time_out)) {
					break;
				}
			}
			if($mc_i == 10){
				throw new Exception("MemCache can't connect {$this->ip} port {$this->port}");
			}
		}
		else{
			$this->mc = self::$mem[$ip.$port];
		}
	}
	public function get($id, $ns= self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false){
		if($_GET['ala_reload_page'])
			return false;

		$key = md5($ns.$id);
		if($key == $this->last_has_key)
			return $this->last_has_data;
		else
			return $this->mc->get($key);
	}

	public function has($id, $ns= self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false){
		if($_GET['ala_reload_page'])
			return false;
		$this->last_has_key = md5($ns.$id);
		$this->last_has_data = $this->mc->get($this->last_has_key);
		return $this->last_has_data === false? false: true;
	}

	public function set($id, $ns = self::DEFAULT_NAMESPACE, $data){
		return $this->mc->set(md5($ns.$id),$data,false, $this->lifeTime);
	}

	public function remove($id, $ns = self::DEFAULT_NAMESPACE){
		return $this->mc->delete(md5($ns.$id));
	}
	public function clean($ns= null, $mode = 'all'){ throw Exception('不支持该方法');}
	public function lastModified($id, $ns = self::DEFAULT_NAMESPACE){ throw Exception('不支持该方法');}
}
