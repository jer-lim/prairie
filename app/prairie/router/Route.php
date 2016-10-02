<?php

class Route {

	private static $routes = array();

	public static function get($route, $controller){
		static::addRoute($route, $controller, "GET");
	}

	public static function post($route, $controller){
		static::addRoute($route, $controller, "POST");
	}

	public static function put($route, $controller){
		static::addRoute($route, $controller, "PUT");
	}

	public static function patch($route, $controller){
		static::addRoute($route, $controller, "PATCH");
	}

	public static function delete($route, $controller){
		static::addRoute($route, $controller, "DELETE");
	}

	public static function head($route,$controller){
		static::addRoute($route, $controller, "HEAD");
	}

	public static function options($route,$controller){
		static::addRoute($route, $controller, "OPTIONS");
	}

	public static function cli($route,$controller){
		static::addRoute($route, $controller, "CLI");
	}

	/**
	 * Bind a route to multiple verbs
	 * @param  array $verbs
	 * @param  string $route      
	 * @param  string $controller Class::method
	 */
	public static function bind($verbs, $route, $controller){
		foreach($verbs as $verb){
			static::addRoute($route, $controller, $verb);
		}
	}
	
	private static function addRoute($route, $controller, $method){
		$r = new StdClass();
		$r->route = explode("/", $route);
		$r->controller = $controller;
		$r->method = $method;
		array_push(static::$routes, $r);
	}

	public static function handleRoute(){
		if(isset($_SERVER['argc'])) $rawPath = $_SERVER['argv'][1]; // CLI
		else $rawPath = $_SERVER['REQUEST_URI'];
		if(strstr($rawPath, "?")) $rawPath = substr($rawPath, 0, strpos($rawPath, "?"));
		$requestedPath = explode("/", $rawPath);
		$variables = array();

		$routes = static::$routes;
		for($i = 0; $i < count($requestedPath); ++$i){
			foreach($routes as $key => $route){
				$fullPath = $route->route;
				if($requestedPath[$i] !== $fullPath[$i] || count($requestedPath) !== count($fullPath)){
					if(!static::isPlaceholder($fullPath[$i])){
						unset($routes[$key]);
					}else{
						$varName = str_replace(array("{","}"), "", $fullPath[$i]);
						$variables[$varName] = $requestedPath[$i];
					}
				}
			}
		}
		if(empty($routes)){
			//route not found
			header("HTTP/1.0 404 Not Found");
			Error404::make(array("route" => $rawPath));
		}else{
			//call appropriate function
			$found = false;
			foreach($routes as $route){
				if(!isset($_SERVER['argc'])){
					if($route->method === $_SERVER['REQUEST_METHOD'] && $_SERVER['REQUEST_METHOD'] !== "CLI"){
						$found = true;
						if(empty($variables)){
							call_user_func($route->controller);
						}else{
							call_user_func_array($route->controller, $variables);
						}
						break;
					}
				}else{
					if($route->method === "CLI"){
						$found = true;
						if(empty($variables)){
							call_user_func($route->controller);
						}else{
							call_user_func_array($route->controller, $variables);
						}
						break;
					}
				}
			}
			if(!$found){
				header("HTTP/1.0 405 Method Not Allowed");
				Error405::make(array("route" => $rawPath));
			}
		}
	}

	private static function isPlaceholder($string){
		if(preg_match("/{.+}/", $string) === 1) return true;
		else return false;
	}
}
