<?php
/**
 * Config file for Autoloader
 * Below is the list of directories and files to load classes from.
 * Only loads Meadow files and all default directories
 */
return array(
	"loadOrder" => array(
		PRAIRIE_PATH . "/controller",
		PRAIRIE_PATH . "/database",
		PRAIRIE_PATH . "/flowers",
		PRAIRIE_PATH . "/logger",
		PRAIRIE_PATH . "/model",
		PRAIRIE_PATH . "/router",
		PRAIRIE_PATH . "/view/View.php",
		PRAIRIE_PATH . "/view/BaseView.php",
		APP_PATH . "/routes.php",
		APP_PATH . "/classes",
		APP_PATH . "/controller",
		APP_PATH . "/model",
		APP_PATH . "/view/base",
		APP_PATH . "/view",
	),
	"ignore" => array(
		PRAIRIE_PATH . "/start.php",
		PRAIRIE_PATH . "/cli.php",
		PRAIRIE_PATH . "/config",
		APP_PATH . "/view/template",
		APP_PATH . "/view/base/template",
		APP_PATH . "/view/css",
		APP_PATH . "/view/js",
		APP_PATH . "/view/img",
	)
);