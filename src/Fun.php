<?php
/**
 // +-------------------------------------------------------------------
 // | SKPHP [ 为web梦想家创造的PHP框架。 ]
 // +-------------------------------------------------------------------
 // | Copyright (c) 2012-2016 http://sk-school.com All rights reserved.
 // +-------------------------------------------------------------------
 // | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 // +-------------------------------------------------------------------
 // | Author:
 // | seven <seven@sk-school.com>
 // | learv <learv@foxmail.com>
 // | ppogg <aweiyunbina3@163.com>
 // +-------------------------------------------------------------------
 // | Knowledge change destiny, share knowledge change you and me.
 // +-------------------------------------------------------------------
 // | To be successful
 // | must first learn To face the loneliness,who can understand.
 // +-----------------------------------------------------------------*/
use Skschool\Jsmin;
use Skschool\Cssmin;
use Skschool\Cache;
use Skschool\Config;
use Skschool\View;

//SKPHP 系统函数库


/**
 * 格式化打印数据
* @param  mixed
* @return mixed
*/
function p($data) {
	echo '<pre>';
	print_r($data);
	echo '<pre>';
}


/**
 * URL
 * @param  string	$url
 * @return
 */
function URL($param) {
	$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$url = 'http://'.$_SERVER['HTTP_HOST'].substr($PHP_SELF,0,strrpos($PHP_SELF, '/')+1);
	$url = substr($url, '-1') == '/' ? substr($url, 0, strlen($url)-1): $url;
	return $url.$param;
}


/**
 * C 读取配置文件
 * @param  string	$name
 * @param  mixed	$value
 * @return
 */
function C($name='', $value='') {
	if(!empty($name))
	{
		$Config = new \Skschool\Config();
		if(empty($value))
		{
			$rs = $Config->get($name);
		}else{
			$rs = $Config->set($name, $value);
		}
		return $rs;
	}else{
		trigger_error('IS NOT SETTING NAME');
	}
}


/**
 * WC 写入读取数据缓存
 * @param  string	$name
 * @param  mixed	$value
 * @return
 */
function WC($name='', $value='') {
	if(!empty($name))
	{
		$cache = new \Skschool\Cache();
		if(empty($value))
		{
			$rs = $cache->get($name);
		}else{
			$rs = $cache->set($name, $value);
		}
		return $rs;
	}else{
		trigger_error('IS NOT SETTING NAME');
	}
}


/**
 * DB方法用于实例化一个没有模型文件的Model
 * @param  mixed   $connection	 数据库连接信息（服务器IP#端口#用户#密码#数据库名#编码）
 * @return Model
 */
function DB($connection='')
{
	$mysql =  Application::$_instance['mysql'];
	
	// 自定义
	if(!empty($connection))
	{
		$arr_db_info = explode('#', $connection);
		$mysql->init($arr_db_info[0], $arr_db_info[1], $arr_db_info[2], $arr_db_info[3], $arr_db_info[4], $arr_db_info[5]);
		return $mysql;
	}

	// 默认
	$db_host = Config::$_config['host'];
	$db_user = Config::$_config['username'];
	$db_pwd = Config::$_config['password'];
	$db_database = Config::$_config['database'];
	$db_charset = Config::$_config['charset'];
	$db_port = '3306';
	if($db_host != '' && $db_user != '' &&  $db_database != '' && $db_charset != '')
	{
		$mysql->init($db_host, $db_port, $db_user, $db_pwd, $db_database, $db_charset);
		return $mysql;
	}else{
		trigger_error('DATABASES DONT CONFIG');
	}
}


/**
 * JS压缩处理
 * @param	string	$src
 * @return
 */
function minJs($src='',$md5_name='') {
	if(empty($src)) trigger_error('JS CONFIG ERROR');
	
	if (strpos($src, '|'))
	{
		$src_content_size = 0;
		$arr_src = explode('|', $src);
		foreach ($arr_src as $v)
		{
			$src_content_size += date("YmdHis", filemtime(APP_ROOT.$v));
		}
	}else {		
		$src_content_size = date("YmdHis", filemtime(APP_ROOT.$src));
	}
	
	$cache_src = STYLE_CACHE_PATH . md5($src.$md5_name.$src_content_size) . '.js';
	if(!file_exists($cache_src)) 
	{
		if (!is_dir(STYLE_CACHE_PATH)) mkdir(STYLE_CACHE_PATH, 0777);
		if (strpos($src, '|'))
		{
			$src_content = '';
			$arr_src = explode('|', $src);
			foreach ($arr_src as $v)
			{
				$src_content .= file_get_contents(APP_ROOT.$v);
			}
		}else {
			$src_content = file_get_contents(APP_ROOT.$src);
		}
		$cache_content = Jsmin::minify($src_content);
		file_put_contents($cache_src, $cache_content);
	}
	return $cache_src;
}


/**
 * CSS压缩处理
 * @param	string	$src
 * @return
 */
function minCss($src='',$md5_name='') {
	if(empty($src)) trigger_error('CSS CONFIG ERROR');
	
	if (strpos($src, '|'))
	{
		$src_content_size = 0;
		$arr_src = explode('|', $src);
		foreach ($arr_src as $v)
		{						
			$src_content_size += date("YmdHis", filemtime(APP_ROOT.$v));
		}
	}else {
		$src_content_size = date("YmdHis", filemtime(APP_ROOT.$src));
	}
	
	$cache_src = STYLE_CACHE_PATH . md5($src.$md5_name.$src_content_size) . '.css';
	if(!file_exists($cache_src))
	{
		if (!is_dir(STYLE_CACHE_PATH)) mkdir(STYLE_CACHE_PATH, 0777);
		if (strpos($src, '|'))
		{
			$src_content = '';
			$arr_src = explode('|', $src);
			foreach ($arr_src as $v)
			{
				$src_content .= file_get_contents(APP_ROOT.$v);
			}
		}else {
			$src_content = file_get_contents(APP_ROOT.$src);
		}
		$cache_content = Cssmin::minify($src_content);
		file_put_contents($cache_src, $cache_content);
	}
	return $cache_src;
}

/**
 * 调用视图
 * @param	string	$file
 * @return
 */
function view($templateFile='',$charset='') {
	define('MODULE_NAME', '');
	define('CONTROLLER_NAME', '');
	define('ACTION_NAME', '');
	$View = new \Skschool\View();
	return $View->display($templateFile);
}
