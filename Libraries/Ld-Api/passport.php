<?php
/**
 * @author zhanglupeng@joyport.com
 * @abstract passport api 服务的客户调用端
 */

define ('PS_API_ENTRY', 'http://pass.ledu.com/api/joyport');
define ('PS_APP_ID','2');
define ('PS_AUTHKEY','bb02IuofhmkQTQWcDoWl/gLz3X969i0+GkMdXESuekSppMqJlgvSSEDklqPMziBfegUUGMkXUyp7Is2PPxvIiNLy2eltBMrvDeFhvATPeEHaUcKdV5cNUAmVNH+E');

/**
 */
class UserClient extends LeduApiClient{
	//email是否可用
	
	function email_exists($email){
		$args = array();
		$args['method'] = 'user.email_exists';
		$args['email'] = $email;
		$res = $this->apiClall($args);
		return json_decode($res);
	}
	
	//用户名是否可用
	function username_exists($username){
		$args = array();
		$args['method'] = 'user.username_exists';
		$args['username'] = $username;
		$res = $this->apiClall($args);
		return json_decode($res);
	}
	//注册账号
	function reg($username, $password, $email ='', $ip = ''){
		$args = array();
		$args['method'] = 'user.reg';
		$args['username'] = $username;
		$args['password'] = $password;
		$args['email'] = $email;
		$arag['ip'] = $ip;
		$res = $this->apiClall($args);
		return json_decode($res);
	}
	//获取用户信息
	function info($username, $isusername  = true) {
		$args = array ();
		$args ['method'] = 'user.info';
		if($isusername){
			$args ['username'] = $username;
		}else{
			$args['uid']= $username;
		}
		$res = $this->apiClall ( $args );
		return json_decode ( $res );
	}
	//获取用户登陆的校验cookie
	function login($username, $password, $ua,$keep_login = 0,$ip = ''){
		$password = md5(md5($password).PS_AUTHKEY);
		$args = array();
		$args ['method'] = 'user.login';
		$args['password'] = $password;
		$args['username'] = $username;
		$args['ip'] = $ip;
		$args['ua'] = $_SERVER['HTTP_USER_AGENT'];
		$args['keep_login'] = $keep_login;
		$res = $this->apiClall ( $args );
		return json_decode ( $res );
	}

    //上次登录ip
    function lastLoginInfo($uid){
        $args = array();
		$args['method'] = 'user.last_login_info';
		$args['uid'] = $uid;
		$res = $this->apiClall($args);
		return json_decode($res);
    }


    //更新用户资料

    function update($uid,$nick_name = '',$real_name = '',$idcard = ''
    				, $email = "", $phone = "", $mobile = "", $address = "", $postcode = "")
    {
        $args = array();
		$args['method'] = 'user.update';
        $args['uid'] = $uid;
		$args['nick_name'] = $nick_name;
        $args['real_name'] = $real_name;
        $args['idcard'] = $idcard;
        
        $args['email'] = $email;
		$args['phone'] = $phone;
		$args['mobile'] = $mobile;
		$args['address'] = $address;
		$args['postcode'] = $postcode;
		$res = $this->apiClall($args);
		return json_decode($res);
    }
	
}


class GameClient extends LeduApiClient{
	/**
	 * 游戏登陆
	 * @param unknown_type $username
	 * @param unknown_type $pasword
	 * @param unknown_type $server
	 * @param unknown_type $code
	 * @param unknown_type $ip
	 */
	function login($username, $pasword, $server, $code='',$ip=''){
		$args = array();
		$args['method'] = 'game.login';
		$args['username'] = $username;
		$args['password'] = md5(md5($pasword).PS_AUTHKEY);
		$args['server'] = $server;
		$args['code'] = $code;
		$args['ip'] = $ip;
		
		$res = $this->apiClall($args);
		return json_decode($res);
	}
	/**
	 *获取用户最近登陆的游戏信息 
	 */
	function played($uid, $game_id = '',$count = 10){
		$args = array();
		$args['method'] = 'game.played';
		$args['uid'] = $uid;
		$args['game_id'] = $game_id;
		$args['count'] = $count;
		$res = $this->apiClall($args);
		return json_decode($res);
	}
	/**
	 * 游戏代理登陆
	 * @param  $args
	 */
	function proxlogin($args = array()){
		$args['method'] = 'game.proxlogin';
		$res = $this->apiClall($args);
		return json_decode($res);
	}


}

class LeduApiClient{
	private $last_url ;
	
	function apiClall($args) {
		$url = $this->makeRequest ( $args );
		return $this->fopen ( $url );
	}
	function makeRequest(array $args) {
		$args ['req_time'] = time ();
		$args ['app_id'] = PS_APP_ID;
		$args ['format'] = 'json';
		$args ['charset'] = 'UTF-8';
		ksort ( $args );
		$sig = "";
		foreach ( $args as $k => $v ) {
			$sig .= "$k=$v&";
		}
		$sig .= PS_AUTHKEY;
		$sig = md5 ( $sig );
		
		$req = PS_API_ENTRY . '?';
		foreach ( $args as $k => $v ) {
			$v = urlencode ( $v );
			$req .= "$k=$v&";
		}
		$req .= "sig=$sig";
		$this->last_url = $req;
		return $req;
	}
	function getLastUrl(){
		return $this->last_url;
	}
	
	function fopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
		$return = '';
		$matches = parse_url ( $url );
		
		! isset ( $matches ['host'] ) && $matches ['host'] = '';
		! isset ( $matches ['path'] ) && $matches ['path'] = '';
		! isset ( $matches ['query'] ) && $matches ['query'] = '';
		! isset ( $matches ['port'] ) && $matches ['port'] = '';
		$host = $matches ['host'];
		$path = $matches ['path'] ? $matches ['path'] . ($matches ['query'] ? '?' . $matches ['query'] : '') : '/';
		$port = ! empty ( $matches ['port'] ) ? $matches ['port'] : 80;
		if ($post) {
			$out = "POST $path HTTP/1.0\r\n";
			$out .= "Accept: */*\r\n";
			//$out .= "Referer: $boardurl\r\n";
			$out .= "Accept-Language: zh-cn\r\n";
			$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$out .= "Host: $host\r\n";
			$out .= 'Content-Length: ' . strlen ( $post ) . "\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Cache-Control: no-cache\r\n";
			$out .= "Cookie: $cookie\r\n\r\n";
			$out .= $post;
		} else {
			$out = "GET $path HTTP/1.0\r\n";
			$out .= "Accept: */*\r\n";
			//$out .= "Referer: $boardurl\r\n";
			$out .= "Accept-Language: zh-cn\r\n";
			$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$out .= "Host: $host\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Cookie: $cookie\r\n\r\n";
		}
		
		$fp = fsockopen ( ($ip ? $ip : $host), $port, $errno, $errstr, $timeout );
		
		if (! $fp) {
			return '';
		} else {
			stream_set_blocking ( $fp, $block );
			stream_set_timeout ( $fp, $timeout );
			@fwrite ( $fp, $out );
			$status = stream_get_meta_data ( $fp );
			if (! $status ['timed_out']) {
				while ( ! feof ( $fp ) ) {
					if (($header = @fgets ( $fp )) && ($header == "\r\n" || $header == "\n")) {
						break;
					}
				}
				
				$stop = false;
				while ( ! feof ( $fp ) && ! $stop ) {
					$data = fread ( $fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit) );
					$return .= $data;
					if ($limit) {
						$limit -= strlen ( $data );
						$stop = $limit <= 0;
					}
				}
			}
			@fclose ( $fp );
			return $return;
		}
	}
}


