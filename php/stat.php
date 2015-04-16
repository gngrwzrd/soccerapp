<?php

require_once("soccer.utils.php");

class Stat {
	
	var $uuid;
	var $type; //install,ios-device-reg,mac-device-reg
	var $date;
	var $user;
	
	const MAX_STATS_ON_DASHBOARD = 40;
	const TYPE_INSTALL = "install";
	const TYPE_DOWNLOAD = "download";
	const TYPE_MAC_DEVICE = "macdevice";
	const TYPE_IOS_DEVICE = "iosdevice";
	
	static function NewStat($type,$user) {
		$utils = SoccerUtils::getInstance();
		$stat = new Stat();
		$stat->uuid = $utils->uuid();
		$stat->type = $type;
		$stat->user = $user;
		$stat->date = time();
		$path = $utils->joinPaths(array($utils->statsPath,$stat->uuid));
		if(!file_exists($path)) {
			mkdir($path);
		}
		return $stat;
	}

	static function StatAtPath($path) {
		$utils = SoccerUtils::getInstance();
		$content = $utils->readFileContent($path);
		$data = json_decode($content);
		$stat = new Stat();
		$stat->uuid = $data->uuid;
		$stat->date = filemtime($path);
		$stat->type = $data->type;
		$stat->user = $data->user;
		return $stat;
	}
	
	static function GetAllStats($count=-1) {
		$utils = SoccerUtils::getInstance();
		$dirs = $utils->getDirsAtPath($utils->statsPath);
		$stats = array();
		foreach($dirs as $uuid) {
			$path = $utils->joinPaths(array($utils->statsPath,$uuid,'stat.json'));
			$stat = Stat::StatAtPath($path);
			array_push($stats,$stat);
		}
		usort($stats,array("SoccerUtils","sortDescendingByDate"));
		if($count > -1) {
			return array_slice($stats,0,$count);
		}
		return $stats;
	}
	
	function save() {
		$utils = SoccerUtils::getInstance();
		$filename = "stat.json";
		$json = json_encode($this);
		$path = $utils->joinPaths(array($utils->statsPath,$this->uuid,$filename));
		$utils->writeFileContent($path,$json);
	}
	
	function getDashboardMessage() {
		$message = '';
		$message .= $this->user->firstname;
		$message .= ' ' . $this->user->lastname;
		if($this->type == "download") {
			$message .= ' downloaded';
		}
		if($this->type == "install") {
			$message .= ' installed';
		}
		return $message;
	}
}

?>
