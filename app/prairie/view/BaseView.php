<?php

class BaseView extends View {

	public static $baseTemplate = null;

	public static function getBaseView(){
		return static::generate(array(), file_get_contents(APP_PATH . static::$baseTemplate));
	}

	public static function make($params = array()){
		$baseView = static::getBaseView();
		$baseParams = array();
		$baseParams['content'] = static::generate($params);
		echo View::generate($baseParams,$baseView);
	}
}