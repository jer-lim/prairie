<?php

if(!isset($argv[1]) && isset($argc)){
	echo "Path should be passed as first parameter";
	exit();
}

define("PRAIRIE_PATH", dirname(__FILE__));
require PRAIRIE_PATH . "/start.php";

exit();