<?php
define('MDS_DEV', 1);
define('APP_DEV', 1);
/*------------------------------------------------------------------------------
    路径常量
------------------------------------------------------------------------------*/
if (defined(MDS_DEV) && MDS_DEV == 1) {
	error_reporting(0);
	ini_set("display_errors", 0);
} else {
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
}
define('BASE_PATH'  , dirname(realpath(dirname(__FILE__))));
define('APP_PATH'   , BASE_PATH .'/Application');
define('CACHE_PATH' , BASE_PATH .'/Resources/Cache');
define('SELF'       , pathinfo(__FILE__, PATHINFO_BASENAME));
define('LIB_PATH'   , BASE_PATH .'/Libraries');
define('WWW', 'D:\wamp\www');

// Set include_path
$include_path[] = '.' ;
$include_path[] = LIB_PATH ;
$include_path[] = LIB_PATH . '/Smarty';
$include_path[] = LIB_PATH . '/APP';
$include_path[] = LIB_PATH . '/php-ofc-library';
$include_path[] = APP_PATH . '/Model';
$include_path[] = get_include_path();
set_include_path(implode(PATH_SEPARATOR, $include_path));


/*------------------------------------------------------------------------------
    全站禁缓存
------------------------------------------------------------------------------*/
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


require_once(LIB_PATH.'/APP/Loader.php');
APP_Loader::Autoload(true);
date_default_timezone_set('Asia/Shanghai');

/*------------------------------------------------------------------------------
    频道登录
------------------------------------------------------------------------------*/
require('APP.php');
$config = APP::LoadConfig();
/*------------------------------------------------------------------------------
    MVC 分发
------------------------------------------------------------------------------*/
require(BASE_PATH.'/Configs/site.consts.php');
$front = APP_Controller_Front::getInstance();
$front->dispatch();
/***********************************************************************************
 公用函数
************************************************************************************/
function curl_file_get_contents($durl){
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $durl);
   curl_setopt($ch, CURLOPT_TIMEOUT, 5);
   curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);
   curl_setopt($ch, CURLOPT_REFERER,_REFERER_);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $r = curl_exec($ch);
   curl_close($ch);
   return $r;
 }
 
function getPageList($sqlcount,$sql,$pageName)
	{
		$db = APP_Model::loadDb('code');
		session_start();
		$pagesize=3;

		$totalrows=$db->fetchOne($sqlcount);
		$totalpages=ceil($totalrows/$pagesize);
		if($pageName=="firstPage")
		{
			$_SESSION['page']=1;
		}
		if($pageName=="previousPage")
		{
			if($_SESSION['page']>1)
				$_SESSION['page']=$_SESSION['page']-1;
			else 
				$_SESSION['page']=1;
		} 
		if($pageName=="nextPage")
		{
			if($_SESSION['page']<$totalpages)
				$_SESSION['page']=$_SESSION['page']+1;
			else
				$_SESSION['page']=$totalpages;
		}
		if($pageName=="lastPage")
		{
			$_SESSION['page']=$totalpages;
		}
		$page=$_SESSION['page']-1;
		$rand=$page*$pagesize;
		
		$sqlres=$sql." limit $rand,$pagesize";
		return $db->fetchAll($sqlres);
	}
