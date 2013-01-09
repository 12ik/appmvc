<?php  if ( ! defined('CACHE_PATH')) exit('CACHE_PATH is not defined , exit!');

//require_once 'APP/Db/Adapter/Abstract.php';

require_once 'APP/Db.php';

require_once 'APP/Cache/Array.php';

require_once('APP/Db/Table/Rowset.php');

require_once('APP/Db/Table/Row.php');

require_once('APP/Loader.php');

abstract class APP_Db_Table_Abstract
{
	/**
	 * APP_Db object.
	 *
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_db;

	/**
	 * The schema name (default null means current schema)
	 *
	 * @var array
	 */
	protected $_schema = null;

	/**
	 * The table name.
	 *
	 * @var array
	 */
	protected $_name = null;

	/**
	 * The table column names derived from Zend_Db_Adapter_Abstract::describeTable().
	 *
	 * @var array
	 */
	protected $_cols;

	/**
	 * The primary key column or columns.
	 *
	 * @var Array/String/null
	 */
	protected $_primary = null;

	/**
	 * If your primary key is a compound key, and one of the columns uses
	 * an auto-increment or sequence-generated value, set _identity
	 * to the ordinal index in the $_primary array for that column.
	 * Note this index is the position of the column in the primary key,
	 * not the position of the column in the table.  The primary key
	 * array is 1-based.
	 *
	 * @var integer
	 */
	protected $_identity = 1;

	/**
	 * Information provided by the adapter's describeTable() method.
	 *
	 * @var array
	 */
	protected $_metadata = array();

	/**
	 * the cache file's full path
	 *
	 * @var String
	 */
	protected $_cachefile ;

	/**
	 * APP_Db_Table_Row_Abstract class name.
	 *
	 * @var string
	 */
	protected $_rowClass = 'APP_Db_Table_Row';

	/**
	 * APP_Db_Table_RowSet_Abstract class name.
	 *
	 * @var string
	 */
	protected $_rowsetClass = 'APP_Db_Table_Rowset';

	/**
	 * Constructor.
	 *
	 * Supported params for $config are:
	 * - db           = user-supplied instance of database connector,
	 *                   or key name of registry instance.
	 * - name           = table name.
	 * - primary         = string or array of primary key(s).
	 * - rowClass       = row class name.
	 * - rowsetClass     = rowset class name.
	 * - referenceMap   = array structure to declare relationship
	 *                   to parent tables.
	 * - dependentTables = array of child tables.
	 * - metadataCache   = cache for information from adapter describeTable().
	 *
	 * @param  mixed $config Array of user-specified config options, or just the Db Adapter.
	 * @return void
	 */
	public function __construct($config = array())
	{
		foreach ($config as $key => $value) {
			switch ($key) {
				case 'db':
					$this->_setAdapter($value);
					break;
				case 'schema':
					$this->_schema = (string) $value;
					break;
				case 'name':
					$this->_name = (string) $value;
					break;
				case 'primary':
					$this->_primary = (array) $value;
					break;
				case 'rowClass':
					$this->setRowClass($value);
					break;
				case 'rowsetClass':
					$this->setRowsetClass($value);
					break;
				default:
					break;
			}
		}

		$this->_setup();
		$this->init();
	}

	/**
	 * Turnkey for initialization of a table object.
	 * Calls other protected methods for individual tasks, to make it easier
	 * for a subclass to override part of the setup logic.
	 *
	 * @return void
	 */
	protected function _setup()
	{
		$this->_setupDatabaseAdapter();
		$this->_setupNames();
		$this->_setupTableCache();
	}

	/**
	 * Initialize object
	 *
	 * Called from {@link __construct()} as final step of object instantiation.
	 *
	 * @return void
	 */
	public function init()
	{
	}

	/**
	 * Initialize database adapter.
	 *
	 * @return void
	 */
	protected function _setupDatabaseAdapter()
	{
		if (! $this->_db ) {
			require_once 'APP/Db/Table/Exception.php';
			throw new APP_Db_Table_Exception('No adapter found for ' . get_class($this));
		}
	}

	/**
	 * Initialize table and schema names.
	 *
	 * @return void
	 */
	protected function _setupNames()
	{
		if (! $this->_name) {
			$this->_name = get_class($this);
		} else if (strpos($this->_name, '.')) {
			list($this->_schema, $this->_name) = explode('.', $this->_name);
		}

		if (! $this->_schema) {
			$db_config = $this->_db->getConfig();
			$this->_schema = $db_config['master']['dbname'];
		}

		$this->_cachefile = CACHE_PATH."/Db/$this->_schema/$this->_name.php";
	}

