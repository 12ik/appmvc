<?
/**
 *  全站异常处理文件
 *
 *  采用APP框架时，全站所有最终未被捕获的异常都会转向本文件进行处理。异常分为两类，MVC异常
 *  和用户异常, 分别对应下面两个方法. 前者是网友访问不存在的url时自动转向的处理代码, 后者
 *  是由程序员编写的应用程序抛出异常后却没有被成功捕获(引起这一原因很可能是应用程序的编写存在
 *  疏忽, 当然也可以有意识的主动利用这种机制，例如通过抛出未捕获异常而进入此分支做全站统一处理,
 *  例如资源回收,日志记录等).
 *
 *  php开发人员需要在相应的两个函数内填入异常处理代码. 这两个函数会在异常未被捕获时自动被
 *  APP框架根据情况调用其中之一, 函数的3个参数也由APP框架自动传入.
 *
 */

class ErrorHandler
{
	/**
	 * 处理MVC异常
	 *
	 * MVC异常是由错误的外界url访问引起的, 引起原因有请求的module, controller, action,
	 * 不存在; 或者url的action的参数数量与定义不符。这种情况下通常是转向一个页面，同时设置
	 * response状态码为404.
	 *
	 * @param $exception  当前异常
	 * @param $request    发生异常时的request请求
	 * @param $response	  发生异常时尚未发出的response
	 *
	 */
	public function MvcError($exception, $request , $response)
	{
		print_r($exception);exit;
		//print_r($exception);exit;
//		$this->log($exception, $request, $response, 'UserError');
	}

	/**
	 * 处理用户异常
	 *
	 * 下面的代码处理由程序员编写的应用程序抛出异常后却没有被成功捕获
	 *
	 * @param $exception  当前异常
	 * @param $request    发生异常时的request请求
	 * @param $response	  发生异常时尚未发出的response
	 *
	 */
	public function UserError($exception, $request, $response)
	{
		AlaException::displayError($exception);exit;
//		$this->log($exception, $request, $response, 'UserError');
	}

	private function log($exception, $request , $response, $prefix='errorLog'){
		$logDir = BASE_PATH . '/Resources/Logs/'; 
		$logName = $prefix. '-'. date("Y-m-d", time()). '.php';
		$logFile = $logDir . $logName ;

		static $SEPERATOR = "---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------\n";
		
		$logDir = BASE_PATH . '/Resources/Logs/'; 
		$logName = $prefix. '-'. date("Y-m-d", time()). '.php';
		$logFile = $logDir . $logName ;
		

		$content = $SEPERATOR;
		$content .= date('Y-m-d H:i:s', time()) . "\n";
		$content .= $SEPERATOR;

		$content .= $exception."\n"; 
		ob_start();
		echo $SEPERATOR;
		//print_r($exception);
		print_r($request);
		//print_r($response);
		$content .= ob_get_contents();
		ob_end_clean();
		$content .= "\n\n";

 		APP_File::write($logFile, $content, 'a'); 

	}
}

