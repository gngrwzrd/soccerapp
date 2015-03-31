<?php

define("BASE_PATH",dirname(__FILE__));
require_once(BASE_PATH . "/Savant/Savant3.php");
require_once(BASE_PATH . "/uuid.php");
require_once(BASE_PATH . "/AppVersion.php");
require_once(BASE_PATH . "/AppCrash.php");

date_default_timezone_set('America/Los_Angeles');

function sortDescendingByDate($a, $b) {
	return $a->date < $b->date;
}

class Soccer {
	
	var $basePath;
	var $baseURL;
	var $baseDashboardURL;
	var $appDataPath;
	var $appData;
	var $crashPath;
	var $isDashboard;

	function __construct($isDashboard) {
		$this->isDashboard = $isDashboard;
		if(isset($_SERVER['HTTPS'])) {
			$this->baseURL = 'https://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
		} else {
			$this->baseURL = 'http://' . $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
		}
		$this->baseURL = rtrim($this->baseURL,'/');
		$this->basePath = realpath(dirname(__FILE__) . '/..');
		$this->baseDashboardURL = $this->joinPaths(array($this->baseURL,"dashboard.php"));
		$this->appDataPath = $this->joinPaths(array($this->basePath,"app.json"));
		$this->appData = json_decode($this->readFileContent($this->appDataPath));
		$this->crashPath = $this->joinPaths(array($this->basePath,"crash"));
	}
	
	function joinPaths($paths=array()) {
		$out = $paths[0];
		for($i = 1; $i < count($paths); $i++) {
			$out .= '/' . $paths[$i];
		}
		return $out;
	}
	
	function readFileContent($path) {
		$size = filesize($path);
		$handle = fopen($path,"r");
		$content = fread($handle,$size);
		fclose($handle);
		return $content;
	}

	function writeFileContent($path,$content) {
		$handle = fopen($path,"w");
		fwrite($handle,$content);
		fclose($handle);
	}

	function getFilesAtPath($path, $ext=array(), $ignore=array()) {
		$rawfiles = scandir($path);
		$realfiles = array();
		for($i = 0; $i < count($rawfiles); $i++) {
			if($rawfiles[$i] != "." && $rawfiles[$i] != "..") {
				$fullpath = $path . "/" . $rawfiles[$i];
				if(is_dir($fullpath)) {
					continue;
				}
				if(count($ext) > 0) {
					$info = pathinfo($fullpath);
					if(array_search($info['extension'],$ext) > -1) {
						if(!array_search($info['filename'],$ignore)) {
							array_push($realfiles,$rawfiles[$i]);
						}
					}
				} else {
					if(!array_search($rawfiles[$i],$ignore)) {
						array_push($realfiles,$rawfiles[$i]);
					}
				}
			}
		}
		return $realfiles;
	}

	function getDeviceUDIDFromData($data) {
		//device id
		$matches = array();
		preg_match('/[a-zA-Z0-9]{40}/',$data,$matches);
		$device = $matches[0];
		return $device;
	}

