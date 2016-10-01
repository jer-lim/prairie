<?php

class IndexView extends View {

	public static $template = "/view/template/Index.template.php";

	public static function make($params=array()){
		$baseView=BaseView::getBaseView();
		$baseParams=array();
		$baseParams['content']=static::generate($params);
		echo View::generate($baseParams,$baseView);
	}
}