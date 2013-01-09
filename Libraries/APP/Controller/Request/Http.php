<?php
/**
 * @category   APP
 * @package	APP_Controller
 * @subpackage Request
 * @version	$Id: Http.php v1.0 2009-2-25 0:08:04 tomsui $
 */
class APP_Controller_Request_Http
{
	/**
	 * REQUEST_URI
	 * @var string;
	 */
	protected $_requestUri;

	/**
	 * Base URL of request
	 * @var string
	 */
	protected $_baseUrl;

	/**
	 * Route infomation of request
	 * @var array
	 */
	protected $_routes = null;

	/**
	 * Default route infomation
	 * @var array
	 */
	protected $_defaultRoutes = array('module'=>'default','controller'=>'index','action'=>'index');


	/**
	 * Params of request
	 * @var array
	 */
	protected $_params = array(
		'USER'      => array(),
		'ACTION'    => array(),
		'PATH_INFO' => array(),
		'GET'       => array(),
		'POST'      => array(),
		'COOKIE'	=> array(),
		'URL_PARAM' => array()
	 );

	/**
	 * Allowed parameter sources
	 * @var array
	 */
	protected $_paramSources = array('USER', 'ACTION', 'PATH_INFO', 'GET', 'POST', 'COOKIE' );

	/**
	 * Constructor
	 *
	 * If a $uri is passed, the object will attempt to populate itself using
	 * that information.
	 *
	 * @param string $requestUri
	 * @return void
	 */
	public function __construct($requestUri='')
	{
		$this->_params['GET']   = $_GET;
		$this->_params['POST']   = $_POST;
		$this->_params['COOKIE'] = $_COOKIE;

//	  $this->setBaseUrl($requestUri);
		$this->setRequestUri($requestUri);
	}

	/**
	 * 获取默认路由
	 *
	 * If a $uri is passed, the object will attempt to populate itself using
	 * that information.
	 *
	 * @param string $requestUri
	 * @return void
	 */
	public function getDefaultRoutes()
	{
		return $this->_defaultRoutes;
	}
	/**
	 * Set the REQUEST_URI on which the instance operates
	 *
	 * If no request URI is passed, uses the value in $_SERVER['REQUEST_URI'],
	 *
	 * @param string $requestUri
	 * @return APP_Controller_Request_Http
	 */
	public function setRequestUri($requestUri=null, $direct = FALSE)
	{
		if($requestUri == null)
		{
//		  $this->setBaseUrl();

			// strip query string
			$requestUri = preg_replace('|\?.*$|', '', $_SERVER['REQUEST_URI']);
		}

		// ensure leading and trailing slash
		$this->_requestUri = preg_replace('|^/*(.*?)/*$|', "/\\1/", $requestUri) ;
		return $this;
	}

	/**
	 * 提取uri中的参数
	 *
	 * 提取uri中破折号形式的参数, 规范化requestUri
	 *
	 * @return APP_Controller_Request_Http
	 */
	protected function extractParams()
	{
		static $bad  = array('$',	 '(',	 ')',	 '%28',  '%29');
		static $good = array('&#36;', '&#40;', '&#41;', '&#40;','&#41;');

		foreach(explode("/", $this->_requestUri) as $val) {
			$val = str_replace($bad, $good, $val);

			if( strpos($val,'-') !== FALSE ) {
				list($val, $params) = explode('-', $val, 2);
				$params = explode('-', $params);
				$this->_params['URL']['_APP_URL'][] = $params;
			} else {
				$this->_params['URL']['_APP_URL'][] = null;
			}

			$segments[] = $val;
		}

		$this->_requestUri = '/' . implode('/', $segments) . '/' ;
		return $this;
	}

	/**
	 * Returns the REQUEST_URI
	 *
	 * @return string
	 */
	public function getRequestUri()
	{
		return $this->_requestUri;
	}

