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
use Skschool\Config;

class Cache {

	private $cache_dir = null;
	private $cache_prefix = null;
	private $cache_time = null;
	private $cache_mode = null;


	/**
	 * 初始化参数
	 * @param	string	$cache_dir		缓存存放的目录
	 * @param	int 	$cache_time		缓存时间
	 * @param	int 	$cache_mod		缓存模式
	 */
	public function __construct() {
		
		$file_cache_time = Config::$_config['file_cache_time'];			//文件缓存时间
		$file_cache_prefix = Config::$_config['file_cache_prefix'];		//文件缓存前缀
		$file_cache_mode = Config::$_config['file_cache_mode'];			//文件缓存模式
		if(empty($file_cache_time)) trigger_error('file_cache_time Not Setting @ Config.php');
		if(empty($file_cache_prefix)) trigger_error('file_cache_prefix Not Setting @ Config.php');
		if(empty($file_cache_mode)) trigger_error('file_cache_mode Not Setting @ Config.php');
		
		$this->cache_dir = DATA_CACHE_PATH;
		$this->cache_prefix = $file_cache_prefix;
		$this->cache_time = $file_cache_time;
		$this->cache_mode = $file_cache_mode;
	}


	/**
	 * 设置缓存
	 * @param string        缓存名
	 * @param array         缓存内容
	 */
	public function set($id,$data){
		if(!isset($id)){
			return false;
		}
		$cache = array(
			'file'	=>	$this->getFileName($id, $this->cache_dir),
			'data'	=>	$data
		);
		return $this->writeCache($cache);
	}


	/**
	 * 获取缓存
	 * @param string $id    缓存名称
	 * @return bool/array
	 */
	public function get($id){
		if(!$this->hasCache($id)){
			return false;
		}
		$data = $this->getCacheData($id);
		return $data;
	}


	/**
	 * 获取缓存目录
	 * @return string
	 */
	public function getCacheDir(){
		return $cache_dir = trim($this->cache_dir,'/');
	}


	/**
	 * 获取完整缓存文件名称
	 * @param	string	$id	缓存名
	 * @return	string
	 */
	public function getFileName($id){
		return $this->getCacheDir().'/'.$this->cache_prefix.$id.'.php';
	}


	/**
	 * 根据缓存文件返回缓存名称
	 * @param type $file
	 */
	public function getCacheName($file){
		if(!file_exists($file)){
			return FALSE;
		}
		$filename = basename($file);
		preg_match('/^'.$this->cache_prefix.'(.*).php$/i', $filename,$matches);
		return $matches[1];
	}


	/**
	 * 写入缓存
	 * @param array $cache  缓存数据
	 */
	public function writeCache($cache = array()){
		if(!is_dir($this->getCacheDir())){
			mkdir($this->getCacheDir(),0777);
		}elseif(!is_writable($this->getCacheDir())){
			chmod($this->getCacheDir(), 0777);
		}

		if($this->cache_mode == 1){
			$content = serialize($cache['data']);
		}else{
			$content  = "<?php\n" .
					" return " .
					var_export($cache['data'], true) .
					";\n";
		}

		if($fp = @fopen($cache['file'], 'w')){
			@flock($fp, LOCK_EX);
			if(fwrite($fp, $content) === false){
				trigger_error('写入缓存失败');
			}
			@flock($fp, LOCK_UN);
			@fclose($fp);
			@chmod($cache['file'], 0777);
			return TRUE;
		}else{
			trigger_error('打开 '.$cache['file'].' 失败！');
			return FALSE;
		}
	}


	/**
	 * 判断缓存是否存在
	 * @param string $id    缓存名
	 * @return boole
	 */
	public function hasCache($id){
		/*检查前删除过期的缓存*/
		if(file_exists($this->getFileName($id))){
			if(time() > filemtime($this->getFileName($id))+$this->cache_time){
				unlink($this->getFileName($id));
			}
		}
		return file_exists($this->getFileName($id)) ?  TRUE :  FALSE;
	}

	/**
	 * 删除一条缓存
	 * @param string $id    缓存名
	 * @return bool
	 */
	public function deleteCache($id) {
		if($this->hasCache($id)){
			return unlink($this->getFileName($id));
		}else{
			trigger_error('缓存不存在');
		}
	}

	/**
	 * 获取缓存数据
	 * @param string $id    缓存名
	 * @return array
	 */
	public function getCacheData($id){
		if(!$this->hasCache($id)){
			return false;
		}
		if($this->cache_mode == 1){
			$fp = @fopen($this->getFileName($id), 'r');
			$data = @fread($fp, filesize($this->getFileName($id)));
			@fclose($fp);
			return unserialize($data);
		}else{
			return include $this->getFileName($id);
		}
	}

	/**
	 * 清空缓存
	 * @return bool
	 */
	public function flushCache(){
		$glob = @glob($this->getCacheDir().'/'.$this->cache_prefix.'*');
		if($glob){
			foreach($glob as $item){
				$id = $this->getCacheName($item);
				$this->deleteCache($id);
			}
		}
		return true;
	}

}