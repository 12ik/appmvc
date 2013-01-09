<?php
require_once(LIB_PATH.'/APP/Controller/Action.php');

/**
 * @category   APP
 * @package	APP_Model
 * @version	$Id: Db.php v1.0 2009-3-31 11:37:18 tomsui $
 */

abstract class APP_Model
{
	/**
	 * Helper method to generate a PDO object
	 *
	 * @param string $dbTag the db tag defined in the config file
	 * @return APP_Db_Adapter_Mysql
	 */
	public static function loadDb($dbTag)
	{
		if(empty($dbTag))
		{
			throw new Exception ("$dbTag is empty!");
		}
		
		require_once(LIB_PATH . '/APP/Registry.php');
		$dbTagKey = '_APP_DATABASE_' . $dbTag;
		if(!APP_Registry::isRegistered($dbTagKey))
		{
			$_CONFIG = APP::loadConfig();
			require_once(LIB_PATH . '/APP/Db.php');
//			$db = APP_Db::factory('mysql',$_CONFIG['db'][$dbTag]);
			$db = new APP_Db($_CONFIG['db'][$dbTag]);
			APP_Registry::set($dbTagKey,$db);
			return $db;
		}
		return APP_Registry::get($dbTagKey);
	}

	/**
	 * Helper method to generate a APP_Db_Table_Abstract object
	 *
	 * @param string $table
	 *  eg. act.tbl_user . 'act' is dbTag, 'tbl_user' is the table's name
	 * @return APP_Db_Table_Abstract
	 */
	public static function loadTable($tableTag)
	{
		require_once(LIB_PATH . '/APP/Registry.php');
		if(is_array($tableTag))
		{
			list($db, $table) = $tableTag;
			$tableTag = $db . '.' . $table;
		}
		if(!APP_Registry::isRegistered($tableTag))
		{
			list($dbTag, $tableName) = explode('.', $tableTag, 2);
			$db = self::loadDb($dbTag);

			require_once(LIB_PATH. '/APP/Db/Table.php');
			$tableClass = 'APP_Db_Table';

			$tbl_params = array( 'db'=>$db ,'name'=>$tableName);
			$table = new $tableClass($tbl_params);
			APP_Registry::set($tableTag,$table);
			return $table;
		}

		return APP_Registry::get($tableTag);
	}

	
	public function loadTpl()
	{
		return APP_Controller_Action::loadTpl();
	}
	
}

