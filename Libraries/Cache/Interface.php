<?php
/**
 * 规范cache接口,方便替换cache,具体缓存调用依次接口为准
 * @author Ericcao
 *
 */
interface Cache_Interface
{
   function add($key,$var,$expire=600);
   function delete($key);
   function get($key);
   function replace($key,$var,$expire=600);
   function set($key,$var,$expire=600);
}
?>