<?php
class APP_Charset_Check
{
    /**
     *  验证字符串指定字符集编码
     *
     *  对字符串进行全扫描，逐字节验证.
     *
	 *  @static
     *  @param   $string 要验证的字符串
     *  @return  boolean
     */
    public static function stringIs($string, $charset)
    {
        $regex = self::validateCharset($charset);
        return preg_match('%^(?:' . $regex . ')*$%xs', $string);
    }

    /**
     *  验证字符串存在指定编码字符
     *
     *  对字符串进行全扫描，逐字节验证.
     *
	 *  @static
     *  @param   $string 要验证的字符串
     *  @return  boolean
     */
    public static function stringHas($string, $charset)
    {
        $regex = self::validateCharset($charset);
        return preg_match('%(?:' . $regex . ')+%xs', $string);
    }

    /**
     *  获取字符串字符集编码
     *
     *  目前仅支持: UTF-8, GB18030, GBK, GB2312, ASCII 五种字符编码，如果字符串不是完
	 *  全由上述编码，则返回false
     *
	 *  @static
     *  @param   $string 要检查的字符串
     *  @return  $mix
     */
	public static function getStringCharset($string)
    {
		foreach(self::$charsetRegex as $charset => $regex)
		{
			if(self::stringIs($string, $charset))
			{
				return $charset;
			}
		}
		return false;
    }

    /**
     *  验证文件为指定字符集编码
     *
     *  对字符串进行全扫描，逐字节验证.
     *
	 *  @static
     *  @param   $string 要验证的字符串
     *  @return  boolean
     */
    public static function fileIs($file_name, $charset)
    {
		require_once(LIB_PATH . '/APP/Exception.php');
		$string = APP_File::f_read($file_name);

		return self::stringIs($string, $charset);
    }

    /**
     *  获取文件字符集编码
     *
     *  目前仅支持: UTF-8, GB18030, GBK, GB2312, ASCII 五种字符编码，如果文件不是完
	 *  全由上述编码，则返回false
     *
	 *  @static
     *  @param   $string 要检查的文件
     *  @return  $mix
     */
	public static function getFileCharset($file_name)
    {
		require_once(LIB_PATH . '/APP/Exception.php');
		$string = APP_File::f_read($file_name);

		return self::getStringCharset($string);
    }


    private static function validateCharset($charset)
    {
        if(!array_key_exists(strtoupper($charset), self::$charsetRegex)){
			require_once(LIB_PATH . '/APP/Exception.php');
            throw new APP_Exception('Does not surpport testing charset of ' . $charset );
        }

        $regex = self::$charsetRegex[strtoupper($charset)];
        return $regex;
    }

    private static $charsetRegex = array (
        'UTF-8' => ' [\x09\x0A\x0D\x20-\x7E]
                   | [\xC2-\xDF][\x80-\xBF]
                   |  \xE0[\xA0-\xBF][\x80-\xBF]
                   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}
                   |  \xED[\x80-\x9F][\x80-\xBF]
                   |  \xF0[\x90-\xBF][\x80-\xBF]{2}
                   | [\xF1-\xF3][\x80-\xBF]{3}
                   |  \xF4[\x80-\x8F][\x80-\xBF]{2}',
        'ASCII'  => '[\x09\x0A\x0D\x20-\x7E]',
        'GB2312' =>' [\x09\x0A\x0D\x20-\x7E]
                   | [\xA1-\xF7][\xA1-\xFE]',
        'GBK'   => ' [\x09\x0A\x0D\x20-\x7E]
                   | [\x81-\xFE][\x40-\xFE]',
        'GB18030'=>' [\x09\x0A\x0D\x20-\x7E]
                   | [\x09\x0A\x0D\x20-\x7E]
                   | [\x81-\xFE][\x40-\xFE]
                   | [\x81-\xFE][\x30-\x39][\x81-\xFE][\x30-\x39]'
    );
}

