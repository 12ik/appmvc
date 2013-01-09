<?

class Test_TplController extends APP_Controller_Action
{
    public function indexAction()
    {
		$tpl_1 = $this->loadTpl();
		$tpl_1->assign('name','xuhe');
		$tpl_1->display('test/helloworld.html');

//		$this->show();
    }

	private function show()
	{
		$tpl_2 = $this->loadTpl();
		$tpl_2->assign('name','ycs');
		$tpl_2->display('test/helloworld.html');
	}

    public function test2Action()
	{
		$config = APP::loadConfig();
		$smarty = $this->loadTpl(); 
		
		print_r($smarty);exit;
	}

    public function paramsAction()
	{
		$p11 = $this->getParams();
		print_r($p11);exit;
		$p = $this->getRequest()->getParams();
		print_r($p);exit;
	}
}
