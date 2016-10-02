<?php
	
class Logger {
	private static $logLocation = "/storage/log";
	private static $file = null;

	public static function log($text){
		$dir = APP_PATH . self::$logLocation;
		if(!is_dir($dir)){
			echo "Log directory not found. Have permissions been granted?";
		}else{
			if(self::$file == null){
				self::$file = fopen($dir . "/prairie.log", "a");
			}

			$time = microtime(true);
			$toWrite = "[" . date("Y-m-d H:i:s") . "." . substr($time - floor($time), 2, 9) . "] " . $text . "\r\n";
			fwrite(self::$file, $toWrite);
		}
	}
}