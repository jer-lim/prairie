<?php

class DB {
	private static $config=null;
	private static $db;

	/**
	 * Get self DB object, function like a singleton
	 * @return DB
	 */
	public static function getDBO(){
		if(is_null(self::$config)) self::loadConfig();
		if(!self::$db) self::$db=new PDO("mysql:host=".self::$config['server'].";dbname=".self::$config['database'],self::$config['username'],self::$config['password']) or die("Failed to connect to database");
		return self::$db;
	}

	/**
	 * Load database configuration from config folder
	 */
	private static function loadConfig(){
		$config=require PRAIRIE_PATH."/config/database.php";
		self::$config=$config;
	}

	/**
	 * Create a MeadowQuery and set it to query a table
	 * @param  string $table
	 * @return MeadowQuery
	 */
	public static function query($table){

		$query=new MeadowQuery();
		$query->table($table);
		return $query;
	}
}