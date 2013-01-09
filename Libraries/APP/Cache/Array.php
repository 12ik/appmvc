<?php if ( !defined('CACHE_PATH')) exit('CONSTANT\'CACHE_PATH\' is not defined , exit!');
/**
 * @category   APP
 * @package	APP_Cache
 * @version	$Id: Array.php v1.0 2009-2-25 0:08:04 tomsui $
 */
Class APP_Cache_Array
{
	/**
	 * Convert php array to literal string
	 *
	 * @param  array   $cachedata   array to be converted
	 * @param  int	 $level	   the deepest layer of the target array
	 * @return string  $cachedata
	 * @throws APP_Cache_Exception
	 */

	public static function arrayeval($array, $level = 0)
	{
		$space = '';
		for ($i = 0; $i <= $level; $i++) {
			$space .= "\t";
		}
		$evaluate = "Array\n$space(\n";
		$comma = $space;
		foreach($array as $key => $val) {
			$key = is_string($key) ? '\''.addcslashes($key, '\'\\').'\'' : $key;
			$val = !is_array($val) && (!preg_match("/^\-?[1-9]\d*$/", $val) || strlen($val) > 12) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
			if (is_array($val)) {
				$evaluate .= "$comma$key => ".self::arrayeval($val, $level + 1);
			} else {
				$evaluate .= "$comma$key => $val";
			}
			$comma = ",\n$space";
		}
		$evaluate .= "\n$space)";
		return $evaluate;
	}

	/**
	 * 将数组写入文件来进行缓存
	 *
	 * @param string  $file_full_path  目标cache文件的完整路径
	 * @param array   $cachedata	   将要缓存的数组
	 * @throws APP_Cache_Exception
	 */
	public static function writeCache($file_full_path, $cachedata)
	{
//		self::_validateCacheDir($file_full_path);

		if (is_array($cachedata)) {
			$cachedata = "\$CACHE_ARRAY = ".self::arrayeval($cachedata);
		}

		$filename  = basename($file_full_path);
		$dir = dirname($file_full_path);

		if (!is_dir($dir)) {
			@exec('mkdir -p '. $dir);
		}

		if (@$fp = fopen("$file_full_path", 'w')) {
				fwrite($fp, "<?php\n//Cache file, DO NOT modify me!\n".
					"//Created on ".date("M j, Y, G:i")."\n\n$cachedata\n;?>");
				fclose($fp);
		} else {
			require_once('APP/Cache/Exception.php');
			throw new APP_Cache_Exception("缓存目录写入失败: $dir ");
		}
	}

	/**
	 * include缓存文件
	 *
	 * @param string   目标cache文件的完整路径
	 * @param boolean  缓存文件是否载入成功
	 * @throws APP_Cache_Exception
	 */
	/*
	public static function loadCache($file_full_path)
	{
		self::_validateCacheDir($file_full_path);
		if (file_exists($file_full_path)){
			require_once($file_full_path);
			return true;
		}

		return false;
	}*/


	/**
	 * clear the cache file
	 *
	 * @param string  $file_full_path  目标cache文件的完整路径,可以是文件/目录名
	 * @throws APP_Cache_Exception
	 */
	public static function clearCache($file_full_path)
	{
//		self::_validateCacheDir($file_full_path);

		if(file_exists($file_full_path)) {
			@unlink($file_full_path);
		}elseif(is_dir($file_full_path)){
			@exec('rm -fr '. $file_full_path);
		}
	}

	/**
	 * 验证删除的缓存文件是否在安全范围内
	 *
	 * @param string  $file_full_path  目标cache文件的完整路径,可以是文件/目录名
	 * @throws APP_Cache_Exception
	 */

//	private static function _validateCacheDir($file_full_path)
//	{
//		if(strpos($file_full_path,CACHE_PATH) < 0){
//			include_once('APP/Cache/Exception.php');
//			throw new APP_Cache_Exception(
//						"目标cache目录$file_full_path不在"
//						.CACHE_PATH."之内,存在严重安全隐患!"
//					);
//		}
//	}
}

