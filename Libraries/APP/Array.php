<?php
class APP_Array
{
	/**
     *  将一维关联数组连接为字符串
	 *
	 *  $glue = '&'
	 *  $array = array('b'=>20, 'c'=>30);
	 *  return : b=20&c=30 
	 */
	public static function implodeAssoc($glue, $array)
	{
		$rev = '';
		if( is_array($array) && !empty($array) )
		{
			$tmp = array();
			foreach($array as $k => $v)
			{
				$tmp[] = $k . '=' . $v;
			}

			$rev = implode($glue, $tmp);
		}

		return $rev;
	}

	/**
     * 将数组转换为字符串
	 *
	 * 多用于数组缓存为文件形式
	 *  
	 * @param  Array    需要字面化的数组
	 * @param  Interger 前置几组空格
	 * @return String   php数组的字面化形式
	 */
    public static function evalToString($array, $level = 0)
    {
        $space = '';
        for ($i = 0; $i <= $level; $i++)
            $space .= "    ";

        $rev = "array (\n";
        $comma = $space;
        foreach($array as $key => $val) {
            $key = is_string($key) ? '\''.addcslashes($key, '\'\\').'\'' : $key;
			if(!is_array($val) && (!preg_match("/^\-?[1-9]\d*$/", $val) || strlen($val) > 12) ){
				$val = '\''.addcslashes($val, '\'\\').'\'';
			}

            if(is_array($val)) {
                $rev .= "$comma$key => ".self::evalToString($val, $level + 1);
            } else {
                $rev .= "$comma$key => $val";
            }
            $comma = ",\n$space";
        }
        $rev .= "\n$space)";
        return $rev;
    }
}

