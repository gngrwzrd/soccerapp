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
			Please enter your information
		</div>
		<div class="sectionMenu">
			Please enter your mac hardware id and model.
		</div>
		<form method="POST" action="<?php echo $this->formActionURL; ?>" id="newMacDeviceForm">
			<div class="sectionRow sectionRowForm">
				<table cellspacing="0" cellpadding="0"><tr>
				<td class="label"><label for="hardwareId">Hardware Id:</label></td>
				<td><input type="text" name="hardwareId" id="hardwareId" /></td>
				</tr></table>
			</div>
			<div class="sectionRow sectionRowForm">
				<table cellspacing="0" cellpadding="0"><tr>
				<td class="label"><label for="model">Model:</label></td>
				<td><input type="text" name="model" id="model" /></td>
				</tr></table>
			</div>
			<div class="sectionRow sectionRowForm">
				<input type="button" style="width:100%" class="button black" value="Continue" 
				onclick="validateNewMacDeviceAndSubmit()"/>
			</div>
		</form>
		<div class="sectionFooter">
			Already registered your device? <a href="<?php echo $this->installLatestLink; ?>">Click here.</a>
		</div>
	</div>
</div>
</body>
</html>