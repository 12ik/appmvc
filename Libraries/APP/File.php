<?php
class APP_File
{
    // 写文件
    public static function write($filename, $content, $mode='w')
    {
        if(!self::canWrite($filename)){
            throw new Exception("can not write into file : '$filename'");
        }
        if (!$fp = @fopen($filename, $mode)){
            throw new Exception("can not open file : '$filename'");
        }

        fwrite($fp, $content);
        fclose($fp);
    }

    // 读文件
    public static function read($filename, $format = 'STRING')
    {
        self::_checkReadException($filename);

        switch(strtoupper($format)){
            case 'ARRAY' :
                    $rev = str_replace("\n", "", file($filename));
                    break;

            case 'STRING' :
            default :
                    $rev = file_get_contents($filename);
                    break;
        }

        return $rev;
    }

    /**
     * 分割文件
     *
     * 返回二维数组，按指定的分隔符对文件的每一行进行
     *
     * @param  String  文件名
     * @param  String  每一行字段的分割符
     * @rev    Array
    */
    public static function explode($filename, $sept = "\t")
    {
        $lines = self::read($filename, 'ARRAY');

        foreach($lines as $line){
            $arr[] = explode($sept,trim($line, $sept));
        }

        return $arr;
    }

    // 文件可读
    public static function canRead($filename)
    {
        return is_readable($filename);
    }

    // 文件可写(仅仅是文件夹存在的前提下)
    public static function canWrite($filename)
    {
		$toTest = file_exists($filename) ? $filename : dirname($filename);
		return is_writable($toTest);
    }

    // 获取后缀名
    public static function getExtention($filename)
    {
        return substr_count(substr($filename,0,-1), ".") == 0 ? "" : substr(strrchr($filename, "."),1);
    }

    /**
     * 获取目录下文件列表
     *
     * 返回二维数组，按指定的分隔符对文件的每一行进行
     *
     * @param  String  目录名
     * @param  String  需要排除的文件
     * @rev    Array
    */
    public static function listFiles($dir, $ex_pattern='')
    {
        $data = self::_listFiles($dir, $ex_pattern);
		$rev = $data[1];
		sort($rev);
        return $rev;
    }

    // 获取目录列表
    public static function listDirs($dir, $ex_pattern='')
    {
        $data = self::_listFiles($dir, $ex_pattern);
		$rev = $data[2];
		sort($rev);
        return $rev;
    }

    // 获取全部列表
    public static function listAll($dir, $ex_pattern='')
	{
		$data = self::_listFiles($dir, $ex_pattern);
		return $data;
	}



    protected static function _listFiles($dir, $ex_pattern = '')
    {
        $matches = array(
					1=>array(),   //    获取文件列表
					2=>array(),   //    获取目录列表
					3=>array()    //    获取无权限列表
				);

        // 目录没有读权限
        if( !self::canRead($dir) ){
			//TODO tomsui 2009-5-20 21:34:47
//            if( $search_type == 0 ){
//
//             }
            $matches[3][] = $dir;
            return $matches;
        }

        // 保存目录
        $matches[2][] = $dir;

        $d = dir($dir);

        while (false !== ($file = $d->read())){
            if (($file == '.') || ($file == '..')
                    || ($ex_pattern && preg_match($ex_pattern, $file))){
                continue;
            }

            if (is_dir("{$dir}/{$file}")){
                $submatches = self::_listFiles("{$dir}/{$file}");
                $matches = array(
                        1 => array_merge($matches[1], $submatches[1]),
                        2 => array_merge($matches[2], $submatches[2]),
                        3 => array_merge($matches[3], $submatches[3])
                    );
            }
            else{
                $matches[1][] = "{$dir}/{$file}";
            }
        }

        return $matches;
    }

    protected static function _checkReadException($filename)
    {
        if( self::canRead($filename) === FALSE ){
            require_once 'APP/File/Exception.php';
            throw new APP_File_Exception("file cant not read  : '$filename' \n");
        }
    }

}

