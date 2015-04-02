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
		
		<?php if(!$this->debug) { ?>
		<div class="desktop">
			<div class="sectionRow">
				Open this page on your iOS device.
			</div>
		</div>
		<?php } ?>
		
		<?php if(!$this->debug) { ?>
		<div class="mobile">
		<?php } ?>
			<div class="sectionHeader">
				Please enter your information
			</div>
			<div class="sectionMenu">
				Before registering your device, we ask that you enter your name and email so we're able to see who's installing <?php echo $this->applicationName; ?>.
			</div>
			<form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>?a=newuser&r=<?php echo $this->redirectAction; ?>">
				<div class="sectionRow sectionRowForm">
					<table cellspacing="0" cellpadding="0"><tr>
					<td class="label"><label for="firstName">First Name:</label></td>
					<td><input type="text" name="firstName" id="firstName" /></td>
					</tr></table>
				</div>
				<div class="sectionRow sectionRowForm">
					<table cellspacing="0" cellpadding="0"><tr>
					<td class="label"><label for="lastName">Last Name:</label></td>
					<td><input type="text" name="lastName" id="lastName" /></td>
					</tr></table>
				</div>
				<div class="sectionRow sectionRowForm">
					<table cellspacing="0" cellpadding="0"><tr>
					<td class="label"><label for="email">Email:</label></td>
					<td><input type="email" name="email" id="email" /></td>
					</tr></table>
				</div>
				<div class="sectionRow sectionRowForm">
					<input type="submit" class="button black" value="Continue" />
				</div>
			</form>
		<?php if(!$this->debug) { ?>
		</div>
		<?php } ?>
	</div>

</div>
</body>
</html>