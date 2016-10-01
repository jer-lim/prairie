<?php 

class Cache {
	public static function save($name, $duration, $content, $cacheSubdirectory = ""){
		$saveName = sha1($name);
		$expire = time() + $duration;
		$saveContent = gzcompress(serialize($content));

		if(!is_dir(APP_PATH . "/storage/cache/$cacheSubdirectory")){
			umask(0000);
			mkdir(APP_PATH . "/storage/cache/$cacheSubdirectory", 0777, true);
		}
		$file = fopen(APP_PATH . "/storage/cache/$cacheSubdirectory/$saveName", "w");
		fwrite($file, $expire . "\n");
		fwrite($file, $saveContent);
		fclose($file);

		return $expire;
	}

	public static function load($name, $cacheSubdirectory = ""){
		$saveName = sha1($name);
		$file = @fopen(APP_PATH . "/storage/cache/$cacheSubdirectory/$saveName", "r");
		if($file === false){
			return false;
		}else{
			$expire = fgets($file);
			if(time() < $expire){
				$content = unserialize(gzuncompress(fread($file, filesize(APP_PATH . "/storage/cache/$cacheSubdirectory/$saveName"))));
				fclose($file);
				return $content;
			}else{
				fclose($file);
				unlink(APP_PATH . "/storage/cache/$cacheSubdirectory/$saveName");
				return false;
			}
		}
	}

	public static function delete($name, $cacheSubdirectory = ""){
		$saveName = sha1($name);
		$file = @fopen(APP_PATH . "/storage/cache/$cacheSubdirectory/$saveName", "r");
		if($file === false){
			return false;
		}else{
			fclose($file);
			unlink(APP_PATH . "/storage/cache/$cacheSubdirectory/$saveName");
			return true;
		}
	}
}