<?php

class DB {
	private static $config=null;
	private static $db = array();

	/**
	 * Get self DB object, function like a singleton
	 * @return DB
	 */
	public static function getDBO($dbName){
		if(is_null(self::$config)) self::loadConfig();
		if(!isset(self::$db[$dbName])) self::$db[$dbName] = new PDO(
			"mysql:host=" . self::$config[$dbName]['server'] . ";dbname=" . self::$config[$dbName]['database'],
			self::$config[$dbName]['username'],
			self::$config[$dbName]['password']
		) or die("Failed to connect to database $dbName");
		return self::$db[$dbName];
	}

	/**
	 * Load database configuration from config folder
	 */
	private static function loadConfig(){
		$config = require PRAIRIE_PATH . "/config/database.php";
		self::$config = $config;
	}

	/**
	 * Create a MeadowQuery and set it to query a table
	 * @param  string $table
	 * @return MeadowQuery
	 */
	public static function query($dbName, $table){
		$query = new MeadowQuery($dbName);
		$query->table($table);
		return $query;
	}
}