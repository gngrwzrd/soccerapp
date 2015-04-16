<html>
<head>
<meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<title><?php echo $this->applicationName; ?></title>
<link rel="stylesheet" href="assets/main.css"/>
<link rel="stylesheet" href="assets/buttons.css"/>
<script type="text/javascript" src="assets/jquery.min.js"></script>
<script type="text/javascript" src="assets/main.js"></script>
</head>
<body>
<div class="content">
	
	<!-- main header -->
	<div class="sectionHeader topHeader">
		<table cellspacing=0 cellpadding=0><tr>
		<td width="75">
			<img class="sectionHeaderIcon" src="assets/icon.png" width="50" height="50" /></td>
		<td>
			<?php echo $this->applicationName; ?>
			<div class="sectionHeaderAppBundleId">
				<?php echo $this->applicationBundleId; ?>
			</div>
		</td>
		</tr></table>
	</div>
	<div class="sectionMenu">
		<?php if ($this->filter && $this->filter != "") { ?>
			<a href="<?php echo $this->dashboardLink; ?>">Dashboard</a>
		<?php } ?>
		<?php if(!$this->filter || $this->filter == "") {?>
			<a href="<?php echo $this->recruitLink; ?>">Recruit Users</a>
			&nbsp;|&nbsp;
			<a href="<?php echo $this->installLink; ?>">Install Latest</a>
		<?php } ?>
		&nbsp;|&nbsp; <a href="?a=faq">FAQ</a>
	</div>
	
	<?php if(!$this->filter || $this->filter == "onlyversions") { ?>
	<!-- app versions -->
	<div class="section">
		<div class="sectionHeader">
			<a href="?a=onlyversions">Versions</a>
		</div>
		<div class="sectionMenu">
			<a href="?a=newversion">Upload New Version</a>
		</div>
		<?php if(count($this->versions) > 0) { ?>
			<?php foreach($this->versions as $version) { ?>
				<div class="sectionRow">
					<table cellpadding="0" cellspacing="0" class="item"><tr>
					<td class="sectionRowTitle">
						<a href="?a=install&v=<?php echo urlencode($version->uuid); ?>"><?php echo $version->name; ?></a>
					</td>
					<td align="right">
						<?php echo $version->datestring; ?> &nbsp;|&nbsp;
						<a href="?a=releasenotes&v=<?php echo $version->uuid; ?>">Notes</a> &nbsp;|&nbsp;
						<a class="delete" href="?a=delversion&filter=<?php echo $this->filter; ?>&v=<?php echo $version->uuid;?>" onclick="return confirmDelete();">DEL</a>
					</td>
					</tr></table>
				</div>
			<?php } ?>
		<?php } else { ?>
			<div class="sectionRow">
				No versions
			</div>
		<?php } ?>
	</div>
	<?php } ?>
	
	<?php if(!$this->filter || $this->filter == "onlycrashes") { ?>
	
	<!-- crash logs -->
	<div class="section">
		<div class="sectionHeader">
			<a href="?a=onlycrashes">Crash Logs</a>
		</div>
			<?php if(count($this->crashes) < 1) { ?>
				<div class="sectionRow">
					No Crashes, yay!
				</div>
			<?php } ?>
			<?php foreach($this->crashes as $crash) {?>
				<div class="sectionRow">
					<table cellpadding="0" cellspacing="0">
					<tr>
					<td class="sectionRowTitle">
						<span class="crashVersion">
							V<?php echo $crash->version; ?>
						</span>
						&nbsp;
						<a href="<?php echo $crash->getCrashFileURL() ; ?>"><?php echo substr($crash->name,0,13); ?></a>
					</td>
					<td>
						<?php echo $crash->hardwareModel; ?>
					</td>
					<td>
						<?php echo $crash->osversion; ?>
					</td>
					<td align="right">
						<?php echo $crash->datestring; ?> &nbsp;|&nbsp;
						<a class="delete" href="?a=delcrash&filter=<?php echo $this->filter; ?>&c=<?php echo $crash->name?>"
							onclick="return confirmDelete();">DEL</a>
					</td></tr></table>
				</div>
			<?php } ?>
	</div>
	<?php } ?>
	
	<?php if(!$this->filter || $this->filter == "onlydevices") { ?>
	<!-- devices -->
	<div class="section">
		<div class="sectionHeader">
			<a href="?a=onlydevices">Devices</a>
		</div>
		<div class="sectionMenu">
			<a href="?a=exportalldevices">Export Devices</a>
		</div>
		<?php foreach($this->devices as $device) { ?>
		<div class="sectionRow">
			<table cellspacing="0" cellspacing="0"><tr>
				<td width="300" class="device">
					<strong><?php echo $device->deviceId; ?> </strong>
				</td>
				<td width="150">
					(<?php echo $device->model; ?>)
				</td>
				<td width="200" align="right">
					<?php echo $device->user->firstname; ?> <?php echo $device->user->lastname; ?>
				</td>
				<td width="50" align="right">
					<a href="?a=deldevice&d=<?php echo $device->deviceId; ?>&filter=<?php echo $this->filter; ?>"
					onclick="return confirmDelete();" class="delete">DEL</a>
				</td>
			</tr></table>
		</div>
		<?php } ?>
	</div>
	<?php } ?>
	
	<?php if(!$this->filter || $this->filter == "onlystats") { ?>
	<!-- statistics -->
	<div class="section">
		<div class="sectionHeader">
			<a href="?a=onlystats">Statistics</a>
		</div>
		<?php  foreach($this->stats as $stat) { ?>
		<div class="sectionRow">
			<?php echo $stat->getDashboardMessage(); ?>
		</div>
		<?php } ?>
	</div>
	<?php } ?>
</div>

<div class="footer">
	created with <a href="http://github.com/gngrwzrd/soccerapp">soccerapp</a>
</div>
</body>
</html>