	/**
	 * Initialize table's metadata and primary
	 *
	 * @return void
	 */
	protected function _setupTableCache()
	{
		if (!file_exists($this->_cachefile)) {
			/**
			 *  Initialize metadata
			 */
			$this->_metadata = $this->_db->describeTable($this->_name, $this->_schema);

			/**
			 *  Initialize metadata
			 */
			$this->_cols = array_keys($this->_metadata);

			/**
			 *  Initialize primary
			 */
			if (!$this->_primary) {
				$this->_primary = array();

				foreach ($this->_metadata as $col) {
					if ($col['PRIMARY']) {
						$this->_primary[ $col['PRIMARY_POSITION'] ] = $col['COLUMN_NAME'];
						if ($col['IDENTITY']) {
							$this->_identity = $col['PRIMARY_POSITION'];
						}
					}
				}
				if (empty($this->_primary)) {
					require_once 'APP/Db/Table/Exception.php';
					throw new APP_Db_Table_Exception('A table must have a primary key, but none was found');
				}
			} else if (!is_array($this->_primary)) {
				$this->_primary = array(1 => $this->_primary);
			} else if (isset($this->_primary[0])) {
				array_unshift($this->_primary, null);
				unset($this->_primary[0]);
			}

			/**
			 *  validates _primary
			 */
			if (! array_intersect((array) $this->_primary, $this->_cols) == (array) $this->_primary)
			{
				require_once 'APP/Db/Table/Exception.php';
				throw new APP_Db_Table_Exception("Primary key column(s) ("
					. implode(',', (array) $this->_primary)
					. ") are not columns in this table ("
					. implode(',', $this->_cols)
					. ")");
			}

			/**
			 *  write the table infomation into cache
			 */
			$CACHE_ARRAY = array(
				'schema'   => $this->_schema,
				'name'   => $this->_name,
				'metadata' => $this->_metadata,
				'cols'   => $this->_cols,
				'primary'  => $this->_primary
			);
			APP_Cache_Array::writeCache($this->_cachefile, $CACHE_ARRAY);
		} else {
			require($this->_cachefile);

			if(!isset($CACHE_ARRAY)) {
				require_once 'APP/Db/Table/Exception.php';
				throw new APP_Db_Table_Exception("DB_Table cache file is broken!");
			}
			$this->_metadata  = $CACHE_ARRAY['metadata'];
			$this->_cols      = $CACHE_ARRAY['cols'];
			$this->_primary   = $CACHE_ARRAY['primary'];
		}
	}

	/**
	 * @param  mixed $db Either an Adapter object, or a string naming a Registry key
	 * @return APP_Db_Table_Abstract Provides a fluent interface
	 */
	protected final function _setAdapter($db)
	{
		$this->_db = self::_setupAdapter($db);
		return $this;
	}

	/**
	 * Gets the APP_Db_Adapter_Abstract for this particular APP_Db_Table object.
	 *
	 * @return APP_Db_Adapter_Abstract
	 */
	public final function getAdapter()
	{
		return $this->_db;
	}

	/**
	 * @param  mixed $db Either an Adapter object, or a string naming a Registry key
	 * @return APP_Db_Adapter_Abstract
	 * @throws APP_Db_Table_Exception
	 */
	protected static final function _setupAdapter($db)
	{
		if ($db === null) {
			return null;
		}

		if (is_string($db)) {
			require_once 'APP/Registry.php';
			$db = APP_Registry::get($db);
		}
		if (!$db instanceof APP_Db)	{
			require_once 'APP/Db/Table/Exception.php';
			throw new APP_Db_Table_Exception('Argument must be of type APP_Db_Adapter_Abstract, or a Registry key where a APP_Db_Adapter_Abstract object is stored');
		}
		return $db;
	}

	/**
	 * @return string
	 */
	public function getRowClass()
	{
		return $this->_rowClass;
	}

