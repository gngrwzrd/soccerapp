<?php

require_once("php/parsedown/Parsedown.php");
require_once("php/Savant/Savant3.php");

class AppVersion {
	
	var $uuid;
	var $name;
	var $extension;
	var $executablePath;
	var $releaseNotes;
	var $releaseNotesPath;
	var $date;
	var $datestring;
	var $tmpFilePath;
	
	const MAX_VERSIONS = 10;

	static function NewAppVersionFromSubmission() {
		$utils = SoccerUtils::getInstance();
		$file = $_FILES['executable'];
		if(!$file) {
			return FALSE;
		}
		$av = new AppVersion();
		$av->uuid = $utils->uuid();
		$av->releaseNotes = $utils->getRequestVar("releaseNotes");
		$av->tmpFilePath = $file['tmp_name'];
		$av->name = $file['name'];
		$av->executablePath = $utils->joinPaths(array($utils->versionsPath,$av->uuid,$av->name));
		$av->releaseNotesPath = $utils->joinPaths(array($utils->versionsPath,$av->uuid,"release-notes.txt"));
		return $av;
	}

	static function GetAppVersion($uuid) {
		if(!$uuid) {
			error_log("AppVersion::GetAppVersion nil uuid");
			return NULL;
		}
		
		$utils = SoccerUtils::getInstance();
		$path = $utils->joinPaths(array($utils->versionsPath,$uuid));
		
		if(!file_exists($path)) {
			return NULL;
		}

		$executables = $utils->getFilesAtPath($path,array("ipa","exe","dmg","zip","apk"));
		$executablePath = NULL;
		$executableName = NULL;
		
		if(count($executables) == 1) {
			$executableName = $executables[0];
			$executablePath = $utils->joinPaths(array($path,$executableName));
		} else {
			error_log("AppVersion has too many executables.");
			return NULL;
		}

		$info = pathinfo($executablePath);

		$av = new AppVersion();
		$av->uuid = $uuid;
		$av->executablePath = $executablePath;
		$av->name = $info['filename'];
		$av->extension = $info['extension'];
		$av->date = filemtime($executablePath);
		$av->datestring = date("m/d/Y",$av->date);
		$av->releaseNotesPath = $utils->joinPaths(array($utils->versionsPath,$av->uuid,"release-notes.txt"));

		return $av;
	}

	static function GetAllAppVersions() {
		$utils = SoccerUtils::getInstance();
		$path = $utils->versionsPath;
		if(!file_exists($path)) {
			return array();
		}
		$rawfiles = scandir($path);
		$allVersions = array();
		$count = 0;
		foreach($rawfiles as $uuid) {
			if($uuid == ".." || $uuid == ".") {
				continue;
			}
			if($count == AppVersion::MAX_VERSIONS) {
				break;
			}
			$uuidPath = $utils->joinPaths(array($path,$uuid));
			if(is_dir($uuidPath)) {
				$av = AppVersion::GetAppVersion($uuid);
				if($av) {
					array_push($allVersions,$av);
					$count++;
				}
			}
		}
		usort($allVersions,array("SoccerUtils","sortDescendingByDate"));
		//usort($allVersions,array("SoccerUtils","sortDescendingByName"));
		return $allVersions;
	}

	function getReleaseNotes() {
		$utils = SoccerUtils::getInstance();
		if(!$this->releaseNotes) {
			$content = $utils->readFileContent($this->releaseNotesPath);
			$parsedown = new Parsedown();
			$this->releaseNotes = $parsedown->text($content);
		}
		return $this->releaseNotes;
	}

	function save() {
		$utils = SoccerUtils::getInstance();
		$path = $utils->joinPaths(array($utils->versionsPath,$this->uuid));
		
		if(!file_exists($path)) {
			mkdir($path);
		}

		if($this->tmpFilePath) {
			if(!move_uploaded_file($this->tmpFilePath,$this->executablePath)) {
				error_log("Error moving uploaded executable file. " . $this->uuid);
				return FALSE;
			}
		}

		if($this->releaseNotes) {
			if(!$utils->writeFileContent($this->releaseNotesPath,$this->releaseNotes)) {
				error_log("Error writing release notes for app version. " . $this->uuid);
			}
		}

		return TRUE;
	}

	function delete() {
		$utils = SoccerUtils::getInstance();
		$path = $utils->joinPaths(array($utils->versionsPath,$this->uuid));
		if(file_exists($path)) {
			$utils->rrmdir($path);
		}
	}

	function getIOSInstallPlist($icon) {
		$utils = SoccerUtils::getInstance();
		$icon = $utils->joinPaths(array($utils->baseURL,"assets","icon.png"));
		$savant = new Savant3();
		$savant->icon = $icon;
		$savant->appVersion = $this;
		$result = $savant->fetch("templates/template.app.plist.php");
		return $result;
	}

	function getApplicationURL() {
		$utils = SoccerUtils::getInstance();
		return $utils->joinPaths(array($utils->versionsURL,$this->uuid,$this->name));
	}
}

?>
