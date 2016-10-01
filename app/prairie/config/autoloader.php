<?php
/**
 * Config file for Autoloader
 * Below is the list of directories and files to load classes from.
 */
return array(
	"directories" => array(
		PRAIRIE_PATH,
		APP_PATH."/classes",
		APP_PATH."/controller",
		APP_PATH."/model",
		APP_PATH."/view",
	),
	"files" => array(
		APP_PATH."/routes.php"
	),
	"ignore" => array(
		PRAIRIE_PATH."/start.php",
		PRAIRIE_PATH."/cli.php",
		APP_PATH."/view/template",
		APP_PATH."/view/base/template",
		APP_PATH."/view/css",
		APP_PATH."/view/js",
		APP_PATH."/view/img"
	)
);