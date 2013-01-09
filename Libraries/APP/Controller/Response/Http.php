<?php
/**
 * @category   APP
 * @package	APP_Controller
 * @subpackage Response
 * @version	$Id: Http.php v1.0 2009-2-25 0:08:04 tomsui $
 */
class APP_Controller_Response_Http
{
	/**
	 * Array of headers. Each header is an array with keys 'name' and 'value'
	 * @var array
	 */
	protected $_headers = array();

	/**
	 * HTTP response code to use in headers
	 * @var int
	 */
	protected $_httpResponseCode = 200;

	/**
	 * Body content
	 * @var array
	 */
	protected $_body = '';

	/**
	 * Exception stack
	 * @var Exception
	 */
	protected $_exceptions = array();

	/**
	 * Flag; is this response a redirect?
	 * @var boolean
	 */
	protected $_isRedirect = false;

	/**
	 * Whether or not to render exceptions; off by default
	 * @var boolean
	 */
	protected $_renderExceptions = false;

	/**
	 * Flag; if true, when header operations are called after headers have been
	 * sent, an exception will be raised; otherwise, processing will continue
	 * as normal. Defaults to true.
	 *
	 * @see canSendHeaders()
	 * @var boolean
	 */
	public $headersSentThrowsException = true;

	/**
	 * Normalize a header name
	 *
	 * Normalizes a header name to X-Capitalized-Names
	 *
	 * @param  string $name
	 * @return string
	 */
	protected function _normalizeHeader($name)
	{
		$filtered = str_replace(array('-', '_'), ' ', (string) $name);
		$filtered = ucwords(strtolower($filtered));
		$filtered = str_replace(' ', '-', $filtered);
		return $filtered;
	}

	/**
	 * Set a header
	 *
	 * If $replace is true, replaces any headers already defined with that
	 * $name.
	 *
	 * @param string $name
	 * @param string $value
	 * @param boolean $replace
	 * @return APP_Controller_Response_Http
	 */
	public function setHeader($name, $value, $replace = false)
	{
		$this->canSendHeaders(true);
		$name  = $this->_normalizeHeader($name);
		$value = (string) $value;

		if ($replace) {
			foreach ($this->_headers as $key => $header) {
				if ($name == $header['name']) {
					unset($this->_headers[$key]);
				}
			}
		}

		$this->_headers[] = array(
			'name'  => $name,
			'value'   => $value,
			'replace' => $replace
		);

		return $this;
	}

	/**
	 * Set redirect URL
	 *
	 * Sets Location header and response code. Forces replacement of any prior
	 * redirects.
	 *
	 * @param string $url
	 * @param int $code
	 * @return APP_Controller_Response_Http
	 */
	public function setRedirect($url, $code = 302)
	{
		$this->canSendHeaders(true);
		$this->setHeader('Location', $url, true)
			 ->setHttpResponseCode($code);

		return $this;
	}

	/**
	 * Is this a redirect?
	 *
	 * @return boolean
	 */
	public function isRedirect()
	{
		return $this->_isRedirect;
	}

	/**
	 * Return array of headers; see {@link $_headers} for format
	 *
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->_headers;
	}

	/**
	 * Clear headers
	 *
	 * @return APP_Controller_Response_Http
	 */
	public function clearHeaders()
	{
		$this->_headers = array();

		return $this;
	}

	/**
	 * Clear all headers, normal and raw
	 *
	 * @return APP_Controller_Response_Http
	 */
	public function clearAllHeaders()
	{
		return $this->clearHeaders()
					->clearRawHeaders();
	}

	/**
	 * Set HTTP response code to use with headers
	 *
	 * @param int $code
	 * @return APP_Controller_Exception
	 */
	public function setHttpResponseCode($code)
	{
		if (!is_int($code) || (100 > $code) || (599 < $code)) {
			require_once 'App/Controller/Exception.php';
			throw new APP_Controller_Exception('Invalid HTTP response code');
		}

		if ((300 <= $code) && (307 >= $code)) {
			$this->_isRedirect = true;
		} else {
			$this->_isRedirect = false;
		}

		$this->_httpResponseCode = $code;
		return $this;
	}

	/**
	 * Retrieve HTTP response code
	 *
	 * @return int
	 */
	public function getHttpResponseCode()
	{
		return $this->_httpResponseCode;
	}

	/**
	 * Can we send headers?
	 *
	 * @param boolean $throw Whether or not to throw an exception if headers have been sent; defaults to false
	 * @return boolean
	 * @throws APP_Controller_Exception
	 */
	public function canSendHeaders($throw = false)
	{
		$ok = headers_sent($file, $line);
		if ($ok && $throw && $this->headersSentThrowsException) {
			require_once 'APP/Controller/Exception.php';
			throw new APP_Controller_Exception('Cannot send headers; headers already sent in ' . $file . ', line ' . $line);
		}
		return !$ok;
	}

	/**
	 * Send all headers
	 *
	 * Sends any headers specified. If an {@link setHttpResponseCode() HTTP response code}
	 * has been specified, it is sent with the first header.
	 *
	 * @return APP_Controller_Response_Http
	 */
	public function sendHeaders()
	{
		// Only check if we can send headers if we have headers to send
		if (count($this->_headers) || (200 != $this->_httpResponseCode)) {
			$this->canSendHeaders(true);
		} elseif (200 == $this->_httpResponseCode) {
			// Haven't changed the response code, and we have no headers
			return $this;
		}

		$httpCodeSent = false;

		foreach ($this->_headers as $header) {
			if (!$httpCodeSent && $this->_httpResponseCode) {
				header($header['name'] . ': ' . $header['value'], $header['replace'], $this->_httpResponseCode);
				$httpCodeSent = true;
			} else {
				header($header['name'] . ': ' . $header['value'], $header['replace']);
			}
		}

		if (!$httpCodeSent) {
			header('HTTP/1.1 ' . $this->_httpResponseCode);
			$httpCodeSent = true;
		}

		return $this;
	}

