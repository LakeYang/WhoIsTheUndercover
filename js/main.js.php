<?php 
include_once dirname(dirname(__FILE__)).'/config/loader.php';
header('Content-Type: application/x-javascript');
?>
/*
This is an open-source web game by LakeYang
GitHub Page: github.com/LakeYang/WhoIsTheUndercover.git
Copyright LakeYang(hihuyang.com) 2016
Licensed under the Apache License, Version 2.0

This Javascript control all the user intractive and object drawings.
The index.php just show a loading progress bar, then all the thing is send to this Javascript to process.
Note because this application is multi-language, so language is injected to this via php.
*/
$(document).ready(function(){
	queue = new createjs.LoadQueue();
	queue.installPlugin(createjs.Sound);
	queue.on("complete",function(){
		wx.config({
			debug: false,
			appId: '<?php echo APPID; ?>',
			timestamp: <?php echo $timestamp; ?>,
			nonceStr: '<?php echo $str; ?>', 
			signature: '<?php echo $signature; ?>',
			jsApiList: ['chooseImage']
		});
		wx.ready(function(){
			//
		});
		wx.error(function(res){
			alert("WeChat Auth Failed"+res);
		});
		$(".enter-btn").css("background","#3498db");
		$(".enter-btn").val("<?php echo trans('Enter'); ?>");
	}, this);
	queue.on("progress", function(){
		$(".loading_progress").css("width",queue.progress*100+"%");
	});
	queue.loadManifest([
		{id:"jweixin", src:"js/libs/jweixin-1.0.0.js"},
		{id:"popup_background", src:"assets/image/old_scroll.png"},
		{id:"red_stamp", src:"assets/image/red_wax_stamp.png"},
		{id:"blue_stamp", src:"assets/image/blue_wax_stamp.png"},
		{id:"modeselect_background", src:"assets/image/modeselect_bg.png"},
		{id:"wood_brand", src:"assets/image/rules.png"},
		{id:"paper1", src:"assets/audio/paper1.mp3"},
		{id:"paper2", src:"assets/audio/paper2.mp3"},
		{id:"sound_click", src:"assets/audio/click.ogg"}
	]);
});