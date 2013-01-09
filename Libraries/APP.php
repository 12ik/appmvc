<?php
// fireBug added by tomsui 2010-12-29 9:30:23 
if (defined('APP_DEV') && APP_DEV == 1) {
	require_once(LIB_PATH . '/FirePHPCore/fb.php');
	ob_start();
} else if (!function_exists('fb')) {
	function fb() {
	}
}
class APP
{
    /**
     * Debug helper function.  This is a wrapper for var_dump() that adds
     * the <pre /> tags, cleans up newlines and indents, and runs
     * htmlentities() before output.
     *
     * @param  mixed  $var The variable to dump.
     * @param  string $label An optional label.
     * @return string
     */
    static public function dump($var, $label=null, $echo=true)
    {
        // format the label
        $label = ($label===null) ? '' : rtrim($label) . ' ';

        // var_dump the variable into a buffer and keep the output
        ob_start();
        var_dump($var);
        $output = ob_get_clean();

        // neaten the newlines and indents
        $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
        if (PHP_SAPI == 'cli') {
            $output = PHP_EOL . $label
                    . PHP_EOL . $output
                    . PHP_EOL;
        } else {
            $output = '<pre>'
                    . $label
                    . htmlentities($output, ENT_QUOTES)
                    . '</pre>';
        }

        if ($echo) {
            echo($output);
        }
        return $output;
    }

    static protected function _loadConfig($name, $type)
    {
        static $holder = array();

		$name = $name == null ? 'site'   : strtolower($name) ;
		$type = $type == null ? 'config' : strtolower($type) ;

		$key = substr(md5($name .'_'. $type), 0 ,16);
		
		// TODO 目前为页面级缓存， 加入EA缓存模块后改为EA缓存.
        if ( !isset($holder[$key]))
        {
			$config_file = BASE_PATH . "/Configs/{$name}.{$type}.php";

			$config_name = '_' . strtoupper($type);
			
			if($name != 'site')
			{
				$config_name .= '_' . strtoupper($name);
			}

            if ( ! file_exists($config_file))
            {
                throw new Exception("The {$type} file  <b>$config_file</b> does not exist.");
            }

			require($config_file);
			
            if ( !isset($$config_name))
            {
                return FALSE;
            }

            $holder[$key] = $$config_name;
        }

        return $holder[$key];
    }

    /**
	 *
	 *  $name = 'vote'  --> vote.config.php  _CONFIG_VOTE[]
	 *
	 */
    static public function loadConfig($name = null)
    {
		return self::_loadConfig($name , 'config');
    }

//    /**
//	 *
//	 *  简单的载入文件
//	 *
//	 */
//    static public function loadConstants($name = null)
//    {
////		return self::_loadConfig($name , 'consts');
//		require(BASE_PATH . "/Configs/{$name}.consts.php");
//		echo BASE_PATH . "/Configs/{$name}.consts.php";
//    }
}

