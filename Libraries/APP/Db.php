<?php
require_once 'APP/Db/Exception.php';

class APP_Db
{
	protected $_config = array();

	protected $_connection = null;

	protected $_fetchMode = PDO::FETCH_ASSOC;

	protected $_caseFolding = PDO::CASE_NATURAL;

	protected $_autoQuoting = true;

	public function __construct($config)
	{
		if(!is_array($config)){
			throw new APP_Db_Exception('Db config parameters must be in an array');
		}

		/*
		if(! array_key_exists('dbname', $config)
			|| !array_key_exists('username', $config)
			|| !array_key_exists('password', $config)
			){
			throw new APP_Db_Exception("Configuration array error : 'dbname/username/password' required");
		}
		*/

		$this->_config = $config;

//		$dbh = new PDO('mysql:host=localhost;dbname=test', $user, $pass, array(
//			PDO::ATTR_PERSISTENT => true
//		));

		$driver_options = array();
		if(isset($config['persistent'])) {
			 $driver_options = array(PDO::ATTR_PERSISTENT => $config['persistent']);
		}

		$this->_config['driver_options'] = $driver_options;

		if(isset($config['caseFolding'])) {
			$this->_caseFolding = $config['caseFolding'];
		}
	}

	public function connect($master=true)
	{
//		static $pdoInstances = array(
//			'master'=>null,
//			'slave'=>null
//		);
		if ($master) {
//			if ($pdoInstances['master']) {
//				$this->_connection = $pdoInstances['master'];
//			} else {
				$this->_connect($this->_config['master']);
//				$pdoInstances['master'] = $this->_connection;
//			}
		} else {
//			if ($pdoInstances['slave']) {
//				$this->_connection = $pdoInstances['slave'];
//			} else {
				$rkey = rand(0, count($this->_config['slave']) -1);
				$this->_connect($this->_config['slave'][$rkey]);
//				$pdoInstances['slave'] = $this->_connection;
			//}
		}
		
		return $this->_connection;
	}

	protected function _connect($dbConfig)
	{
		if ($this->_connection) {
			return;
		}

		if (!isset($dbConfig['driver_options'])) {
			$dbConfig['driver_options'] = array();
		}
		$dsn = 'mysql:host='.$dbConfig['dbhost'].';dbname='.$dbConfig['dbname'];

		if(isset($dbConfig['port'])){
			$dsn .= ';port=' . $dbConfig['port'];
		}

		try{
			$this->_connection = new PDO(
				$dsn,
				$dbConfig['username'],
				$dbConfig['password'],
				$dbConfig['driver_options']
			);
			$this->_connection->setAttribute(PDO::ATTR_CASE, $this->_caseFolding);
			$this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			if ($_SERVER['SERVER_ADDR'] != '192.168.116.14') {
				$stmt = $this->_connection->prepare('set names UTF8');
				$stmt->setFetchMode($this->_fetchMode);
				$stmt->execute();
			}

		}catch (PDOException $e){
			throw new APP_Db_Exception($e->getMessage());
		}
	}

	public function getConfig()
	{
		return $this->_config;
	}

	public function query($sql) 
	{
		try{
			$this->connect();
			if (defined('DEBUG_APP_DB')
				|| defined('debug_app_db')
				|| isset($_GET['DEBUG_APP_DB'])
				|| isset($_GET['debug_app_db'])
				|| isset($_GET['APP_DB_DEBUG'])
				|| isset($_GET['app_db_debug'])
				) { 
				echo $sql . ";<br />\n"; 
			}
			$stmt = $this->_connection->prepare($sql);
			$stmt->setFetchMode($this->_fetchMode);
			$stmt->execute();

			return $stmt;

		}catch (PDOException $e){
			throw new APP_Db_Exception($e->getMessage());
		}
	}

	public function fetchAll($sql)
	{
		$stmt = $this->query($sql);
		return $stmt->fetchAll();
	}

