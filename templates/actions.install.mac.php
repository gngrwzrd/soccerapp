<html>
<head>
<meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<title>Install <?php echo $this->applicationName; ?></title>
<link rel="stylesheet" href="assets/main.css"/>
<link rel="stylesheet" href="assets/buttons.css"/>
<script type="text/javascript" src="assets/jquery.min.js"></script>
<script type="text/javascript" src="assets/main.js"></script>
</head>
<body>
<div class="mobileContent">
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
	<div class="section">
		<div class="sectionHeader">
			<?php echo $this->appVersion->name; ?>
		</div>
		<div class="sectionMenu">
			<input type="button" class="button black" style="width:100%"
				onclick="macInstallSaveStat('<?php echo $this->appVersion->uuid;?>','<?php echo $this->statURL;?>','<?php echo $this->appVersion->getApplicationURL(); ?>')";
				
				value="Download Now" />
				<!-- onclick="window.location='<?php echo $this->appVersion->getApplicationURL(); ?>'"-->
		</div>
		<div class="sectionRow releaseNotes">
			<div class="releaseNotesDate">
				<?php echo $this->appVersion->datestring; ?>
			</div>
<?php echo $this->appVersion->getReleaseNotes(); ?>
		</div>
	</div>
</div>
</body>
</html>