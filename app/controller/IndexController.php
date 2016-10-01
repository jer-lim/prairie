<?php

class IndexController extends Controller {

	public static $exampleVar = "Example attribute of object called in view";

	public static function showPage(){
		$GLOBALS['exampleVar'] = "Example global variable called in view";
		$params = array();
		$params['testParam'] = "Example parameter passed to view";
		IndexView::make($params);
	}

	public static function testMethod(){
		return "Example method called in view";
	}
}
