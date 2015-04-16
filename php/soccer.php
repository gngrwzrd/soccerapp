<?php

session_start();

define("BASE_PATH",dirname(__FILE__));
require_once(BASE_PATH . "/uuid.php");
require_once(BASE_PATH . "/soccer.utils.php");
require_once(BASE_PATH . "/user.php");
require_once(BASE_PATH . "/appversion.php");
require_once(BASE_PATH . "/crash.php");
require_once(BASE_PATH . "/stat.php");
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
			case 'deldevice':
				return $this->deleteDevice();
			case 'exportalldevices':
				return $this->exportAllDevices();
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
				return $this->iOSDeviceSuccess();
			case 'install':
				return $this->installApplication();
			case 'crash':
				return $this->handleCrash();
			case 'plist':
				return $this->servePlist();
			case 'faq':
				return $this->faq();
			case 'newdevicemac':
				return $this->newDeviceMac();
			case 'newdevicemacsubmit':
				return $this->newDeviceMacSubmitted();
			case 'savestat':
				return $this->saveStat();
			default:
				return $this->registerDevice();
			}
		}
	}

	//returns default savant object with vars configured.
	function getDefaultSavant() {
		$utils = SoccerUtils::getInstance();
		$savant = new Savant3();
		$savant->debug = $this->debug;
		$savant->applicationName = $utils->appData->name;
		$savant->applicationBundleId = $utils->appData->bundleId;
		$savant->enterprise = $utils->appData->enterprise;
		$savant->appType = $utils->appData->type;
		return $savant;
	}
	
	//dashboard index with optional filter.
	//url: /dashboard.php
	//@param filer, onlyversions, onlycrashes, onlydevices
	function dashboardIndex($filter="") {
		error_log("dashboardIndex");
		$savant = $this->getDefaultSavant();
		
		if($filter == "onlyversions") {
			$savant->versions = AppVersion::GetAllAppVersions();
		} else {
			$savant->versions = AppVersion::GetAllAppVersions(AppVersion::MAX_VERSIONS);
		}
		
		if($filter == "onlystats") {
			$savant->stats = Stat::GetAllStats();
		} else {
			$savant->stats = Stat::GetAllStats(Stat::MAX_STATS_ON_DASHBOARD);
		}
		
		$latestVersion = AppVersion::GetLatestVersion();
		$savant->crashes = Crash::GetAllCrashes();
		$savant->devices = Device::GetAllDevices();
		$savant->stats = Stat::GetAllStats();
		$savant->filter = $filter;
		$savant->dashboardLink = $this->config->dashboardURL;
		$savant->recruitLink = $this->config->baseURL;
		$savant->installLink = $this->config->baseURL . "?a=install&v=" . $latestVersion->uuid;
		$result = $savant->fetch("templates/actions.dashboard.index.php");
		return $result;
	}

	//user submission handler
	//url: index.php?a=newuser
	//&r= redirect to wherever
	//&v= app version
	function newUserSubmitted() {
		error_log("newUserSubmitted");

		$firstname = $this->config->getRequestVar("firstName");
		$lastname = $this->config->getRequestVar("lastName");
		$email = $this->config->getRequestVar("email");
		$user = User::NewUser($firstname,$lastname,$email);
		$user->save();
		
		$redirect = $this->config->getRequestVar("r");
		$versionUUID = $this->config->getRequestVar("v");
		$redirectURL = $this->config->baseURL . "?a=" . $redirect;
		if($versionUUID) {
			$redirect .= '&v=' . $versionUUID;
		}
		header("Location: " . $this->config->baseURL . "?a=" . $redirect);
	}
	
	//url: index.php?a=register
	function registerDevice() {
		error_log("registerDevice");
		if($this->config->appData->type == "mac") {
			return $this->registerDeviceMac();
		}
		
		if($this->config->appData->type == "ios") {
			return $this->registerDeviceIOS();
		}
	}
	
	//register device for IOS.
	//url: index.php?a=register
	function registerDeviceIOS() {
		error_log("registerDeviceIOS");
		$savant = $this->getDefaultSavant();

		//check if user is tagged, if not show user form.
		$user = User::GetUserFromSession();
		if(!$user) {
			
			$savant->formActionURL = $this->config->baseURL . "?a=newuser&r=register";
			$result = $savant->fetch("templates/actions.userform.php");
			return $result;
		}
		
		//render ios register page, which links to the mobile config.
		$savant->mobileConfigURL = $this->config->baseURL."?a=config";
		$result = $savant->fetch("templates/actions.register.php");
		return $result;
	}

	//register device for mac. 
	//url: index.php?a=register
	function registerDeviceMac() {
		error_log("registerDeviceMac");
		
		//check if user is tagged. if not show user form.
		$user = User::GetUserFromSession();
		if(!$user) {
			$latestVersion = AppVersion::GetLatestVersion();
			$savant = $this->getDefaultSavant();
			$savant->formActionURL = $this->config->baseURL . "?a=newuser&r=newdevicemac&v=" . $latestVersion->udid;
			$result = $savant->fetch("templates/actions.userform.php");
			return $result;
		}
		
		//redirect to new device page.
		$redir = $this->config->baseURL . "?a=newdevicemac";
		header("Location: " . $redir);
	}
	
	//new device for mac
	//url:index.php?a=newdevicemac
	function newDeviceMac() {
		error_log("newDeviceMac");
		
		$savant = $this->getDefaultSavant();
		$savant->formActionURL = $this->config->baseURL."?a=newdevicemacsubmit";
		$savant->installLatestLink = $this->config->baseURL."?a=install&v=".AppVersion::GetLatestVersion()->uuid;
		$result = $savant->fetch("templates/actions.newdevicemac.php");
		return $result;
	}

	//new device submitted
	//url:index.php?a=newdevicemacsubmitted
	function newDeviceMacSubmitted() {
		error_log("newDeviceMacSubmitted");
		
		//make new device
		$user = User::GetUserFromSession();
		if(!$user) {
			$redir = $this->config->baseURL;
			header("Location: " . $redir);
			return;
		}
		
		//make a new device.
		$hardwareId = $this->config->getRequestVar("hardwareId");
		$model = $this->config->getRequestVar("model");
		$device = Device::NewMacDevice($user,$hardwareId,$model);
		$device->save();
		
		//redirect to install page.
		$latestVersion = AppVersion::GetLatestVersion();
		$redir = $this->config->baseURL . '?a=install&v='.$latestVersion->uuid;
		header("Location: " . $redir);
	}

	//serve ios mobile config.
	function serveMobileConfigFile() {
		$user = User::GetUserFromSession();
		$content = $user->getMobileConfig();
		header("Content-Type: application/x-apple-aspen-config");
		echo $content;
	}

	//iOS device registration callback from iOS Settings app.
	//url: index.php
	//&a=payload
	//&u=user uuid
	function handleConfigPayload() {
		$data = file_get_contents('php://input');
		$user = User::GetUser($this->config->getRequestVar("u"));
		$device = Device::NewDevice($user,$data);
		$device->save();
		header("Location: " . $this->config->regiseredURL);
	}

	//successful registration for iOS
	//url:index.php
	//&a=success
	function iOSDeviceSuccess() {
		error_log("iOSDeviceSuccess");
		$savant = $this->getDefaultSavant();
		$result = $savant->fetch("templates/actions.success.php");
		return $result;
	}

	//install application
	function installApplication() {
		error_log("installApplication");

		if($this->config->appData->type == "ios") {
			return $this->installApplicationIOS();
		}

		if($this->config->appData->type == "mac") {
			return $this->installApplicationMac();
		}
	}

	//install application for IOS
	//url: index.php
	//&a=install
	//&v=app version uuid
	function installApplicationIOS() {
		error_log("installApplicationIOS");

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
		$result = $savant->fetch("templates/actions.install.ios.php");
		return $result;
	}

	//Serve plist file to iOS to install the application.
	//url: index.php
	//&v = app version uuid
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

	//install application for mac
	//url: index.php
	//&a=install
	//&v=app version uuid
	function installApplicationMac() {
		error_log("installApplicationMac");
		
		$user = User::GetUserFromSession();
		if(!$user) {
			$redir = $this->config->baseURL;
			header("Location: " . $redir);
			return;
		}
		
		$vuuid = $this->config->getRequestVar("v");
		$av = AppVersion::GetAppVersion($vuuid);
		
		//render template
		$savant = $this->getDefaultSavant();
		$savant->appVersion = $av;
		$savant->downloadURL = $av->getApplicationURL();
		$savant->statURL = $this->config->baseURL . "?a=savestat&type=download";
		$result = $savant->fetch("templates/actions.install.mac.php");
		return $result;
	}

	//handle crashes
	function handleCrash() {
		if($this->config->appData->type == "ios") {
			return $this->handleIOSOrMacCrash();
		}

		if($this->config->appData->type == "mac") {
			return $this->handleIOSOrMacCrash();
		}
	}
	
	//handle ios crash or mac crash.
	function handleIOSOrMacCrash() {
		$data = file_get_contents('php://input');
		$uuid = $this->config->UUID();
		$version = $this->config->getVersionFromIOSCrash($data);
		$versionPath = $this->config->joinPaths(array($this->config->crashPath,$version));
		mkdir($versionPath);
		$path = $this->config->joinPaths(array($versionPath,$uuid.".txt"));
		$this->config->writeFileContent($path,$data);
		return "";
	}
	
	//new version
	//url:dashboard.php
	//&a=newversion
	function newVersion() {
		$savant = $this->getDefaultSavant();
		$savant->dashboardLink = $this->config->dashboardURL;
		$result = $savant->fetch("templates/actions.dashboard.newversion.php");
		return $result;
	}
	
	//new version submitted
	//url:dashboard.php
	//&a=newversionsubmit
	function submitNewVersion() {
		$av = AppVersion::NewAppVersionFromSubmission();
		if(!$av->save()) {
			error_log("error saving new app version.");
		}
		
		//redirect back to dashboard.
		header("Location: " . $this->config->dashboardURL);
	}
	
	//release notes detail page
	//url:dashboard.php
	//&a=releasenotes
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
	
	//delete a version
	//url:dashboard.php
	//&a=delversion
	//&v=app version uuid
	//&filter = rediret to new action, onlycrashes,onlyversions,onlydevices
	function deleteVersion() {
		$uuid = $this->config->getRequestVar("v");
		AppVersion::DeleteAppVersion($uuid);
		$filter = $this->config->getRequestVar("filter");
		$redirect = $this->config->dashboardURL;
		if($filter) {
			$redirect .= "?a=".$filter;
		}
		header("Location: " . $redirect);
	}

	//delete a crash
	//url: dashboard.php
	//&a=delcrash
	//&c=crash uuid
	//&filter = redirect to new action, onlycrashes,onlyversions,onlydevices
	function deleteCrash() {
		Crash::DeleteCrashFile($this->config->getRequestVar("c"));
		$filter = $this->config->getRequestVar("filter");
		$redir = $this->config->dashboardURL;
		if($filter) {
			$redit .= '?a=' . $filter;
		}
		header("Location: " . $redir);
	}
	
	//delete a device
	//url: dashboard.php
	//&a=deldevice
	//&d=device id.
	function deleteDevice() {
		$uuid = $this->config->getRequestVar("d");
		Device::DeleteDevice($uuid);
		$filter = $this->config->getRequestVar("filter");
		$redir = $this->config->dashboardURL;
		if($filter) {
			$redit .= '?a=' . $filter;
		}
		header("Location: " . $redir);
	}

	//export all devices.
	//url: dashboard.php
	//&a=exportalldevices
	function exportAllDevices() {
		$output = Device::GetExportAllDevices();
		header("Content-Type: text/plain");
		return $output;
	}

	//faq
	//url:dashboard.php
	//&a=faq
	function dashboardFAQ() {
		$savant = $this->getDefaultSavant();
		$result = $savant->fetch("templates/actions.faq.php");
		return $result;
	}

	function saveStat() {
		error_log("saveStat");
		$type = $this->config->getRequestVar("type");
		if($type == "install") {
			$this->saveStatInstall();
		} else if($type == "download") {
			$this->saveStatDownload();
		}
	}

	//download statistic
	function saveStatDownload() {
		error_log("saveStatDownload");
		$user = User::GetUserFromSession();
		if(!$user) {
			error_log("no user");
		}
		$stat = Stat::NewStat(Stat::TYPE_DOWNLOAD,$user);
		$stat->save();
	}

	//install statistic
	function saveStatInstall() {
		error_log("saveStatInstall");
		$user = User::GetUserFromSession();
		if(!$user) {
			error_log("no user");
		}
		$stat = Stat::NewStat(Stat::TYPE_INSTALL,$user);
		$stat->save();
	}

	//register device state
	function statRegisterDevice() {
		$user = User::GetUserFromSession();
		$stat = NULL;

		if($this->config->type == "mac") {
			$stat = Stat::NewStat(State::TYPE_MAC_DEVICE,$user);
		}

		if($this->config->type == "ios") {
			$stat = Stat::NewStat(State::TYPE_IOS_DEVICE,$user);
		}

		$stat->save();
	}
}
?>