<?php

class Curler {

	var $curly = array();
	var $id = 0;
	var $mh;

	//http://www.phpied.com/simultaneuos-http-requests-in-php-with-curl/
	
	function __construct(){
		$this->mh = curl_multi_init();
	}

	public function get($url, $opts = array(), $curlOpts = array()) {
		  // array of curl handles
		$curly = array();
		  // data to be returned
		$result = array();
		
		  // multi handle
		$mh = curl_multi_init();
		
		$id = 0;
		$curly[$id] = curl_init();

		if(!empty($array)){
			$url .= "?";
			foreach($opts as $name => $val){
				$url .= "&" . $name . "=" . $val;
			}
		}
		
		curl_setopt($curly[$id], CURLOPT_URL,            $url);
		curl_setopt($curly[$id], CURLOPT_HEADER,         0);
		curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curly[$id], CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curly[$id], CURLOPT_MAXREDIRS , 5);
		curl_setopt($curly[$id], CURLOPT_CAINFO, STORAGE_PATH . "/cacert.pem");

		if(!empty($curlOpts)){
			curl_setopt_array($curly[$id], $curlOpts);
		}
		
		curl_multi_add_handle($mh, $curly[$id]);
		
		  // execute the handles
		$running = null;
		do {
			curl_multi_exec($mh, $running);
		} while($running > 0);
		
		
		  // get content and remove handles
		foreach($curly as $id => $c) {
			$result[$id] = curl_multi_getcontent($c);
			curl_multi_remove_handle($mh, $c);
		}
		
		  // all done
		curl_multi_close($mh);

		return $result[0];
	}

	public function post($url, $opts = array(), $curlOpts = array()) {
		  // array of curl handles
		$curly = array();
		  // data to be returned
		$result = array();
		
		  // multi handle
		$mh = curl_multi_init();
		
		$id = 0;
		$curly[$id] = curl_init();
		
		curl_setopt($curly[$id], CURLOPT_URL,            $url);
		curl_setopt($curly[$id], CURLOPT_HEADER,         0);
		curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curly[$id], CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curly[$id], CURLOPT_MAXREDIRS , 5);
		curl_setopt($curly[$id], CURLOPT_CAINFO, STORAGE_PATH . "/cacert.pem");

		curl_setopt($curly[$id], CURLOPT_POST,       1);
		curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $opts);

		if(!empty($curlOpts)){
			curl_setopt_array($curly[$id], $curlOpts);
		}
		
		curl_multi_add_handle($mh, $curly[$id]);
		
		  // execute the handles
		$running = null;
		do {
			curl_multi_exec($mh, $running);
		} while($running > 0);
		
		
		  // get content and remove handles
		foreach($curly as $id => $c) {
			$result[$id] = curl_multi_getcontent($c);
			curl_multi_remove_handle($mh, $c);
		}
		
		  // all done
		curl_multi_close($mh);

		return $result[0];
	}

	public function addGet($url, $opts = array(), $curlOpts = array()){
		$this->curly[$this->id] = curl_init();

		if(!empty($array)){
			$url .= "?";
			foreach($opts as $name => $val){
				$url .= "&" . $name . "=" . $val;
			}
		}

		curl_setopt($this->curly[$this->id], CURLOPT_URL,            $url);
		curl_setopt($this->curly[$this->id], CURLOPT_HEADER,         0);
		curl_setopt($this->curly[$this->id], CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->curly[$this->id], CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($this->curly[$this->id], CURLOPT_MAXREDIRS , 5);
		curl_setopt($this->curly[$this->id], CURLOPT_CAINFO, STORAGE_PATH . "/cacert.pem");

		if(!empty($curlOpts)){
			curl_setopt_array($this->curly[$this->id], $curlOpts);
		}
		
		curl_multi_add_handle($this->mh, $this->curly[$this->id]);
		$this->id++;
	}

	public function addPost($url, $opts = array(), $curlOpts = array()){
		$this->curly[$this->id] = curl_init();

		curl_setopt($this->curly[$this->id], CURLOPT_URL,            $url);
		curl_setopt($this->curly[$this->id], CURLOPT_HEADER,         0);
		curl_setopt($this->curly[$this->id], CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->curly[$this->id], CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($this->curly[$this->id], CURLOPT_MAXREDIRS , 5);
		curl_setopt($this->curly[$this->id], CURLOPT_CAINFO, STORAGE_PATH . "/cacert.pem");

		curl_setopt($this->curly[$this->id], CURLOPT_POST,       1);
		curl_setopt($this->curly[$this->id], CURLOPT_POSTFIELDS, $opts);

		if(!empty($curlOpts)){
			curl_setopt_array($this->curly[$this->id], $curlOpts);
		}
		
		curl_multi_add_handle($this->mh, $this->curly[$this->id]);
		$this->id++;
	}

	public function execute(){
		// data to be returned
		$result = array();
		
		// execute the handles
		$running = null;
		do {
			curl_multi_exec($this->mh, $running);
		} while($running > 0);
		
		
		// get content and remove handles
		foreach($this->curly as $id => $c) {
			$result[$id] = curl_multi_getcontent($c);
			curl_multi_remove_handle($this->mh, $c);
		}
		
		// all done
		curl_multi_close($this->mh);
		$this->id = 0;

		return $result;
	}
}
