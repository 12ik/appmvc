<?php
/**
 * 验证模块
 * @author Ericcao
 */

class Util_Validate
{
    private $validateArray;
    private $message;
     
    function __construct(&$validateArray)
    {
        $this->validateArray = &$validateArray;
        $this->message = "";
    }
     
    function getMessage()
    {
        return $this->message;
    }
     
     
    function &notEmpty($key,$message = "")
    {
        if(!isset($this->validateArray[$key])|| $this->validateArray[$key]=="")
        {
            $this->message = $message;
            throw new Exception($key."验证错误:".$message);
        }
        return $this;
    }
     
    /**
     * 校验字符串长度
     * @param $key string key
     * @param $param array(min=>0,max=>10)
     * @param $message
     * @return this Util_Validate
     * @exception Exception 验证异常
     */
    function &stringLength($key,$param,$message = "")
    {
        if(!isset($this->validateArray[$key]) || !is_string($this->validateArray[$key]))
        {
            $this->message = $message;
            throw new Exception($key."验证错误:".$message);
        }
        if(isset($param["min"]) && strlen($this->validateArray[$key])<$param["min"])
        {
            $this->message = $message;
            throw new Exception($key."验证错误:".$message);
        }
         
        if(isset($param["max"]) && strlen($this->validateArray[$key])>$param["max"])
        {
            $this->message = $message;
            throw new Exception($key."验证错误:".$message);
        }
        return $this;
    }
     
    /**
     * 验证key的值是否在这个 $param 中
     * @param $key string
     * @param $param array
     * @param $message string
     * @return this Util_Validate
     * @exception Exception 验证异常
     */
    function &in($key,$param,$message = "")
    {
        if(!isset($this->validateArray[$key])||!in_array($this->validateArray[$key],$param))
        {
            $this->message = $message;
            throw new Exception($key."验证错误:".$message);
        }
        return $this;
    }

    /**
     * 验证QQ号码
     * @param $key
     * @param 错误信息
     * @return this Util_Validate
     * @exception Exception 验证异常
     */
    function &qq($key,$message="QQ号码错误")
    {
        if(!isset($this->validateArray[$key]))
        {
            $this->message = $message;
            throw new Exception($key."验证错误:".$message);
        }
        $uin = intval($this->validateArray[$key]);
        if($uin<10000 || $uin>1200000000)
        {
            $this->message = $message;
            throw new Exception($key."验证错误:".$message);
        }
        return $this;
    }

    /**
     * 验证是否合法url
     * @param $key
     * @param 错误信息
     * @return this Util_Validate
     * @exception Exception 验证异常
     */
    function &url($key,$message="url错误")
    {
        if(!isset($this->validateArray[$key]))
        {
            $this->message = $message;
            throw new Exception($key."验证错误:".$message);
        }
        $url = $this->validateArray[$key];

        if ($url == "qq.com" || $url == "soso.com" || $url == "paipai.com")
        {
            return $this;
        }
        
        $urlregex = "^((https?|ftp)\:\/\/)?";

        // USER AND PASS (optional)
        $urlregex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";
        
        // HOSTNAME OR IP 限定域名
        $urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*(\.qq\.com)|(\.soso\.com)|(\.paipai\.com)";  // http://x = allowed (ex. http://localhost, http://routerlogin)
        //$urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)+";  // http://x.x = minimum
        //$urlregex .= "([a-z0-9+\$_-]+\.)*[a-z0-9+\$_-]{2,3}";  // http://x.xx(x) = minimum
        //use only one of the above
        
        // PORT (optional)
        $urlregex .= "(\:[0-9]{2,5})?";
        // PATH  (optional)
        $urlregex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?";
        // GET Query (optional)
        $urlregex .= "(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?";
        // ANCHOR (optional)
        $urlregex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?\$";
        
        // check
        if (!eregi($urlregex, $url))
        {
            $this->message = $message;
            throw new Exception($key."验证错误:".$message);
        }

        /*
        if (!preg_match("/^((?:([^:\/?#.]+):)?(?:\/\/)?(?:([^:@]*):?([^:@]*)?@)?([^:\/?#]*)(?::(\d*))?)((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?/", $url))
        {
            $this->message = $message;
            throw new Exception($key."验证错误:".$message);
        }
        */
        return $this;
    }
    
    /**
     * 验证QQ群号码
     * @param $key
     * @param 错误信息
     * @return this Util_Validate
     * @exception Exception 验证异常
     */
    function &groups($key,$message="QQ群号码错误")
    {
        if(!isset($this->validateArray[$key]))
        {
            $this->message = $message;
            throw new Exception($key."验证错误:".$message);
        }

        $groupArray = explode(';',$this->validateArray[$key]);
        
        $newarray = array();
        if($groupArray)
        {
            foreach($groupArray as &$value)
            {
                $groupid = intval($value);
                if($groupid<10000 || $groupid>2000000000)
                {
                    $this->message = $message;
                    //throw new Exception($key."验证错误:".$message);
                }
                else
                {
                    array_push($newarray,$groupid);
                }
            }
        }
        
        $this->validateArray[$key] = implode(";",$newarray);
        
        return $this;
    }
     
     
    /**
     * 验证文件数上传
     * @param $file
     * @return true | false
     */
    static function uploadFile(&$file)
    {
        $fileSize = $file['size'];
        $tmpFile = $file['tmp_name'];
        if($fileSize<0 || $file['error']>0 || empty($tmpFile))
        {
            return false;
        }
        if(!is_uploaded_file($tmpFile))
        {
            return false;
        }
        return true;
    }

}