	/**
	 * 设置request路由
	 *
	 * 设置request路由. 如果没有指定相应路由数组, 采用默认路由.
	 *
	 * @param array|null $routes 
	 * @return APP_Controller_Request_Http
	 */
	public function setRoutes($routes = null)
	{
		if($routes == null) {
			$this->_routes = $this->_defaultRoutes;
		} else {
			$this->_routes = $routes;
		}
		return $this;
	}

	/**
	 * Get request's route infomation
	 *
	 * @param string $key  key must is one of 'module','controller','action'
	 * @return array
	 */
	public function getRoutes($key='')
	{
		if(isset($this->_routes[strtolower($key)])) {
			return $this->_routes[strtolower($key)];
		}
		return $this->_routes;
	}

	/**
	 * Set allowed parameter sources
	 *
	 * Can be empty array, or contain one or more of 'USER', 'ACTION', 'PATH_INFO', 'GET', 'POST' .
	 *
	 * @param  array $paramSoures
	 * @return APP_Controller_Request_Http
	 */
	public function setParamSources(array $paramSources = array())
	{
		$this->_paramSources = $paramSources;
		return $this;
	}

	/**
	 * 设置特定类型的一个参数
	 *
	 * 如果值为null, 那么删除这个参数
	 *
	 * @param  string $key
	 * @param  mixed  $value
	 * @param  mixed  $type   参数的五种类型之一, 默认是'USER'
	 * @return APP_Controller_Request_Http
	 */
	public function setParam($key, $value, $type='USER')
	{
		$key = (string) $key;

		if (null === $value) {
			if(isset($this->_params[$type][$key])) {
				unset($this->_params[$type][$key]);
			}
		} else {
			$this->_params[$type][$key] = $value;
		}

		return $this;
	}

	/**
	 * 设置特定类型的全部参数
	 *
	 * 对特定参数类型进行整体赋值(数组)
	 *
	 * @param  array $array
	 * @param  array $type   参数的五种类型之一, 默认是'USER'
	 * @return APP_Controller_Request_Http
	 */
	public function setParams(array $array, $type='USER')
	{
		$this->_params[$type] = $this->_params[$type] + (array) $array;

		foreach ($this->_params[$type] as $key => $value) {
			if (null === $value) {
				unset($this->_params[$type][$key]);
			}
		}

		return $this;
	}

	/**
	 * 清空全部参数
	 *
	 * 清空特定类型的全部参数
	 *
	 * @param  array $type   参数的五种类型之一, 默认是'USER'
	 * @return APP_Controller_Request_Http
	 */
	public function emptyParams($type)
	{
		$this->_params[$type] = array();
		return $this;
	}

	/**
	 * 获取指定一个参数值
	 *
	 * 如果没有指定type , 则在paramSources中依次按类型检索key
	 * 如果已经指定type , 则仅在指定的type类型中检索key
	 *
	 *
	 * @param string $key	 参数的key
	 * @param mixed  $default 没有找到指定key对应的键值时返回的默认值, 否则返回null
	 * @return mixed		  结果
	 */
	public function getParam($key, $type = null, $default = null)
	{
		if($type != null) {
			if (isset($this->_params[$type][$key])) {
				return $this->_params[$type][$key];
			}
		} else {
			foreach($this->_paramSources as $type) {
				if (in_array($type, $this->_paramSources) && (isset($this->_params[$type][$key]))) {
					return $this->_params[$type][$key];
				}
			}
		}

		return $default;
	}

	/**
	 * 获取某个类型的全部参数值
	 *
	 * 如果没有指定type , 返回paramSources指定的所有类型参数值
	 * 如果已经指定type , 则仅在指定的类型的全部参数值
	 *
	 * @param string $type  the specified kind of params to get if not empty
	 * @return array
	 */
	public function getParams($type = null)
	{
		if($type !== null) {
			return $this->_params[$type];
		}

		$type = $this->_paramSources;

		$rev = array();
		foreach($type as $type) {
			if (isset($this->_params[$type]) && is_array($this->_params[$type])) {
				$rev += $this->_params[$type];
			}
		}

		return $rev;
	}


