<?php 
include_once dirname(dirname(__FILE__)).'/config/loader.php';
header('Content-Type: application/x-javascript');
?>
/*
This is an open-source web game by LakeYang
GitHub Page: githun.com/LakeYang/WhoIsTheUndercover.git
Copyright LakeYang(hihuyang.com) 2016
Licensed under the Apache License, Version 2.0

This Javascript control all the user intractive and object drawings.
The index.php just show a loading progress bar, then all the thing is send to this Javascript to process.
Note because this application is multi-language, so language is injected to this via php.
*/
$(document).ready(function(){
	var queue = new createjs.LoadQueue();
	queue.installPlugin(createjs.Sound);
	queue.on("complete",function(){
		$(".enter-btn").css("background","#3498db");
		$(".enter-btn").val("<?php echo trans('Enter'); ?>");
	}, this);
	queue.on("progress", function(){
		$(".loading_progress").css("width",queue.progress*100+"%");
	});
	queue.loadManifest([
		{id:"jweixin", src:"js/libs/jweixin-1.0.0.js"}
	]);
});