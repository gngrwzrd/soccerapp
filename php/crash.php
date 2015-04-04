<?php

require_once("soccer.utils.php");

class Crash {
	
	var $uuid;
	var $name;
	var $version;
	var $osversion;
	var $hardwareModel;
	var $data;
	var $date;
	var $datestring;
	
	static function GetCrash($uuid) {
		$utils = SoccerUtils::getInstance();
		$crash = new Crash();
		$crash->uuid = $uuid;
		$crash->name = $uuid . ".txt";
		$crash->date = filemtime( $utils->joinPaths(array($utils->crashPath,$crash->name)));
		$crash->datestring = date("m/d/Y",$crash->date);
		$crashFile = $utils->rsearch($utils->crashPath,$crash->name);
		$header = $utils->readLinesAsArray($crashFile,16);
		$crash->osversion = Crash::GetOSVersionFromIOSCrashHeader($header);
		$crash->hardwareModel = Crash::GetHardwareModelFromIOSCrashHeader($header);
		$crash->version = Crash::GetVersionFromIOSCrash($header);
		return $crash;
	}

	static function NewCrash($data) {
		$utils = SoccerUtils::getInstance();
		$crash = new Crash();
		$crash->uuid = $utils->uuid();
		$crash->name = $crash->uuid . ".txt";
		$crash->data = $data;
		$header = explode("\n",$data);
		$header = array_slice($header,0,30);
		$crash->version = Crash::GetVersionFromIOSCrash($header);
		$crash->hardwareModel = Crash::GetHardwareModelFromIOSCrashHeader($header);
		$crash->osversion = Crash::GetOSVersionFromIOSCrashHeader($header);
		return $crash;
	}

	static function DeleteCrash($uuid) {
		$utils = SoccerUtils::getInstance();
		$path = $utils->joinPaths(array($utils->crashPath,$uuid.'.txt'));
		if(file_exists($path)) {
			unlink($path);
		}
	}

	static function DeleteCrashFile($filename) {
		$utils = SoccerUtils::getInstance();
		$path = $utils->joinPaths(array($utils->crashPath,$filename));
		if(file_exists($path)) {
			unlink($path);
		}	
	}

	static function UUIDFromFilename($filename) {
		return preg_replace('/\.txt/',"",$filename);
	}

	static function GetAllCrashes() {
		$utils = SoccerUtils::getInstance();
		$logs = $utils->getFilesAtPath($utils->crashPath,array("txt"));
		$crashes = array();
		foreach($logs as $log) {
			$uuid = preg_replace('/\.txt/',"",$log);
			$crash = Crash::GetCrash($uuid);
			array_push($crashes,$crash);
		}
		usort($crashes,array("SoccerUtils","sortDescendingByDate"));
		return $crashes;
	}

	static function GetOSVersionFromIOSCrashHeader($header) {
		$osline = $header[11];
		return substr($osline,17);
	}

	static function GetHardwareModelFromIOSCrashHeader($header) {
		$hwl = $header[2];
		return substr($hwl,17);
	}

	static function GetVersionFromIOSCrash($header) {	
		return substr($header[6],17);
	}

	function save() {
		$utils = SoccerUtils::getInstance();
		$path = $utils->joinPaths(array($utils->crashPath,$this->name));
		if(!$this->data) {
			error_log("Error saving crash, no data found.");
			return;
		}
		$utils->writeFileContent($path,$this->data);
	}

	function delete() {
		$utils = SoccerUtils::getInstance();
		$path = $utils->joinPaths(array($utils->crashPath,$this->name));
		if(file_exists($path)) {
			unlink($path);
		}
	}

	function getCrashFilePath() {
		$utils = SoccerUtils::getInstance();
		return $inst->joinPaths(array($utils->crashPath,$this->name));
	}

	function getCrashFileURL() {
		$utils = SoccerUtils::getInstance();
		return $utils->joinPaths(array($utils->baseURL,"crash",$this->name));
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