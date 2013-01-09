<?php
/**
 * @category   APP
 * @package	APP_Controller
 * @subpackage Plugin
 * @version	$Id: ErrorHandler.php v1.0 2009-2-25 0:08:04 tomsui $
 */
class APP_Controller_Plugin_ErrorHandler extends APP_Controller_Plugin_Abstract
{
	/**
	 * Module to use for errors; defaults to default module in dispatcher
	 * @var string
	 */
	protected $_errorModule = 'default';

	/**
	 * Controller to use for errors; defaults to 'error'
	 * @var string
	 */
	protected $_errorController = 'error';

	/**
	 * Flag; are we already inside the error handler loop?
	 * @var bool
	 */
	protected $_isInsideErrorHandlerLoop = false;

	/**
	 * Exception count logged at first invocation of plugin
	 * @var int
	 */
	protected $_exceptionCountAtFirstEncounter = 0;

	/**
	 * Constructor
	 *
	 * Options may include:
	 * - module
	 * - controller
	 *
	 * @param  Array $options
	 * @return void
	 */
	public function __construct(Array $options = array())
	{
		$this->setErrorHandler($options);
	}

	/**
	 * setErrorHandler() - setup the error handling options
	 *
	 * @param  array $options
	 * @return APP_Controller_Plugin_ErrorHandler
	 */
	public function setErrorHandler(Array $options = array())
	{
		if (isset($options['module'])) {
			$this->_errorModule = (string) $options['module'];
		}
		if (isset($options['controller'])) {
			$this->_errorController = (string) $options['controller'];
		}
		return $this;
	}

	/**
	 * check for exceptions and dispatch error handler if necessary
	 *
	 * @param  APP_Controller_Request_Abstract $request
	 * @return void
	 */
	public function postDispatch($request)
	{
		$frontController = APP_Controller_Front::getInstance();

		if ($frontController->getParam('noErrorHandler')) {
			return;
		}

		$response = $this->getResponse();

		if ($this->_isInsideErrorHandlerLoop) {
			$exceptions = $response->getExceptions();
			if (count($exceptions) > $this->_exceptionCountAtFirstEncounter) {
				throw array_pop($exceptions);
			}
		}

		if (($response->isException()) && (!$this->_isInsideErrorHandlerLoop)) {
			$this->_isInsideErrorHandlerLoop = true;

			// Get exception information
			$error		  = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
			$exceptions	= $response->getExceptions();
			$exception	  = $exceptions[0];

			$exceptionType  = get_class($exception);
			$error->exception = $exception;
			switch ($exceptionType) {
				case 'APP_Controller_Exception':
					if (404 == $exception->getCode()) {
						$errorAction = 'Mvc';
					}
					break;
				default:
						$errorAction = 'User';
					break;
			}

			// Keep a copy of the original request
			$error->request = clone $request;

			// Get a count of the number of exceptions encountered
			$this->_exceptionCountAtFirstEncounter = count($exceptions);

			// Forward to the error handler
			$request->setParam('app_exceptions', $error);
			$request->setRoutes(array(
									'module' => $this->_errorModule,
									'controller' => $this->_errorController,
									'action' => $errorAction
									)
								);
			$frontController->forwardRequest($request, $response);
		}
	}
}

