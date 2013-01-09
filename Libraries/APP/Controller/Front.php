<?php
/** APP_Controller_Request_Http */
require_once(LIB_PATH.'/APP/Controller/Request/Http.php');

/** APP_Controller_Response_Http */
require_once(LIB_PATH.'/APP/Controller/Response/Http.php');

/** APP_Controller_Router_Simple */
require_once(LIB_PATH.'/APP/Controller/Router/Simple.php');

/** APP_Controller_Dispatcher_Standard */
require_once(LIB_PATH.'/APP/Controller/Dispatcher/Standard.php');

/** APP_Controller_Dispatcher_Action */
require_once(LIB_PATH.'/APP/Controller/Action.php');

/** APP_Controller_Action_Exception */
require_once(LIB_PATH.'/APP/Controller/Exception.php');

/** APP_Controller_Plugin_Abstract */
require_once(LIB_PATH.'/APP/Controller/Plugin/Abstract.php');

/** APP_Controller_Action_Plugin_Broker */
require_once(LIB_PATH.'/APP/Controller/Plugin/Broker.php');

/**
 * @category   APP
 * @package	APP_Controller
 * @copyright  Copyright (c) 1998 - 2009 Tencent. (http://www.qq.com)
 * @version	$Id: Front.php v1.0 2009-2-25 0:08:04 tomsui $
 */
class APP_Controller_Front
{
	/**
	 * Instance of APP_Controller_Request_Abstract
	 * @var APP_Controller_Request_Http
	 */
	protected $_request  = null;

	/**
	 * Instance of APP_Controller_Response_Abstract
	 * @var APP_Controller_Response_Http
	 */
	protected $_response = null;

	/**
	 * Instance of APP_Controller_Router_Simple
	 * @var APP_Controller_Router_Simple
	 */
	protected $_router = null;

	/**
	 * Instance of APP_Controller_Router_Simple
	 * @var APP_Controller_Router_Simple
	 */
	protected $_dispatcher = null;

	/**
	 * Instance of APP_Controller_Plugin_Broker
	 * @var APP_Controller_Plugin_Broker
	 */
	protected $_plugins = null;

	/**
	 * Array of invocation parameters to use when instantiating action
	 * controllers
	 * @var array
	 */
	protected $_invokeParams = array();

	/**
	 * Whether or not to return the response prior to rendering output while in
	 * {@link dispatch()}; default is to send headers and render output.
	 * @var boolean
	 */
	protected $_returnResponse = false;

	/**
	 * Singleton instance
	 *
	 * Marked only as protected to allow extension of the class. To extend,
	 * simply override {@link getInstance()}.
	 *
	 * @var APP_Controller_Front
	 */
	protected static $_instance = null;

	/**
	 * Constructor
	 *
	 * Instantiate using {@link getInstance()}; front controller is a singleton
	 * object.
	 *
	 * Instantiates the plugin broker.
	 *
	 * @return void
	 */
	private function __construct()
	{
		$this->_plugins = new APP_Controller_Plugin_Broker();

		$this->_request  = new APP_Controller_Request_Http();

		$this->_response = new APP_Controller_Response_Http();

		$this->_router = new APP_Controller_Router_Simple();

		$this->_dispatcher = new APP_Controller_Dispatcher_Standard();;
	}

	/**
	 * Enforce singleton; disallow cloning
	 *
	 * @return void
	 */
	private function __clone()
	{
	}

