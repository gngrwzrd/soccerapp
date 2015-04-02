<?php

class AppCrash {
	
	var $name;
	var $date;
	var $datestring;

	function saveCrash($data) {

	}

	function initWithPath($path) {
		$this->name = basename($path);
		$this->date = filemtime($path);
		$this->datestring = date("m/d/Y",$this->date);
	}
}

?>