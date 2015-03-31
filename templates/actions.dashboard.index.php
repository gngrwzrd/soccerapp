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
	<div class="sectionHeader">
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
		<a href="<?php echo $this->recruitLink; ?>">Recruit Users</a> &nbsp;|&nbsp; <a href="<?php echo $this->installLink; ?>">Install Latest</a>
	</div>
	
	<!-- app versions -->
	<div class="section">
		<div class="sectionHeader">
			Versions
		</div>
		<div class="sectionMenu">
			<a href="?a=newversion">Upload New Version</a>
		</div>
		<?php if(count($this->versions) > 0) { ?>
			<?php foreach($this->versions as $version) { ?>
				<div class="sectionRow">
					<table cellpadding="0" cellspacing="0" class="item"><tr>
					<td>
						<a href="?a=install&v=<?php echo urlencode($version->uuid); ?>"><?php echo $version->name; ?></a>
					</td>
					<td align="right">
						<?php echo $version->datestring; ?> &nbsp;|&nbsp;
						<a href="">Delete</a>
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
	
	<!-- crash logs -->
	<div class="section">
		<div class="sectionHeader">
			Crash Logs
		</div>
		
		<?php if( count($this->crashes)) { ?>
			<?php foreach($this->crashes as $crash) {?>
			<div class="sectionRow">
				<table cellpadding="0" cellspacing="0"><tr>
				<td>
					<a href="<?php echo $this->crashLink . '/' . $crash->name; ?>"><?php echo substr($crash->name,0,18); ?></a>
				</td>
				<td align="right">
					<?php echo $crash->datestring; ?> &nbsp;|&nbsp;
					<a href="">Delete</a>
				</td></tr></table>
			</div>
			<?php } ?>
		<?php } else { ?>
			<div class="sectionRow">
				No crash logs
			</div>
		<?php } ?>

		<div class="sectionFooter">
			How to <a href="?a=crashintegrate">integrate crash reports</a> &nbsp;with&nbsp; <a href="https://www.plcrashreporter.org/" target="_blank">plcrashreporter</a>
			&nbsp;|&nbsp;
			<a href="?a=symbolicate">How to Symbolicate</a>
		</div>
	</div>
	
	<!-- devices -->
	<div class="section">
		<div class="sectionHeader">
			Devices
		</div>
		<div class="sectionMenu">
			<a href="">Export Devices</a> &nbsp;|&nbsp; <a href="">Add A Device</a>
		</div>
		<div class="sectionRow">
		</div>
	</div>
	
	<!-- statistics -->
	<div class="section">
		<div class="sectionHeader">
			Statistics
		</div>
		<div class="sectionRow">

		</div>
	</div>
</div>

<div class="footer">
	created with <a href="http://">soccerapp</a>
</div>
</body>
</html>