	public function fetchRow($sql)
	{
		$stmt = $this->query($sql);
		$result = $stmt->fetch($this->_fetchMode);
		$stmt->closeCursor();
		return $result;
	}

	public function fetchOne($sql)
	{
		$stmt = $this->query($sql);
		$result = $stmt->fetchColumn(0);
		$stmt->closeCursor();
		return $result;
	}

	/**
	 * 获取结果集第一列
	 *
	 * 获取结果集第一列, 返回一维数字索引数组
	 *
	 * @param string $sql 
	 * @return array
	 */
	public function fetchCol($sql)
	{
		$stmt = $this->query($sql);
		$result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
		return $result;
	}

	/**
	 * 插入一条记录
	 *
	 * @param string  表名
	 * @param array   该条记录的相关数组(key为字段名，value为字段值)
	 * @return int	成功返回1 (成功插入的数据条数)
	 * @throw APP_Db_Exception  插入失败时抛出异常
	 */
	public function insert($table, array $bind)
	{
		$cols = array();
		$vals = array();
		foreach ($bind as $col => $val) {
			$cols[] = $this->quoteIdentifier($col);
			if( $this->_autoQuoting ) {
				$vals[] = $this->quoteValue($val);
			}else{
				$vals[] = $val;
			}
		}
		$table =  $this->quoteIdentifier($table);

		$sql = "INSERT INTO $table ( %s ) VALUES ( %s ) ";
		$sql = sprintf( $sql , implode( ',', $cols ), implode( ",", $vals ) );
		$stmt = $this->query($sql);
		$result = $stmt->rowCount();
		return $result;
	}

	public function lastInsertId()
	{
		$this->connect();
		return $this->_connection->lastInsertId();
	}

	/**
	 * 更改数据记录 update
	 *
	 * @param string		表名
	 * @param array		 更改字段的键值对 eg. array('name'=>'tomsui')
	 * @param array|string  where 条件	  eg. "name='tomsui' and age='27'"
							注意：$where 的数组形式是'与'的关系 array('id=14','id=28')是没有意义的

	 * @return int		  返回执行影响的数据条目数
	 */
	public function update($table, array $bind, $where = '')
	{
		$set = array();
		foreach ($bind as $col => $val) {
			$col = $this->quoteIdentifier($col);
			if( $this->_autoQuoting ) {
				$val = $this->quoteValue($val);
			}
			$set[] = "$col=$val";
		}
		$table = $this->quoteIdentifier($table);
		$where = $this->where($where);

		$sql = "UPDATE $table SET " . implode(', ', $set)
			 . (($where) ? " $where" : '');

		$stmt = $this->query($sql);

		return $stmt->rowCount();
	}

	/**
	 * 删除数据记录 delete
	 *
	 * @param string		表名
	 * @param array|string  where 条件  eg. "name='tomsui' and age='27'"
							注意：$where 的数组形式时是'与'的关系，见update
	 * @return int		  返回执行影响的数据条目数
	 */
	public function delete($table, $where = '')
	{
		$where = $this->where($where);
		$sql = "DELETE FROM "
			 . $this->quoteIdentifier($table)
			 . (($where) ? " $where" : '');

		$stmt = $this->query($sql);
		$result = $stmt->rowCount();
		return $result;
	}

	/**
	 * Convert an array, string, into a string to put in a WHERE clause.
	 *
	 * @param mixed $where string|Array
	 * @return string
	 *
	 * $where = "name='tomsui'"			   => (name='tomsui')
	 * $where = array("name='tomsui'", "age=27") => (name='tomsui') AND (age=27)
	 */
	public function where($where)
	{
		if (empty($where)){
			return $where;
		}
		if (!is_array($where)){
			$where = array($where);
		}
		foreach ($where as &$iterm){
			$iterm = '(' . $iterm . ')';
		}
		array_unshift($where, ' Where 1 = 1 ');
		$where = implode(' AND ', $where);
		return $where;
	}

