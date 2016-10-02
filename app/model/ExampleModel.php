<?php

class ExampleModel extends Model {
	protected static $dbConfig = "default";
	protected static $table = "example";
	protected static $attributes = array("id", "value");
	protected static $primaryKey = "id";
	protected static $useCache = true;
	protected static $cacheTime = 60;
}