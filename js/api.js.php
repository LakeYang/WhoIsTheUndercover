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
		modeselect("asdsa ahkjashdkj ashdkjadfdsafdsafdafdafdasfdfdsfadsfdsafdaf 伤不起真的伤不起爱双卡双待卡上的空间划收款机电话即可",function(){});
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
	if(!callbackFunction){
		callbackFunction=function(){};
	}
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
	var show_text = new createjs.Text(info, "80px Arial", "black");
	show_text.x = 387;
	show_text.y = 200;
	show_text.maxWidth=show_text.lineWidth=463;
	show_text.textBaseline = "hanging";
	show_text.textAlign = "center";
	calert.alertlist[calert.counter].addChild(show_text);
	var ok_btn = new createjs.Container();
	calert.alertlist[calert.counter].addChild(ok_btn);
	var ok_btn_shape = new createjs.Shape();
	ok_btn_shape.graphics.beginFill("#965632").drawRoundRect(70,50,450,160,25);
	ok_btn.addChild(ok_btn_shape);
	var ok_btn_stamp = new createjs.Bitmap(queue.getResult("blue_stamp"));
	ok_btn.addChild(ok_btn_stamp);
	var ok_btn_text = new createjs.Text(btntext, "130px Arial", "white");
	ok_btn_text.x = 200;
	ok_btn_text.y = 130;
	ok_btn_text.textBaseline = "middle";
	ok_btn.scaleY=ok_btn.scaleX=0.8;
	ok_btn.regX = 295;
	ok_btn.y = 700;
	ok_btn.x = 386;
	ok_btn.addChild(ok_btn_text);
	ok_btn.addEventListener("click", function(evt) {
		play("paper2");
		createjs.Tween.get(evt.target.parent.parent).to({y:$(window).height()/2-50},100).to({alpha:0,y:$(window).height()/2+100},100).call(function(){
			top_ui.removeChild(evt.target.parent.parent);
		});
		evt.remove();
    });
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

//canvas modeselect function
function modeselect(info,callbackFunction,btnsingletext,btnnetworktext){
	if(!btnsingletext){
		btnsingletext = "<?php echo trans('Single'); ?>";
	}
	if(!btnnetworktext){
		btnnetworktext = "<?php echo trans('Network'); ?>";
	}
	if(!modeselect.counter){
		modeselect.counter = 0;
		modeselect.alertlist=[];
	}
	modeselect.alertlist[modeselect.counter] = new createjs.Container();
	top_ui.addChild(modeselect.alertlist[modeselect.counter]);
	//Draw background here.
	var ctn_back = new createjs.Bitmap(queue.getResult("modeselect_background"));
	modeselect.alertlist[modeselect.counter].addChild(ctn_back);
	modeselect.alertlist[modeselect.counter].regX=180;
	modeselect.alertlist[modeselect.counter].regY=320;
	modeselect.alertlist[modeselect.counter].scaleX = $(window).width()/360;
	modeselect.alertlist[modeselect.counter].scaleY = $(window).height()/640;
	modeselect.alertlist[modeselect.counter].x=$(window).width()/2;
	modeselect.alertlist[modeselect.counter].y=$(window).height()/2;
	modeselect.alertlist[modeselect.counter].alpha=0;
	createjs.Tween.get(modeselect.alertlist[modeselect.counter]).to({alpha: 1},500).call(function(){
	});
	rules = new createjs.Container();
	top_ui.addChild(rules);
	//Draw rules here.
	var rules_brand = new createjs.Bitmap(queue.getResult("wood_brand"));
	var ctn_scale = Math.min($(window).width()*0.8,$(window).height()*0.8)/500;
	rules.addChild(rules_brand);
	rules.regX=250;
	rules.regY=250;
	rules.x=$(window).width()/2;
	rules.y=-500*ctn_scale/2;
	rules.scaleX = rules.scaleY = ctn_scale;
	rules.alpha=0;
	createjs.Tween.get(rules).to({alpha: 1,y:500*ctn_scale/2},500).to({y:460*ctn_scale/2},200).to({y:500*ctn_scale/2},250).call(function(){
	});
	var rules_text = new createjs.Text(info, "30px Arial", "black");
	rules.addChild(rules_text);
	rules_text.x = 260;
	rules_text.y = 200;
	rules_text.maxWidth=rules_text.lineWidth=300;
	rules_text.textBaseline = "hanging";
	rules_text.textAlign = "center";
	//Draw buttons here.
	var single_btn = new createjs.Container();
	top_ui.addChild(single_btn);
	var single_btn_shape = new createjs.Shape();
	single_btn_shape.graphics.beginFill("rgba(225,145,51,0.3)").drawRoundRect(0,-13,200,90,25);
	single_btn.addChild(single_btn_shape);
	var single_btn_scale = Math.min($(window).width()*0.8,$(window).height()*0.8)/400;
	single_btn.regX=200;
	single_btn.regY=90;
	single_btn.x=$(window).width()/2;
	single_btn.y=$(window).height()/10*9;
	single_btn.scaleX = single_btn.scaleY = single_btn_scale ;
	var single_btn_text = new createjs.Text(btnsingletext, "50px Arial", "white");
	single_btn.addChild(single_btn_text);
	single_btn.addEventListener("click", function(evt) {
		play("paper2");
		createjs.Tween.get(evt.target.parent.parent).to({alpha:0},500).call(function(){
		top_ui.removeChild(evt.target.parent.parent);
		});
    });
	var network_btn = new createjs.Container();
	top_ui.addChild(network_btn);
	var network_btn_shape = new createjs.Shape();
	network_btn_shape.graphics.beginFill("rgba(24,161,95,0.5)").drawRoundRect(0,-13,200,90,25);
	network_btn.addChild(network_btn_shape);
	var network_btn_scale = Math.min($(window).width()*0.8,$(window).height()*0.8)/400;
	network_btn.regX=200;
	network_btn.regY=90;
	network_btn.x=$(window).width()/4*2.9;
	network_btn.y=$(window).height()/10*9;
	network_btn.scaleX = network_btn.scaleY = network_btn_scale ;
	var network_btn_text = new createjs.Text(btnnetworktext, "50px Arial", "white");
	network_btn.addChild(network_btn_text);
	network_btn.addEventListener("click", function(evt) {
		play("paper2");
		createjs.Tween.get(evt.target.parent.parent).to({alpha:0},500).call(function(){
		top_ui.removeChild(evt.target.parent.parent);
		});
    });
	modeselect.counter++;
	callbackFunction();
	
}