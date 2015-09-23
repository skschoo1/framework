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

class Routing
{
	
	/**
	 * @var array Conditions for this route's URL parameters
	 */
    public static $routes = [];
    
    
    /**
     * @var array HTTP methods supported by this Route
     */
    public static $methods = [];
    
    
    /**
     * @var mixed The route callable
     */
    public static $callbacks = [];

    
    /**
     * @var array Convert URL params into regex patterns
     */
    public static $patterns = [
        ':num' => '[0-9]+',
        ':all' => '.*'
    ];
    
    
    /**
     * @var mixed Defines callback if route is not found
     */
    public static $error_callback;

    
    /**
     * 定义路由/回调和方法
     *
     * @param  string  $uri
     * @param  string  $method
     * @param  mixed   $params
     * @return void
     */
    public static function __callstatic($method, $params) 
    {
        $uri = $params[0];
        $callback = $params[1];
        
        if ( $method == 'any' ) {
        	self::pushToArray($uri, 'get', $callback);
        	self::pushToArray($uri, 'post', $callback);
        } else {
        	self::pushToArray($uri, $method, $callback);
        }
    }
    
    
    /**
     * Push route items to class arrays
     * @return void
     */
    private static function pushToArray($uri, $method, $callback)
    {
    	array_push(self::$routes, $uri);
    	array_push(self::$methods, strtoupper($method));
    	array_push(self::$callbacks, $callback);
    }
    
    
    /**
     * detect true URI, inspired by CodeIgniter 2
     * @return void
     */
    private static function detect_uri()
    {
    	$uri = $_SERVER['REQUEST_URI'];
    	if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
    		$uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
    	} elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
    		$uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
    	}
    	if ($uri == '/' || empty($uri)) return '/'; 
    	$uri = parse_url($uri, PHP_URL_PATH);
    	return str_replace(array('//', '../'), '/', trim($uri));
    }
    
    
    /**
     * 路由派遣
     * @return void
     */
    public static function dispatch()
    {
		$uri = self::detect_uri();
        $method = $_SERVER['REQUEST_METHOD']; 

        $searches = array_keys(static::$patterns);
        $replaces = array_values(static::$patterns);
        $is_find_route = false;

        if (in_array($uri, self::$routes)) 
        {
            $arr_route_pos = array_keys(self::$routes, $uri);
            $pos = $arr_route_pos[0];
            if (self::$methods[$pos] == $method) 
 			{
 				$is_find_route = true;
                if(!is_object(self::$callbacks[$pos]))
                {
                	self::execute($pos,$uri,''); 
				} else {
					call_user_func(self::$callbacks[$pos]);die;
				}
			}			
        } else {         	
            foreach (self::$routes as $pos=>$route) 
            {
				if (strpos($route, ':') !== false) $route = str_replace($searches, $replaces, $route);
				if (preg_match('#^' . $route . '$#', $uri, $matched)) 
				{
                    if (self::$methods[$pos] == $method) 
                    {
                        $is_find_route = true;
                        array_shift($matched);
                        if(!is_object(self::$callbacks[$pos]))
                        {
                        	self::execute($pos,$uri,$matched);    
                        } else {
                            call_user_func_array(self::$callbacks[$pos], $matched);die;
                        }
                    }
                }
            }
        }
 
        // run the error callback if the route was not found
        if ($is_find_route == false) 
        {
            if (empty(self::$error_callback)) 
            {
                self::$error_callback = function() {
                    header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
                    echo '404';
                };
            }
            call_user_func(self::$error_callback);
        }
    }
    
    
    /**
     * 执行
     * @return void
     */
    private static function execute($pos,$uri,$matched)
    {
    	$parts = explode('/',self::$callbacks[$pos]);
    	$last = end($parts);
    	$segments = explode('@',$last);
    	$objName = '\App\Controllers\\'.$segments[0];
    	$controller = new $objName();
    	if(gettype($controller) != 'object')  trigger_error('Not Find Controller '.$segments[0]);
    	if(!method_exists($controller,$segments[1]))  trigger_error('Undefined Function '.$segments[1].' @ '.$segments[0]);
    
    	// View Setting
    	if(strpos($segments[0], '\\'))
    	{
    		$arrDefine = explode('\\', $segments[0]);
    		define('MODULE_NAME', $arrDefine[0]);
    		define('CONTROLLER_NAME', str_replace('Controller', '', $arrDefine[1]));
    	}else{
    		define('MODULE_NAME', '');
    		define('CONTROLLER_NAME', str_replace('Controller', '', $segments[0]));
    	}    	
    	define('ACTION_NAME', $segments[1]);
    			
    	// pass any extra parameters to the method 
    	if(!empty($matched))
    	{
    		preg_match_all('/\(:[^:]+\)/',self::$routes[$pos],$getMatches);
    		if(!empty($getMatches[0]))
    		{
    			foreach ($getMatches[0] as $getMatchesK=>$getMatchesV)
    			{
    				$getKey = str_replace(')','',str_replace('(:','',$getMatchesV));
    				if(!empty($getKey)) $_GET[$getKey] = $matched[$getMatchesK];
    			}
    			unset($_GET[$uri]);
    		}
    	}
    
    	//call method
    	$controller->$segments[1]();
    }
    
}
