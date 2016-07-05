<?php 
include_once dirname(dirname(__FILE__)).'/config/loader.php';
header('Content-Type: application/x-javascript');
?>
/*
This is an open-source web game by LakeYang
GitHub Page: github.com/LakeYang/WhoIsTheUndercover.git
Copyright LakeYang(hihuyang.com) 2016
Licensed under the Apache License, Version 2.0

This Javascript include all custom function used, Seperated from main.js.php to make it easy for development.
When the software release, two Javascript(actually PHP) files can be merged to reduce http requests to improve performance.
*/

//User clicked enter, initiate canvas.
function init(){
	$("body").append('<canvas id="mainCanvas" height="'+$(window).height()+'px" width="'+$(window).width()+'px" style="position: absolute; left: 0px; top: 0px;"></canvas>');
	stage = new createjs.Stage("mainCanvas");
	createjs.Touch.enable(stage);
	play("sound_click");
	//Draw background that fade in
	background = new createjs.Shape();
	background.alpha=0;
	background.graphics.beginFill("rgb(187,209,232)").drawRect(0, 0, $(window).width(), $(window).height());
	stage.addChild(background);
	//top UI, container for calert() cconfirm(), etc.
	top_ui = new createjs.Container();
	stage.addChild(top_ui);
	createjs.Tween.get(background).to({alpha: 1}, 1000).call(function(){
		$("#top_ui").remove();
		calert("asdsa",function(){})
	});
	createjs.Ticker.setFPS(30);
	createjs.Ticker.addEventListener("tick", stage);
}

//function used to play sound
function play(sound_id){
	if(play.enabled){
		createjs.Sound.play(sound_id);
	}
}

//canvas alert function
function calert(info,callbackFunction,btntext){
	if(!btntext){
		btntext = "<?php echo trans('OK'); ?>";
	}
	if(!calert.counter){
		calert.counter = 0;
		calert.alertlist=[];
	}
	calert.alertlist[calert.counter] = new createjs.Container();
	top_ui.addChild(calert.alertlist[calert.counter]);
	//Draw things here.
	var ctn_scale = Math.min($(window).width()*0.8,$(window).height()*0.75*0.773)/773;
	var ctn_back = new createjs.Bitmap(queue.getResult("popup_background"));
	calert.alertlist[calert.counter].addChild(ctn_back);
	calert.alertlist[calert.counter].regX=386;
	calert.alertlist[calert.counter].regY=500;
	calert.alertlist[calert.counter].scaleY=calert.alertlist[calert.counter].scaleX = ctn_scale;
	calert.alertlist[calert.counter].x=$(window).width()/2;
	calert.alertlist[calert.counter].y=$(window).height()/2-60;
	calert.alertlist[calert.counter].alpha=0;
	play("paper1");
	createjs.Tween.get(calert.alertlist[calert.counter]).to({alpha: 1,y:$(window).height()/2},500).call(function(){
	});
	calert.counter++;
	callbackFunction();
}

//canvas confirm function
function cconfirm(info,callbackFunction,btnoktext,btncanceltext){
	if(!btnoktext){
		btnoktext = "<?php echo trans('OK'); ?>";
	}
	if(!btncanceltext){
		btncanceltext = "<?php echo trans('Cancel'); ?>";
	}
	if(!cconfirm.counter){
		cconfirm.counter = 0;
		cconfirm.alertlist=[];
	}
	cconfirm.alertlist[cconfirm.counter] = new createjs.Container();
	top_ui.addChild(cconfirm.alertlist[cconfirm.counter]);
	//Draw things here.
	cconfirm.counter++;
	callbackFunction(1);
}