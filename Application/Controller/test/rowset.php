<?
/**
  *  APP_Db_Table_Rowset 是APP_Db_Table_Row的集合
  *  它实现了IteratorAggregate, ArrayAccess, Countable这三个SPL接口，从而具备了一些额外
  *  的特性，完全可以将其作为数组对待,见下例：
  */
class Test_RowsetController extends APP_Controller_Action
{
    public function testAction()
    {
		// 通过table查询获取一个rowset
        $table = $this->loadTable('testTag1.Games');
        $rowset = $table->fetchAll();
		
		// rowset 可以 foreach 进行遍历
		foreach($rowset as $row)
		{
			echo '<pre>'; print_r($row); echo '</pre><br />';
		}

		// rowset 也可以 for循环，通过数字进行索引
		for($i = 0 ; $i < count($rowset); $i ++)
		{
			echo '<pre>'; print_r($rowset[$i]); echo '</pre><br />';
		}
    }
}