	/**
	 * Set body content
	 *
	 * @param string $content
	 * @return APP_Controller_Response_Http
	 */
	public function setBody($content)
	{
		$this->_body = (string) $content;
		return $this;
	}

	/**
	 * Append content to the body content
	 *
	 * @param string $content
	 * @return APP_Controller_Response_Http
	 */
	public function appendBody($content)
	{
		$this->_body .= (string) $content;
	}

	/**
	 * Clear body
	 *
	 * @param  string $name Named segment to clear
	 * @return boolean
	 */
	public function clearBody()
	{
		$this->_body = '';
		return true;
	}

	/**
	 * Return the body content
	 *
	 * @param boolean $spec
	 * @return string
	 */
	public function getBody()
	{
		return $this->_body;
	}


	/**
	 * Prepend body
	 *
	 * @param string $content
	 * @return APP_Controller_Response_Http
	 */
	public function prependBody($content)
	{
		$this->_body = (string) $content . $this->_body;
		return $this;
	}


	/**
	 * Echo the body string
	 *
	 * @return void
	 */
	public function outputBody()
	{
		echo $this->_body;
	}

	/**
	 * Register an exception with the response
	 *
	 * @param Exception $e
	 * @return APP_Controller_Response_Http
	 */
	public function setException(Exception $e)
	{
		$this->_exceptions[] = $e;
		return $this;
	}

	/**
	 * Retrieve the exception stack
	 *
	 * @return array
	 */
	public function getExceptions()
	{
		return $this->_exceptions;
	}

	/**
	 * Has an exception been registered with the response?
	 *
	 * @return boolean
	 */
	public function isException()
	{
		return !empty($this->_exceptions);
	}

	/**
	 * Does the response object contain an exception of a given type?
	 *
	 * @param  string $type
	 * @return boolean
	 */
	public function hasExceptionOfType($type)
	{
		foreach ($this->_exceptions as $e) {
			if ($e instanceof $type) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Does the response object contain an exception with a given message?
	 *
	 * @param  string $message
	 * @return boolean
	 */
	public function hasExceptionOfMessage($message)
	{
		foreach ($this->_exceptions as $e) {
			if ($message == $e->getMessage()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Does the response object contain an exception with a given code?
	 *
	 * @param  int $code
	 * @return boolean
	 */
	public function hasExceptionOfCode($code)
	{
		$code = (int) $code;
		foreach ($this->_exceptions as $e) {
			if ($code == $e->getCode()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieve all exceptions of a given type
	 *
	 * @param  string $type
	 * @return false|array
	 */
	public function getExceptionByType($type)
	{
		$exceptions = array();
		foreach ($this->_exceptions as $e) {
			if ($e instanceof $type) {
				$exceptions[] = $e;
			}
		}

		if (empty($exceptions)) {
			$exceptions = false;
		}

		return $exceptions;
	}

	/**
	 * Retrieve all exceptions of a given message
	 *
	 * @param  string $message
	 * @return false|array
	 */
	public function getExceptionByMessage($message)
	{
		$exceptions = array();
		foreach ($this->_exceptions as $e) {
			if ($message == $e->getMessage()) {
				$exceptions[] = $e;
			}
		}

		if (empty($exceptions)) {
			$exceptions = false;
		}

		return $exceptions;
	}

	/**
	 * Retrieve all exceptions of a given code
	 *
	 * @param mixed $code
	 * @return void
	 */
	public function getExceptionByCode($code)
	{
		$code	  = (int) $code;
		$exceptions = array();
		foreach ($this->_exceptions as $e) {
			if ($code == $e->getCode()) {
				$exceptions[] = $e;
			}
		}

		if (empty($exceptions)) {
			$exceptions = false;
		}

		return $exceptions;
	}

	/**
	 * Whether or not to render exceptions (off by default)
	 *
	 * If called with no arguments or a null argument, returns the value of the
	 * flag; otherwise, sets it and returns the current value.
	 *
	 * @param boolean $flag Optional
	 * @return boolean
	 */
	public function renderExceptions($flag = null)
	{
		if (null !== $flag) {
			$this->_renderExceptions = $flag ? true : false;
		}

		return $this->_renderExceptions;
	}

	/**
	 * Send the response, including all headers, rendering exceptions if so
	 * requested.
	 *
	 * @return void
	 */
	public function sendResponse()
	{
		$this->sendHeaders();

		if ($this->isException() && $this->renderExceptions()) {
			$exceptions = '';
			foreach ($this->getException() as $e) {
				$exceptions .= $e->__toString() . "\n";
			}
			echo $exceptions;
			return;
		}

		$this->outputBody();
	}

	/**
	 * Magic __toString functionality
	 *
	 * Proxies to {@link sendResponse()} and returns response value as string
	 * using output buffering.
	 *
	 * @return string
	 */
	public function __toString()
	{
		ob_start();
		$this->sendResponse();
		return ob_get_clean();
	}
}