	function getDeviceModelFromData($data) {
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

	function saveDeviceIdAndModel($device,$model) {
		$devices = $this->joinPaths(array($this->basePath,"devices.txt"));
		
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
		
		fclose($handle);
	}
	
	function getRequestVar($var,$default=False) {
		if(isset($_REQUEST[$var])) {
			return $_REQUEST[$var];
		}
		return $default;
	}

	function getAllVersions() {
		$path = $this->joinPaths(array($this->basePath,"versions"));

		if(!file_exists($path)) {
			return array();
		}

		$rawfiles = scandir($path);
		$allVersions = array();
		
		//rawfiles will be UDID folders.
		foreach($rawfiles as $UDID) {
			if($UDID == ".." || $UDID == ".") {
				continue;
			}
			$UDIDPath = $this->joinPaths(array($path,$UDID));
			if(is_dir($UDIDPath)) {
				//UDID is dir, look for files in that dir.
				$versions = $this->getFilesAtPath($UDIDPath,array("ipa"));
				if(count($versions) == 1) {
					$filename = $versions[0];
					$filepath = $this->joinPaths(array($UDIDPath,$filename));	
					$appVersion = new AppVersion($filepath,$UDID,$filename);
					array_push($allVersions,$appVersion);
				} else if(count($versions) > 1) {
					error_log("Version folder has multiple versions in it. " . $UDIDPath);
				} else if(count($versions) < 1) {
					error_log("Version folder has no versions in it. " . $UDIDPath);
				}
			}
		}
		usort($allVersions,"sortDescendingByDate");
		return $allVersions;
	}

	function getVersionFileName($version) {
		$path = $this->joinPaths(array($this->basePath,"versions"));
		$rawfiles = scandir($path);
		foreach($rawfiles as $UDID) {
			if($UDID == $version) {
				$UDIDPath = $this->joinPaths(array($path,$UDID));
				$versions = $this->getFilesAtPath($UDIDPath,array("ipa"));
				if(count($versions) == 1) {
					return $versions[0];
				} else if(count($versions) > 1) {
					error_log("Version folder has multiple versions in it. " . $UDIDPath);
				} else if(count($versions) == 0) {
					error_log("Version folder has no versions in it. " . $UDIDPath);
				}
			}
		}
		return "";
	}

	function getAllCrashes() {
		$crashes = $this->getFilesAtPath($this->crashPath,array("txt"));
		$allcrashes = array();
		foreach ($crashes as $crashfile) {
			$path = $this->joinPaths(array($this->crashPath,$crashfile));
			$crash = new AppCrash($path,$crashfile);
			array_push($allcrashes,$crash);
		}
		usort($allcrashes,"sortDescendingByDate");
		return $allcrashes;
	}
	
	function handleRequest() {
		$action = $this->getRequestVar('a');
		if($this->isDashboard) {
			switch ($action) {
				case 'install':
					return $this->installApplication();
				case 'newversion':
					return $this->newVersion();
				case 'submitnewversion':
					return $this->submitNewVersion();
					break;
				case 'crashintegrate':
					return $this->integrateCrashReports();
				default:
					return $this->dashboardIndex();
					break;
			}
		} else {
			switch($action) {
				case 'register':
					return $this->registerDevice();
				case 'config':
					return $this->serveMobileConfigFile();
				case 'payload':
					return $this->handleConfigPayload();
				case 'success':
					return $this->deviceSuccess();
				case 'install':
					return $this->installApplication();
				case 'crash':
					return $this->handleCrash();
				case 'plist':
					return $this->servePlist();
				default:
					return $this->registerDevice();
			}
		}
	}

	function createMobileConfig() {
		$template = $this->readFileContent($this->joinPaths(array($this->basePath,"templates","template.mobileconfig")));
		$savant = new Savant3();
		$savant->retrieveURL = $this->baseURL . "?a=payload";
		$result = $savant->fetch("templates/template.mobileconfig.php");
		$path = $this->joinPaths(array($this->basePath,"profile.mobileconfig"));
		$this->writeFileContent($path,$result);
		//TODO: sign config if ssl certs are in the dir.
		return $result;
	}

	function setMetaDataOnSavant($savant) {
		$savant->applicationName = $this->appData->name;
		$savant->applicationBundleId = $this->appData->bundleId;
		$savant->enterprise = $this->appData->enterprise;
	}

	function registerDevice() {
		$savant = new Savant3();
		$savant->mobileConfigURL = $this->baseURL."?a=config";
		$this->setMetaDataOnSavant($savant);
		$result = $savant->fetch("templates/actions.register.php");
		return $result;
	}

	function handleConfigPayload() {
		$data = file_get_contents('php://input');
		$device = $this->getDeviceUDIDFromData($data);
		$model = $this->getDeviceModelFromData($data);
		$this->saveDeviceIdAndModel($device,$model);
		header("Location: " . $this->baseURL."?a=success");
	}

	function installApplication() {
		$savant = new Savant3();
		$this->setMetaDataOnSavant($savant);
		$savant->applicationPlist = urlencode($this->baseURL."?a=plist&v=".$this->getRequestVar('v'));
		$result = $savant->fetch("templates/actions.install.php");
		return $result;
	}

	function servePlist() {
		$version = $this->getRequestVar('v');
		$filename = $this->getVersionFileName($version);
		$savant = new Savant3();
		$savant->icon = $this->joinPaths(array($this->baseURL,"assets","icon.png"));
		$savant->applicationURL = $this->joinPaths(array($this->baseURL,"versions",$version,$filename));
		$this->setMetaDataOnSavant($savant);
		$result = $savant->fetch("templates/template.app.plist.php");
		header("Content-Type: application/xml");
		echo $result;
	}

	function serveMobileConfigFile() {
		$config = $this->joinPaths(array($this->basePath,"profile.mobileconfig"));
		if(file_exists($config)) {
			$content = $this->readFileContent($config);
		} else {
			$this->createMobileConfig();
			$content = $this->readFileContent($config);
		}
		header("Content-Type: application/x-apple-aspen-config");
		echo $content;
	}

	function deviceSuccess() {
		$savant = new Savant3();
		$this->setMetaDataOnSavant($savant);
		$result = $savant->fetch("templates/actions.success.php");
		return $result;	
	}

	function handleCrash() {
		$data = file_get_contents('php://input');
		if(!file_exists($this->crashPath)) {
			mkdir($this->crashPath);
		}
		$uuid = UUID::v4();
		$path = $this->joinPaths(array($this->crashPath,$uuid . ".txt"));
		$this->writeFileContent($path,$data);
	}
	
	function dashboardIndex() {
		$versions = $this->getAllVersions();
		$latestVersion = $versions[0];
		$crashes = $this->getAllCrashes();
		$savant = new Savant3();
		$this->setMetaDataOnSavant($savant);
		$savant->versions = $versions;
		$savant->crashes = $crashes;
		$savant->crashLink = $this->joinPaths(array($this->baseURL,"crash"));
		$savant->recruitLink = $this->baseURL;
		$savant->installLink = $this->baseURL . "?a=install&v=" . $latestVersion->uuid;
		$result = $savant->fetch("templates/actions.dashboard.index.php");
		return $result;
	}
	
	function integrateCrashReports() {
		$savant = new Savant3();
		$this->setMetaDataOnSavant($savant);
		$savant->crashURL = $this->joinPaths(array($this->baseURL,"crash"));
		$result = $savant->fetch("templates/actions.dashboard.crashintegrate.php");
		return $result;
	}

	function newVersion() {
		$savant = new Savant3();
		$this->setMetaDataOnSavant($savant);
		$savant->dashboardLink = $this->dashboardLink;
		$result = $savant->fetch("templates/actions.dashboard.newversion.php");
		return $result;
	}

	function submitNewVersion() {
		$file = $_FILES['ipaFile'];
		$tmpfile = $file['tmp_name'];
		$ipaName = $file['name'];
		$uuid = UUID::v4();
		$path = $this->joinPaths(array($this->basePath,"versions",$uuid));	
		mkdir($path);
		$path = $this->joinPaths(array($path,$ipaName));
		copy($tmpfile,$path);
	}
}
?>