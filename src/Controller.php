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
namespace Skschool;

class Controller
{
	
	/**
	 * 视图实例对象
	 * @var 	view
	 * @access  protected
	 */
	protected $view = [];
	
	
	public function __construct() {
		//实例化视图类
		$this->view	= new \Skschool\View();
	}	
	
	
	/**
	 * 模板变量赋值
	 * @access	protected
	 * @param	mixed	   $name   要显示的模板变量
	 * @param	mixed 	   $value  变量的值
	 * @return	Action
	 */
	protected function assign($name,$value='') {
		$this->view->assign($name,$value);
		return $this;
	}	
	
	
	/**
	 * 模板显示 调用内置的模板引擎显示方法，
	 * @access 	protected
	 * @param 	string 	  $templateFile  指定要调用的模板文件
	 * @param 	string 	  $charset 		   输出编码
	 * @return 	void
	 */
	protected function display($templateFile='',$charset=''){
		$module_path = MODULE_NAME != ''?MODULE_NAME.'/':'';
		$templateFile = empty($templateFile)?$module_path.CONTROLLER_NAME.'/'.ACTION_NAME:$module_path.CONTROLLER_NAME.'/'.$templateFile;
		$this->view->display($templateFile,$charset);
	}
	
}
