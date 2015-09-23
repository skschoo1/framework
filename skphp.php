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
use Skschool\Config;
use Skschool\Routing;

// 系统常量定义
define('APP_ROOT', dirname(dirname($_SERVER['SCRIPT_FILENAME']))."/");	// 应用根目录
define('VIEW_PATH', 			APP_ROOT."app/views/");
define('VIEW_CACHE_PATH', 		  APP_ROOT."tmp/tpl/");		// 应用模板缓存目录
define('STYLE_CACHE_PATH',     APP_ROOT.'tmp/assets/'); 	// 应用样式缓存目录
define('DATA_CACHE_PATH',        APP_ROOT.'tmp/data/'); 	// 应用样式缓存目录
define('LOG_PATH',       		 APP_ROOT.'tmp/logs/'); 	// 应用日志目录
define('SKPHP_CORE', 		dirname(__FILE__)."/src/");		// SKPHP核心目录

// 定义当前请求的系统常量
define('NOW_TIME',      $_SERVER['REQUEST_TIME']);
define('REQUEST_METHOD',$_SERVER['REQUEST_METHOD']);
define('IS_GET',        REQUEST_METHOD =='GET' ? true : false);
define('IS_POST',       REQUEST_METHOD =='POST' ? true : false);


class Application 
{
	
	/**
	 *  实例化对象（静态变量保存全局实例）
	 * @access	public
	 * @return 	void
	 */
	static public $_instance = [];
	
	
	/**
	 * 初始化
	 * @access	public
	 * @return 	void
	 */
	static public function init() {
		require APP_ROOT . 'vendor/autoload.php';
		$model_path = APP_ROOT.'config/model.php';
		if(file_exists($model_path)) require $model_path;
		require SKPHP_CORE . 'Mysql.php';
		require SKPHP_CORE . 'Fun.php';
		self::$_instance['mysql'] =  Mysql::getInstance();
		// 加载应用配置和方法
		self::mergeConfig();
		if(empty(Config::$_config['charset'])) trigger_error('IS Not Setting charset @ config.php');
		header("Content-type:text/html;charset=".Config::$_config['charset']);
	}
	

	/**
	 * 合并配置文件
	 * @access	public
	 * @return 	void
	 */
	static public function mergeConfig() {
		$config_path1 = APP_ROOT.'config/config.php';
		if(!file_exists($config_path1)) trigger_error('Not Find '.$config_path1);
		$config_path2 = APP_ROOT.'config/databases.php';
		if(!file_exists($config_path2)) trigger_error('Not Find '.$config_path2);
		$config = array_merge(require $config_path1, require $config_path2);
		if(!empty($config['load_ext_config']))
		{
			$arr_config = explode(',', $config['load_ext_config']);
			foreach ($arr_config as $v)
			{
				$tmp = APP_ROOT.'config/'.$v.'.php';
				if(file_exists($tmp))
				{
					$config = array_merge($config, require $tmp);
				}else{
					trigger_error('Not Find '.$tmp.', Beacuse load_ext_config setting.');
				}
			}
		}
		Config::$_config = $config;
				
		$routes_path = APP_ROOT.'config/routes.php';
		if(!file_exists($routes_path)) trigger_error('Not Find '.$routes_path);
		require $routes_path;
	}
	
	
	/**
	 * 创建应用
	 * @access	public
	 * @param	array	$config
	 */
	static public function run()
	{
		// 设定错误和异常处理
		register_shutdown_function('Application::fatalError');
		set_error_handler('Application::appError');
		set_exception_handler('Application::appException');		

		// init
		self::init();

		Routing::dispatch();
	}
	
	
	/**
	 * 错误输出
	 * @param mixed $error 错误
	 * @return void
	 */
	static public function halt($error) {
		$e = array();
		if (APP_DEBUG) {
			//调试模式下输出错误信息
			if (!is_array($error)) {
				$trace          = debug_backtrace();
				$e['message']   = $error;
				$e['file']      = $trace[0]['file'];
				$e['line']      = $trace[0]['line'];
				ob_start();
				debug_print_backtrace();
				$e['trace']     = ob_get_clean();
			} else {
				$e              = $error;
			}
		} else {
			$error_page = Config::$_config['error_page'];	//否则定向到错误页面
			if (!empty($error_page)) {
				header("Location: " . $error_page);exit;
			} else {
				$message        = is_array($error) ? $error['message'] : $error;
				$e['message']   = false ? $message : '页面错误！请稍后再试～';
			}
		}
		// 包含异常页面模板
		$exceptionFile =  SKPHP_CORE.'Error/sk_exception.tpl';
		include $exceptionFile;
		exit;
	}
	
		
	/**
	 * 致命错误捕获
	 * @desc error_get_last - 函数获取最后发生的错误
	 * 返回的数组包含 4 个键和值：
	 * [type] 	 -  错误类型
	 * [message] -  错误消息
	 * [file] 	 -  发生错误所在的文件
	 * [line] 	 -  发生错误所在的行
	 */
	static public function fatalError() {
		if (@$e = error_get_last()) {
			switch($e['type']){
				case E_ERROR:
				case E_PARSE:
				case E_CORE_ERROR:
				case E_COMPILE_ERROR:
				case E_USER_ERROR:
					ob_end_clean();
					self::halt($e);
					break;
			}
		}
	}	
	
	
	/**
	 * 自定义错误处理
	 * @access public
	 * @param 	int 	$errno		错误类型
	 * @param 	string	$errmsg		错误信息
	 * @param 	string 	$errfile	错误文件
	 * @param 	int 	$errline	错误行数
	 * @return	void
	 */
	static public function appError($errno='', $errmsg='', $errfile='', $errline='') {
		if (!APP_DEBUG)
		{
			$error_page = Config::$_config['error_page'];
			if (!empty($error_page)) {
				header("Location: " . $error_page);exit;
			} else {
				$errmsg = 'APP_DEBUG CLOSE, Please setting redirect URL';
			}
		}
		@ob_end_clean();
		$e['message']   = $errmsg;
		$e['file']      = $errfile;
		$e['line']      = $errline;
		$exceptionFile =  SKPHP_CORE.'Error/sk_exception.tpl';
		include $exceptionFile;
		exit;
	}
		
	
	/**
	 * 自定义异常处理
	 * @access 	public
	 * @param 	mixed $e 异常对象
	 */
	static public function appException($e) {
		$error = array();
		$error['message']   =   $e->getMessage();
		$trace              =   $e->getTrace();
		if('E'==$trace[0]['function']) {
			$error['file']  =   $trace[0]['file'];
			$error['line']  =   $trace[0]['line'];
		}else{
			$error['file']  =   $e->getFile();
			$error['line']  =   $e->getLine();
		}
		$error['trace']     =   $e->getTraceAsString();
		
		header('HTTP/1.1 404 Not Found');
		header('Status:404 Not Found');
		self::halt($error);
	}
	
}


// 应用初始化
Application::run();