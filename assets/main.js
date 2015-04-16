$(document).ready(function() {
	if(isIOSDevice()) {
		$(".desktop").hide();
		$(".mobile").show();
	}
});

function isIOSDevice() {
	return navigator.userAgent.match(/(iPad|iPhone|iPod)/g);
}

function onInstall() {
	setTimeout(function(){
		$(".gohome").show();
	},2000);
}

function macInstallSaveStat(appVersionUUID,statURL,redir) {
	var data = {'v':appVersionUUID};
	$.ajax(statURL,{
		complete:function(jqxhr,status) {
			//window.location=redir;
		},
	});
}

function confirmDelete() {
	var result = confirm("Are you sure?");
	return result;
}

function validateFields(fields) {
	var result = true;
	for(var i = 0; i < fields.length; i++) {
		var field = $(fields[i]);
		field.removeClass("rederror");
		if(field.val() == "" || field.val() == undefined) {
			field.addClass("rederror");
			result = false;
		}
	}
	return result;
}

function validateNewVersionAndSubmit() {
	var submit = validateFields(["#executable","#releaseNotes"]);
	if(submit) {
		$("#newVersionForm").get(0).submit();
	}
}

function validateNewUserAndSubmit() {
	var submit = validateFields(["#firstName","#lastName","#email"]);
	if(submit) {
		$("#newUserForm").get(0).submit();
	}
}

function validateNewMacDeviceAndSubmit() {
	var submit = validateFields(["#hardwareId","#model"]);
	if(submit) {
		$("#newMacDeviceForm").get(0).submit();
	}
}