	/**
	 * form the sub order sql  从Table抄来，作为工具类
	 *
	 * @param array
	 * @return string
	 */
	public function order($order)
	{
		if (!is_array($order)) {
			$order = array($order);
		}

		if (empty($order)) {
			return '';
		}

		$order = ' ORDER BY ' . implode(' , ', $order);
		return $order;
	}

	
	/**
	 * 为字段名或表名加反撇
	 *
	 * @param  string $ident  字段名或表名( 如:  db.table  )
	 * @return string $quoted 被反引的结果( 如: `db`.`table` )
	 */
	public function quoteIdentifier($ident)
	{
		if(is_string($ident) && strpos($ident ,'.' ) > 0){
			$ident = explode('.', $ident);
		}

		if (is_array($ident)){
			$segments = array();
			foreach ($ident as $segment){
				$segments[] = $this->quoteIdentifier($segment);
			}
			$quoted = implode('.', $segments);
		}else{
			$q = '`';
			$quoted = $q . str_replace("$q", "$q$q", $ident) . $q;
		}
		return $quoted;
	}

	/**
	 * 转义字符串
	 *
	 * <code>
	 * $text = "WHERE date < ?";
	 * $date = "2005-01-02";
	 * $safe = $db->quoteInto($text, $date);
	 * // $safe = "WHERE date < '2005-01-02'"
	 * </code>
	 *
	 */
	public function quoteInto($text, $value)
	{
		return str_replace('?', $this->quote($value), $text);
	}

	/**
	 * 转义字符串
	 * 
	 * 是对quoteValue的包装, 处理如果目标是数组，会返回这个数组的逗号分割形式的字符串
	 *
	 * @param mixed  $value 
	 * @return mixed An SQL-safe quoted value (or string of separated values).
	 */
	public function quote($value)
	{
		if (is_array($value)){
			foreach ($value as &$val){
				$val = $this->quote($val);
			}
			return implode(', ', $value);
		}

		return $this->quoteValue($value);
	}

	/**
	 * 转义字符串 
	 *
	 * 直接调用PDO::quote()对字符串(不包括数字)进行转义
	 *
	 * @param string $value	Raw string
	 * @return string		  转义后的字符串
	 */
	public function quoteValue($value)
	{
		if (is_int($value) || is_float($value)){
			return $value;
		}
		$this->connect();
		return $this->_connection->quote($value);
	}


/*------------------------------------------------------------------------------
------------------------------------------------------------------------------*/

	/**
	 * Returns a list of the tables in the database.
	 *
	 * @return array
	 */
	public function listTables()
	{
		return $this->fetchCol('SHOW TABLES');
	}

