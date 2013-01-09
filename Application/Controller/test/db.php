<?
class Test_DbController extends APP_Controller_Action
{
	/**
	 * 生成db对象方法一: 直接生成 
	 *
	 * 在Action中直接给出数据库具体参数，调用APP_Db工厂函数
	 *
	 * 说明：强烈不建议使用此方法生成db,仅示范性展示，
	 */
    public function test1Action()
    {
        require('APP/Db.php');
		/*
		$db_params = array(
			'dbhost'=>'127.0.0.1',              // 必填
			'dbname'=>'test',                   // 必填
			'username'=>'root',                 // 必填
			'password'=>'',                     // (选填)
			'port'=>'3306',                     // (选填)
			'persistent'=>false,                // (选填)  
			'caseFolding'=>PDO::CASE_NATURAL    // (选填) PDO::CASE_LOWER , PDO::CASE_UPPER, PDO::CASE_NATURAL
		);
		*/

		$db_params = array(
			'master' => array(
							'dbhost'=>'localhost',
							'dbname'=>'test',
							'username'=>'root',
							'password'=>''
			),
			'slave' => array(
				'0' => array(
							'dbhost'=>'localhost',
							'dbname'=>'test',
							'username'=>'root',
							'password'=>''
				 ),
				'1' => array(
							'dbhost'=>'localhost',
							'dbname'=>'test',
							'username'=>'root',
							'password'=>''
				),
				'2' => array(
							'dbhost'=>'localhost',
							'dbname'=>'test',
							'username'=>'root',
							'password'=>''
				)
			)
		);
	
		$db = new APP_Db($db_params);

		echo '<pre>'; print_r($db);	echo '</pre>';
	}

	/**
	 * 生成db对象方法二: 控制器方法 
	 *
	 * 用法：需要在/Configs/site.config.php中写入配置数组，例如
	 *		$_CONFIG['db']['test1'] = array(                   
	 *									'host'=>'localhost',
     *									'dbname'=>'test',     
	 *									'username'=>'root',   
	 *									'password'=>''        
	 *								);
	 * 说明： a. 建议使用该方法，这是在MVC控制器中加入的快捷方法，将生成的$db静态注册给
	 *			APP_Registery,从而保持db实例单例
	 *       b. 'test1' 作为APP_Controller_Action::loadDb($dbTag)的参数,唯一标示
	 *          了其代表的所指定server的具体database
	 */
    public function test2Action()
    {
		$db = $this->loadDb('test1');

		echo '<pre>'; print_r($db);	echo '</pre>';
	}

	/**
	 * APP_Db::query($sql)做直接查询
	 *
	 * $sql 必须为查询性质语句，是select,desc,show之一，否则会抛出异常.
	 *      APP_Db::exec($sql)与该方法相对，执行更改性质的语句

	 * 说明：不建议使用该方法直接拼装sql语句, 
	 *	    如果确要直写sql,请采用APP_Db的fetch系列方法.
	 *      如无直写sql必要，请使用APP_Db_Table的OOP形式
	 */
    public function test3Action()
    {
		$db = $this->loadDb('test1');
		$sql = 'select * from Games';

        // 返回一个PDOStatement对象
		$stmt = $db->query($sql);
        
		// 后续采用PDO方法做直接处理
		$data = $stmt->fetchAll();
		print_r($data);
	}

	/**
	 * APP_Db::query($sql)做直接查询
	 *
	 * $sql 执行更改性质的语句
	 *      

	 * 说明：不建议使用该方法直接拼装sql语句, 
	 *	    如果确要直写sql,请采用APP_Db在本文档后续提供的方法.
	 *      如无直写sql必要，请使用APP_Db_Table的OOP形式
	 */
    public function test4Action()
    {
		$db = $this->loadDb('test1');
		$sql = 'delete from Games where id = 132';

        // 返回一个PDOStatement对象
		$stmt = $db->query($sql);
        print_r($stmt);exit;
		// 后续采用PDO方法做直接处理
		// ...
	}


	/**
	 * APP_Db::fetch系列函数
	 *
	 * $sql 执行select查询动作
	 *      

	 * 说明：不建议使用该方法直接拼装sql语句, 
	 *	    如果确要直写sql,请采用APP_Db在本文档后续提供的方法.
	 *      如无直写sql必要，请使用APP_Db_Table的OOP形式
	 *      关于这些函数的更多用法参见api文档
	 */
    public function test5Action()
    {
		$db = $this->loadDb('test1');

        // 返回全部结果集，以递增的数字索引数组(默认下)
        $data1 = $db->fetchAll('select * from Games');

        // 返回全部数据，数组的key是标示主键
//        $data2 = $db->fetchAssoc('select * from Games');

        // 返回结果集第一行数据
        $data3 = $db->fetchRow('select * from Games');

        // 返回全部结果集的第一列数据(以数组形式)
        $data4 = $db->fetchCol('select * from Games');

		// 返回全部结果集的前两列，第一列作为key
//        $data5 = $db->fetchPairs('select * from Games');

        // 仅返回第一行第一列
        $data6 = $db->fetchOne('select * from Games');

	    print_r($data4);
	}


	/**
	 * APP_Db::insert
	 *
	 * 插入一条记录
	 */
    public function test6Action()
    {
		$db = $this->loadDb('test1');

		$num = $db->insert('Games', array('GameName'=>'测试'));
        echo $num . '<br />';

        $id = $db->lastInsertId();
        echo $id . '<br />';
	}

	/**
	 * APP_Db::update
	 *
	 * 修改一条或多记录
	 */
    public function test7Action()
    {
		$db = $this->loadDb('test1');

        $num = $db->update('Games', array('GameName'=>'hello','GameWidth'=>'100'), 'id=145');
        echo $num .'<br />';

        $num = $db->update('Games', array('GameName'=>'hello2'), 'id=127 or id=146');
        echo $num .'<br />';
	}


	/**
	 * APP_Db::update
	 *
	 * 删除一条或多记录
	 */
    public function test8Action()
    {
		$db = $this->loadDb('test1');

        $num = $db->delete('Games', 'id=14');
        echo $num .'<br />';
	}

	/**
	 * APP_Db::listTables()
	 *
	 * 返回一个数字索引的表名数组
	 */
    public function test9Action()
    {
		$db = $this->loadDb('test1');

        $tables = $db->listTables();
        echo '<pre>' ; print_r($tables) ; '</pre>';
	}

	/**
	 * APP_Db::describeTable($tbl)
	 *
	 * 返回一个数字索引的表名数组
	 */
    public function test10Action()
    {
		$db = $this->loadDb('test1');

        $tbl_info = $db->describeTable('Games');
        echo '<pre>' ; print_r($tbl_info) ; '</pre>';
	}

	/**
	 * APP_Db::beginTransaction()
	 *
	 * 事务处理
	 *
	 * 注意： MyISAM不支持事物.
	 */
    public function test11Action()
    {
		$db = $this->loadDb('test1');

		$db->beginTransaction(); 
		echo 'begin transaction : <br />';

		try { 
			$data1 = $db->fetchAll('select * from Games');
			echo 'querying now.<br />';
			$db->commit(); 
			echo 'commit the query.<br />';
		} 
		catch (Exception $e) 
		{
			echo 'transaction fails , roll back. <br />';
			$db->rollBack(); 
			echo $e->getMessage(); 
		}
	}

	public function test12Action()
	{
		$db = $this->loadDb('test1');
		$value = $db->quote('St John"s Wort');
		echo $value . '<br />';

		$value = $db->quote(array('a', 'b', 'c')); 
		echo $value . '<br />';
	}

}

