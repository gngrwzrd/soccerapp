<?php

class AppVersion {
	
	var $name;
	var $extension;
	var $date;
	var $datestring;
	var $uuid;

	function __construct($path,$uuid,$name) {
		$this->uuid = $uuid;
		$this->name = $name;
		$info = pathinfo($path);
		$this->extension = $info['extension'];
		$this->date = filemtime($path);
		$this->datestring = date("m/d/Y",$this->date);
	}
}

?>
