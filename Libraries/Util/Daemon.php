<?php
/**
 * 守护进程包装
 * @author Ericcao
 */
/**
 * 做守护进程用的包装类，详细看看daemon下面的程序吧。
 * @author mangoguo@tencent.com
 * @version 0.0.1
 */
if (!defined('SIGHUP'))
{
	define('SIGHUP', 1);
}
if (!defined('SIGTERM'))
{
	define('SIGTERM',15);
}

class Util_Daemon
{
	/**
	 * Dir that stored daemon log
	 *
	 * @var string
	 */
	private $_LogDir;
	
	/**
	 * Enter description here...
	 *
	 * @varint daemon pid
	 */
	private $_pid = 0;
	
	/**
	 * Enter description here...
	 *
	 * @array daemon pids
	 */
	private $_pids = array();
	
	/**
	 * Daemon exec file
	 *
	 * @var string
	 */
	private $_execFile = "";

	public $proc_num = 0;
	
	public $is_watchdog = 1;

	/**
	 * Constructor...
	 *
	 * @param string $pidDir pid保存路径
	 */
	public function __construct($pidDir = "")
	{
		if (!empty($pidDir))
		{
			$this->_LogDir = $pidDir;
		}
		else
		{
		   $this->_LogDir = realpath(dirname(__FILE__))."/pid/";
		}
		$this->check_dir();
	}

	public function __destruct()
	{
	}

	private function check_dir()
	{
		if (!is_dir($this->_LogDir))
		{
			@umask(0000);
			@mkdir($this->_LogDir, 0777);
		}
		
		if (!is_writeable($this->_LogDir))
		{
			@umask(0000);
			@chmod($this->_LogDir, 0777);
		}
	}

	/**
	 * return daemon pid
	 * @access public
	 */
	public function pid()
	{
		return $this->_pid;
	}
	
	/**
	 * return all daemon pids
	 * @access public
	 */
	public function pids()
	{
		return $this->_pids;
	}
	
	/**
	 * Set signal handler
	 *
	 * @param int $signo
	 * @return bool
	 * @throws Exception
	 */
	public function sig_handler( $signo ) 
	{
		switch ( $signo )
		{
			case SIGTERM:
				exit();
				break;
			case SIGUSR1:
				$this->stop_children();
				exit();
				break;
			default:
				// handle all other signals
				break;
		}
	}

	private function stop_children()
	{
		try
		{
			$this->get_children_pids();
		}
		catch ( Exception $e )
		{
			throw $e;
		}
		
		//Kill process
		foreach ($this->_pids as $pid)
		{
			$pid = intval($pid);

			$result = posix_kill($pid, SIGKILL);
			if (!$result)
			{
				throw new Exception( __METHOD__ . "|" . __LINE__ .
					"Cannot kill daemon pid: {$pid}" );
			}

			$this->daemon_log( "Daemon Process {$pid} Shutdown" );

			$result = @unlink($this->_LogDir.'/'.$pid.'.pid');

			if ( $result === false )
			{
				$this->daemon_log( "Cannot delete child pid {$pid} log file" );
			}
			else
			{
				$this->daemon_log( "Child pid {$pid} log file deleted" );
			}

			sleep(1);
		}
	}

	/**
	 * Get pid from pid log file
	 *
	 * @throws Exception
	 */
	private function get_children_pids()
	{
		$this->_pids = array();

		$handle = opendir($this->_LogDir);

		while(($file=readdir($handle)) !== false)
		{
			if (is_file($this->_LogDir.'/'.$file) && preg_match("/^(\d+)(\.pid)$/i", $file, $matches))
			{
				$this->_pids[] = intval($matches[1]);
			}
		}

		closedir($handle);
	}
	
	private function get_watchdog_pid()
	{
		try
		{
			$watchdog = @file_get_contents($this->_LogDir.'/watchdog.pid');
			$watchdog = intval($watchdog);
		}
		catch ( Exception $e )
		{
			throw $e;
		}

		return $watchdog;
	}
	
	/**
	 * Start daemon
	 *
	 * @throws Exception
	 */
	public function start($variable)
	{
		//Setup signal handlers
		pcntl_signal(SIGHUP,  SIG_IGN);
		pcntl_signal(SIGINT,  SIG_IGN);
		pcntl_signal(SIGTTIN, SIG_IGN);
		pcntl_signal(SIGTTOU, SIG_IGN);
		pcntl_signal(SIGQUIT, SIG_IGN);

		if ( !pcntl_signal( SIGTERM, array( $this, "sig_handler" ) ) )
		{
			die( "Cannot setup signal handler for SIGTERM" );
		}
		
		if ( !pcntl_signal( SIGUSR1, array( $this, "sig_handler" ) ) )
		{
			die( "Cannot setup signal handler for SIGUSR1" );
		}
		
		//Daemonize...
		try
		{
			$this->daemonize($variable);
		}
		catch ( Exception $e )
		{
			throw $e;
		}
		
		$this->daemon_log("Daemon Process {$this->_pid} Started");
	}

