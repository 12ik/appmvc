<?

class Test_Param1Controller extends APP_Controller_Action
{
	/********************************************************************************
		全部参数
	********************************************************************************/
    public function testAction()
    {
		$params = $this->getParams();
		print_r($params);exit;
		/*
			Array
			(
				[age] => 27
				[name] => xuhe
				[hobby] => chess
			)
		*/
    }

	/********************************************************************************
		单一参数
	********************************************************************************/
    public function test1Action()
    {
		$age = $this->getParam('age');
		echo($age);                              // 27
    }

	/********************************************************************************
		单类参数
	********************************************************************************/
    public function test2Action()
    {
		$params = $this->getParams('USER');
		print_r($params);echo '<br />';         // Array ( ) 
		
		$params = $this->getParams('GET');
		print_r($params);echo '<br />';         // Array ( [hobby] => chess )

    }

	/********************************************************************************
		ACTION 参数
	********************************************************************************/
    public function test11Action()
	{
		$param1 = $this->getParams('ACTION');
		print_r($param1);                      // Array ( [age] => 27 ) 
		echo '<br />';

		$param2 = $this->getActionParamList();
		print_r($param2);                      // Array ( [0] => age [1] => 27 )
	}

	/********************************************************************************
		PATH_INFO 参数
	********************************************************************************/
    public function test12Action()
	{
		$param1 = $this->getParams('PATH_INFO');
		print_r($param1);                      // Array ( [name] => xuhe )
		echo '<br />';

		$param2 = $this->getPathInfoList();
		print_r($param2);                      // Array ( [age] => 27 ) 
	}
}
