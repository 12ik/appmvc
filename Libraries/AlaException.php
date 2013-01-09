<?php
/**
 * 异常调试工具类
 * @author zhanglupeng@joyport.com
 *
 */
class AlaException extends Exception {
	protected $name = null;

	/**
	 * Class constructor.
	 *
	 * @param string The error message
	 * @param int    The error code
	 */
	public function __construct($message = null, $code = 0) {
		if ($this->getName() === null) {
			$this->setName('AlaException');
		}

		parent :: __construct($message, $code);
/*
	//	if (Config :: get('ala_logging_enabled') && $this->getName() != 'StopException') {
        if (defined('ala_logging_enabled') && $this->getName() != 'StopException') {
			$logger = new Logger();
			$logger->error('{'.$this->getName().'} '.$message."\n". 
				var_export($this->getTraces($this), true));
		}*/
	}

	/**
	 * Retrieves the name of this exception.
	 *
	 * @return string This exception's name
	 */
	public function getName() {
		return $this->name;
	}

	public function printStackTrace($exception = null){
		self::displayError($this);
	}

	/**
	 * Prints the stack trace for this exception.
	 *
	 * @param Exception An Exception implementation instance
	 */
	static public function  displayError($exception){
		// don't print message if it is an StopException exception
		if (method_exists($exception, 'getName') && $exception->getName() == 'StopException') {
			/*if (! Config :: get('ala_test')) {
				exit (1);
			}
			if(! defined('ALA_TEST')){
				exit (1);
			}*/

			return;
		}
/*
		//if (! Config :: get('ala_test')) {
        if (! defined('ALA_DEBUG')) {
			header('HTTP/1.0 500 Internal Server Error');

			// clean current output buffer
			while (@ ob_end_clean());

			ob_start();
		}

		// send an error 500 if not in debug mode
		//if (!Config :: get('ala_debug')) {
          if (! defined('ALA_DEBUG')) {
			error_log($exception->getMessage());
			include (Config :: get('ala_htdocs_dir') . '/errors/error500.php');
			if (!Config :: get('ala_test')) {
				exit (1);
			}
			return;
		}
*/
		//if in debug mode print the bug info to brower
		$message = null !== $exception->getMessage() ? $exception->getMessage() : 'n/a';
		$name = get_class($exception);
		$format = 0 == strncasecmp(PHP_SAPI, 'cli', 3) ? 'plain' : 'html';
		$traces = self::getTraces($exception, $format);
		$html = "<html><header><title>debuging</title><script>function toggle(id){bid = document.getElementById(id); if(bid.style.display == 'block') bid.style.display = 'none'; else  bid.style.display = 'block'; } </script></header><body>";
		$html .= "<div style='color:red;font-weight:bold;'><span style='color:#000000;'>Error:</span>$message</div>";
		
		foreach($traces as $tr){
			$html .= $tr;
		}
		$html .= "</body></html>";
		echo $html;
		// if test, do not exit
		//if (!Config :: get('ala_test')) {
         if (! defined('ALA_TEST')) {
			exit (1);
		}
	}

	/**
	 * Returns an array of exception traces.
	 *
	 * @param Exception An Exception implementation instance
	 * @param string The trace format (plain or html)
	 *
	 * @return array An array of traces
	 */
  	public	static	 function getTraces($exception, $format = 'plain') {
		$traceData = $exception->getTrace();
		array_unshift($traceData, array (
			'function' => '',
		'file' => $exception->getFile() != null ? $exception->getFile() : 'n/a', 'line' => $exception->getLine() != null ? $exception->getLine() : 'n/a', 'args' => array (),));
		
		$traces = array ();
		if ($format == 'html') {
			$lineFormat = '<div style="background-color:#eee;margin-top:5px;padding:5px;">at %s%s%s (%s) in %s  line %s <a href="#" onclick="toggle(\'%s\'); return false;">..</a> </div><div style="border:solid 1px #eeeeee;">%s<ul id="%s" style="display: %s">%s</ul></div>';
		} else {
			$lineFormat = 'at %s%s%s(%s) in %s line %s';
		}
		for ($i = 0, $count = count($traceData); $i < $count; $i++) {
			$line = isset ($traceData[$i]['line']) ? $traceData[$i]['line'] : 'n/a';
			$file = isset ($traceData[$i]['file']) ? $traceData[$i]['file'] : 'n/a';
			$args = isset ($traceData[$i]['args']) ? $traceData[$i]['args'] : array ();
			$traces[] = sprintf($lineFormat, 
				(isset ($traceData[$i]['class']) ? $traceData[$i]['class'] : ''), 
				(isset ($traceData[$i]['type']) ? $traceData[$i]['type'] : ''),
				$traceData[$i]['function'],
				self::formatArgs($args, false, $format), 
				basename($file), 
				$line, 
				'trace_' .  $i, 
				$file,'trace_' .  $i, $i == 0 ? 'block' : 'none', 
				self::fileExcerpt($file, $line));
		}
		return $traces;
	
	}

	/**
	 * Returns an HTML version of an array as YAML.
	 *
	 * @param array The values array
	 *
	 * @return string An HTML string
	 */
	static function formatArrayAsHtml($values) {
		return '<pre>' . Yaml::dump($values) . '</pre>';
	}

	/**
	 * Returns an excerpt of a code file around the given line number.
	 *
	 * @param string A file path
	 * @param int The selected line number
	 *
	 * @return string An HTML string
	 */
	static function fileExcerpt($file, $line) {
		if (is_readable($file)) {
			$content = preg_split('#<br />#', highlight_file($file, true));

			$lines = array ();
			for ($i = max($line -3, 1), $max = min($line +3, count($content)); $i <= $max; $i++) {
				$lines[] = '<li' . ($i == $line ? ' class="selected"' : '') . '>' . $content[$i -1] . '</li>';
			}

			return '<ol start="' . max($line -3, 1) . '">' . implode("\n", $lines) . '</ol>';
		}
	}

	/**
	 * Formats an array as a string.
	 *
	 * @param array The argument array
	 * @param boolean 
	 * @param string The format string (html or plain)
	 *
	 * @return string
	 */
	static function formatArgs($args, $single = false, $format = 'html') {
		$result = array ();

		$single and $args = array (
			$args
		);

		foreach ($args as $key => $value) {
			if (is_object($value)) {
				$result[] = ($format == 'html' ? '<em>object</em>' : 'object') . '(\'' . get_class($value) . '\')';
			} else
				if (is_array($value)) {
					$result[] = ($format == 'html' ? '<em>array</em>' : 'array') . '(' . self :: formatArgs($value) . ')';
				} else
					if ($value === null) {
						$result[] = '<em>null</em>';
					} else
						if (!is_int($key)) {
							$result[] = "'$key' =&gt; '$value'";
						} else {
							$result[] = "'" . $value . "'";
						}
		}

		return implode(', ', $result);
	}

	/**
	 * Sets the name of this exception.
	 *
	 * @param string An exception name
	 */
	protected function setName($name) {
		$this->name = $name;
	}
}
