<?php

class HttpRequest
{
	public $url = null;
	public $parameters = array();
	public $headers = null;
	public $cookies = null;
	public $body = null;
	public $followRedirect = true;
	public $maxRedirect = 3;
	public $numRedirect = 0;
	public $timeout = 30;
	public $curlOpts = array();

	function & post() {
		return HttpClient::post($this);
	}

	function & get() {
		return HttpClient::get($this);
	}
}

class HttpResponse
{
	public $version = null;
	public $statusCode = null;
	public $statusMessage = null;
	public $headers = array();
	public $body = null;
	public $file = null;

	public $error_no;
	public $error_mess;
	public $request;


	/*
		检查是否发生错误
	*/
	public function error(){

		if(($this->statusCode == 200) && empty($this->error_no) && $this->checkHeader($ctype)){
			return false;
		}

		return true;
	}

	public function errorMsg(){
		if(empty($this->error_no))
			return $this->statusMessage;
		else
			return $this->error_mess;
	}
	public function errorCode(){
		$this->error_no;  
	}

	public function checkheader($ctype){
		if(empty($ctype))
			return true;

		if(! preg_match($ctype,$this->headers['Content-Type'])){
			$this->error_no = 1001;
			$this->error_mess = "错误的文件类型";
			return false;
		}
		return true;
	}
							/*
									将获取的数据保存到文件
										执行成功返回文件路径否则返回false
							 */
	public function save($file = null){
		if($this->error()){
			return false;
		}
		
		if(empty($file))
			$file = $this->getTmpPath();

		$dir = dirname($file);
		if(! is_dir($dir)){
			system('mkdir -p '. $dir);
		}


		$fp = fopen($file, "w");		

		if(! empty($fp) )
			fwrite($fp, $this->body);
		else{
			$this->error_no = 1000;
			$this->error_mess = "file:$file is not writable!";
			return false;
		}
		fclose($fp);
		$this->file = $file;
		return $file;
	}
							/*
									直接显示所获取的的数据
							 */
	public function display(){
		if($this->error())
			return false;

		header("content-type: " .$this->headers['Content-Type']);
		echo $this->body;
		return true;
	}

	private function getTmpPath(){
		$dstr = "";
		$m = "Za0YbXc1WdVe2UfTg3ShRiQ4jPk5Ol6NmM7nL8oKpJqIr9HsGtFuEvDwCxByAz";
		for( $i = 1;$i <= 8;$i++ ) {
			mt_srand( ( double )microtime() * 1000000 );
			$ta = mt_rand( 0, 61 );
			$dstr = $dstr . substr( $m, $ta, 1 );
		} ;
		$ext = (substr($this->headers['Content-Type'],strrpos($this->headers['Content-Type'],"/")+1));
		return "/tmp/".$dstr.".".$ext;
	}
}



class HttpClient
{	
	function & get(&$httpRequest){
		$url = HttpClient::getURL($httpRequest);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if($httpRequest->cookies != null)
			curl_setopt($ch, CURLOPT_COOKIE,  HttpClient::getCookies($httpRequest));

		curl_setopt($ch, CURLOPT_TIMEOUT, $httpRequest->timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $httpRequest->timeout);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		if($httpRequest->headers != null)
			curl_setopt($ch, CURLOPT_HTTPHEADER,  HttpClient::getHeaders($httpRequest));

		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
		HttpClient::setExtraCurlOptions($httpRequest, $ch);

		$response = curl_exec($ch);	
		$error_no =  curl_errno($ch);
		$error_mess = curl_error($ch);
		curl_close($ch);

		if(!empty($error_no)){
			$httpResponse = new HttpResponse();
			$httpResponse->error_no = $error_no;
			$httpResponse->error_mess = $error_mess;
		}else{
			$httpResponse = HttpClient::parseResponse($response);
		}

		if ( $httpRequest->followRedirect === true && $httpRequest->numRedirect < $httpRequest->maxRedirect)
		{
			if ( array_key_exists('Location',$httpResponse->headers) )
			{
				$httpRequest->url = $httpResponse->headers['Location'];
				$httpRequest->parameters = null;
				$httpRequest->numRedirect++;
				$httpResponse =& HttpClient::get($httpRequest);
			}
		}

		$httpResponse->request = &$httpRequest;
		return $httpResponse;
	}

	function & post(&$httpRequest){
		$url = $httpRequest->url;

		if ( $httpRequest->body != null )
			$body =& $httpRequest->body;
		else
			$body = HttpClient::buildQuery($httpRequest->parameters);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if($httpRequest->cookies != null)
			curl_setopt($ch, CURLOPT_COOKIE,  HttpClient::getCookies($httpRequest));
		if ( $body !== null )
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_TIMEOUT, $httpRequest->timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $httpRequest->timeout);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		if($httpRequest->headers != null)
			curl_setopt($ch, CURLOPT_HTTPHEADER,  HttpClient::getHeaders($httpRequest));

		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
		HttpClient::setExtraCurlOptions($httpRequest, $ch);
		$response = curl_exec($ch);

