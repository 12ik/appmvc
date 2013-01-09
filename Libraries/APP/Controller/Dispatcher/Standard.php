<?php
/**
 * @category   APP
 * @package    APP_Controller
 * @subpackage Dispatcher
 * @version $Id: Standard.php v1.0 2009-2-25 0:08:04 tomsui $
 */
class APP_Controller_Dispatcher_Standard
{
	/**
	 * Dispatch the request
	 *
	 * Dispatch the request according to its route infomation.
	 *
	 * @param APP_Controller_Request_Http
	 * @param APP_Controller_Response_Http
	 * @return void
	 * @throws APP_Controller_Exception with invalid MVC path
	 */
	public static function dispatch($request,$response)
	{
		$controller = self::load_controller($request, $response);

		$action = $controller->getRequest()->getRoutes('action');

		$action .=  'Action';
//	  $action = ucfirst( strtolower($action) ) . 'Action';
		try {
			$controller->preDispatch();

			if (method_exists($controller , $action)) {
				$controller->$action();

				/*
				// 校验urlAction实参个数
				$params = $request->getParam('_APP_ACTION', 'URL_PARAM');
				$reflect = new ReflectionMethod($controller, $action);
				
				if($action != 'MvcAction') {
					if(count($params) < $reflect->getNumberOfRequiredParameters()
						|| count($params) > $reflect->getNumberOfParameters()) {
						self::throwMvcException(get_class($controller) .'::' .$action .' Action param\' num not match!');
					}
				}

				call_user_func_array(array($controller ,$action), $params);
				*/

			} else {
				self::throwMvcException(get_class($controller) .'::' .$action . ' Action not exists!');
			}

			$controller->postDispatch();
		} catch(Exception $e){
			$response->setException($e);
		}
	}

	/**
	 * Load the controller
	 *
	 * Load the controller class file , return the controller's object
	 *
	 * @param APP_Controller_Request_Http
	 * @param APP_Controller_Response_Http
	 * @return APP_Controller_Action
	 * @throws APP_Controller_Exception with invalid MVC path
	 */
	protected static function load_controller($request, $response)
	{
		$rsegments = $request->getRoutes();

		$module = strtolower($rsegments['module']);
		$controller = strtolower($rsegments['controller']);

		if ($module == 'default') {
			$path  = APP_PATH . "/Controller/${controller}.php";
			$class = ucfirst($controller)."Controller";
		} else {
			$path = APP_PATH . "/Controller/$module/${controller}.php";
			$class = ucfirst($module) . '_' . ucfirst($controller) . 'Controller';
		}
		
		if (!file_exists($path)) {
			self::throwMvcException('<b>Controller not exists</b> : ' . $path );
		} else {
			require_once($path);
		}

		return new $class($request, $response);
	}

	protected static function throwMvcException($msg)
	{
		require_once('APP/Controller/Exception.php');
		throw new APP_Controller_Exception($msg, 404);
	}
}

