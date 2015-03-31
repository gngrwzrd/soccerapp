<?php

class AppCrash {
	
	var $name;
	var $date;
	var $datestring;
	
	function __construct($path,$name) {
		$this->name = $name;
		$this->date = filemtime($path);
		$this->datestring = date("m/d/Y",$this->date);
	}
}

?>