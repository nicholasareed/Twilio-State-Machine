<?php
/**
 * This file is loaded automatically by the app/webroot/index.php file after the core bootstrap.php
 *
 * This is an application wide file to load any function that is not used within a class
 * define. You can also use this to include or require any files in your application.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.app.config
 * @since         CakePHP(tm) v 0.10.8.2117
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * The settings below can be used to set additional paths to models, views and controllers.
 * This is related to Ticket #470 (https://trac.cakephp.org/ticket/470)
 *
 * App::build(array(
 *     'plugins' => array('/full/path/to/plugins/', '/next/full/path/to/plugins/'),
 *     'models' =>  array('/full/path/to/models/', '/next/full/path/to/models/'),
 *     'views' => array('/full/path/to/views/', '/next/full/path/to/views/'),
 *     'controllers' => array('/full/path/to/controllers/', '/next/full/path/to/controllers/'),
 *     'datasources' => array('/full/path/to/datasources/', '/next/full/path/to/datasources/'),
 *     'behaviors' => array('/full/path/to/behaviors/', '/next/full/path/to/behaviors/'),
 *     'components' => array('/full/path/to/components/', '/next/full/path/to/components/'),
 *     'helpers' => array('/full/path/to/helpers/', '/next/full/path/to/helpers/'),
 *     'vendors' => array('/full/path/to/vendors/', '/next/full/path/to/vendors/'),
 *     'shells' => array('/full/path/to/shells/', '/next/full/path/to/shells/'),
 *     'locales' => array('/full/path/to/locale/', '/next/full/path/to/locale/')
 * ));
 *
 */

/**
 * As of 1.3, additional rules for the inflector are added below
 *
 * Inflector::rules('singular', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 * Inflector::rules('plural', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 *
 */



// Credentials
include_once('credentials.php');


Configure::write('regex_chars',array(' ','[','\\','/','^','$','.','|','?','*','+','(',')','"'));
Configure::write('http_chars',array('{','}','&','?',':','.','=','-','_'));

// Server
// - should determine the Event here
// - like on Spotversation, ACE, IDC, etc.	
$server_name = env('SERVER_NAME');
if(substr($server_name,0,4) == 'www.'){
	$server_name = substr($server_name,4);
}
define('SERVER_NAME',$server_name);


// Incoming SMS Routing (fun!)
$url = explode('.',env('HTTP_HOST')); 

switch ($url[0]) { 
	case "incoming":              
		//Configure::write('Routing.admin', 'admin'); 
		$_GET["url"] = "texts/incoming";
	break; 
	default: 
} 

// FUNCTIONS

// Easily Sanitize an array by specifying how each field should be sanitized
function arraySanitize($array = array(), $methods = array()){
	App::import('Sanitize');

	foreach($array as $field => $value){
		// $key must be in $methods
		if(!array_key_exists($field,$methods)){
			continue;
		}

		if(is_array($methods[$field]) && count($methods[$field]) > 1){
			// Options exist for this Sanitize call
			//  - call basic options

			switch($methods[$field][0]){
				case 'paranoid':
					$array[$field] = Sanitize::paranoid($array[$field],$methods[$field][1]);
					break;
				case 'html':
					$array[$field] = Sanitize::html($array[$field],$methods[$field][1]);
					break;
				case 'escape':
					$array[$field] = Sanitize::escape($array[$field],$methods[$field][1]);
					break;
				default:
					break;
			}

		} else {
			// Just using a normal field
			// - or a single array field

			if(is_array($methods[$field])){
				$methods[$field] = $methods[$field][0]; // Get first input. There should only be one: 'firstname' => array('paranoid')
			}

			switch($methods[$field]){
				case 'paranoid':
					$array[$field] = Sanitize::paranoid($array[$field]);
					break;
				case 'html':
					$array[$field] = Sanitize::html($array[$field]);
					break;
				case 'escape':
					$array[$field] = Sanitize::escape($array[$field]);
					break;
				case 'phone':
					// Nothing extra besides numbers allowed
					$array[$field] = Sanitize::paranoid($array[$field]);
					break;
				case 'email':
					// All characters allowed, validation removes "bad" ones
					$array[$field] = $array[$field];
					break;
				default:
					break;
			}
		}

	}

	// Nicely sanitized
	return $array;
}


function walk_dir($dir) {
	if(!file_exists($dir)){
		return array();
	}
	$root = scandir($dir);
	$result = array();
	foreach($root as $value) { 
		if($value === '.' || $value === '..') {
			continue;
		} 
		if(is_file("$dir/$value")) {
			// Add as a file and continue
			$time = filemtime("$dir/$value");
			$result[]=array("$dir/$value",$dir,$value,$time);
			continue;
		} 
		foreach(walk_dir("$dir/$value") as $value) { 
			$result[]=$value; 
		} 
	} 
	return $result; 
}



// JSON responses to Ajax requests

function jsonData($data = array(),$code = 200){
	if(!isset($data['code'])){
		$data['code'] = $code;
	}
	return json_encode($data);
}


function jsonSuccess($msg = ''){
	return json_encode(array('code' => 200,
							 'msg' => $msg));
}


function jsonError($errorCode = 1, $msg = ''){
	$default = array('code' => $errorCode,
					 'msg' => 'Unknown Message');
	if(is_array($msg)){
		// Merge
		$default = array_merge($default,$msg);
	} else {
		$default['msg'] = $msg;
	}
	return json_encode($default);
}


function jsonIndent($json){

	$result      = '';
	$pos         = 0;
	$strLen      = strlen($json);
	$indentStr   = '  ';
	$newLine     = "\n";
	$prevChar    = '';
	$outOfQuotes = true;

	for ($i=0; $i<=$strLen; $i++) {

		// Grab the next character in the string.
		$char = substr($json, $i, 1);

		// Are we inside a quoted string?
		if ($char == '"' && $prevChar != '\\') {
			$outOfQuotes = !$outOfQuotes;
		
		// If this character is the end of an element, 
		// output a new line and indent the next line.
		} else if(($char == '}' || $char == ']') && $outOfQuotes) {
			$result .= $newLine;
			$pos --;
			for ($j=0; $j<$pos; $j++) {
				$result .= $indentStr;
			}
		}
		
		// Add the character to the result string.
		$result .= $char;

		// If the last character was the beginning of an element, 
		// output a new line and indent the next line.
		if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
			$result .= $newLine;
			if ($char == '{' || $char == '[') {
				$pos ++;
			}
			
			for ($j = 0; $j < $pos; $j++) {
				$result .= $indentStr;
			}
		}
		
		$prevChar = $char;
	}

	return $result;
}








