<?php

require_once("soccer.utils.php");

class Device {

	var $uuid;
	var $user;
	var $deviceId;
	var $model;

	static function NewMacDevice($user,$hardwareId,$model) {
		$device = new Device();
		$device->uuid = $hardwareId;
		$device->user = $user;
		$device->deviceId = $hardwareId;
		$device->model = $model;
		return $device;
	}

	static function NewDevice($user,$data) {
		$udid = Device::GetIOSDeviceUDIDFromData($data);
		if(Device::HasDevice($udid)) {
			return;
		}
		$utils = SoccerUtils::getInstance();
		$device = new Device();
		$device->uuid = $udid;
		$device->user = $user;
		$device->deviceId = $udid;
		$device->model = Device::GetIOSDeviceModelFromData($data);
		return $device;
	}

	static function DeleteDevice($deviceId) {
		$utils = SoccerUtils::getInstance();
		$path = $utils->joinPaths(array($utils->devicesPath,$deviceId.'.json'));
		if(file_exists($path)) {
			unlink($path);
		}
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

	static function GetDevice($uuid) {
		$utils = SoccerUtils::getInstance();
		$path = $utils->joinPaths(array($utils->devicesPath,$uuid.".json"));
		$data = json_decode($utils->readFileContent($path));
		$device = new Device();
		$device->uuid = $uuid;
		$device->deviceId = $data->deviceId;
		$device->model = $data->model;
		$device->user = new User();
		$device->user->firstname = $data->firstname;
		$device->user->lastname = $data->lastname;
		$device->user->email = $data->email;
		return $device;
	}

	static function HasDevice($uuid) {
		$utils = SoccerUtils::getInstance();
		$path = $utils->joinPaths(array($utils->devicesPath,$uuid.'.json'));
		if(file_exists($path)) {
			return TRUE;
		}
		return FALSE;
	}

	static function GetDeviceAtPath($path) {
		$filename = basename($path);
		$uuid = preg_replace('/\.json/',"",$filename);
		return Device::GetDevice($uuid);
	}
	
	static function GetExportAllDevices() {
		$devices = Device::GetAllDevices();
		$devicesOutput = "deviceIdentifier\tdeviceName\n";
		foreach($devices as $device) {
			$name = $device->user->firstname . ' ' . $device->user->lastname;
			$name .= ' ' . $device->model;
			$devicesOutput .= $device->deviceId . "\t" . $name . "\n";
		}
		return $devicesOutput;
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
		error_log("Device.save " . $path);
		$data = array(
			"deviceId" => $this->deviceId,
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