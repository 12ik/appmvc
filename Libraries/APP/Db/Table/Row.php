<?php
 /**
 * @category   APP
 * @package    APP_Db
 * @subpackage Table
 * @version    $Id: Row.php v1.0 2009-2-25 0:08:04 tomsui $
 */
class APP_Db_Table_Row implements ArrayAccess
{
    /**
     * APP_Db_Table_Abstract parent class or instance.
     *
     * @var Zend_Db_Table_Abstract
     */
    protected $_table = null;

    /**
     * Name of the class of the APP_Db_Table_Abstract object.
     *
     * @var string
     */
    protected $_tableClass = null;

    /**
     * Primary row key(s).
     *
     * @var array
     */
    protected $_primary= array();

    /**
     * A row is marked read only if it is created by APP_Db_Table_Abstract::createRow()
     *
     * @var boolean
     */
    protected $_isInsert = true;

    /**
     * Constructor.
     *
     * Supported params for $config are:-
     * - table       = object of type APP_Db_Table_Abstract
     * - insert      = if this object is created new
     *
     * @param  APP_Db_Table_Abstract which table the row belongs to
     * @param  boolean  if newly created
     * @return void
     */
    public function __construct($table, $insert)
    {
        $this->_table = $table;

        $this->_tableClass = get_class($this->_table);

        $this->_primary = $this->_table->getPrimaryKey();

        $this->_isInsert = $insert;

        $this->init();
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
     * Returns the table object, or null if this is disconnected row
     *
     * @return APP_Db_Table_Abstract|null
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * Query the class name of the Table object for which this
     * Row was created.
     *
     * @return string
     */
    public function getTableClass()
    {
        return $this->_tableClass;
    }

    /**
     * Saves the properties to the database.
     *
     * This performs an intelligent insert/update
     *
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function save()
    {
        if ($this->_isInsert) {
            return $this->_doInsert();
        } else {
            return $this->_doUpdate();
        }
    }

    /**
     * Deletes existing rows.
     *
     * @return int The number of rows deleted.
     */
    public function delete()
    {
        $where = $this->_getWhereQuery();

        $result = $this->_table->delete($where);

        foreach($this as $key => $value)
        {
            if(substr($key, 0, 1) == '_')
            {
                continue;
            }
            $this->$key = null;
        }

        return $result;
    }

    /**
     * Returns the column/value data as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $data = array();
        foreach(get_object_vars($this) as $key => $value)
        {
            if(substr($key, 0, 1) == '_')
            {
                continue;
            }
            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * Refreshes properties from the database.
     *
     * @return void
     */
    public function refresh()
    {
        $where = $this->_getWhereQuery();

        $that = $this->_table->fetchRow($where);

        foreach($that as $key => $value)
        {
            if(substr($key, 0, 1) == '_')
            {
                continue;
            }
            $this->$key = $value;
        }

        $this->_isInsert = false;
    }

    /**
     * Sets all data in the row from an array.
     *
     * @param  array $data
     * @return APP_Db_Table_Row_Abstract Provides a fluent interface
     */
    public function setFromArray(array $data)
    {
        $defaults = $this->_table->getDefaults();

        $data = array_intersect_key($data, $defaults);
        $data += $defaults;
        foreach ($data as $key => $value) {
			// 禁止修改主键 这样可以在$data无主键时实现数据更新
			// add by tomsui 2010-4-7 17:21:52
 			if (in_array($key, $this->_primary)) {
				continue;
			}
            // No exception is thrown when primary key is edited!
            // tomsui 2009-2-23 3:45:10
            $this->$key = $value;
        }
        return $this;
    }

    /**
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    protected function _doUpdate()
    {
        $where = $this->_getWhereQuery();

        $this->_table->update($this->toArray(), $where);

        $primaryKey = $this->_getPrimaryKey();

        if (count($primaryKey) == 1)
        {
            return current($primaryKey);
        }

        return $primaryKey;
    }

    /**
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    protected function _doInsert()
    {
        $data = $this->toArray();

        $primaryKey = $this->_table->insert($data);

        if (is_array($primaryKey))
        {
            $newPrimaryKey = $primaryKey;
        }
        else
        {
            $newPrimaryKey = array(current((array) $this->_primary) => $primaryKey);
        }

        $this->_isInsert = false;

        foreach($newPrimaryKey as $key =>$value)
        {
            $this->$key = $value;
        }

        return $primaryKey;
    }

    /**
     * Retrieves an associative array of primary keys.
     *
     * @param bool $useDirty
     * @return array
     */
    protected function _getPrimaryKey()
    {
        if (!is_array($this->_primary))
        {
            require_once('APP/Db/Exception.php');
            throw new APP_Db_Exception("The primary key must be set as an array");
        }

        $primary = array_flip($this->_primary);
        $arr = $this->toArray();

        $array = array_intersect_key($arr, $primary);

        if (count($primary) != count($array))
        {
            require_once('APP/Db/Exception.php');
            throw new APP_Db_Exception("The specified Table '$this->_tableClass' does not have the same primary key as the Row");
        }

        return $array;
    }

    /**
     * Constructs where statement for retrieving row(s).
     *
     * @param bool $useDirty
     * @return array
     */
    protected function _getWhereQuery()
    {
        $where = array();

        $db = $this->_table->getAdapter();

        $primaryKey = $this->_getPrimaryKey();

        $where = array();
        foreach ($primaryKey as $columnName => $value)
        {
            $column = $db->quoteIdentifier($columnName, true);
            $where[] = $db->quoteInto("$column = ?", $value);
        }

        return $where;
    }

    /**
     *  offsetExists
     *
     *  Not designed for delvelopers. just method to implements ArrayAccess
     *
     * @param string $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->$key);
    }

    /**
     *  offsetGet
     *
     *  Not designed for delvelopers. just method to implements ArrayAccess
     *
     * @param string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        $this->_validateKey($key);

        return $this->$key;
    }

    /**
     *  offsetSet
     *
     *  Not designed for delvelopers. just method to implements ArrayAccess
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->_validateKey($key);

        $this->$key = $value;
    }

    /**
     *  offsetUnset
     *
     *  Not designed for delvelopers. just method to implements ArrayAccess
     *
     * @param string $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->_validateKey($key);

        unset($this->$key);
    }

    /**
     *  validate Key
     *
     *  if the key of $this starts with '_', throws an exception.
     *
     * @param  string $key
     * @return void
     * @throws  APP_Db_Exception
     */
    protected function _validateKey($key)
    {
        if(substr($key, 0, 1) == '_')
        {
            require_once('APP/Db/Exception.php');
            throw new APP_Db_Exception("APP_Db_Table_Row::$offset can not be get/set/unset directly!");
        }
    }
}

