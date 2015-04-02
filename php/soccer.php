<?php

session_start();

define("BASE_PATH",dirname(__FILE__));
require_once(BASE_PATH . "/uuid.php");
require_once(BASE_PATH . "/AppVersion.php");
require_once(BASE_PATH . "/AppCrash.php");
require_once(BASE_PATH . "/AppCrashGroup.php");
require_once(BASE_PATH . "/soccer.utils.php");
require_once(BASE_PATH . "/Savant/Savant3.php");
require_once(BASE_PATH . "/parsedown/Parsedown.php");

class Soccer {
	
	var $config;
	var $appData;
	var $isDashboard;
	var $debug;

	function __construct($isDashboard) {
		$this->debug = TRUE;
		$this->isDashboard = $isDashboard;
		$this->config = SoccerUtils::getInstance();
		$this->appData = json_decode($this->config->readFileContent($this->config->appDataPath));
	}
	
	function handleRequest() {
		$action = $this->config->getRequestVar('a');
		if($this->isDashboard) {
			switch ($action) {
				case 'install':
					return $this->installApplication();
				case 'newversion':
					return $this->newVersion();
				case 'submitnewversion':
					return $this->submitNewVersion();
					break;
				case 'releasenotes':
					return $this->releaseNotes();
				case 'crashintegrate':
					return $this->integrateCrashReports();
				case 'onlyversions':
					return $this->dashboardIndex('onlyversions');
				case 'onlycrashes':
					return $this->dashboardIndex('onlycrashes');
				case 'onlydevices':
					return $this->dashboardIndex('onlydevices');
				case 'onlystats':
					return $this->dashboardIndex('onlystats');
				case 'delversion':
					return $this->deleteVersion();
				case 'delcrash':
					return $this->deleteCrash();
				case 'faq':
					return $this->dashboardFAQ();
				default:
					return $this->dashboardIndex();
					break;
			}
		} else {
			switch($action) {
				case 'register':
					return $this->registerDevice();
				case 'newuser':
					return $this->newUserSubmitted();
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
				case 'faq':
					return $this->faq();
				default:
					return $this->registerDevice();
			}
		}
	}

	function joinPaths($paths=array()) {
		$out = $paths[0];
		for($i = 1; $i < count($paths); $i++) {
			$out .= '/' . $paths[$i];
		}
		return $out;
	}