	/**
	 * Return the method by which the request was made
	 *
	 * @return string
	 */
	public function getMethod()
	{
		return $this->getServer('REQUEST_METHOD');
	}

	/**
	 * Return the raw body of the request, if present
	 *
	 * @return string|false Raw body, or false if not present
	 */
	public function getRawBody()
	{
		$body = file_get_contents('php://input');

		if (strlen(trim($body)) > 0) {
			return $body;
		}

		return false;
	}

	/**
	 * Return the value of the given HTTP header. Pass the header name as the
	 * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
	 * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
	 *
	 * @param string $header HTTP header name
	 * @return string|false HTTP header value, or false if not found
	 * @throws APP_Controller_Request_Http
	 */
	public function getHeader($header)
	{
		if (empty($header)) {
			throw new APP_Exception('An HTTP header name is required');
		}

		$temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
		if (!empty($_SERVER[$temp])) { 
			return $_SERVER[$temp];
		}

		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			if (!empty($headers[$header])) {
				return $headers[$header];
			}
		}

		return false;
	}

//	/**
//	 * Is the request a Javascript XMLHttpRequest?
//	 *
//	 * Should work with Prototype/Script.aculo.us, possibly others.
//	 *
//	 * @return boolean
//	 */
//	public function isXmlHttpRequest()
//	{
//		return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
//	}
//
//	/**
//	 * Is this a Flash request?
//	 *
//	 * @return bool
//	 */
//	public function isFlashRequest()
//	{
//		return ($this->getHeader('USER_AGENT') == 'Shockwave Flash');
//	}
//
//	/**
//	 * Retrieve the module name
//	 *
//	 * @return string
//	 */
	public function getModuleName()
	{
		return $this->_routes['module'];
	}
//
//	/**
//	 * Retrieve the controller name
//	 *
//	 * @return string
//	 */
	public function getControllerName()
	{
		return $this->_routes['controller'];
	}
//
//	/**
//	 * Retrieve the action name
//	 *
//	 * @return string
//	 */
	public function getActionName()
	{
		return $this->_routes['action'];
	}
//
//	/**
//	 * Set the module name to use
//	 *
//	 * @param string $value
//	 * @return APP_Controller_Request_Http
//	 */
//	public function setModuleName($module)
//	{
//		$this->_routes['module'] = $module;
//		return $this;
//	}
//
//	/**
//	 * Set the controller name to use
//	 *
//	 * @param string $value
//	 * @return APP_Controller_Request_Http
//	 */
//	public function setControllerName($controller)
//	{
//		$this->_routes['controller'] = $controller;
//		return $this;
//	}
//
//	/**
//	 * Set the action name to use
//	 *
//	 * @param string $value
//	 * @return APP_Controller_Request_Http
//	 */
//	public function setActionName($action)
//	{
//		$this->_routes['action']  = $action;;
//		return $this;
//	}
//
//	/**
//	 * Set the base URL of the request; i.e., the segment leading to the script name
//	 *
//	 * E.g.:
//	 * - /admin
//	 * - /myapp
//	 * - /subdir/index.php
//	 *
//	 * @param string $baseUrl
//	 * @return APP_Controller_Request_Http
//	 */
//	public function setBaseUrl($base = '')
//	{
//		if (empty($base))
//		{
//			$uri  = explode('/',$_SERVER['REQUEST_URI']);
//			$self = explode('/',$_SERVER['PHP_SELF']);
//			for($base = '/',$i = 0; $uri[$i] == $self[$i] && $i < count($uri); $i ++)
//			{
//				$base .= $uri[$i];
//			}
//		}
//
//		$this->_baseUrl = '/' . trim($base, '/');
//
//		return $this;
//	}
//
//	/**
//	 * Everything in REQUEST_URI before PATH_INFO
//	 * <form action="<?$baseUrl?\>/news/submit" method="POST"/>
//	 *
//	 * @return string
//	 */
//	public function getBaseUrl()
//	{
//		if (null === $this->_baseUrl) {
//			$this->setBaseUrl();
//		}
//
//		return $this->_baseUrl;
//	}

}

