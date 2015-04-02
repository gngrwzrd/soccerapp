<?php

require_once("soccer.utils.php");
require_once("AppCrash.php");

class AppCrashGroup {
	
	var $groupLabel;
	var $crashes; //array of AppCrash objects

	function __construct($groupLabel,$path) {
		$this->groupLabel = $groupLabel;
		$files = SoccerUtils::getInstance()->getFilesAtPath($path,array("txt"));
		$crashes = array();
		foreach($files as $crashlog) {
			$crashPath = SoccerUtils::getInstance()->joinPaths(array($path,$crashlog));
			$crash = new AppCrash();
			$crash->initWithPath($crashPath);
			array_push($crashes,$crash);
		}
		usort($crashes,array("SoccerUtils","sortDescendingByDate"));
		$this->crashes = array_slice($crashes,0,SoccerUtils::getInstance()->maxCrashesInGroup);
		//$this->crashes = $crashes;
	}

	static function sortDescendingByGroupLabel($a,$b) {
		return $a->date < $b->date;
	}

	static function AllAppGroups() {

	}
}
?>