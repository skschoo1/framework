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

class View {
	
	
	/**
	 * 模板输出变量
	 * @var data
	 * @access protected
	 */
	public $data = [];
	
	
	/**
	 * 模板名
	 * @var theme
	 * @access protected
	 */
	public $template_name = '';
		
	
	/**
	 * 模板变量赋值
	 * @access public
	 * @param mixed $name
	 * @param mixed $value
	 */
	public function assign($name,$value=''){
		if(is_array($name)) {
			$this->data   =  array_merge($this->data,$name);
		}else {
			$this->data[$name] = $value;
		}
	}
	
	
	/**
	 * 加载模板和页面输出 可以返回输出内容
	 * @access public
	 * @param string $templateFile 模板文件名
	 * @param string $charset 模板输出字符集
	 * @return mixed
	 */
	public function display($templateFile='',$charset='') {
		$content = $this->fetch($templateFile);	// 解析并获取模板内容
		$this->render($content,$charset);		// 输出模板内容
	}
	
	
    /**
     * 解析和获取模板内容 用于输出
     * @access 	public
     * @param	string 	$templateFile 模板文件名
     * @return 	string
     */
	public function fetch($templateFile) {
		$view_file = VIEW_PATH . strtolower($templateFile) . '.php';
		if (file_exists($view_file)) 
       	{
       		$filetime = date("YmdHis", filemtime($view_file));
       		$view_file_cache = VIEW_CACHE_PATH . md5(MODULE_NAME.CONTROLLER_NAME.ACTION_NAME.$view_file.$filetime) . '.php';
			if (!file_exists($view_file_cache)) $this->writeTplCache($view_file_cache,file_get_contents($view_file));
			extract($this->data);
			ob_start();
        	ob_implicit_flush(0);
            include $view_file_cache;
			$content = ob_get_contents();
			ob_end_clean();
            return $content;
		} else {
			trigger_error('加载 ' . $view_file . ' 模板不存在');
		}
	}
	
	
	/**
	 * 输出内容文本可以包括Html
	 * @access private
	 * @param string $content 输出内容
	 * @param string $charset 模板输出字符集
	 * @param string $contentType 输出类型
	 * @return mixed
	 */
	private function render($content,$charset='',$contentType=''){
		if(empty($charset))  $charset = 'utf-8';
		if(empty($contentType)) $contentType = 'text/html';
		header('Content-Type:'.$contentType.'; charset='.$charset); // 网页字符编码
		header('Cache-control: private');  							// 页面缓存控制
		header('X-Powered-By:SKPHP');
		echo $content;die;	// 输出模板文件
	} 
	
	
	/**
	 * 写入静态化文件
	 * @param 	$view_file_cache  模版缓存地址
	 * @param	$content   		      内容
	 * @access	public
	 */
	public function writeTplCache($view_file_cache,$content) {
		if (!is_dir(VIEW_CACHE_PATH)) mkdir(VIEW_CACHE_PATH, 0777);
		if (!$fp = @fopen($view_file_cache, 'w'))  trigger_error('文件 ' . $view_file_cache . ' 不能打开');
		$content = $this->replaceTag($content);
		if (fwrite($fp, $content) == FALSE) trigger_error('文件 ' . $view_file_cache . ' 写入失败'); 
		fclose($fp);
	}
	
	
	/**
	 * 标签替换
	 * @param string $content
	 */
	private function replaceTag($content)
	{
		$md5_name = md5(MODULE_NAME.CONTROLLER_NAME.ACTION_NAME);
		// js 
		$content = preg_replace('/<js\s*src=[\'"]{1}/i','<script src="<?php echo minJs(\'',$content);
		$content = preg_replace('/[\'"]{1}\s*>\s*<\/js>/i','\',\''.$md5_name.'\'); ?>" ></script>',$content);
		// css
		$content = preg_replace('/<css\s*src=[\'"]{1}/i','<link rel="stylesheet" href="<?php echo minCss(\'',$content);
		$content = preg_replace('/[\'"]{1}\s*>\s*<\/css>/i','\',\''.$md5_name.'\'); ?>" />',$content);
		return $content;
	}
	
	
	
}