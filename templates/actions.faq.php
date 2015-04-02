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
			iOS
		</div>
	</div>
	<div class="section">
		<div class="sectionRow">
			How do I integrate crash reports?
		</div>
		<div class="sectionRow">
			<pre>
- (BOOL)application:(UIApplication *)application didFinishLaunchingWithOptions:(NSDictionary *)launchOptions {
	PLCrashReporter * crash = [PLCrashReporter sharedReporter];
	if([crash hasPendingCrashReport]) {
		NSData * crashData = [crash loadPendingCrashReportData];
		if(crashData) {
			NSURL * url = [NSURL URLWithString:@"<?php echo $this->crashURL; ?>"];
			NSMutableURLRequest * request = [NSMutableURLRequest requestWithURL:url];
			[request setHTTPMethod:@"POST"];
			PLCrashReport *report = [[PLCrashReport alloc] initWithData:crashData error:nil];
			NSString * log = [PLCrashReportTextFormatter stringValueForCrashReport:report withTextFormat:PLCrashReportTextFormatiOS];
			NSData * logData = [log dataUsingEncoding:NSUTF8StringEncoding];
			NSURLSessionUploadTask * upload = [[NSURLSession sharedSession]
			  uploadTaskWithRequest:request fromData:logData completionHandler:^(NSData *data, NSURLResponse *response, NSError *error)
			{
				if(error) {
					NSLog(@"%@",error);
				}
			}];
			[upload resume];
		}
	}
	[crash enableCrashReporter];
	return YES;
}
			</pre>
		</div>
	</div>
</div>
</body>
</html>