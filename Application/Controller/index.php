<?php
/**
 * app
 * @author  xuhe
 * @version $Id: index.php v1.0 2010-11-04 14:08:04 xuhe $
 */
class IndexController extends APP_Controller_Action
{
    public function preDispatch()
    { 
        $this->Model = APP_Controller_Action::loadModel('indexModel');
        $this->tpl = $this->loadTpl();
    }
    public function indexAction()
    {
    	echo "hello world!";
	}
	
}
