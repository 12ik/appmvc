<?
class Test_TableController extends APP_Controller_Action
{

    /**
     * 生成table对象方法一: 直接生成
     *
     * 在Action中直接给出数据库具体参数，调用APP_Db_Table构造器
     *
     * 说明：强烈不建议使用此方法生成table,仅示范性展示，
     */
    public function test1Action()
    {
        $db = $this->loadDb('testTag1');

        require('APP/Db/Table.php');
        $params = array( 'db'=>$db ,'name'=>'tbl_test_2');
        $tbl = new APP_Db_Table($params);
		print_r($tbl);exit;
    }

    /**
     * 生成table对象方法二: 控制器方法
     *
     * 说明：'testTag1.tbl_test' 意味着'testTag1'库下的tbl_test表
     * 注意： 1) 使用APP_Db_Table的表时， 数据库中的真实字段名不能以下划线'_'开始。
     *       2) 如果没有在/Application/Model/$dbTag/下定义APP_Db_Table_Abstract,
     *          会默认生成一个APP_Db_Table实例，否则会生成自定制的Model实例.
     *           APP_Db_Table_Abstract扩展方法：
     *           a. 文件位置/Application/Model/$dbTag/$tbl_name.php;
     *           b. 类名为$dbTag_$tbl_name, 且首字母大写.
     *           例如：
     *           // Application/Model/test/tbl_test_2.php
     *           class Test_TblTest2 extends APP_Db_Table_Abstract
     *           {
     *           }
     *       2) 正常情况下，框架会缓存表结构到一个数组文件里，因此/Resources/Cache/Db/
     *          必须保持apache进程的可写的权限.
     */
    public function test2Action()
    {
        $table = $this->loadTable('testTag1.Games');
		print_r($table);
    }

    /**
     * insert.
     *
     * @param  array        $data  Column-value pairs.
     * @return int|array    返回主键新值(如果是复合主键的情况，返回复合主键的相关数组)
     */
    public function insertAction()
    {
        $table = $this->loadTable('testTag1.tbl_test_2');

        $pk = $table->insert(array('name'=>'AndyLau','age'=>48));
		print_r($pk);
    }

    /**
     * 更新记录.update
     *
     * @param  array        $data  Column-value pairs.
     * @param  array|string $where An SQL WHERE clause, or an array of SQL WHERE clauses.
     * @return int        更新行为影响的记录条数
     */
    public function updateAction()
    {
        $table = $this->loadTable('testTag1.tbl_test_2');

        $num = $table->update(array('name'=>'AndyLau','age'=>48) ,'id=327');

        print_r($num);exit;
    }

    /**
     * 删除记录
     *
     * @param  array|string $where SQL WHERE clause(s).
     * @return int        删除的记录条数
     */
    public function deleteAction()
    {
        $table = $this->loadTable('testTag1.tbl_test_2');

        $num =  $table->delete('id=326');
    }

    /**
     * 根据主键查找记录. 形参指定了一个或多个主键值(复合主键的情况下).如果想通过主键查找多个
     * 值，参数应该是一个数组.
     *
     * 本函数接收可变数量的参数. 如果table具有复合主键，那么参数的数量必须与真实表中复合主键
     * 数量相一致，在这种的情况下，如果想通过主键查找多个值，每个参数都应该是一个数组，且这些
     * 数组必须具有相同数量的元素.
     *
     * 注意：find()方法仅返回Rowset对象，即便是只获取一条结果记录的情况.
     *
     *
     * @param  mixed $key                     主键的值
     * @return APP_Db_Table_Rowset_Abstract   符合条件的记录结果集
                   |APP_Db_Table_Row_Abstract
     * @throws APP_Db_Table_Exception
     */
    public function findAction()
    {
        $table = $this->loadTable('testTag1.Games');

        // APP_Db_Table_Row_Abstract
        $row = $table->find(15);
		print_r($row);exit;

        // APP_Db_Table_Rowset_Abstract
        $rowset = $table->find(array(15,17));
    }

    /**
     * fetchAll 获取全部记录.
     *
     * @param string|array  $where  一个可选的 SQL WHERE 子句
     * @param string|array  $order  一个可选的 SQL ORDER 子句.
     * @param int          $count  一个可选的 SQL LIMIT count.
     * @param int          $offset 一个可选的 SQL LIMIT offset.
     * @return            返回APP_Db_Table_Rowset_Abstract对象
     */
    public function fetchAllAction()
    {
        $table = $this->loadTable('testTag1.Games');

        $rowset1 = $table->fetchAll();

        // where
        $rowset2 = $table->fetchAll('GameID=15 or GameID=16');

        // order
        $rowset3 = $table->fetchAll(null, array('GameID desc'));

        // where + order
        $rowset4 = $table->fetchAll('GameID=15 or GameID=16', array('GameID','desc'));
    }

    /**
     * 获取table中的一条记录.
     *
     * @param string|array  $where  一个可选的 SQL WHERE 子句
     * @param string|array  $order  一个可选的 SQL ORDER 子句.
     * @return            返回APP_Db_Table_Row_Abstract对象
     */
    public function fetchRowAction()
    {
        $table = $this->loadTable('testTag1.Games');

        $row1 = $table->fetchRow();

        // where
        $row2 = $table->fetchRow('GameID=15');

        // order
        $row3 = $table->fetchRow(null, array('GameID desc'));
    }

    /**
     * 新建一个APP_Db_Table_Row对象
     *
     * @param   array 是否提供的数据可选，提供的数据也不必是完整的
     * @return  返回APP_Db_Table_Row对象
     */
    public function createRowAction()
    {
        $table = $this->loadTable('testTag1.tbl_test_2');

        $row1 = $table->createRow();

        $row2 = $table->createRow(array('name'=>'AndyLau','age'=>48));
    }

    /**
     * 获取表信息
     *
     * 可以获得表的如下信息： 
     * 'schema', 'name', 'cols', 'primary', 'metadata', 'rowClass', 'rowsetClass'
	 * 
     */
    public function infoAction()
    {
        $table = $this->loadTable('testTag1.tbl_test_2');

        // 表的全部信息的数组
        $info_all = $table->info();

        // 包含表的列清单
        $info_cols = $table->info('cols');
    }


    /**
     * 分表测试
     *
     */
    public function tablesAction()
    {
        $db = $this->loadDb('testTag1');

        require('APP/Db/Table.php');
		$id=2;
        $params = array( 'db'=>$db ,'name'=>'tbl_test_'.$id);
        $tbl = new APP_Db_Table($params);
    }

};