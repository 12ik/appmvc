<?php
class Cache_Eaccelerator implements Cache_Interface
{
   public function add($key,$var,$expire=600)
   {
       //return eaccelerator_put($key, $var, $expire);
   }
   
   public function delete($key)
   {
        //return eaccelerator_rm($key);
   }
   
   public function get($key)
   {
       return false;
//       $result = eaccelerator_get($key);
//       if($result==NULL)
//       {
//           return false;
//       }
//       return $result;
   }
   
   function replace($key,$var,$expire=600)
   {
       $this->add($key,$ver,$expire);
   }
   
   function set($key,$var,$expire=600)
   {
       $this->add($key,$ver,$expire);
   }
}