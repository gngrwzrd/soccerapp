<?php

require_once("soccer.utils.php");

class Device {

	var $uuid;
	var $user;
	var $udid;
	var $model;

	//{udid,device,model,firstname,lastname,email,exported}

	static function ExportAllDevices() {
		/*$devices = $this->util->joinPaths(array($this->config->basePath,"devices.txt"));
		
		if(!file_exists($devices)) {
			$handle = fopen($devices,"w");
			fwrite($handle,"deviceIdentifier\tdeviceName\n");
			fclose($handle);
		}
		
		$size = filesize($devices);
		$handle = fopen($devices,"r+");
		$write = TRUE;
		
		while(($line=fgets($handle))) {
			if(preg_match('/'.$device.'/',$line)) {
				$write = FALSE;
			}
		}
		
		if($write) {
			$line = $device . "\t" . $model . "\n";
			fseek($handle,$size);
			fwrite($handle,$line);
		}
		
		fclose($handle);*/
	}

	static function GetDevice($uuid) {
		$utils = SoccerUtils::getInstance();
		$path = $utils->joinPaths(array($utils->devicesPath,$uuid.".json"));
		$data = json_decode($utils->readFileContent($path));
		$device = new Device();
		$device->uuid = $uuid;
		$device->udid = $data->udid;
		$device->model = $data->model;
		$device->user = new User();
		$device->user->firstname = $data->firstname;
		$device->user->lastname = $data->lastname;
		$device->user->email = $data->email;
		return $device;
	}

	static function GetDeviceAtPath($path) {
		$filename = basename($path);
		$uuid = preg_replace('/\.json/',"",$filename);
		return Device::GetDevice($uuid);
	}

	static function NewDevice($user,$data) {
		$utils = SoccerUtils::getInstance();
		$device = new Device();
		$device->uuid = $utils->uuid();
		$device->user = $user;
		$device->udid = Device::GetIOSDeviceUDIDFromData($data);
		$device->model = Device::GetIOSDeviceModelFromData($data);
		return $device;
	}

	static function GetAllDevices() {
		$utils = SoccerUtils::getInstance();
		$devices = $utils->getFilesAtPath($utils->devicesPath,array("json"));
		$result = array();
		foreach ($devices as $device) {
			$d = Device::GetDeviceAtPath($device);
			array_push($result,$d);
		}
		return $result;
	}

	static function GetIOSDeviceUDIDFromData($data) {
		//device id
		$matches = array();
		preg_match('/[a-zA-Z0-9]{40}/',$data,$matches);
		$device = $matches[0];
		return $device;
	}
	
	static function GetIOSDeviceModelFromData($data) {
		//iPhone
		$matches = array();
		preg_match('/iPhone[0-9]{1,2}\,[0-9]{1,2}/',$data,$matches);
		if(count($matches) > 0) {
			$model = $matches[0];
		}
		
		//iPad
		$matches = array();
		preg_match('/iPad[0-9]{1,2}\,[0-9]{1,2}/',$data,$matches);
		if(count($matches) > 0) {
			$model = $matches[0];
		}

		//iPod
		$matches = array();
		preg_match('/iPod[0-9]{1,2}\,[0-9]{1,2}/',$data,$matches);
		if(count($matches) > 0) {
			$model = $matches[0];
		}

		return $model;
	}

	function save() {
		$utils = SoccerUtils::getInstance();
		$path = $utils->joinPaths(array($utils->devicesPath,$this->uuid . ".json"));
		$data = array(
			"udid" => $this->udid,
			"model" => $this->model,
			"firstname" => $this->user->firstname,
			"lastname" => $this->user->lastname,
			"email" => $this->user->email,
			"exported" => FALSE,
		);
		$json = json_encode($data);
		$utils->writeFileContent($path,$json);
	}

	function delete() {
		$utils = SoccerUtils::getInstance();
		$path = $utils->joinPaths(array($utils->devicesPath,$this->uuid . ".json"));
		if(file_exists($path)) {
			unlink($path);
		}
	}

}

?>