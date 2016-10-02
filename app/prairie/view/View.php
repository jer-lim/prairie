<?php

class View {

	/**
	 * This is the view. Extend it to create other views.
	 * The default template is classname.template.php
	 * Valid placeholders you can put in the template are:
	 * 	{{ var(Class::method()) }}
	 * 	{{ var(Class::variable) }}
	 * 	{{ var($variable) }} Global variable
	 * 	{{ var($object->sub->var) }} and even deeper levels
	 * 	{{ param($parameter) }} which is passed in in $params in generate()
	 * 	{{ template(Class) }} loads the template of that class and replaces this
	 */

	/**
	 * Path of the template
	 * @var string
	 */
	public static $template = null;

	/**
	 * Create and output the page without any parameters
	 * Override this method to create more complicated views
	 */
	public static function make($params = array()){
		echo static::generate($params);
	}

	/**
	 * Generate HTML code of the view
	 * @param  array  $params Array of optional parameters
	 * @param string $t Text template to use instead of the one in the path
	 * @return string         HTML code
	 */
	public static function generate($params = array(), $t = null){
		if(is_null($t)){
			if(is_null(static::$template)){
				$source = APP_PATH."/view/template/" . get_called_class() . ".template.php";
			}else{
				$source = APP_PATH . static::$template;
			}
			$template = file_get_contents($source);
		}else{
			$template = $t;
		}
		$placeholders = null;
		$unfilledPlaceholders = array();
		preg_match_all("/{{.+?}}/", $template, $matches);
		//while(preg_match("/{{.+}}/", $template, $match)!==0){
		foreach($matches[0] as $match){
			//strip all spaces
			$originalPlaceholderPattern = $match;
			$placeholderPattern = str_replace(" ", "", $originalPlaceholderPattern);
			$toReplace = false;
			$replacement = str_replace(array("{{", ")}}"), "", $placeholderPattern);
			if(strpos($replacement, "var($") === 0){
				//is a global variable
				//get variable name
				$var=str_replace("var($", "" , $replacement);
				//see if the var is an object
				if(strstr($var, "->") === false){
					$currentStep = $GLOBALS[$var];
				}else{
					//extract first portion of var
					$workingString = substr($var, strpos($var, "->"));
					$var = str_replace($workingString, "", $var);
					$var = $GLOBALS[$var];
					//trim ->
					$workingString = substr($workingString, 2);
					$currentStep = static::translateStringToObject($var, $workingString);
				}
				$toReplace = $currentStep;
			}else if(strpos($replacement, "var(") === 0){
				//is a static variable
				//get variable name
				$var = str_replace("var(", "", $replacement);
				$workingString = substr($var, strpos($var, "::"));
				$class = str_replace($workingString, "", $var);
				//is not a function
				if(strstr($workingString, "(") === false){
					$var = substr($workingString, 2);
					$toReplace = $class::$$var;
				//is a function
				}else{
					preg_match("/\(.+\)/", $workingString, $arguments);
					$functionName = str_replace($arguments, "", $workingString);
					$functionName = substr($functionName, 2, strlen($functionName) - 4);
					$arguments = str_replace(array(" ", "(", ")"), "", $arguments);
					if(!empty($arguments)){
						$argumentsArray = explode(",", $arguments[0]);
						$toReplace = call_user_func_array($class . "::" . $functionName, $argumentsArray);
					}else{
						$toReplace = call_user_func($class . "::" . $functionName);
					}
				}
			}else if(strpos($replacement, "param($") === 0){
				//is a parameter
				//get variable name
				$var = str_replace("param($", "", $replacement);
				if(array_key_exists($var, $params)){
					$toReplace = $params[$var];
				}
			}else if(strpos($replacement, "template(") === 0){
				//is a template
				//get class
				$var = str_replace("template(", "", $replacement);
				$toReplace = file_get_contents(APP_PATH . $var::$template);
			}

			if(!$toReplace && $toReplace !== ""){
				array_push($unfilledPlaceholders, $placeholderPattern);
			}else{
				//replace all instances of the placeholder with variable value
				$template = str_replace($originalPlaceholderPattern, $toReplace, $template);
			}
		}
		return $template;
	}

	/**
	 * Translate string from view template into object
	 * @param  StdClass $var  The original object
	 * @param  string $string Remaining unprocessed string
	 * @return mixed
	 */
	private static function translateStringToObject($var, $string){
		$var;
		//cannot recurse anymore
		if(strstr($string, "->") === false){
			//is not a function
			if(strpos($string,"()") === false){
				$var = $var->$string;
			//is a function
			}else{
				$functionName = str_replace("()", "", $string);
				$var = $var->$functionName();
			}
			
			return $var;
		}else{
			//recursively go deeper into the variable
			$temp = substr($string, strpos($string, "->"));
			$varAddOn = str_replace($temp, "", $string);
			//is not a function
			if(strpos($varAddOn, "()") === false){
				$var = $var->$varAddOn;
			//is a function
			}else{
				$functionName = str_replace("()", "", $varAddOn);
				$var = $var->$functionName();
				$string = substr($string, 2);
			}
			$string = $temp;
			$string = substr($string, 2);
			return static::translateStringToObject($var, $string);
		}
	}
}