	/**
	 * Stop daemon
	 *
	 * @return bool
	 */
	public function stop()
	{
		try
		{
			$watchdog = $this->get_watchdog_pid();
		}
		catch ( Exception $e )
		{
			throw $e;
		}
		
		$result = posix_kill($watchdog, SIGUSR1);
		
		if (!$result)
		{
			throw new Exception( __METHOD__ . "|" . __LINE__ .
				"Cannot kill daemon watchdog pid: {$watchdog}" );
		}

		$this->daemon_log( "Watchdog Process {$watchdog} Shutdown" );

		$result = @unlink($this->_LogDir.'/watchdog.pid');

		if ( $result === false )
		{
			$this->daemon_log( "Cannot delete watchdog pid {$watchdog} log file" );
		}
		else
		{
			$this->daemon_log( "Watchdog pid {$watchdog} log file deleted" );
		}
	}

	/**
	 * Restart daemon
	 *
	 * @return bool
	 */
	public function restart($variable)
	{
		try
		{
			$this->stop();
			$this->start($variable);
		}
		catch ( Exception $e )
		{
			throw $e;
		}
	}
	
	/**
	 * Get process arguments from cmdline file of Linux ProcFS
	 *
	 * @param int $intPid
	 * @return string
	 * @throws Exception
	 */
	private function get_php_process_args( $intPid )
	{
		$strProcCmdlineFile = "/proc/" . $intPid . "/cmdline";
		
		$strContents = @file_get_contents($strProcCmdlineFile);
		
		$strContents = preg_replace( "/[^\w\.\/\-]/", " "
			, trim( $strContents ) );
		$strContents = preg_replace( "/\s+/", " ", $strContents );
		
		$arrTemp = explode( " ", $strContents );
		
		if ( count( $arrTemp ) < 2 )
		{
			return "";
		}
		
		return trim( $arrTemp[1] );
	}
	
	private function get_php_processes()
	{
		$php_processes = array();
		$master_pid = posix_getpid();

		if ( @is_dir("/proc/") )
		{
			$handle = @opendir("/proc/");

			while (($pid = @readdir($handle)) !== false)
			{
				if (intval($pid) > 0)
				{
					$process_exec = "/proc/" . $pid . "/exe";
					if (is_link($process_exec))
					{
						$process_exec_link = @readlink($process_exec);
						if ( $process_exec_link == PHP_BINDIR . '/php' && $pid != $master_pid)
						{
							$php_processes[] = $pid;
						}
					}
				}
			}
			@closedir($handle);
		}
		
		return $php_processes;
	}
	
