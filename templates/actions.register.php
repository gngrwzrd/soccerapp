<html>
<head>
<meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<title>Register Device for <?php echo $this->applicationName; ?></title>
<link rel="stylesheet" href="assets/main.css"/>
<link rel="stylesheet" href="assets/buttons.css"/>
<script type="text/javascript" src="assets/jquery.min.js"></script>
<script type="text/javascript" src="assets/main.js"></script>
</head>
<body>
<div class="content">
	<div class="sectionHeader topHeader">
		<table cellspacing=0 cellpadding=0><tr>
		<td width="75"><img class="sectionHeaderIcon" src="assets/icon.png" width="50" height="50" /></td>
		<td>
			<?php echo $this->applicationName; ?>
			<div class="sectionHeaderAppBundleId"><?php echo $this->applicationBundleId; ?></div>
		</td>
		</tr></table>
	</div>
	<div class="section">
		
		<?php if(!$this->debug) { ?>
			<div class="desktop">
				<div class="sectionRow center">
					Open this page on your iOS device.
				</div>
			</div>
		<?php } ?>
		
		<?php if(!$this->debug) {?>
			<div class="mobile">
		<?php } ?>
				<div class="sectionMenu">
					<input class="button black" style="width:100%" type="button" onclick="window.location='<?php echo $this->mobileConfigURL; ?>'" value="Tap to register your device" />
				</div>
		<?php if(!$this->debug); {?>
			</div>
		<?php } ?>

	</div>
</div>
</body>
</html>