	function saveDeviceIdAndModel($device,$model) {
		$devices = $this->joinPaths(array($this->config->basePath,"devices.txt"));
		
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

	
	
	function setMetaDataOnSavant($savant) {
		$savant->debug = $this->debug;
		$savant->applicationName = $this->appData->name;
		$savant->applicationBundleId = $this->appData->bundleId;
		$savant->enterprise = $this->appData->enterprise;
	}

	function newUserSubmitted() {
		$redirect = $this->config->getRequestVar("r");
		$firstname = $this->config->getRequestVar("firstName");
		$lastname = $this->config->getRequestVar("lastName");
		$email = $this->config->getRequestVar("email");
		$uuid = $this->config->UUID();
		$userFolder = $this->config->getUserFolderPath($uuid);
		mkdir($userFolder);
		$userFilePath = $this->config->getUserJSONDataPath($uuid);
		$data = array('firstName'=>$firstname,'lastName'=>$lastname,'email'=>$email);
		$json = json_encode($data);
		$this->config->writeFileContent($userFilePath,$json);
		$this->config->setSession($this->config->userUUIDSessionVar,$uuid);
		header("Location: " . $this->config->baseURL . "?a=" . $redirect);
	}

	function registerDevice() {
		$userUUID = $this->config->getSession($this->config->userUUIDSessionVar);
		$savant = new Savant3();
		$this->setMetaDataOnSavant($savant);
		$userFile = $this->config->getUserJSONDataPath($userUUID);
		if(!$userUUID || !file_exists($userFile)) {
			$savant->redirectAction = "register";
			$result = $savant->fetch("templates/actions.userform.php");
			return $result;
		}
		$savant->mobileConfigURL = $this->config->baseURL."?a=config";
		$result = $savant->fetch("templates/actions.register.php");
		return $result;
	}

	function serveMobileConfigFile() {
		$userUUID = $this->config->getSession($this->config->userUUIDSessionVar);
		$cachedPath = $this->config->getCachedUserMobileConfigPath($userUUID);
		$result = $this->config->getCachedUserMobileConfig($userUUID);
		if(!$result) {
			$savant = new Savant3();
			$savant->retrieveURL = $this->config->baseURL . "?a=payload&amp;u=" . $userUUID;
			$result = $savant->fetch("templates/template.mobileconfig.php");
			$this->config->writeFileContent($cachedPath,$result);
		}
		header("Content-Type: application/x-apple-aspen-config");
		echo $result;
	}

	function handleConfigPayload() {
		$data = file_get_contents('php://input');
		$userUUID = $this->config->getRequestVar("u");
		$device = $this->config->getIOSDeviceUDIDFromData($data);
		$model = $this->config->getIOSDeviceModelFromData($data);
		//TODO: add user info to the devices saved.
		//TODO: Maybe switch what's saved to txt files in the devices/ folder. That's all easier to read/parse than a single txt file.
		$this->saveDeviceIdAndModel($device,$model);
		header("Location: " . $this->config->regiseredURL);
	}

	function installApplication() {
		$userUUID = $this->config->getSession($this->config->userUUIDSessionVar);
		$savant = new Savant3();
		$this->setMetaDataOnSavant($savant);
		$version = $this->config->getRequestVar("v");
		$savant->version = $version;
		
		//check if the user has been tagged. if not make them fill out info.
		if(!$userUUID) {
			$savant->redirectAction = "install";
			$result = $savant->fetch("templates/actions.userform.php");
			return $result;
		}

		$savant->versionName = $this->config->getVersionFileName($version);
		$savant->releaseNotes = $this->config->getReleaseNotesForVersion($version);
		$savant->applicationPlist = urlencode($this->config->baseURL."?a=plist&v=".$version);
		$result = $savant->fetch("templates/actions.install.php");
		return $result;
	}

	function servePlist() {
		$version = $this->config->getRequestVar('v');
		$filename = $this->config->getVersionFileName($version);
		$savant = new Savant3();
		$savant->icon = $this->joinPaths(array($this->config->baseURL,"assets","icon.png"));
		$savant->applicationURL = $this->joinPaths(array($this->config->versionsPath,$version,$filename));
		$this->setMetaDataOnSavant($savant);
		$result = $savant->fetch("templates/template.app.plist.php");
		header("Content-Type: application/xml");
		echo $result;
	}

	function deviceSuccess() {
		$savant = new Savant3();
		$this->setMetaDataOnSavant($savant);
		$result = $savant->fetch("templates/actions.success.php");
		return $result;
	}
	
	function handleIOSCrash() {
		$data = file_get_contents('php://input');
		$uuid = $this->config->UUID();
		$version = $this->config->getVersionFromIOSCrash($data);
		$versionPath = $this->joinPaths(array($this->config->crashPath,$version));
		mkdir($versionPath);
		$path = $this->joinPaths(array($versionPath,$uuid.".txt"));
		$this->config->writeFileContent($path,$data);
	}
	
	function dashboardIndex($filter="") {
		$versions = $this->config->getAllVersions();
		$latestVersion = $versions[0];
		$savant = new Savant3();
		$this->setMetaDataOnSavant($savant);
		$savant->versions = $versions;
		$savant->crashGroups = $this->config->getAllCrashGroups();
		$savant->filter = $filter;
		$savant->dashboardLink = $this->config->dashboardURL;
		$savant->crashLink = $this->joinPaths(array($this->config->baseURL,"crash"));
		$savant->recruitLink = $this->config->baseURL;
		$savant->installLink = $this->config->baseURL . "?a=install&v=" . $latestVersion->uuid;
		$result = $savant->fetch("templates/actions.dashboard.index.php");
		return $result;
	}
	
	function integrateCrashReports() {
		$savant = new Savant3();
		$this->setMetaDataOnSavant($savant);
		$savant->crashURL = $this->joinPaths(array($this->config->baseURL,"crash"));
		$result = $savant->fetch("templates/actions.dashboard.crashintegrate.php");
		return $result;
	}
	
	function newVersion() {
		$savant = new Savant3();
		$this->setMetaDataOnSavant($savant);
		$savant->dashboardLink = $this->config->dashboardURL;
		$result = $savant->fetch("templates/actions.dashboard.newversion.php");
		return $result;
	}
	
	function submitNewVersion() {
		//setup vars.
		$file = $_FILES['ipaFile'];
		$tmpfile = $file['tmp_name'];
		$ipaName = $file['name'];
		$releaseNotes = $this->config->getRequestVar("releaseNotes");
		$uuid = $this->config->UUID();
		
		//make new UDID folder in versions/
		$path = $this->joinPaths(array($this->config->versionsPath,$uuid));
		mkdir($path);

		//copy uploaded file to new folder.
		$path = $this->joinPaths(array($path,$ipaName));
		copy($tmpfile,$path);
		
		//save release notes.
		$path = $this->joinPaths(array($this->config->versionsPath,$uuid,$ipaName.".txt"));
		$this->config->writeFileContent($path,$releaseNotes);

		//redirect back to dashboard.
		header("Location: " . $this->config->dashboardURL);
	}
	
	function releaseNotes() {
		$version = $this->config->getRequestVar("v");
		$savant = new Savant3();
		$this->setMetaDataOnSavant($savant);
		$savant->versionName = $this->config->getVersionFileName($version);
		$savant->releaseNotes = $this->config->getReleaseNotesForVersion($version);
		$result = $savant->fetch("templates/actions.dashboard.releasenotes.php");
		return $result;
	}
	
	function deleteVersion() {
		$version = $this->config->getRequestVar("v");
		$filter = $this->config->getRequestVar("filter");
		$path = $this->joinPaths(array($this->config->versionsPath,$version));
		if(file_exists($path)) {
			$this->config->rrmdir($path);
		}
		$redir = $this->config->dashboardURL;
		if($filter) {
			$redir .= "?a=".$filter;
		}
		header("Location: " . $redir);
	}

	function deleteCrash() {
		$crash = $this->config->getRequestVar("c");
		$version = $this->config->getRequestVar("v");
		$filter = $this->config->getRequestVar("filter");
		$path = $this->joinPaths(array($this->config->crashPath,$version,$crash));
		if(file_exists($path)) {
			unlink($path);
		}
		$redir = $this->config->dashboardURL;
		header("Location: " . $redir);
	}

	function dashboardFAQ() {
		$savant = new Savant3();
		$this->setMetaDataOnSavant($savant);
		$result = $savant->fetch("templates/actions.faq.php");
		return $result;
	}

	
}
?>