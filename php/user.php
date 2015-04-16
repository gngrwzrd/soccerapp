<?php

require_once("php/Savant/Savant3.php");
require_once("soccer.utils.php");

class User {
	
	//vars for all users.
	var $uuid;
	var $firstname;
	var $lastname;
	var $email;

	static function NewUser($firstname,$lastname,$email) {
		$utils = SoccerUtils::getInstance();
		$user = new User();
		$user->firstname = $utils->getRequestVar("firstName");
		$user->lastname = $utils->getRequestVar("lastName");
		$user->email = $utils->getRequestVar("email");
		$user->uuid = $utils->UUID();
		return $user;
	}
	
	static function GetUser($uuid) {
		$util = SoccerUtils::getInstance();
		$json = $util->joinPaths(array($util->usersPath,$uuid,'user.json'));
		if(!file_exists($json)) {
			error_log("not exist");
			return NULL;
		}
		$content = $util->readFileContent($json);
		$data = json_decode($content);
		$user = new User();
		$user->firstname = $data->firstname;
		$user->lastname = $data->lastname;
		$user->email = $data->email;
		$user->uuid = $uuid;
		return $user;
	}

	static function GetUserFromSession() {
		$util = SoccerUtils::getInstance();
		$userUUID = False;

		if(isset($_COOKIE[$util->userSessionVar])) {
			$userUUID = $_COOKIE[$util->userSessionVar];
		}

		if(!$userUUID && isset($_SESSION[$util->userSessionVar])) {
			$userUUID = $_SESSION[$util->userSessionVar];
		}

		if($userUUID) {
			$user = User::GetUser($userUUID);
			if(!$user) {
				User::DeleteUser($userUUID);
			}
		}

		return $user;
	}
	
	static function DeleteUser($uuid) {
		$utils = SoccerUtils::getInstance();
		$path = $utils->joinPaths(array($utils->usersPath,$uuid));
		$utils->rrmdir($path);
		unset($_SESSION[$utils->userSessionVar]);
		unset($_COOKIE[$utils->userSessionVar]);
	}
	
	static function HasUser($uuid) {
		$result = False;
		$util = SoccerUtils::getInstance();
		$path = $util->joinPaths(array($util->usersPath,$uuid));
		if(file_exists($path) && is_dir($path)) {
			$path = $util->joinPaths(array($path,'user.json'));
			if(file_exists($path)) {
				$result = True;
			}
		}
		return $result;
	}
	
	function save() {
		$utils = SoccerUtils::getInstance();

		//create folder for user.
		$userFolder = $utils->joinPaths(array($utils->usersPath,$this->uuid));
		if(!file_exists($userFolder)) {
			mkdir($userFolder);
		}
		
		//json data.
		$jsonFilePath = $utils->joinPaths(array($userFolder,'user.json'));
		$data = array('firstname'=>$this->firstname,'lastname'=>$this->lastname,'email'=>$this->email);
		$json = json_encode($data);
		
		//write file content to folder.
		$utils->writeFileContent($jsonFilePath,$json);

		//save user to session.
		$this->saveToSession();
	}
	
	function saveToSession() {
		$util = SoccerUtils::getInstance();
		setcookie($util->userSessionVar,$this->uuid,time()+strtotime("+1 year"));
		$util->setSession($util->userSessionVar,$this->uuid);
	}
	
	function getMobileConfig() {
		$utils = SoccerUtils::getInstance();
		$savant = new Savant3();
		$savant->retrieveURL = $utils->baseURL . "?a=payload&amp;u=" . $this->uuid;
		$result = $savant->fetch("templates/template.mobileconfig.php");
		return $result;
	}
}

?>