	/**
	 * 返回表的列描述
	 *
	 * 返回的结果集是以列名为key的相关数组
	 *
	 * 该相关数组的每一个元素的值具有如下含义;
	 *
	 * SCHEMA_NAME	  => string;  Database's name
	 * TABLE_NAME	   => string;  table's name
	 * COLUMN_NAME	  => string;  column's name
	 * COLUMN_POSITION  => number;  该列在表中的原始位置(以1起始)
	 * DATA_TYPE		=> string;  SQL datatype name of column
	 * DEFAULT		  => string;  该列的默认值default expression of column, null if none
	 * NULLABLE		 => boolean; 该列是否可以为NULL
	 * LENGTH		   => number;  char/varchar的长度
	 * SCALE			=> number;  scale of NUMERIC/DECIMAL
	 * PRECISION		=> number;  precision of NUMERIC/DECIMAL
	 * UNSIGNED		 => boolean; unsigned property of an integer type
	 * PRIMARY		  => boolean; true if column is part of the primary key
	 * PRIMARY_POSITION => integer; position of column in primary key
	 * IDENTITY		 => integer; true if column is auto-generated with unique values
	 *
	 * @param string $tableName
	 * @param string $schemaName OPTIONAL
	 * @return array
	 */
	public function describeTable($tableName, $schemaName = null)
	{
		if (!$schemaName){
			$schemaName = $this->_config['dbname'];
		}

		$sql = 'DESCRIBE ' . $this->quoteIdentifier("$schemaName.$tableName");
		$stmt = $this->query($sql);

		$result = $stmt->fetchAll(PDO::FETCH_NUM);
		$field   = 0;
		$type   = 1;
		$null   = 2;
		$key	 = 3;
		$default = 4;
		$extra   = 5;

		$desc = array();
		$i = 1;
		$p = 1;

		foreach ($result as $row)
		{
			list($length, $scale, $precision, $unsigned, $primary, $primaryPosition, $identity)
				= array(null, null, null, null, false, null, false);
			if (preg_match('/unsigned/', $row[$type])) {
				$unsigned = true;
			}

			if (preg_match('/^((?:var)?char)\((\d+)\)/', $row[$type], $matches)) {
				$row[$type] = $matches[1];
				$length = $matches[2];
			}
			else if (preg_match('/^decimal\((\d+),(\d+)\)/', $row[$type], $matches)) {
				$row[$type] = 'decimal';
				$precision = $matches[1];
				$scale = $matches[2];
			}
			else if (preg_match('/^((?:big|medium|small|tiny)?int)\((\d+)\)/', $row[$type], $matches)) {
				$row[$type] = $matches[1];
//			  $length = $matches[2];  //just for display width
			}
			if (strtoupper($row[$key]) == 'PRI') {
				$primary = true;
				$primaryPosition = $p;
				if ($row[$extra] == 'auto_increment') {
					$identity = true;
				}  else {
					$identity = false;
				}
				++$p;
			}
			$desc[$this->foldCase($row[$field])] = array(
				'SCHEMA_NAME'	 => $schemaName,
				'TABLE_NAME'	   => $this->foldCase($tableName),
				'COLUMN_NAME'	 => $this->foldCase($row[$field]),
				'COLUMN_POSITION'  => $i,
				'DATA_TYPE'	 => $row[$type],
				'DEFAULT'		 => $row[$default],
				'NULLABLE'	   => (bool) ($row[$null] == 'YES'),
				'LENGTH'		   => $length,
				'SCALE'		 => $scale,
				'PRECISION'	 => $precision,
				'UNSIGNED'	   => $unsigned,
				'PRIMARY'		 => $primary,
				'PRIMARY_POSITION' => $primaryPosition,
				'IDENTITY'	   => $identity
			);
			++$i;
		}
		return $desc;
	}

	public function limit($count, $offset = 0)
	{
		$limit = '';
		$count = intval($count);
		if ($count > 0) {
			$limit = " LIMIT $count";

			$offset = intval($offset);
			if ($offset > 0) {
				$limit .= " OFFSET $offset";
			}
		}

		return $limit;
	}

	public function getFetchMode()
	{
		return $this->_fetchMode;
	}

	public function setFetchMode($mode)
	{
		switch ($mode){
			case PDO::FETCH_LAZY:
			case PDO::FETCH_ASSOC:
			case PDO::FETCH_NUM:
			case PDO::FETCH_BOTH:
			case PDO::FETCH_NAMED:
			case PDO::FETCH_OBJ:
				$this->_fetchMode = $mode;
				break;
			default:
				throw new APP_Db_Exception("Invalid fetch mode '$mode' specified");
				break;
		}
	}

	public function foldCase($key)
	{
		switch ($this->_caseFolding){
			case PDO::CASE_LOWER:
				return strtolower((string) $key);
			case PDO::CASE_UPPER:
				return strtoupper((string) $key);
			case PDO::CASE_NATURAL:
			default:
				return (string) $key;
		}
	}

	public function beginTransaction()
	{
		$this->connect();
		$this->_connection->beginTransaction();
		return true;
	}

	public function commit()
	{
		$this->connect();
		$this->_connection->commit();
		return true;
	}

	public function rollBack()
	{
		$this->connect();
		$this->_connection->rollBack();
		return true;
	}
	
	public function detailTables($tablename)
	{
		return $this->fetchRow("show table status where Name ='$tablename' ");
	}
}

