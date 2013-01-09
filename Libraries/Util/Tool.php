<?php
/**
 * 工具
 * @author Ericcao
 *
 */
class Util_Tool
{
    static function microtime()
    {
       list($usec, $sec) = explode(" ", microtime());
       return ((float)$usec + (float)$sec);
    }
    
    static function jsonEncode(&$str)
    {
        return str_replace(array("\"","\\","\n","\r"),array("\\\"","\\\\","\\n","\\r"),htmlspecialchars($str));
    }
    
	static function iso7064($vString)
	{
		$wi = array(1, 2, 4, 8, 5, 10, 9, 7, 3, 6);
		$hash_map = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
		
		$i_size = strlen($vString);
		$bModify = '?' == substr($vString, -1);
		
		$i_size1 = $bModify ? $i_size : $i_size + 1;
		for ($i = 1; $i <= $i_size; $i++) {
		$i1 = $vString[$i - 1] * 1;
		$w1 = $wi[($i_size1 - $i) % 10];
		$sigma += ($i1 * $w1) % 11;
		}
		if($bModify) return str_replace('?', $hash_map[($sigma % 11)], $vString);
		else return $hash_map[($sigma % 11)];
	}
	
	public static function getRealIP() {
        if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $user_ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            $user_ip=$_SERVER["REMOTE_ADDR"];
        }
        return $user_ip;
    }
}