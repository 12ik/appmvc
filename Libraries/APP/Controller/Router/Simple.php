<?php
/**
 * @category   APP
 * @package APP_Controller
 * @subpackage Router
 * @version $Id: Simple.php v1.0 2009-2-25 0:08:04 tomsui $
 */
class APP_Controller_Router_Simple
{
	/**
	 * route items load from the route config file
	 * @var array
	 */
	protected $routes  = array();

	protected $req  = null;

	/**
	 * 构造器
	 *
	 * 从配置文件中载入路由数组
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->routes = self::loadRoutes();
	}

	/**
	 * route the request
	 *
	 * route the request accord to the route'rules, first generate route segments
	 * from the req's requestUri and set the request's route array , then inject
	 * the params in the request uri back into the Req.
	 *
	 * @return APP_Controller_Request_Http
	 */
	public function route($request)
	{
		// 设置req
		$this->setRequest($request);

		// 路由uri
		if($this->routes) {
			$this->lookupRoutes();
		}

		// 提取路由数组
		$this->extractRoutes();

		// 执行清理动作
		$this->unsetRequest($request);
	}


	/**
	 * 查询路由
	 *
	 * 查询路由, 根据配置的路由条目来修改request对象的requestUri
	 *
	 * @return String  原始请求的uri
	 * @return String  路由之后的uri
	 */
	protected function lookupRoutes()
	{
		// 存在路由标志
		$bFlag = FALSE; 

		// 原始请求的uri
		$uri = $this->request->getRequestUri();

		// 静态路由转换
		if (isset($this->routes['static'][$uri])) {
			$uri = $this->routes['static'][$uri];
			$bFlag = TRUE;
		}
		// 动态路由转换
		elseif(isset($this->routes['dynamic']) )
		{
			foreach ($this->routes['dynamic'] as $key => $val) {
				$uri = rtrim($uri, "/");
				if (preg_match('|^'.$key.'$|', $uri)) {
					if (strpos($val, '$') !== FALSE 
						&& strpos($key, '(') !== FALSE) {
						$val = preg_replace('|^'.$key.'$|', $val, $uri);
					}
					$uri = $val;
					$bFlag = TRUE;
					break;
				}
			}
		}
		
		// 路由之后的uri
 		if($bFlag === TRUE) {
			$this->request->setRequestUri($uri);
		}
	}

	/**
	 * 提取路由
	 *
	 * 提取路由, 根据调整后的uri字符串设置req的路由字段信息
	 *
	 * @return String  路由后的uri
	 * @return Array   通过uri得到得路由数组信息.
	 */
	protected function extractRoutes()
	{
		$uri = trim($this->request->getRequestUri(), '/');

		/**
		 * 1. requestUri为空情况, 直接设置默认路由数组返回
		 */
		if($uri == '') {
			$this->request->setRoutes();
			return;
		}

		/**
		 * 2. requestUri不为空, 则对从字符串其中提取路由数组, ACTION 和 PATH_INFO参数 
		 */
		static $bad  = array('$',	 '(',	 ')',	 '%28',  '%29');
		static $good = array('&#36;', '&#40;', '&#41;', '&#40;','&#41;');
		foreach(explode("/", $uri) as $val) {
			$segments[] = str_replace($bad, $good, $val);
		}

		// 默认模块
		if (!is_dir(APP_PATH.'/Controller/'.$segments[0].'/')) {
			array_unshift($segments,'default');
		}

		// 2.1.1 获取路由数组
		$rsegments = array_slice($segments, 0, 3);

		// 2.1.2 获取ACTION 参数
		if(isset($rsegments[2]) && strpos($rsegments[2],'-') !== FALSE) {
			list($rsegments[2], $params) = explode('-', $rsegments[2], 2);
			$action_params = explode('-', $params);
		} else {
			$action_params = array();
		}

		// 2.1.3 获取PATH_INFO 参数
		$path_info = array_slice($segments, 3);


		// 2.2.1 设置request的路由数组
		$defaults = $this->request->getDefaultRoutes();
		$rsegments = $rsegments + array_values($defaults);
		$routes = array_combine(array_keys($defaults), $rsegments);
		$this->request->setRoutes($routes);

		// 2.2.2 保存ACTION参数
		$this->saveURLParams($action_params, 'ACTION');

		// 2.1.3 保存PATH_INFO 参数
		$this->saveURLParams($path_info,  'PATH_INFO');
	}

	protected function saveURLParams($params, $type)
	{
		$tmp = array();
		for($i=0;$i < count($params); $i++) {
			$tmp[$params[$i]] = isset($params[++$i])? $params[$i] : "" ;
		}

		$this->request->setParams($tmp, $type);
		
		$this->request->setParam('_APP_'.$type, $params, 'URL_PARAM');
	}

	/**
	 * 在配置文件中读入路由条目
	 *
	 * @return array
	 */
	static protected function loadRoutes()
	{
		@include(BASE_PATH.'/Configs/site.routes.php');

		if( !empty($_ROUTE)) {
			return $_ROUTE;
		} else {
			return null;
		}
	}

	public function setRequest($request)
	{
		$this->request = $request;
		return $this;
	}

	public function unsetRequest()
	{
		$this->request = null;
		return $this;
	}

}

