<?
/**
 *  APP_Db_Table_Row 对象可以通过三种情况获得：
 *    a) APP_Db_Table::createRow()
 *    b) APP_Db_Table::fetchRow()
 *    c) APP_Db_Table_Rowset 中取出
 *
 *  APP_Db_Table_Row的特性是可以数组化访问，例如以下两种赋值都是合法的
 *    $row['name'] = 'xuhe';
 *    $row->name = 'xuhe';
 *
 *  当对一个row对象发生了变化后，可以通过APP_Db_Table_Row::save()方法将数据保存到数据库中
 *    $row->name = 'xuhe';
 *    $row->save();
 *
 *  可以直接调用row对象的delete方法，实现数据库的删除:
 *    $row->delete();
 *
 *  对row的元素值进行赋值时，可以逐一对各元素直接进行访问，也可以通过函数统一赋值
 *    $row->setFromArray(array('name'=>'jeckyChueng','age'=>45));
 *    说明： 如果表没有name字段，则简单的忽略，不会报错。
 *
 *
 */

class Test_RowController extends APP_Controller_Action
{
    public function test2Action()
    {
        $table = $this->loadTable('testTag1.tbl_test_2');

        // 通过查询获取一个row
        $row = $table->fetchRow('id=328');

        // row 字段值数组化访问
        echo $row['name']   . "<br />";        // xuhe
        // row 字段值数组化访问
        echo $row->name   . "<br />";          // xuhe

        // row 更改
        $row['name'] = 'xuhe2009';
        $row->save();

        // row 字段值数组化访问
        echo $row['name']   . "<br />";        // xuhe2009
        // row 字段值数组化访问
        echo $row->name   . "<br />";          // xuhe2009
    }


    public function test1Action()
    {
		// 获取表
        $table = $this->loadTable('testTag1.tbl_test_2');

        // row 新建
       // $row = $tbl->createRow('name'=>'tts');

        // row 赋值
        $row->setFromArray(array('name'=>'jeckyChueng','age'=>45));

		// 更新到数据库
        $row->save();

        // 读取一遍数据库，根据读取内容刷新row对象自身.
        $row->refresh();

        // 删除数据库中的本条记录
		$row->delete();
    }
}
