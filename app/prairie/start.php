<?php

define("APP_PATH", dirname(__FILE__) . "/..");
define("STORAGE_PATH", dirname(__FILE__) . "/../storage");

//Load classes
require_once PRAIRIE_PATH . "/autoloader/Autoloader.php";
Autoloader::loadAll();

Route::handleRoute();
