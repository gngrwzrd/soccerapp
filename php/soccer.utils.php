<?php

require_once("uuid.php");
date_default_timezone_set('America/Los_Angeles');

class SoccerUtils {

	var $basePath;
	var $baseURL;
	var $dashboardURL;
	var $versionsURL;
	var $registeredURL;
	var $appDataPath;
	var $appData;
	var $crashPath;
	var $usersPath;
	var $versionsPath;
	var $devicesPath;
	
	var $userSessionVar;
	var $maxCrashGroups;
	var $maxCrashesInGroup;

	static function getInstance() {
		static $instance = NULL;
		if($instance == NULL) {
			$instance = new SoccerUtils();
		}
		return $instance;
	}

	protected function __construct() {
		$this->userSessionVar = "user_uuid";
		$this->maxCrashGroups = 5;
		$this->maxCrashesInGroup = 10;

		$this->basePath = realpath(dirname(__FILE__) . '/..');
		
		if(isset($_SERVER['HTTPS'])) {
			$this->baseURL = 'https://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
		} else {
			$this->baseURL = 'http://' . $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
		}
		
		$this->baseURL = rtrim($this->baseURL,'/');
		$this->dashboardURL = $this->joinPaths(array($this->baseURL,"dashboard.php"));
		$this->versionsURL = $this->joinPaths(array($this->baseURL,"versions"));
		$this->regiseredURL = $this->baseURL . "/registered";
		$this->crashPath = $this->joinPaths(array($this->basePath,"crash"));
		$this->versionsPath = $this->joinPaths(array($this->basePath,"versions"));
		$this->usersPath = $this->joinPaths(array($this->basePath,"users"));
		$this->devicesPath = $this->joinPaths(array($this->basePath,"devices"));
		$this->appDataPath = $this->joinPaths(array($this->basePath,"app.json"));
		$this->appData = json_decode($this->readFileContent($this->appDataPath));

		if(!file_exists($this->crashPath)) {
			if(!mkdir($this->crashPath)) {
				error_log("Cannot create 'crash' folder. Check permissions.");
			}
		}

		if(!file_exists($this->usersPath)) {
			if(!mkdir($this->usersPath)) {
				error_log("Cannot create 'users' folder. Check permissions.");
			}
		}

		if(!file_exists($this->versionsPath)) {
			if(!mkdir($this->versionsPath)) {
				error_log("Cannot create 'versions' folder. Check permissions");
			}
		}
	}

	function UUID() {
		return strtoupper(UUID::v4());
	}
	
	function sortDescendingByDate($a, $b) {
		return $a->date < $b->date;
	}

	function sortDescendingByName($a, $b) {
		return $a->name < $b->name;
	}

	function getRequestVar($var,$default=False) {
		if(isset($_REQUEST[$var])) {
			return $_REQUEST[$var];
		}
		return $default;
	}
	
	function getSession($var,$default=False) {
		if(isset($_SESSION[$var])) {
			return $_SESSION[$var];
		}
		return $default;
	}

	function setSession($key,$value) {
		$_SESSION[$key] = $value;
	}
	
	function joinPaths($paths=array()) {
		$out = $paths[0];
		for($i = 1; $i < count($paths); $i++) {
			$out .= '/' . $paths[$i];
		}
		return $out;
	}

	function readFileContent($path) {
		$size = filesize($path);
		$handle = fopen($path,"r");
		$content = fread($handle,$size);
		fclose($handle);
		return $content;
	}

	function readLines($path,$lines) {
		$lines = array();
		$handle = fopen($path,"r");
		while(($line=fgets($handle)) && count($lines) < $lines) {
			array_push($lines,$line);
		}
		fclose($handle);
		$content = join("",$lines);
		return $content;
	}

	function readLinesAsArray($path,$lines) {
		$lines = array();
		$handle = fopen($path,"r");
		while(($line=fgets($handle)) && count($lines) < $lines) {
			array_push($lines,$line);
		}
		fclose($handle);
		return $lines;
	}

	function writeFileContent($path,$content) {
		$handle = fopen($path,"w");
		fwrite($handle,$content);
		fclose($handle);
	}

	function rrmdir($dir) { 
		if(is_dir($dir)) { 
			$objects = scandir($dir);
			foreach($objects as $object) { 
				if($object != "." && $object != "..") { 
					if(filetype($dir."/".$object) == "dir") {
						$this->rrmdir($dir."/".$object);
					} else {
						unlink($dir."/".$object);
					}
				}
	     	}
			reset($objects);
			rmdir($dir);
		}
	}

	function getFilesAtPath($path,$ext=array(),$ignore=array(),$maxFiles=-1) {
		$rawfiles = scandir($path);
		$realfiles = array();
		$count = 0;
		error_log($maxFiles);
		for($i = 0; $i < count($rawfiles); $i++) {
			if($rawfiles[$i] != "." && $rawfiles[$i] != "..") {
				$fullpath = $path . "/" . $rawfiles[$i];
				if(is_dir($fullpath)) {
					continue;
				}
				if(count($ext) > 0) {
					$info = pathinfo($fullpath);
					if(array_search($info['extension'],$ext) > -1) {
						if(!array_search($info['filename'],$ignore)) {
							if($maxFiles == -1 || $count < $maxFiles) {
								$count++;
								array_push($realfiles,$rawfiles[$i]);
							}
						}
					}
				} else {
					if(!array_search($rawfiles[$i],$ignore)) {
						if($maxFiles == -1 || $count < $maxFiles) {
							$count++;
							array_push($realfiles,$rawfiles[$i]);
						}
					}
				}
			}
		}
		return $realfiles;
	}

	function getDirsAtPath($path,$maxDirs=-1) {
		$rawfiles = scandir($path);
		$realfiles = array();
		$count = 0;
		for($i = 0; $i < count($rawfiles); $i++) {
			if($rawfiles[$i] != "." && $rawfiles[$i] != "..") {
				$fullpath = $path . "/" . $rawfiles[$i];
				if(is_dir($fullpath)) {
					if($maxDirs == -1 || $count < $maxDirs) {
						array_push($realfiles,$rawfiles[$i]);
					}
				}
			}
		}
		return $realfiles;
	}

	function rsearch($path,$needle) {
		$rawfiles = scandir($path);
		foreach($rawfiles as $file) {
			if($file == "." || $file == "..") {
				continue;
			}
			$search = $path . '/' . $file;
			if($file == $needle) {
				return $search;
			} else if(is_dir($search)) {
				return $this->rsearch($search,$needle);
			}
		}
	}
}

?>