	/**
	 * Check whether daemon is running or not
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function check_running()
	{
		switch ( strtolower( PHP_OS ) )
		{
			case "freebsd":
			case "linux":
			{
				try
				{
					$php_processes = $this->get_php_processes();
				}
				catch ( Exception $e )
				{
					return false;
				}

				foreach ($php_processes as $proc_id)
				{
					$exec_args = $this->get_php_process_args( $proc_id );
					if ( strcmp($exec_args, $_SERVER['PHP_SELF']) == 0 )
					{
						return true;
					}
				}				
				return false;
				
				break;
			}
			default:
				return false;
				break;
		}
	}
	
	/**
	 * Daemonize program
	 *
	 * @throws Exception
	 */
	private function daemonize($variable)
	{
		if ( $this->check_running() === true )
		{
			echo "Daemon already running\n";
			exit();
		}

		//------------------------------------------
		// First Fork
		//------------------------------------------
	
		$pid = pcntl_fork();
		if ($pid == -1)
		{
			throw new Exception( __METHOD__ . "|" . __LINE__ .
								": fork() error");
		}
		elseif ($pid > 0)
		{
			exit(0);
		}
		unset($pid);

		//------------------------------------------
		//Detatch from the controlling terminal
		//------------------------------------------

		if ( !posix_setsid() )
		{
			throw new Exception( __METHOD__ . "|" . __LINE__ .
				": Cannot detach from terminal" );
		}

		//------------------------------------------
		// Second Fork
		//------------------------------------------
	
		$pid = pcntl_fork();
		if ($pid == -1)
		{
			throw new Exception( __METHOD__ . "|" . __LINE__ .
								": fork() error");
		}
		elseif ($pid > 0)
		{
			exit(0);
		}
		unset($pid);

		//------------------------------------------
		// Third Fork
		//------------------------------------------
	
		$pid = posix_getpid();
		$this->_pids = array();

		for ($i = 0; $i < $variable; $i++)
		{
			$child_pid = pcntl_fork();

			sleep(1);

			if ($child_pid == -1)
			{
				throw new Exception( __METHOD__ . "|" . __LINE__ .
										": fork() error");
			}
			else if ($child_pid)
			{
				// we are the parent
				$this->is_watchdog = 1;
				$this->_pid = $pid;
				$this->_pids[] = $child_pid;
			}
			else
			{
				// we are the child
				// get pid again
				$this->is_watchdog = 0;
				$this->proc_num = $i;
				$this->_pid = posix_getpid();

				//Child ignore SIGUSR1
				pcntl_signal(SIGUSR1, SIG_IGN); 
				@file_put_contents($this->_LogDir.'/'.$this->_pid.'.pid', $this->proc_num);
				break;
			}
		}

		if ($this->is_watchdog == 1)
		{
			@file_put_contents($this->_LogDir.'/watchdog.pid', $this->_pid);
			$this->daemon_log("Watchdog Process {$this->_pid} Started");

			while(true)
			{
				$pid = pcntl_waitpid(0, $status, WNOHANG);
				if ($pid == 0)
				{
    				sleep(2);
    				continue;
				}
				$this->refork_child($pid, $status);
				if ($this->is_watchdog == 0)
				{
					break;
				}
			}
		}
	}

	public function refork_child($pid, $status)
	{
		// the child process exited normal 
		if (pcntl_wifexited($status) === true)
		{
			$code = pcntl_wexitstatus($status);
		}

		// the child process exited as a result of it being sent a signal that was not handled 
		if (pcntl_wifsignaled($status) === true)
		{
			$code = pcntl_wtermsig($status);
		}

		// the child process is currently stopped, and whose status has not been reported
		if (pcntl_wifstopped($status) === true)
		{
			$code = pcntl_wstopsig($status);
		}

		$this->daemon_log( "Child pid {$pid} {$code} exited" );

		$pro_num = @file_get_contents($this->_LogDir.'/'.$pid.'.pid');
		$pro_num = intval($pro_num);

		$new_pids = array();

		foreach ($this->_pids as $p)
		{
			if ($p != $pid)
			{
				$new_pids[] = $p;
			}
		}

		$this->_pids = $new_pids;

		$child_pid = pcntl_fork();

		if ($child_pid == -1)
		{
			throw new Exception( __METHOD__ . "|" . __LINE__ .
										": fork() error");
		}
		else if ($child_pid)
		{
			// we are the parent
			$this->_pids[] = $child_pid;
		}
		else
		{
			// we are the child
			// get pid again
			$this->is_watchdog = 0;
			$this->proc_num = $pro_num;
			$this->_pid = posix_getpid();

			//Child ignore SIGUSR1
			pcntl_signal(SIGUSR1, SIG_IGN);
			@unlink($this->_LogDir.'/'.$pid.'.pid');
			@file_put_contents($this->_LogDir.'/'.$this->_pid.'.pid', $this->proc_num);
		}
	}

	/**
	 * Log daemon event
	 *
	 * @throws Exception
	 */
	public function daemon_log($message)
	{
	    $date = date('Y-m-d');
		$content = "[".$date."]".$message."\n";

		$log_file = $this->_LogDir.'/'.$date.'.log';

		$this->check_dir();

		if ( file_put_contents($log_file, $content, FILE_APPEND | LOCK_EX ) === false )
		{
			throw new Exception( __METHOD__ . "|" . __LINE__ .
				": Cannot write contents to file " . $log_file );
		}
	}

	public function show_help($full_file)
	{
		$file = basename($full_file); 
		$php_exec = PHP_BINDIR . '/php';

	print <<<EOF
	Usage:
			{$file} <option>
			{$full_file} <option>

			php {$file} <option>
			php {$full_file} <option>

			{$php_exec} {$file} <option>
			{$php_exec} {$full_file} <option>

	Options:
			start [processes] - Start the daemon, you can set processes number
			stop - Stop daemon
			restart [processes] - Restart daemon, you can set processes number
			help - Show help

EOF;
	}

}