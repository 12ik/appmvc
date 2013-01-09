<?php
define ('LEDU_AUTH_NAME','backauth');
define ('LEDU_LOGIN_URL','http://port.ledu.com/leduuser/login');
class AdminUser{
	static $obj;
	private $uid;
	private $username;
	private  $auth_name = LEDU_AUTH_NAME;
	
	private function  __construct(){
		if (! empty ( $_COOKIE [$this->auth_name] )) {
			list ( $uid, $username, $ua, $tm ) = @$this->decodeAuth ($_COOKIE [$this->auth_name]);
			//ua检验
			if (empty ( $uid ) || $ua !== md5($_SERVER ['HTTP_USER_AGENT']))
				return;
			//TODO:过期时间检验
			
			$this->uid = $uid;
			$this->username = $username;
		}
	}
	
	static public function instance(){
		if(self::$obj)
			return self::$obj;
		else{
			self::$obj = new AdminUser();
		}
		return self::$obj;
	}

	
	/**
	 * 用户是否登陆
	 * */
	public function isLogin(){
		if(! empty($this->uid))
			return true; 
		else
			return false;
	}
	/**
	 * 
	 * 跳转到登录页面
	 * @param unknown_type $forward
	 * @param unknown_type $exit
	 */
	public function requireLogin($forward = '', $exit = true){
		if(! $this->isLogin()){
			if($forward === null){
				header("location: " . LEDU_LOGIN_URL);
			}
			else{
				if(empty($forward)){
					$forward = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
				}
				$forward = urlencode($forward);
				header("location: ". LEDU_LOGIN_URL . "?forward=$forward");
			}
			if($exit)
				exit;
		}
	}
	/**
	 * 
	 *设置登录状态
	 * @param unknown_type $uid
	 * @param unknown_type $username
	 * @param unknown_type $ua
	 * @param unknown_type $outtime
	 */
	
	public function setLogin($uid, $username, $ua = null,$outtime = null){
		
		if(empty($ua)){
			$ua = $_SERVER['HTTP_USER_AGENT'];
		}
		$str = $this->encodeAuth($uid, $username, $ua);

		setcookie($this->auth_name,urlencode($str),$outtime,'/','ledu.com');
	}
	/**
	 * 用户退出
	  */
	public function setLogout(){
		setcookie($this->auth_name,'',-1,'/','ledu.com');
	}
	
	public function __get($key){
		if('uid' == $key)
			return $this->uid;
		elseif ('username' == $key) {
			return $this->username;
		}
		return ;
	}
	
	public  function getUid(){
		return $this->uid;
	}	
	
	public function getUserName(){
		return $this->username;
	}

	/**
	 * 生成加密的登陆cookie
	 */
	private function  encodeAuth($uid,$username,$ua){
		$tm = time();
		$ua = md5($ua);
		$info = "$uid\t$username\t$ua\t$tm";
		$des = new DES();
		return $des->encrypt($info);
	}

	/**
	 * 解析加密cookie 
	 */
	private function decodeAuth($str){
		$des = new DES();
		$info = explode("\t",@$des->decrypt($str));
		if(is_array($info)){
			return $info;
		}else{
			return array();
		}
	}
}