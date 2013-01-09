<?php
/**
 * @category   APP
 * @package    APP_Controller
 * @subpackage Plugin
 * @version    $Id: Abstract.php v1.0 2009-2-25 0:08:04 tomsui $
 */
abstract class APP_Controller_Plugin_Abstract
{
    /**
     * @var APP_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * @var APP_Controller_Response_Abstract
     */
    protected $_response;

    /**
     * Get request object
     *
     * @return APP_Controller_Request_Http $request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Get response object
     *
     * @return APP_Controller_Response_Http $response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Set request object
     *
     * @param APP_Controller_Request_Http $request
     * @return APP_Controller_Plugin_Abstract
     */
    public function setRequest($request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Set response object
     *
     * @param APP_Controller_Response_Http $response
     * @return APP_Controller_Plugin_Abstract
     */
    public function setResponse($response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Called before APP_Controller_Front begins evaluating the
     * request against its routes.
     *
     * @param APP_Controller_Request_Abstract $request
     * @return void
     */
//    public function routeStartup($request)
//    {
//    }

    /**
     * Called after APP_Controller_Router exits.
     *
     * Called after APP_Controller_Front exits from the router.
     *
     * @param  APP_Controller_Request_Abstract $request
     * @return void
     */
//    public function routeShutdown($request)
//    {
//    }

    /**
     * Called before APP_Controller_Front enters its dispatch loop.
     *
     * @param  APP_Controller_Request_Abstract $request
     * @return void
     */
//    public function dispatchLoopStartup($request)
//    {
//    }

    /**
     * Called before an action is dispatched by APP_Controller_Dispatcher.
     *
     * This callback allows for proxy or filter behavior.
     *
     * @param  APP_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch($request)
    {
    }

    /**
     * Called after an action is dispatched by APP_Controller_Dispatcher.
     *
     * This callback allows for proxy or filter behavior.
     *
     * @param  APP_Controller_Request_Abstract $request
     * @return void
     */
    public function postDispatch($request)
    {
    }

    /**
     * Called before APP_Controller_Front exits its dispatch loop.
     *
     * @return void
     */
//    public function dispatchLoopShutdown()
//    {
//    }
}

