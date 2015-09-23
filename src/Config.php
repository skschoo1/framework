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

class Config
{
    /**
     * 核心配置
     * @var array  public protected
     */

    static public $_config = [];
    

    /**
     * Get the specified configuration value.
     *
     * @param  string  $key
     * @return mixed
     */
    static public function get($key)
    {
        return empty(self::$_config[$key])?'':self::$_config[$key];
    }

    /**
     * Set a given configuration value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
     * @return void
     */
    static public function set($key, $value = null)
    {
    
    	$file = APP_ROOT . 'config/config.php';
    	$config = array_merge(include $file, array_change_key_case(array($key=>$value), CASE_LOWER));
    	$str = "<?php\r\nreturn " . str_replace('array (', '[', substr(var_export($config, true), 0, strlen(var_export($config, true))-1)) . "];\r\n?>";
    	if (file_put_contents($file, $str)) {
			return true;
		} else {
			return false;
		}
    }

    /**
     * Get all of the configuration items for the application.
     *
     * @return array
     */
    static public function all()
    {
        return self::$_config;
    }
    
}
