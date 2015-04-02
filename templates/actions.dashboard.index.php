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
		<a href="<?php echo $this->recruitLink; ?>">Recruit Users</a> &nbsp;|&nbsp; <a href="<?php echo $this->installLink; ?>">Install Latest</a>
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
						<a class="delete" href="?a=delversion&filter=<?php echo $this->filter; ?>&v=<?php echo $version->uuid;?>" onclick="return confirmDelete();">Delete</a>
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
		<?php if( count($this->crashGroups) > 0) { ?>
			<?php foreach($this->crashGroups as $group) {?>
				<div class="sectionRow groupTitle">
					<table cellspacing="0" cellspacing="0"><tr>
					<td>
						<a href="">
							<span class="groupTitleText">Version <?php echo $group->groupLabel; ?></span>
						</a>
					</td>
					<td align="right"><span class="groupTitleLinks"><a href="">Download all</a></span></td>
					</tr></table>
				</div>
				<?php foreach($group->crashes as $crash) {?>
					<div class="sectionGroupRow">
						<table cellpadding="0" cellspacing="0"><tr>
						<td class="sectionRowTitle">
							<a href="<?php echo $this->crashLink . '/' . $group->groupLabel . '/' . $crash->name; ?>"><?php echo substr($crash->name,0,18); ?></a>
						</td>
						<td align="right">
							<?php echo $crash->datestring; ?> &nbsp;|&nbsp;
							<a class="delete" href="?a=delcrash&filter=<?php echo $this->filter; ?>&c=<?php echo $crash->name?>&v=<?php echo $group->groupLabel; ?>" onclick="return confirmDelete();">Delete</a>
						</td></tr></table>
					</div>
				<?php } ?>
			<?php } ?>
		<?php } else { ?>
			<div class="sectionRow">
				No crash logs
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
			<a href="">Export Devices</a> &nbsp;|&nbsp; <a href="">Add A Device</a>
		</div>
		<div class="sectionRow">
		</div>
	</div>
	<?php } ?>
	
	<?php if(!$this->filter || $this->filter == "onlystats") { ?>
	<!-- statistics -->
	<div class="section">
		<div class="sectionHeader">
			<a href="?a=onlystats">Statistics</a>
		</div>
		<div class="sectionRow">

		</div>
	</div>
	<?php } ?>
</div>

<div class="footer">
	created with <a href="http://github.com/gngrwzrd/soccerapp">soccerapp</a>
</div>
</body>
</html>
