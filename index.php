<?php
require_once("php/soccer.php");
class Handler {
	function __construct(){
		$soccer = new Soccer(FALSE);
		echo $soccer->handleRequest();
	}
}
new Handler();
?>