<?php
class APP_Controller_Error
{
	public static function HandleException($request, $response)
	{
		$exception = array_pop($response->getExceptions());

		$error->exception = $exception;

		if( get_class($exception) == 'APP_Controller_Exception'){
			$action = 'MvcError';
		}
		else{
			$action = 'UserError';
		}
		require(APP_PATH. '/Controller/error.php');
		$handler = new ErrorHandler;

		$handler->$action($exception, $request, $response);
	}
}