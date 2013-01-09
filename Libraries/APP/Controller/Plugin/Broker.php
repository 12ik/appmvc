<?php
/**
 * @category   APP
 * @package    APP_Controller
 * @subpackage Plugin
 * @version    $Id: Broker.php v1.0 2009-2-25 0:08:04 tomsui $
 */
class APP_Controller_Plugin_Broker extends APP_Controller_Plugin_Abstract
{
    /**
     * Array of instance of objects extending APP_Controller_Plugin_Abstract
     *
     * @var array
     */
    protected $_plugins = array();

    /**
     * Register a plugin.
     *
     * @param  APP_Controller_Plugin_Abstract $plugin
     * @param  int $stackIndex
     * @return APP_Controller_Plugin_Broker
     */
    public function registerPlugin( $plugin, $stackIndex = null)
    {
        if (false !== array_search($plugin, $this->_plugins, true)) {
            throw new APP_Controller_Exception('Plugin already registered');
        }

        $stackIndex = (int) $stackIndex;

        if ($stackIndex) {
            if (isset($this->_plugins[$stackIndex])) {
                throw new APP_Controller_Exception('Plugin with stackIndex "' 
					. $stackIndex . '" already registered');
            }
            $this->_plugins[$stackIndex] = $plugin;
        } else {
            $stackIndex = count($this->_plugins);
            while (isset($this->_plugins[$stackIndex])) {
                ++$stackIndex;
            }
            $this->_plugins[$stackIndex] = $plugin;
        }

        $request = $this->getRequest();
        if ($request) {
            $this->_plugins[$stackIndex]->setRequest($request);
        }
        $response = $this->getResponse();
        if ($response) {
            $this->_plugins[$stackIndex]->setResponse($response);
        }

        ksort($this->_plugins);

        return $this;
    }

    /**
     * Unregister a plugin.
     *
     * @param string|APP_Controller_Plugin_Abstract $plugin Plugin object or class name
     * @return APP_Controller_Plugin_Broker
     */
    public function unregisterPlugin($plugin)
    {
        if ($plugin instanceof APP_Controller_Plugin_Abstract) {
            $key = array_search($plugin, $this->_plugins, true);
            if (false === $key) {
                throw new APP_Controller_Exception('Plugin never registered.');
            }
            unset($this->_plugins[$key]);
        } elseif (is_string($plugin)) {
            foreach ($this->_plugins as $key => $_plugin) {
                $type = get_class($_plugin);
                if ($plugin == $type) {
                    unset($this->_plugins[$key]);
                }
            }
        }
        return $this;
    }

    /**
     * Is a plugin of a particular class registered?
     *
     * @param  string $class
     * @return bool
     */
    public function hasPlugin($class)
    {
        foreach ($this->_plugins as $plugin) {
            $type = get_class($plugin);
            if ($class == $type) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve a plugin or plugins by class
     *
     * @param  string $class Class name of plugin(s) desired
     * @return false|APP_Controller_Plugin_Abstract|array Returns false if none found, plugin if only one found, and array of plugins if multiple plugins of same class found
     */
    public function getPlugin($class)
    {
        $found = array();
        foreach ($this->_plugins as $plugin) {
            $type = get_class($plugin);
            if ($class == $type) {
                $found[] = $plugin;
            }
        }

        switch (count($found)) {
            case 0:
                return false;
            case 1:
                return $found[0];
            default:
                return $found;
        }
    }

    /**
     * Retrieve all plugins
     *
     * @return array
     */
    public function getPlugins()
    {
        return $this->_plugins;
    }

    /**
     * Set request object, and register with each plugin
     *
     * @param APP_Controller_Request_Abstract $request
     * @return APP_Controller_Plugin_Broker
     */
    public function setRequest($request)
    {
        $this->_request = $request;

        foreach ($this->_plugins as $plugin) {
            $plugin->setRequest($request);
        }

        return $this;
    }

    /**
     * Get request object
     *
     * @return APP_Controller_Request_Abstract $request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Set response object
     *
     * @param APP_Controller_Response_Abstract $response
     * @return APP_Controller_Plugin_Broker
     */
    public function setResponse($response)
    {
        $this->_response = $response;

        foreach ($this->_plugins as $plugin) {
            $plugin->setResponse($response);
        }

        return $this;
    }

    /**
     * Get response object
     *
     * @return APP_Controller_Response_Abstract $response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Called before an action is dispatched by APP_Controller_Dispatcher.
     *
     * @param  APP_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch($request)
    {
        foreach ($this->_plugins as $plugin) {
            try {
                $plugin->preDispatch($request);
            } catch (Exception $e) {
                $this->getResponse()->setException($e);
            }
        }
    }

    /**
     * Called after an action is dispatched by APP_Controller_Dispatcher.
     *
     * @param  APP_Controller_Request_Abstract $request
     * @return void
     */
    public function postDispatch($request)
    {
        foreach ($this->_plugins as $plugin) {
            try {
                $plugin->postDispatch($request);
            } catch (Exception $e) {
                $this->getResponse()->setException($e);
            }
        }
    }
}

