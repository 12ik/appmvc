<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty truncate modifier plugin
 * @author   ericcao
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @param boolean
 * @return string
 */

function smarty_modifier_cntruncate($string, $strlen = 10, $etc = '...',$charset = 'gbk', $keep_first_style = false)
{
     $strlen = $strlen*2;
      if(strlen($string) <= $strlen)
          return $string;
      $p = 0;
      for($i=0; $i<$strlen; $i++)
      {
          if(ord($string[$i])<128)
          {
              $p++;
          }else
          {
              $p += 2;
              $i++;
          }
      }
      return substr($string,0,$p).$etc;
//    iconv_set_encoding("internal_encoding", $charset);
//    if(iconv_strlen($string) <= $strlen)
//    {
//        return $string;
//    }
//    return iconv_substr($string,0,$strlen,$charset).$etc;
}
/* vim: set expandtab: */

?>
