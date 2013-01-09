<?
  
class Test_RegisteryController extends APP_Controller_Action
{
    public function preDispatch()
    {
		APP_Registry::set('key1', 'andy');
		APP_Registry::set('key2', 'jacky');
		APP_Registry::set('key2', 'faywang');
	}

    public function testAction()
	{
		echo APP_Registry::get('key1') . '<br />';
		echo APP_Registry::get('key2') . '<br /><pre>';
		print_r(APP_Registry::getAllRegistedData()); echo '</pre><br />';

		var_dump(APP_Registry::isRegistered('key1'));
	}
}
