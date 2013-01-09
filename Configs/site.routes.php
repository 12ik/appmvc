<?
/**
 *
 * 路由
 *
 * 分两类   : 静态路由和动态路由.
 *
 * 优先级   : a. 静态路由优先级高于动态路由;
 *           b. 同类路由中，先定义的路由条目优先级高
 *
 * 定义方法 : $_ROUTE['static'][$key] = $value
             $key  代表原始请求的uri (静态路由是一个字符串，动态路由是一个正则表达式)
			 $value代表期望转向的uri (对于两种路由都是目的字符串字符串)
 *			 
 *
 * 静态路由 : $_ROUTE['static'][$key] = $value
 *            只有当request_uri 字符串等于 $key 时，才会将请求导向$value，例：
 *            $_ROUTE['static']['/vote/index/']   = '/v/test/';
 *            访问 http://test.com/vote/index,
 *            那么 request_uri 为 '/vote/index/'(末尾的反斜线会自动补全)
 *            于是 自动转向： http://test.com/v/test/
 *
 *           注意: 配置静态路由条目时,requestUri的前后反斜线必须存在,否则无法正常匹配;
 *                反斜线是自动补全的是仅针对用户请求的uri.
 *
 * 动态路由： $_ROUTE['static'][$key] = $value
 *
 *
 *
 *
 * 注意： 路由仅支持一级。相当于apache rewrite中的[L]
 *
 */




/*******************************************************************************
1. 定义静态路由
*******************************************************************************/
//$_ROUTE['static']['/vote/index/']   = '/test/tpl/';
//$_ROUTE['static']['/index/index/']   = '/index/show1/';


/*******************************************************************************
2. 定义动态路由
*******************************************************************************/
/*
$_ROUTE['dynamic']['/([0-9]+)/']   = '/demo/index/num/$1';
	如果访问： http://club.webdev.com/2000
	真实导向： http://club.webdev.com/demo/index/num/10
*/
//$_ROUTE['dynamic']['/game/list/([0-9]+)-(.+)-(.+).html'] = '/index/list/cat_id/$1/order/$2/Page/$3';
//$_ROUTE['dynamic']['/game/play/([0-9]*).html'] = '/index/play/id/$1';
