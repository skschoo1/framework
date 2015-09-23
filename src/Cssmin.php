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

class Cssmin {

	/**
	 * Minifies stylesheet definitions
	 *
	 * @param 	string	$v	Stylesheet definitions as string
	 * @return 	string		Minified stylesheet definitions
	 */
	public static function minify($v)
	{
		$v = trim($v);
		$v = str_replace("\r\n", "\n", $v);
		$search = array("/\/\*[\d\D]*?\*\/|\t+/", "/\s+/", "/\}\s+/");
		$replace = array(null, " ", "}\n");
		$v = preg_replace($search, $replace, $v);
		$search = array("/\\;\s/", "/\s+\{\\s+/", "/\\:\s+\\#/", "/,\s+/i", "/\\:\s+\\\'/i", "/\\:\s+([0-9]+|[A-F]+)/i");
		$replace = array(";", "{", ":#", ",", ":\'", ":$1");
		$v = preg_replace($search, $replace, $v);
		$v = str_replace("\n", null, $v);
		return $v;
	}

}
?>