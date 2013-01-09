<?php
include_once 'Snoopy.class.php';

/*
    1.  set http raw-headers
    2.  set referer                      执行抓取时：
    3.  set user-agent                      get  返回string
    4.  set cookie                          post 返回string
    5.  set proxy
    6.  set auth                            抓取页面源码 (保存到字符串/文件)
    7.  set params(get/post)                抓取页面内容 (保存到字符串/文件)

*/
class APP_File_Snoopy
{
    protected  $_instance = null;

    public function __construct()
    {
         $this->_instance = new Snoopy;
    }

    // 1. set http raw-headers
    public function setHeaders($headers = array())
    {
//		$headers = array('Cookie' => 'FG=1;BOLFONT=0' );
        $this->_instance->rawheaders = $headers;
        return $this;
    }

    // 2. set referer
    public function setReferer($referer = '')
    {
        $this->_instance->referer = $referer;
        return $this;
    }

    // 3. set user-agent
    public function setUserAgent($referer = '')
    {
        $this->_instance->agent = $referer;
        return $this;
    }
    // 4. set cookie
    public function setCookies($cookies = array())
    {
        $this->_instance->cookies = $cookies;
        return $this;
    }

    // 5. set proxy
    public function setProxy($proxy = array( 'host'=>'', 'port'=>'', 'user'=>'', 'pass'=>''))
    {
        foreach($proxy as $key => $val)
        {
            $key = 'proxy_'.$key ;
            $this->_instance->$key = $val;
        }
        return $this;
    }

    // 6. set auth
    public function setAuth($auth = array( 'user'=>'', 'pass'=>''))
    {
        foreach($proxy as $key => $val)
        {
            $this->_instance->$key = $val;
        }
        return $this;
    }


    /**
     * 抓取网页
     *
     * 抓取网页纯文字(不包括html tags)
     *
     * @param $url

     * @return 如果file_name存在，则将抓取结果写入文件,返回true; 否则返回抓取的字符串.
     */
    public function doGet($url, $html_tags = true, $file_name ='')
    {
        if($html_tags)
        {
			$this->_instance->fetch($url);
			$str =  $this->_instance->results;
        }
        else
        {
            $str = $this->_instance->fetchtext($url);
			$str =  $this->_instance->results;
        }

        if( !empty($file_name) )
        {
            require_once(LIB_PATH . '/APP/File.php');
            try{
                APP_File::write($file_name, $str);
                return true;
            }
            catch (Exception $e){
                echo "fetchtext succeeds , but can not write into file '$file_name' ";
                print $e->getMessage();
                exit();
            }
        }

        return $str;
    }

    public function doPost($url, $params = array())
    {
		$this->_instance->submit($url,$params);
		return $this->_instance->results;
    }
}

