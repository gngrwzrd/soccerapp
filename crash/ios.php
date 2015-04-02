<?php
require_once("../php/soccer.php");
class Handler {
	function __construct(){
		$soccer = new Soccer();
		echo $soccer->handleIOSCrash();
	}
}
new Handler();
?>