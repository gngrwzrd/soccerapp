<?php

require_once("uuid.php");
date_default_timezone_set('America/Los_Angeles');

class SoccerUtils {

	var $basePath;
	var $baseURL;
	var $dashboardURL;
	var $appDataPath;
	var $crashPath;
	var $usersPath;
	var $versionsPath;
	var $registeredURL;
	
	var $userUUIDSessionVar;
	var $maxCrashGroups;
	var $maxCrashesInGroup;
	var $maxVersions;
	
	static function getInstance() {
		static $instance = NULL;
		if($instance == NULL) {
			$instance = new SoccerUtils();
		}
		return $instance;
	}

	protected function __construct() {
		$this->userUUIDSessionVar = "user_uuid";
		$this->maxCrashGroups = 5;
		$this->maxVersions = 10;
		$this->maxCrashesInGroup = 10;

		$this->basePath = realpath(dirname(__FILE__) . '/..');
		
		if(isset($_SERVER['HTTPS'])) {
			$this->baseURL = 'https://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
		} else {
			$this->baseURL = 'http://' . $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
		}
		
		$this->baseURL = rtrim($this->baseURL,'/');
		$this->dashboardURL = $this->joinPaths(array($this->baseURL,"dashboard.php"));
		$this->crashPath = $this->joinPaths(array($this->basePath,"crash"));
		$this->versionsPath = $this->joinPaths(array($this->basePath,"versions"));
		$this->usersPath = $this->joinPaths(array($this->basePath,"users"));
		$this->regiseredURL = $this->baseURL . "/registered";
		$this->appDataPath = $this->joinPaths(array($this->basePath,"app.json"));

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

	function getUserFolderPath($uuid) {
		return $this->joinPaths(array($this->usersPath,$uuid));
	}

	function getUserJSONDataPath($uuid) {
		return $this->joinPaths(array($this->getUserFolderPath($uuid),"user.json"));
	}

	function getUserJSONData($uuid) {
		$content = $this->readFileContent($this->getUserJSONDataPath);
		return json_decode($content);
	}

	function getCachedUserMobileConfigPath($uuid) {
		return $this->joinPaths(array($this->getUserFolderPath($uuid),"profile.mobileconfig"));
	}

	function UUID() {
		return strtoupper(UUID::v4());
	}
	
	function sortDescendingByDate($a, $b) {
		return $a->date < $b->date;
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

	function setSession($var,$value) {
		error_log("set session: " . $var . ' ' . $value);
		$_SESSION[$var] = $value;
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

	function getIOSDeviceUDIDFromData($data) {
		//device id
		$matches = array();
		preg_match('/[a-zA-Z0-9]{40}/',$data,$matches);
		$device = $matches[0];
		return $device;
	}
	
	function getIOSDeviceModelFromData($data) {
		//iPhone
		$matches = array();
		preg_match('/iPhone[0-9]{1,2}\,[0-9]{1,2}/',$data,$matches);
		if(count($matches) > 0) {
			$model = $matches[0];
		}
		
		//iPad
		$matches = array();
		preg_match('/iPad[0-9]{1,2}\,[0-9]{1,2}/',$data,$matches);
		if(count($matches) > 0) {
			$model = $matches[0];
		}

		//iPod
		$matches = array();
		preg_match('/iPod[0-9]{1,2}\,[0-9]{1,2}/',$data,$matches);
		if(count($matches) > 0) {
			$model = $matches[0];
		}

		return $model;
	}

	function getVersionFromIOSCrash($data) {
		$matches = array();
		preg_match('/Version:\s+([0-9])+\n/',$data,$matches);
		if(count($matches) > 0) {
			return $matches[1];
		}
		return "Unknown";
	}

	function getReleaseNotesForVersion($versionUUID) {
		$search = $this->joinPaths(array($this->versionsPath,$versionUUID));
		$txt = $this->getFilesAtPath($search,array("txt"));
		if(count($txt) == 1) {
			$content = $this->readFileContent($this->joinPaths(array($search,$txt[0])));
			$parsedown = new Parsedown();
			return $parsedown->text($content);
		}
		return "Not found";
	}

	function getCachedUserMobileConfig($uuid) {
		$path = $this->getCachedUserMobileConfigPath($uuid);
		if(!file_exists($path)) {
			return FALSE;
		}
		return $this->readFileContent($path);
	}

	function getAllVersions() {
		$path = $this->versionsPath;
		if(!file_exists($path)) {
			return array();
		}
		$rawfiles = scandir($path);
		$allVersions = array();
		$count = 0;
		foreach($rawfiles as $UDID) {
			if($UDID == ".." || $UDID == ".") {
				continue;
			}
			if($count == $this->maxVersions) {
				break;
			}
			$UDIDPath = $this->joinPaths(array($path,$UDID));
			if(is_dir($UDIDPath)) {
				//UDID is dir, look for files in that dir.
				$versions = $this->getFilesAtPath($UDIDPath,array("ipa"));
				if(count($versions) == 1) {
					$filename = $versions[0];
					$filepath = $this->joinPaths(array($UDIDPath,$filename));	
					$appVersion = new AppVersion($filepath,$UDID,$filename);
					$count++;
					array_push($allVersions,$appVersion);
				} else if(count($versions) > 1) {
					error_log("Version folder has multiple versions in it. " . $UDIDPath);
				} else if(count($versions) < 1) {
					error_log("Version folder has no versions in it. " . $UDIDPath);
				}
			}
		}
		usort($allVersions,array("SoccerUtils","sortDescendingByDate"));
		return $allVersions;
	}

	function getVersionFileName($version) {
		$path = $this->versionsPath;
		$rawfiles = scandir($path);
		foreach($rawfiles as $UDID) {
			if($UDID == $version) {
				$UDIDPath = $this->joinPaths(array($path,$UDID));
				$versions = $this->getFilesAtPath($UDIDPath,array("ipa"));
				if(count($versions) == 1) {
					return $versions[0];
				} else if(count($versions) > 1) {
					error_log("Version folder has multiple versions in it. " . $UDIDPath);
				} else if(count($versions) == 0) {
					error_log("Version folder has no versions in it. " . $UDIDPath);
				}
			}
		}
		return "";
	}

	function getAllCrashGroups() {
		$path = $this->crashPath;
		$dirs = $this->getDirsAtPath($path);
		rsort($dirs);
		$count = 0;
		$crashesByVersion = array();
		foreach($dirs as $version) {
			if($count == $this->maxCrashGroups) {
				break;
			}
			$buildVersionPath = $this->joinPaths(array($path,$version));
			$group = new AppCrashGroup($version,$buildVersionPath);
			$crashesByVersion[$version] = $group;
			$count++;
		}
		krsort($crashesByVersion,SORT_NUMERIC);
		return $crashesByVersion;
	}
}

?>