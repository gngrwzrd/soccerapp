<?php

require_once("soccer.utils.php");

class Crash {
	
	var $uuid;
	var $name;
	var $version;
	var $data;
	var $date;
	var $datestring;
	
	function saveCrash($data) {

	}
	
	function initWithPath($path) {
		$this->name = basename($path);
		$this->date = filemtime($path);
		$this->datestring = date("m/d/Y",$this->date);
	}

	static function GetCrash($uuid) {
		$utils = SoccerUtils::getInstance();
		$crash = new Crash();
		$crash->uuid = $uuid;
		$crash->name = $uuid; // . ".txt";
		$crash->date = filemtime($path);
		$crash->datestring = date("m/d/Y",$crash->date);
		$crashFile = $utils->rsearch($utils->crashPath,$crash->name);
		//var_dump($crashFile);
		$crashContent = $utils->readLines($crashFile,10);
		$crash->version = Crash::GetVersionFromIOSCrash($crashContent);
		//echo $crash->version;
		return $crash;
	}

	static function NewCrash($data) {
		$utils = SoccerUtils::getInstance();
		$crash = new Crash();
		$crash->uuid = $utils->uuid();
		$crash->name = $crash->uuid . ".txt";
		$crash->data = $data;
		$crash->version = Crash::GetVersionFromIOSCrash($data);
		return $crash;
	}

	static function GetVersionFromIOSCrash($data) {
		$matches = array();
		preg_match('/Version:\s+([0-9])+\n/',$data,$matches);
		if(count($matches) > 0) {
			return $matches[1];
		}
		return "Unknown";
	}

	function save() {
		$utils = SoccerUtils::getInstance();
		$path = $utils->joinPaths(array($utils->crashPath,$this->version,$this->name));
		if(!$this->data) {
			error_log("Error saving crash, no data found.");
			return;
		}
		$utils->writeFileContent($path,$this->data);
	}

	function delete() {
		$utils = SoccerUtils::getInstance();
		$path = $utils->joinPaths(array($utils->crashPath,$this->version,$this->name));
		if(file_exists($path)) {
			unlink($path);
		}
	}

	function getCrashFilePath() {
		$utils = SoccerUtils::getInstance();
		return $inst->joinPaths(array($utils->crashPath,$this->version,$this->name));
	}

	function getCrashFileURL() {
		$utils = SoccerUtils::getInstance();
		return $utils->joinPaths(array($utils->baseURL,"crash",$this->version,$this->name));
	}

	function getCrashLog() {
		$utils = SoccerUtils::getInstance();
		if($this->data) {
			return $this->data;
		}
		$path = $this->getCrashFilePath();
		$content = $inst->readFileContent($path);
		return $content;
	}
}

?>