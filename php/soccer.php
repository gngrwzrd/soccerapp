<?php

session_start();

define("BASE_PATH",dirname(__FILE__));
require_once(BASE_PATH . "/uuid.php");
require_once(BASE_PATH . "/soccer.utils.php");
require_once(BASE_PATH . "/user.php");
require_once(BASE_PATH . "/appversion.php");
require_once(BASE_PATH . "/crash.php");
require_once(BASE_PATH . "/device.php");
require_once(BASE_PATH . "/Savant/Savant3.php");
require_once(BASE_PATH . "/parsedown/Parsedown.php");

class Soccer {
	
	var $config;
	var $isDashboard;
	var $debug;

	function __construct($isDashboard) {
		$this->debug = TRUE;
		$this->isDashboard = $isDashboard;
		$this->config = SoccerUtils::getInstance();
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
		}

		if(!$this->isDashboard) {
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

	function getDefaultSavant() {
		$utils = SoccerUtils::getInstance();
		$savant = new Savant3();
		$savant->debug = $this->debug;
		$savant->applicationName = $utils->appData->name;
		$savant->applicationBundleId = $utils->appData->bundleId;
		$savant->enterprise = $utils->appData->enterprise;
		return $savant;
	}
	
	function dashboardIndex($filter="") {
		$versions = AppVersion::GetAllAppVersions();
		$latestVersion = $versions[0];
		$savant = $this->getDefaultSavant();
		$savant->versions = $versions;
		$savant->crashes = Crash::GetAllCrashes();
		$savant->devices = Device::GetAllDevices();
		$savant->filter = $filter;
		$savant->dashboardLink = $this->config->dashboardURL;
		$savant->crashLink = $this->config->joinPaths(array($this->config->baseURL,"crash"));
		$savant->recruitLink = $this->config->baseURL;
		$savant->installLink = $this->config->baseURL . "?a=install&v=" . $latestVersion->uuid;
		$result = $savant->fetch("templates/actions.dashboard.index.php");
		return $result;
	}

	/** user submission handler **/

	function newUserSubmitted() {
		$redirect = $this->config->getRequestVar("r");
		$versionUUID = $this->config->getRequestVar("v");
		$firstname = $this->config->getRequestVar("firstName");
		$lastname = $this->config->getRequestVar("lastName");
		$email = $this->config->getRequestVar("email");
		$user = User::NewUser($firstname,$lastname,$email);
		$user->save();
		$redirectURL = $this->config->baseURL . "?a=" . $redirect;
		if($versionUUID) {
			$redirect .= '&v=' . $versionUUID;
		}
		header("Location: " . $this->config->baseURL . "?a=" . $redirect);
	}

	/** device registration methods **/

	//step 1, show either user or registration template.
	function registerDevice() {
		$savant = $this->getDefaultSavant();
		$user = User::GetUserFromSession();
		if(!$user) {
			$savant->formActionURL = $this->config->baseURL . "?a=newuser&r=register";
			$result = $savant->fetch("templates/actions.userform.php");
			return $result;
		}
		$savant->mobileConfigURL = $this->config->baseURL."?a=config";
		$result = $savant->fetch("templates/actions.register.php");
		return $result;
	}

	//step 2. service the mobileconfig file to iOS Settings
	function serveMobileConfigFile() {
		$user = User::GetUserFromSession();
		$content = $user->getMobileConfig();
		header("Content-Type: application/x-apple-aspen-config");
		echo $content;
	}

	//setp 3. Handle the callback from iOS. Get user info and device/model info.
	function handleConfigPayload() {
		$data = file_get_contents('php://input');
		$user = User::GetUser($this->config->getRequestVar("u"));
		$device = Device::NewDevice($user,$data);
		$device->save();
		header("Location: " . $this->config->regiseredURL);
	}

	//step 4. render success page.
	function deviceSuccess() {
		$savant = $this->getDefaultSavant();
		$result = $savant->fetch("templates/actions.success.php");
		return $result;
	}

	/** Install application methods **/

	//step 1. render either user form or install view.
	function installApplication() {
		$savant = $this->getDefaultSavant();
		$vuuid = $this->config->getRequestVar("v");
		
		//render user form if no user info in session.
		$user = User::GetUserFromSession();
		if(!$user) {
			$savant->formActionURL = $this->config->baseURL . "?a=newuser&r=install&v=" . $vuuid;
			$result = $savant->fetch("templates/actions.userform.php");
			return $result;
		}
		
		//get app version
		$av = AppVersion::GetAppVersion($vuuid);
		if(!$av) {
			$result = $savant->fetch("templates/404.php");
			return $result;
		}

		//render template
		$savant->appVersion = $av;
		$savant->applicationPlist = urlencode($this->config->baseURL."?a=plist&v=".$vuuid);
		$result = $savant->fetch("templates/actions.install.php");
		return $result;
	}

	//step 2. Serve plist file to iOS to install the application.
	function servePlist() {
		$uuid = $this->config->getRequestVar('v');
		$av = AppVersion::GetAppVersion($uuid);
		if(!$av) {
			$savant = $this->getDefaultSavant();
			$result = $savant->fetch("templates/404.php");
			return $result;
		}
		$result = $av->getIOSInstallPlist();
		header("Content-Type: application/xml");
		return $result; //used to be echo....
	}

	/** ios crash submission handler **/

	function handleIOSCrash() {
		$data = file_get_contents('php://input');
		$uuid = $this->config->UUID();
		$version = $this->config->getVersionFromIOSCrash($data);
		$versionPath = $this->config->joinPaths(array($this->config->crashPath,$version));
		mkdir($versionPath);
		$path = $this->config->joinPaths(array($versionPath,$uuid.".txt"));
		$this->config->writeFileContent($path,$data);
	}
	
	/** new version methods **/

	function newVersion() {
		$savant = $this->getDefaultSavant();
		$savant->dashboardLink = $this->config->dashboardURL;
		$result = $savant->fetch("templates/actions.dashboard.newversion.php");
		return $result;
	}
	
	function submitNewVersion() {
		//setup vars.
		$av = AppVersion::NewAppVersionFromSubmission();
		if(!$av->save()) {
			error_log("error saving new app version.");
		}
		
		//redirect back to dashboard.
		header("Location: " . $this->config->dashboardURL);
	}
	
	/** release notes detail page **/
	
	function releaseNotes() {
		$uuid = $this->config->getRequestVar("v");
		$av = AppVersion::GetAppVersion($uuid);
		if(!$av) {
			$savant = $this->getDefaultSavant();
			$result = $savant->fetch("templates/404.php");
			return $result;
		}
		$savant = $this->getDefaultSavant();
		$savant->appVersion = $av;
		$result = $savant->fetch("templates/actions.dashboard.releasenotes.php");
		return $result;
	}
	
	/** delete a version **/

	function deleteVersion() {
		$uuid = $this->config->getRequestVar("v");
		$av = AppVersion::GetAppVersion($uuid);
		if($av) {
			$av->delete();
		}
		$filter = $this->config->getRequestVar("filter");
		$redirect = $this->config->dashboardURL;
		if($filter) {
			$redirect .= "?a=".$filter;
		}
		header("Location: " . $redirect);
	}

	/** delete a crash **/
	
	function deleteCrash() {
		Crash::DeleteCrashFile($this->config->getRequestVar("c"));
		$filter = $this->config->getRequestVar("filter");
		$redir = $this->config->dashboardURL;
		if($filter) {
			$redit .= '?a=' . $filter;
		}
		header("Location: " . $redir);
	}

	/** FAQ **/

	function dashboardFAQ() {
		$savant = $this->getDefaultSavant();
		$result = $savant->fetch("templates/actions.faq.php");
		return $result;
	}

}
?>