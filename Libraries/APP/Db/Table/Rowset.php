<?php
 /**
 * @category   APP
 * @package    APP_Db
 * @subpackage Table
 * @version    $Id: Rowset.php v1.0 2009-2-25 0:08:04 tomsui $
 */
class APP_Db_Table_Rowset implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * The APP_Db_Table_Row object for each row.
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Constructor.
     *
     * @param array $_data
     */
    public function __construct($_data)
    {
        $this->_data = $_data;
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
     * Returns all data as an array.
     *
     * Not needed , just for compatibility with Zend
     *
     * @return array
     */
    public function toArray()
    {
		$rev = array();
		foreach($this->_data as $item){
			$rev[] = $item->toArray();
		}
        return $rev;
    }

    /**
     *  offsetExists
     *
     *  Not designed for delvelopers. just method to implements IteratorAggregate
     *
     * @param string $key
     * @return bool
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_data);
    }

    /**
     *  offsetExists
     *
     *  Not designed for delvelopers. just method to implements ArrayAccess
     *
     * @param string $key
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    /**
     *  offsetGet
     *
     *  Not designed for delvelopers. just method to implements ArrayAccess
     *
     * @param string $key
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if($this->offsetExists($offset)){
            return $this->_data[$offset];
        }

        return null;
    }

    /**
     *  offsetSet
     *
     *  Not designed for delvelopers. just method to implements ArrayAccess
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
    }

    /**
     *  offsetUnset
     *
     *  Not designed for delvelopers. just method to implements ArrayAccess
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    /**
     *  offsetUnset
     *
     *  Not designed for delvelopers. just method to implements Countable
     *
     * @param string $offset
     * @return void
     */
    public function count()
    {
        return count($this->_data);
    }
}

