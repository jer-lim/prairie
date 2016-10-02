<?php

class BaseView extends View {

	public static $baseTemplate = null;

	public static function getBaseView(){
		return static::generate(array(), static::$baseTemplate);
	}

	public static function make($params=array()){
		echo "test";
		$baseView=static::getBaseView();
		$baseParams=array();
		$baseParams['content']=static::generate($params);
		echo View::generate($baseParams,$baseView);
	}
}