<?php
/**
 * @category   APP
 * @package	APP_Controller
 * @version	$Id: Action.php v1.0 2009-2-25 0:08:04 tomsui $
 */
abstract class APP_Controller_Action
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
	 * constructor
	 *
	 * @param APP_Controller_Request_Http
	 * @param APP_Controller_Response_Http
	 * @return void
	 */
	final public function __construct($request,$response)
	{
		$this->_request = $request;
		$this->_response = $response;
		$this->init();
	}

	/**
	 * Initialize object
	 *
	 * Called from {@link __construct()} as final step of object instantiation.
	 *
	 * @return void
	 */
	public function init()
	{
	}

	/**
	 * Pre-dispatch routines
	 *
	 * Called before action method.
	 *
	 * @return void
	 */
	public function preDispatch()
	{
	}

	/**
	 * Post-dispatch routines
	 *
	 * Called after action method execution.
	 *
	 * Common usages for postDispatch() include rendering content in a sitewide
	 * template, link url correction, setting headers, etc.
	 *
	 * @return void
	 */
	public function postDispatch()
	{
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
	 * set the request object.
	 *
	 * @return APP_Controller_Action
	 */
	public function setRequest($request)
	{
		$this->_request = $request;
		return $this;
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
	 * set the response object.
	 *
	 * @return APP_Controller_Action
	 */
	public function setResponse($response)
	{
		$this->_response = $response;
		return $this;
	}

	/**
	 * Forward to another controller/action.
	 *
	 * It is important to supply the unformatted names, i.e. "article"
	 * rather than "ArticleController".  The dispatcher will do the
	 * appropriate formatting when the request is received.
	 *
	 * If only an action name is provided, forwards to that action in this
	 * controller.
	 *
	 * If an action and controller are specified, forwards to that action and
	 * controller in this module.
	 *
	 * Specifying an action, controller, and module is the most specific way to
	 * forward.
	 *
	 * A fourth argument, $params, will be used to set the request parameters.
	 * If either the controller or module are unnecessary for forwarding,
	 * simply pass null values for them before specifying the parameters.
	 *
	 * @param string $action
	 * @param string $controller
	 * @param string $module
	 * @param array $params
	 * @return void
	 */
	final protected function _forward($action, $controller = null, $module = null, array $params = null)
	{
		$request = $this->getRequest();
		$response = $this->getResponse();

		if (null !== $params) {
			$request->setParams($params);
		}

		$module = $module ? $module : 'default';
		$controller = $controller ? $controller : 'index';
		$routes = array(
			'module' => $module,
			'controller' => $controller,
			'action' => $action
		);


		$request->setRoutes($routes);
		APP_Controller_Dispatcher_Standard::dispatch($request, $response);
	}

	/**
	 * Redirect to another URL
	 *
	 * @param string $url
	 * @return void
	 */
	final protected function _redirect($url)
	{
		if (strpos($url,'://') === false) {
			$url = '/' . ltrim($url, '/');
		}
		$this->_response->setRedirect($url);
		$this->_response->sendHeaders();
		exit;
	}

	/**
	 * Set allowed parameter sources from the http request
	 *
	 * Can be empty array, or contain one or more of 'USER', 'CONTROLLER', 'ACTION', 'PATH_INFO', 'GET', 'POST' .
	 *
	 * @param  array $paramSoures
	 * @return APP_Controller_Request_Http
	 */
	public function setParamSources(array $paramSources = array())
	{
		$this->_request->setParamSources($paramSources);
		return $this;
	}

	/**
	 * Gets a parameter from the {@link $_request Request object}.  If the
	 * parameter does not exist, NULL will be returned.
	 *
	 * 查询参数值
	 *
	 * 如果没有指定type , 则在paramSources中依次按类型检索key
	 * 如果已经指定type , 则仅在指定的type类型中检索key
	 *
	 * If the parameter does not exist and $default is set, then
	 * $default will be returned instead of NULL.
	 *
	 * @param string $paramName
	 * @param mixed $default
	 * @return mixed
	 */
	protected function getParam($key, $type = null , $default = null)
	{
		return $this->_request->getParam($key, $type, $default);
	}

	/**
	 * Return all parameters in the {@link $_request Request object}
	 * as an associative array.
	 *
	 * @return array
	 */
	public function getParams($types=null)
	{
		return $this->_request->getParams($types);
	}

	/**
	 * 获取以列表形式返回action参数
	 *
	 * @return array
	 */
	public function getActionParamList()
	{
		$params = $this->getRequest()->getParam('_APP_ACTION', 'URL_PARAM');
		return $params;
	}

	/**
	 * 获取以列表形式返回action参数
	 *
	 * @return array
	 */
	public function getPathInfoList()
	{
		$params = $this->getRequest()->getParam('_APP_PATH_INFO','URL_PARAM');
		return $params;
	}

	/**
	 * Return request base url
	 *
	 * @param  boolean  true returns the host-name as prefix
	 * @return string
	 */
	 /*
	public function getBaseURL($flag = true)
	{
		return ( $flag ? 'http://'. $_SERVER['HTTP_HOST'] : "" )
			. $this->_request->getBaseURL();
	}
	*/

	/**
	 * Helper method to generate a Smarty object
	 *
	 * @return Smarty object
	 */
	public static function loadTpl()
	{
		require_once(LIB_PATH . '/APP/Registry.php');

		if(!APP_Registry::isRegistered('_APP_TEMPLATE')) {
			$_CONFIG = APP::loadConfig();
			require_once(LIB_PATH .'/Smarty/Smarty.class.php');

			$tpl = new Smarty($_CONFIG['tpl']);
			foreach($_CONFIG['tpl'] as $k => $val) {
				$tpl->$k = $val;
			}
			APP_Registry::set('_APP_TEMPLATE',$tpl);
			return $tpl;
		}

		return APP_Registry::get('_APP_TEMPLATE');
	}

	/**
	 * Helper method to generate a PDO object
	 *
	 * @param string $dbTag the db tag defined in the config file
	 * @return APP_Db_Adapter_Mysql
	 */
	public static function loadDb($dbTag)
	{
		require_once(LIB_PATH . '/APP/Model.php');
		return APP_Model::loadDb($dbTag);
	}

	/**
	 * 生成一个APP_Db_Table_Abstract的辅助函数
	 *
	 * @param string $tableTag
	 *  eg. act.tbl_user . 'act' is dbTag, 'tbl_user' is the table's name
	 *  by default , this lookup the Model directory to load the specified
	 *  APP_Db_Table_Abstract class, if not exists , will return a APP_Db_Table
	 *  object for use.
	 * @return APP_Db_Table_Abstract
	 */
	public function loadTable($tableTag)
	{
		require_once(LIB_PATH . '/APP/Model.php');
		return APP_Model::loadTable($tableTag);
	}

	/**
	 * Helper method to generate a APP_Db_Table_Abstract object
	 *
	 * @param string $model  Model的真实类名, 例如TestModel， 或Test_TestModel
	 * @return APP_Model
	 */
	public static function loadModel($modelClass)
	{
		require_once(LIB_PATH . '/APP/Registry.php');
		if (!APP_Registry::isRegistered($modelClass))
		{
			$model_file = preg_replace(array('/Model$/', '/_/'), array('', '/'), $modelClass) . '.php';
			$model_file = APP_PATH . '/Model/' . strtolower($model_file);
			if (!file_exists($model_file)){
				throw new APP_Controller_Exception("Model <b>$modelClass</b> not exists : $model_file"); 
			}

			require_once($model_file); 
			$model = new $modelClass;

			APP_Registry::set($modelClass,$model);
			return $model;
		}

		return APP_Registry::get($modelClass);
	}
}

