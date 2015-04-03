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
		<a href="<?php echo $_SERVER['PHP_SELF']; ?>">Dashboard</a>
	</div>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="a" value="submitnewversion" />
		<div class="section">
			<div class="sectionHeader">
				New Version
			</div>
			<div class="sectionRow sectionRowForm">
				<table cellpadding="0" cellspacing="0"><tr>
				<td class="label"><label for="executable">IPA:</label></td>
				<td><input type="file" name="executable" id="executable" /></td>
				</tr></table>
			</div>
			<div class="sectionRow sectionRowForm">
				<table cellpadding="0" cellspacing="0">
				<tr>
					<td class="label"></td>
					<td><span class="" style="font-size:10px;">(Markdown supported)</span></td>
				</tr>
				</table>
				<div style="height:10px;" ></div>
				<table cellpadding="0" cellspacing="0">
				<tr><td class="label"><label for="releaseNotes">Release Notes</label></td>
				<td><textarea name="releaseNotes" id="releaseNotes"></textarea></td>
				</tr></table>
			</div>
			<div class="sectionRow sectionRowForm center">
				<input type="submit" class="button black" />
			</div>
		</div>
	</form>
</div>

<div class="footer">
	created with <a href="http://">soccerapp</a>
</div>
</body>
</html>