		$error_no =  curl_errno($ch);
		$error_mess = curl_error($ch);
		curl_close($ch);

		if(!empty($error_no)){
			$httpResponse = new HttpResponse();
			$httpResponse->error_no = $error_no;
			$httpResponse->error_mess = $error_mess;
		}else{
			$httpResponse = HttpClient::parseResponse($response);
		}

		$httpResponse->request = &$httpRequest;
		return $httpResponse;
	}

	/**
	 * 构造查询串
	 */

	function buildQuery($arr){
		if ( $arr == null )
			return false;

		$url = '';
		$init = false;
		foreach($arr as $key=>$value)
		{
			if ( is_array($value) )
			{
				foreach($value as $val)
					$url .= urlencode($key).'='.urlencode($val).'&';
			}
			else
			{
				$url .= urlencode($key).'='.urlencode($value).'&';
			}
		}
		return $url;
	}
	/**
	 * 拼接url
	 */
	function getURL(&$httpRequest){
		$query = HttpClient::buildQuery($httpRequest->parameters);
		if ( $query == false )
			return $httpRequest->url;
		if ( strpos($httpRequest->url,'?')==false )
			return $httpRequest->url.'?'.$query;
		else
			return $httpRequest->url.'&'.$query;
	}

	function & getHeaders(&$httpRequest)
	{
		$headers = array();
		foreach($httpRequest->headers as $key=>$val)
		{

			if ( is_string($key) )
				$headers[] = $key.': '.$val;
			else
				$headers[] = $val;
		}
		return $headers;
	}
	function  & getCookies(&$httpRequest)
	{
		if(is_string($httpRequest->cookies ))
			return $httpRequest->cookies;
		$cookies = "";
		foreach($httpRequest->cookies as $key=>$val)
		{
			if( is_array($val)) 
			{
				foreach($val as $vkey=>$vval )
				{
					$cookies .= ';'.$key.'['.$vkey.']'.'='.urlencode($vval);
				}

			} else {
				if (strpos($val,'=') === false)
					$cookies .= ';'.$key.'='.urlencode($val);
				else
					$cookies .= ";".urlencode($val);
			}

		}
		return $cookies;
	}

	function setExtraCurlOptions(&$httpRequest, &$ch){
		if (! is_array($httpRequest->curlOpts) )
			return;

		foreach ($httpRequest->curlOpts as $key=>$value)
		{
			curl_setopt($ch, $key, $value);
		}
	}

	function & parseResponse(&$response){
		$httpResponse = new HttpResponse();

		$parts = preg_split('/\r\n\r\n/',$response,2);
		$nparts = count($parts);
		$headerLines = $nparts>0 ? $parts[0] : null;
		$contentLines = $nparts>1 ? $parts[1] : null;
		while ( preg_match('/^HTTP/',$contentLines) )
		{
			$parts = preg_split('/\r\n\r\n/',$contentLines,2);
			$nparts = count($parts);
			$headerLines = $nparts>0 ? $parts[0] : null;
			$contentLines = $nparts>1 ? $parts[1] : null;
		}
		$httpResponse->body =& $contentLines;
		$httpResponse->headers = array();

		$lines = explode("\r\n",$headerLines);
		if($lines)
		{
			foreach($lines as $line)
			{
				$parts = array();
				if( preg_match('/^([a-zA-Z -]+): +(.*)$/',$line,$parts) )
				{
					if(isset($httpResponse->headers[$parts[1]]))
					{
						if(is_array($httpResponse->headers[$parts[1]]))
						{
							$httpResponse->headers[$parts[1]][] = $parts[2];
						} else
						{
							$preExisting = $httpResponse->headers[$parts[1]];
							$httpResponse->headers[$parts[1]]= array($preExisting,$parts[2]); 	
						}
					} else
						{
							$httpResponse->headers[$parts[1]]=$parts[2];
						}

				}
				else if ( preg_match('/^HTTP/',$line) )
				{
					$parts = preg_split('/\s+/',$line,3);
					$nparts = count($parts);
					if ( $nparts > 0 )
						$httpResponse->version = $parts[0];
					if ( $nparts > 1 )
						$httpResponse->statusCode = $parts[1];
					if ( $nparts > 2 )
						$httpResponse->statusMessage = $parts[2];
				}
			}
		}
		return $httpResponse;
	}
}
