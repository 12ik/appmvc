<?php

/*******************************************************************************
	db配置
*******************************************************************************/
	
// tomsui's dev env 

		$_CONFIG['db']['code'] = array(
		'master' => array(
			'dbhost'=>'127.0.0.1',
			'dbname'=>'test',
			'username'=>'root',
			'password'=>'123456'
		),
		'slave' => array(
			'0' => array(
				'dbhost'=>'127.0.0.1',
				'dbname'=>'test',
				'username'=>'root',
				'password'=>'123456'
			)
		)
	);
	/*******************************************************************************
	membercache配置
	*******************************************************************************/
	$_CONFIG['cache'] = array(
	"host"=>"192.168.9.10",
	"port"=>"11211"
	);



/*******************************************************************************
	tpl配置
*******************************************************************************/
$_CONFIG['tpl'] = array(
	"template_dir" => array(APP_PATH ."/View/",'/data/apache/www/template/'),
	"compile_dir" => BASE_PATH . "/Resources/Smarty/compile",
	"config_dir" => BASE_PATH . "/Resources/Smarty/config",
	"cache_dir" => BASE_PATH . "/Resources/Smarty/cache",
	"left_delimiter" => "<!--{",
	"right_delimiter" => "}-->"
);


