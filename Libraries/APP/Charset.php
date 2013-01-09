<?php

class APP_Charset
{

}

/*
	$file = mb_convert_encoding(file_get_contents($remote . "qqstarold/" . $f), "utf-8", "gb2312");


	function convert_xml_file_charset($src_file, $dest_file)
	{
		$cmd = "iconv -f gbk -t utf-8  -o $dest_file -c $src_file";
		exec($cmd);
		$content = file_get_contents($dest_file);
		$content = str_replace('encoding="GB2312"', 'encoding="UTF-8"', $content);
		f_write($dest_file, $content, 'w');
	}
*/


	// 转换string字符集
	function convert($str, $src_charset = 'utf-8', $dest_charset = 'gbk//TRANSLIT' )
	{
		if($src_charset == $dest_charset)
		{
			return $str;
		}

		return iconv($src_charset, $dest_charset, $str);
	}

	// 转换file字符集
	function convert_file($src_file, $dest_file , $src_charset = 'utf-8', $dest_charset = 'gbk' )
	{
		$cmd = "iconv -f $src_charset -t $dest_charset  -o $dest_file -c $src_file";
		exec($cmd);
	}

	// 转换xml字符集
	function convert_xml($src_file, $dest_file , $src_charset = 'utf-8', $dest_charset = 'gbk' )
	{
		convert_file($src_file, $dest_file , $src_charset , $dest_charset )
		$content = file_get_contents($dest_file);
		$content = str_ireplace('encoding="GB2312"', 'encoding="UTF-8"', $content);
		f_write($dest_file, $content, 'w');
	}