	/**
	 * @param  string $classname
	 * @return APP_Db_Table_Abstract Provides a fluent interface
	 */
	public function setRowClass($classname)
	{
		$this->_rowClass = (string) $classname;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getRowsetClass()
	{
		return $this->_rowsetClass;
	}

	/**
	 * @param  string $classname
	 * @return APP_Db_Table_Abstract Provides a fluent interface
	 */
	public function setRowsetClass($classname)
	{
		$this->_rowsetClass = (string) $classname;

		return $this;
	}

	/**
	 * Returns table information.
	 *
	 * You can select to return only a part of this information by supplying its key name,
	 * otherwise all information is returned as an array.
	 *
	 * @param  $key The specific info part to return OPTIONAL
	 * @return mixed
	 */
	public function info($key = null)
	{
		$info = array(
			'schema' => $this->_schema,
			'name' => $this->_name,
			'cols' => (array) $this->_cols,
			'primary' => (array) $this->_primary,
			'metadata' => $this->_metadata,
			'rowClass' => $this->_rowClass,
			'rowsetClass' => $this->_rowsetClass
		);

		if ($key === null) {
			return $info;
		}

		if (!array_key_exists($key, $info)) {
			require_once 'APP/Db/Table/Exception.php';
			throw new APP_Db_Table_Exception('There is no table information for the key "' . $key . '"');
		}

		return $info[$key];
	}

	/**
	 * fetch the table's primary key
	 *
	 * @return array
	 */
	public function getPrimaryKey()
	{
		return (array) $this->_primary;
	}

	/**
	 * Inserts a new row.
	 *
	 * @param  array        $data Column-value pairs.
	 * @return int|array     The primary key of the row inserted.
	 */
	public function insert(array $data)
	{
		$primary = (array) $this->_primary;
		$pkIdentity = $primary[(int)$this->_identity];

		if (array_key_exists($pkIdentity, $data) && $data[$pkIdentity] === null) {
			unset($data[$pkIdentity]);
		}

		$tableSpec = ($this->_schema ? $this->_schema . '.' : '') . $this->_name;
		$this->_db->insert($tableSpec, $data);

		if (empty($data[$pkIdentity])) {
			$data[$pkIdentity] = $this->_db->lastInsertId();
		}

		$pkData = array_intersect_key($data, array_flip($primary));
		if (count($primary) == 1) {
			reset($pkData);
			return current($pkData);
		}

		return $pkData;
	}

	/**
	 * Updates existing rows.
	 *
	 * @param  array        $data  Column-value pairs.
	 * @param  array|string $where An SQL WHERE clause, or an array of SQL WHERE clauses.
	 * @return int          The number of rows updated.
	 */
	public function update(array $data, $where)
	{
		$tableSpec = ($this->_schema ? $this->_schema . '.' : '') . $this->_name;
		return $this->_db->update($tableSpec, $data, $where);
	}

	/**
	 * Deletes existing rows.
	 *
	 * @param  array|string $where SQL WHERE clause(s).
	 * @return int        The number of rows deleted.
	 */
	public function delete($where)
	{
		$tableSpec = ($this->_schema ? $this->_schema . '.' : '') . $this->_name;
		return $this->_db->delete($tableSpec, $where);
	}

	/**
	 * Fetches rows by primary key.  The argument specifies one or more primary
	 * key value(s).  To find multiple rows by primary key, the argument must
	 * be an array.
	 * @param  mixed $key                   The value(s) of the primary keys.
	 * @return APP_Db_Table_Rowset_Abstract  Row(s) matching the criteria.
	 * @throws APP_Db_Table_Exception
	 */
	public function find()
	{
		$args = func_get_args();
		$keyNames = array_values((array) $this->_primary);

		if (count($args) != count($keyNames))
		{
			require_once 'APP/Db/Table/Exception.php';
			throw new APP_Db_Table_Exception("columns should equals the num of primary key");
		}

		$whereList = array();
		$numberTerms = 0;
		foreach ($args as $keyPosition => $keyValues)
		{
			if (!is_array($keyValues))
			{
				$keyValues = array($keyValues);
			}
			if ($numberTerms == 0)
			{
				$numberTerms = count($keyValues);
			}
			else if (count($keyValues) != $numberTerms)
			{
				require_once 'APP/Db/Table/Exception.php';
				throw new APP_Db_Table_Exception("Missing value(s) for the primary key");
			}
			for ($i = 0; $i < count($keyValues); ++$i)
			{
				$whereList[$i][$keyPosition] = $keyValues[$i];
			}
		}

		$whereClause = null;
		if (count($whereList))
		{
			$whereOrTerms = array();
			foreach ($whereList as $keyValueSets)
			{
				$whereAndTerms = array();
				foreach ($keyValueSets as $keyPosition => $keyValue)
				{
					$whereAndTerms[] = $this->_db->quoteInto(
						$this->_db->quoteIdentifier($keyNames[$keyPosition], true) . ' = ?',
						$keyValue);
				}
				$whereOrTerms[] = '(' . implode(' AND ', $whereAndTerms) . ')';
			}
			$whereClause = '(' . implode(' OR ', $whereOrTerms) . ')';
		}

		if (count($whereList) == 1)
		{
			return $this->fetchRow($whereClause);
		}
		else
		{
			return $this->fetchAll($whereClause);
		}

		return $this->fetchAll($whereClause);
	}


	/**
	 * Fetches all rows.
	 *
	 * @param string|array  $where  OPTIONAL An SQL WHERE clause 
	 * @param string|array  $order  OPTIONAL An SQL ORDER clause.
	 * @param int          $count   OPTIONAL An SQL LIMIT count. 
	 * @param int          $offset  OPTIONAL An SQL LIMIT offset.
	 * @return             APP_Db_Table_Rowset_Abstract object
	 */
	public function fetchAll($where = null, $order = null, $count = null, $offset = null)
	{
		$table = $this->_schema . '.' . $this->_db->quoteIdentifier($this->_name);
		$select = "SELECT * FROM $table";

//		if ($where !== null)
		if ($where !== null && !empty($where))
		{
			$select .= ' WHERE ' . $this->_where($where);
		}

		if ($order !== null && !empty($order))
		{
			$select .= ' ORDER BY ' . $this->_order($order);
		}


		if ($count !== null || $offset !== null)
		{
			$select = $select . $this->_db->limit($count, $offset);
//			$select = $this->_db->limit($select, $count, $offset);
		}

		$data = $this->_fetch($select);

		require_once 'APP/Loader.php';
		APP_Loader::loadClass($this->_rowsetClass);
		return new $this->_rowsetClass($data);
	}

	public function fetchSql($sql)
	{
		$table = $this->_schema . '.' . $this->_db->quoteIdentifier($this->_name);
		$sql = str_replace("#table#",$table,$sql);
		$data = $this->_fetch($sql);
		return $data;
	}

	/**
	 *  Fetches one row
	 *
	 * @param string|array  $where    OPTIONAL An SQL WHERE clause. 
	 * @param string|array  $order    OPTIONAL An SQL ORDER clause.
	 * @return            返回APP_Db_Table_Row_Abstract对象
	 */
	public function fetchRow($where = null, $order = null)
	{
		/**
		 * 拼装sql语句
		 */
		$table = $this->_schema . '.' . $this->_db->quoteIdentifier($this->_name);
		$select = "SELECT * FROM $table";

		if ($where !== null)
		{
			$select .= ' WHERE ' . $this->_where($where);
		}

		if ($order !== null)
		{
			$select .= ' ORDER BY ' . $this->_order($order);
		}

		$select .= ' LIMIT 1';
		/**
		 * 调用PDO查询, 获取数组.
		 */
		$row = $this->_fetch($select);

		if (count($row) == 0)
		{
			return null;
		}

		/**
		 * 提取数组中的APP_Db_Table_Row对象，作为结果返回.
		 */
		return $row[0];
	}

	/**
	 * 获取总条目
	 *
	 * 默认情况下获取表中全部记录条数
	 *
	 * @param   String|Array 附加的条件
	 * @return  Integer
	 */
	public function count($where = null)
	{
		/**
		 * 拼装sql语句
		 */
		$table = $this->_schema . '.' . $this->_db->quoteIdentifier($this->_name);
		$select = "SELECT count(*) as count FROM $table";

		if ($where !== null)
		{
			$select .= ' WHERE ' . $this->_where($where);
		}

		/**
		 * 调用PDO查询, 获取数组.
		 */
		$row = $this->_fetch($select);
		return $row[0]['count'];

	}

	/**
	 * 获取表中各字段的的默认值
	 *
	 * @return  Array 返回数组的key为字段名，value为该字段默认值.
	 */
	public function getDefaults()
	{
		foreach($this->_metadata as $key => $value)
		{
			$defaults[$key] = $value["DEFAULT"];
		}
		return $defaults;
	}

	/**
	 * 新建一个APP_Db_Table_Row对象
	 *
	 * @param   array 是否提供的数据可选，提供的数据也不必是完整的
	 * @return  返回APP_Db_Table_Row对象
	 */
	public function createRow(array $data = array())
	{
		@APP_Loader::loadClass($this->_rowClass);
		$row = new $this->_rowClass($this,true);
		$row->setFromArray($data);
		return $row;
	}

	/**
	 * form the sub where sql
	 *
	 * @param array
	 * @return string
	 */
	protected function _where($where)
	{
		if (!is_array($where)) {
			$where = array($where);
		}

		foreach ($where as &$term) {
			$term = '(' . $term . ')';
		}

		$where = implode(' AND ', $where);
		return $where;
	}

	/**
	 * form the sub order sql
	 *
	 * @param array
	 * @return string
	 */
	protected function _order($order)
	{
		if (!is_array($order))
		{
			$order = array($order);
		}

		$order = implode(' , ', $order);
		return $order;
	}

	/**
	 * 返回一个数字索引数组，数组中的元素均为APP_Db_Table_Row的对象
	 *
	 * @param  String $select 一条普通的sql查询语句
	 * @return Array  数组中的元素是APP_Db_Table_Row的对象
	 */
	protected function _fetch($select)
	{
		$stmt = $this->_db->query($select);
		$data = $stmt->fetchAll(PDO::FETCH_CLASS, $this->_rowClass, array($this,false));
		return $data;
	}
}

