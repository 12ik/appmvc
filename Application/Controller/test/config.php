<?
class Test_ConfigController extends APP_Controller_Action
{

	/**
	 * 获取主配置文件数组
	 *
	 * 主配置文件位置  : /Configs/site.config.php
	 * 配置数组名必须为 : $_CONFIG
	 */
    public function indexAction()
    {
		$config  = APP::loadConfig();
		print_r($config);
	}


	/**
	 * 获取模块配置文件数组
	 *
	 * 以vote模块为例：
	 * a. 主配置文件位置  : /Configs/module.config.vote.php
	 * b .配置数组名必须为 : $_CONFIG_VOTE
	 *
	 */
    public function index2Action()
    {
		$config  = APP::loadConfig('vote');
		print_r($config);
	}
}