	/**
	 * Singleton instance
	 *
	 * @return APP_Controller_Front
	 */
	public static function getInstance()
	{
		if (null === self::$_instance)
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * dispath the request
	 *
	 * @param string|array $controllerDirectory Path to APP_Controller_Action
	 * controller classes or array of such paths
	 * @return void
	 * @throws APP_Controller_Exception if called from an object instance
	 */
	public static function run()
	{
		self::getInstance()->dispatch();
	}

	/**
	 * Return the request object.
	 *
	 * @return APP_Controller_Request_Http
	 */
	public function getRequest()
	{
		return $this->_request;
	}

	/**
	 * Return the response object.
	 *
	 * @return APP_Controller_Response_Http
	 */
	public function getResponse()
	{
		return $this->_response;
	}

	/**
	 * Return the router object.
	 *
	 * @return APP_Controller_Router_Simple
	 */
	public function getRouter()
	{
		return $this->_router;
	}

	/**
	 * Return the dispatcher object.
	 *
	 * @return APP_Controller_Dispatcher_Standard
	 */
	public function getDispatcher()
	{
		return $this->_dispatcher;
	}

	/**
	 * Add or modify a parameter to use when instantiating an action controller
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return APP_Controller_Front
	 */
	public function setParam(array $params)
	{
		$name = (string) $name;
		$this->_invokeParams[$name] = $value;
		return $this;
	}

	/**
	 * Set parameters to pass to action controller constructors
	 *
	 * @param array $params
	 * @return APP_Controller_Front
	 */
	public function setParams(array $params)
	{
		$this->_invokeParams = array_merge($this->_invokeParams, $params);
		return $this;
	}

	/**
	 * Retrieve a single parameter from the controller parameter stack
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getParam($name)
	{
		if (isset($this->_invokeParams[$name]))
		{
			return $this->_invokeParams[$name];
		}

		return null;
	}

	/**
	 * Retrieve action controller instantiation parameters
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->_invokeParams;
	}

	/**
	 * Clear the controller parameter stack
	 *
	 * By default, clears all parameters. If a parameter name is given, clears
	 * only that parameter; if an array of parameter names is provided, clears
	 * each.
	 *
	 * @param null|string|array single key or array of keys for params to clear
	 * @return APP_Controller_Front
	 */
	public function clearParams($name = null)
	{
		if (null === $name)
		{
			$this->_invokeParams = array();
		}
		elseif (is_string($name) && isset($this->_invokeParams[$name]))
		{
			unset($this->_invokeParams[$name]);
		}
		elseif (is_array($name))
		{
			foreach ($name as $key)
			{
				if (is_string($key) && isset($this->_invokeParams[$key]))
				{
					unset($this->_invokeParams[$key]);
				}
			}
		}

		return $this;
	}

	/**
	 * Register a plugin.
	 *
	 * @param  APP_Controller_Plugin_Abstract $plugin
	 * @param  int $stackIndex Optional; stack index for plugin
	 * @return APP_Controller_Front
	 */
	public function registerPlugin(APP_Controller_Plugin_Abstract $plugin, $stackIndex = null)
	{
		$this->_plugins->registerPlugin($plugin, $stackIndex);
		return $this;
	}

	/**
	 * Unregister a plugin.
	 *
	 * @param  string|APP_Controller_Plugin_Abstract $plugin Plugin class or object to unregister
	 * @return APP_Controller_Front
	 */
	public function unregisterPlugin($plugin)
	{
		$this->_plugins->unregisterPlugin($plugin);
		return $this;
	}

	/**
	 * Is a particular plugin registered?
	 *
	 * @param  string $class
	 * @return bool
	 */
	public function hasPlugin($class)
	{
		return $this->_plugins->hasPlugin($class);
	}

	/**
	 * Retrieve a plugin or plugins by class
	 *
	 * @param  string $class
	 * @return false|APP_Controller_Plugin_Abstract|array
	 */
	public function getPlugin($class)
	{
		return $this->_plugins->getPlugin($class);
	}

	/**
	 * Retrieve all plugins
	 *
	 * @return array
	 */
	public function getPlugins()
	{
		return $this->_plugins->getPlugins();
	}


	/**
	 * Set whether {@link dispatch()} should return the response without first
	 * rendering output. By default, output is rendered and dispatch() returns
	 * nothing.
	 *
	 * @param boolean $flag
	 * @return boolean|APP_Controller_Front Used as a setter, returns object; as a getter, returns boolean
	 */
	public function returnResponse($flag = null)
	{
		if (true === $flag)
		{
			$this->_returnResponse = true;
			return $this;
		}
		elseif (false === $flag)
		{
			$this->_returnResponse = false;
			return $this;
		}

		return $this->_returnResponse;
	}

	/**
	 * Dispatch an HTTP request according to the route rule.
	 *
	 * @return void|APP_Controller_Response_Abstract Returns response object if 
	 * returnResponse() is true
	 */
	public function dispatch()
	{
		$this->_plugins
			 ->setRequest($this->_request)
			 ->setResponse($this->_response);

		try
		{
			$this->_router->route($this->_request);
			$this->forwardRequest($this->_request, $this->_response);

		}catch (Exception $e){
			$this->_response->setException($e);
		}


		if ($this->returnResponse()){
			return $this->_response;
		}

		$this->_response->sendResponse();
	}

	/**
	 * Dispatch an HTTP request directly
	 *
	 * @return void
	 */
	public function forwardRequest($request, $response)
	{
		$this->_plugins->preDispatch($request);

		try {
			$this->_dispatcher->dispatch($request, $response);
		} catch (Exception $e){
			$this->_response->setException($e);
		}

		$this->_plugins->postDispatch($request);

		if($response->isException()) {
			require_once(LIB_PATH . '/APP/Controller/Error.php');
			APP_Controller_Error::HandleException($request, $response);
		}
	}
}
