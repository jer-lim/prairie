<?php

class Autoloader {

	private static $ignore=array();
	/**
	 * Load all files in the config file
	 */
	public static function loadAll(){
		$settings=require PRAIRIE_PATH."/config/autoloader.php";
		$toLoad=$settings["directories"];
		$files=$settings["files"];
		static::$ignore=$settings["ignore"];

		foreach($toLoad as $directory){
			static::load($directory);
		}

		foreach($files as $file){
			require $file;
		}
	}

	/**
	 * Recursively load the directory
	 * @param  string $directory
	 */
	public static function load($directory){
		$directoryScan=array_diff(scandir($directory), array('..', '.'));
		foreach($directoryScan as $entry){
			$file=$directory."/".$entry;
			if(is_dir($file) && !in_array($file, static::$ignore)){
				static::load($file);
			}else if(!in_array($file, static::$ignore)){
				require_once($file);
			}
		}
	}

}