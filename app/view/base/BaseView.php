<?php

class BaseView extends View {

	public static $template = "/view/base/template/BaseView.template.php";

	public static function getBaseView(){
		$code = static::generate();
		return $code;
	}
}