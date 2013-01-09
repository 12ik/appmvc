<?
class Test_ModelController extends APP_Controller_Action
{
    public function test1Action(){
		$test = $this->loadModel('Test_TblModel');
//		print_r($test);exit;
		$test->test1();
    }
}

