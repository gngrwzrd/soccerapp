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
<div class="content">
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
		
		<?php if(!$this->debug) { ?>
		<div class="desktop">
			<div class="sectionRow center">
				Open this page on your iOS device.
			</div>
		</div>
		<?php } ?>
		
		<?php if(!$this->debug) { ?>
			<div class="mobile">
		<?php } ?>
				<div class="sectionMenu">
					<script>var url = "itms-services://?action=download-manifest&amp;&url=<?php echo $this->applicationPlist; ?>";</script>
					<input type="button" class="button black" style="width:100%" onclick="window.location=url;" value="Tap to Install" />
					<!--<a class="button black" style="" onclick="" href="itms-services://?action=download-manifest&amp;url=<?php echo $this->applicationPlist; ?>">
					Tap to Install</a>-->
				</div>
		<?php if(!$this->debug) { ?>
			</div>
		<?php } ?>
		
		<div class="sectionRow releaseNotes">
<?php echo $this->appVersion->getReleaseNotes(); ?>
		</div>
	</div>
</div>
</body>
</html>