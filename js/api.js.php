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
	//ui container for main page
	main_ui = new createjs.Container();
	stage.addChild(main_ui);
	//ui container for the first mode page
	mode_ui = new createjs.Container();
	stage.addChild(mode_ui);
	//top UI, container for calert() cconfirm(), etc.
	top_ui = new createjs.Container();
	stage.addChild(top_ui);
	createjs.Tween.get(background).to({alpha: 1}, 1000).call(function(){
		$("#top_ui").remove();
		modeselect();
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
	ok_btn_text.maxWidth = 320;
	ok_btn_text.textBaseline = "middle";
	ok_btn.scaleY=ok_btn.scaleX=0.8;
	ok_btn.regX = 295;
	ok_btn.y = 700;
	ok_btn.x = 386;
	ok_btn.addChild(ok_btn_text);
	ok_btn.addEventListener("click", function(evt) {
		callbackFunction();
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
	var ctn_scale = Math.min($(window).width()*0.8,$(window).height()*0.75*0.773)/773;
	var ctn_back = new createjs.Bitmap(queue.getResult("popup_background"));
	cconfirm.alertlist[cconfirm.counter].addChild(ctn_back);
	var show_text = new createjs.Text(info, "80px Arial", "black");
	show_text.x = 387;
	show_text.y = 200;
	show_text.maxWidth=show_text.lineWidth=463;
	show_text.textBaseline = "hanging";
	show_text.textAlign = "center";
	cconfirm.alertlist[cconfirm.counter].addChild(show_text);
	var ok_btn = new createjs.Container();
	cconfirm.alertlist[cconfirm.counter].addChild(ok_btn);
	var ok_btn_shape = new createjs.Shape();
	ok_btn_shape.graphics.beginFill("#965632").drawRoundRect(70,50,450,160,25);
	ok_btn.addChild(ok_btn_shape);
	var ok_btn_stamp = new createjs.Bitmap(queue.getResult("blue_stamp"));
	ok_btn.addChild(ok_btn_stamp);
	var ok_btn_text = new createjs.Text(btnoktext, "130px Arial", "white");
	ok_btn_text.x = 200;
	ok_btn_text.y = 130;
	ok_btn_text.maxWidth = 320;
	ok_btn_text.textBaseline = "middle";
	ok_btn.scaleY=ok_btn.scaleX=0.6;
	ok_btn.regX = 295;
	ok_btn.y = 700;
	ok_btn.x = 225;
	ok_btn.addChild(ok_btn_text);
	ok_btn.addEventListener("click", function(evt) {
		callbackFunction(1);
		play("paper2");
		createjs.Tween.get(evt.target.parent.parent).to({y:$(window).height()/2-50},100).to({alpha:0,y:$(window).height()/2+100},100).call(function(){
			top_ui.removeChild(evt.target.parent.parent);
		});
		evt.remove();
    });
	
	var cancel_btn = new createjs.Container();
	cconfirm.alertlist[cconfirm.counter].addChild(cancel_btn);
	var cancel_btn_shape = new createjs.Shape();
	cancel_btn_shape.graphics.beginFill("#965632").drawRoundRect(70,50,450,160,25);
	cancel_btn.addChild(cancel_btn_shape);
	var cancel_btn_stamp = new createjs.Bitmap(queue.getResult("red_stamp"));
	cancel_btn.addChild(cancel_btn_stamp);
	var cancel_btn_text = new createjs.Text(btncanceltext, "130px Arial", "white");
	cancel_btn_text.x = 200;
	cancel_btn_text.y = 130;
	cancel_btn_text.maxWidth = 320;
	cancel_btn_text.textBaseline = "middle";
	cancel_btn.scaleY=cancel_btn.scaleX=0.6;
	cancel_btn.regX = 295;
	cancel_btn.y = 700;
	cancel_btn.x = 568;
	cancel_btn.addChild(cancel_btn_text);
	cancel_btn.addEventListener("click", function(evt) {
		callbackFunction(0);
		play("paper2");
		createjs.Tween.get(evt.target.parent.parent).to({y:$(window).height()/2-50},100).to({alpha:0,y:$(window).height()/2+100},100).call(function(){
			top_ui.removeChild(evt.target.parent.parent);
		});
		evt.remove();
    });
	
	cconfirm.alertlist[cconfirm.counter].regX=386;
	cconfirm.alertlist[cconfirm.counter].regY=500;
	cconfirm.alertlist[cconfirm.counter].scaleY=cconfirm.alertlist[cconfirm.counter].scaleX = ctn_scale;
	cconfirm.alertlist[cconfirm.counter].x=$(window).width()/2;
	cconfirm.alertlist[cconfirm.counter].y=$(window).height()/2-60;
	cconfirm.alertlist[cconfirm.counter].alpha=0;
	play("paper1");
	createjs.Tween.get(cconfirm.alertlist[cconfirm.counter]).to({alpha: 1,y:$(window).height()/2},500).call(function(){
	});
	cconfirm.counter++;
}

//canvas modeselect function
function modeselect(){
		btnsingletext = "<?php echo trans('Single'); ?>";
		btnnetworktext = "<?php echo trans('Network'); ?>";
		info = "<?php echo trans('Rules'); ?>";
	if(!modeselect.counter){
		modeselect.counter = 0;
	}
	ms_background = new createjs.Container();
	main_ui.addChild(ms_background);
	//Draw background here.
	var ctn_back = new createjs.Bitmap(queue.getResult("modeselect_background"));
	ms_background.addChild(ctn_back);
	ms_background.regX=180;
	ms_background.regY=320;
	ms_background.scaleX = $(window).width()/360;
	ms_background.scaleY = $(window).height()/640;
	ms_background.x=$(window).width()/2;
	ms_background.y=$(window).height()/2;
	ms_background.alpha=0;
	createjs.Tween.get(ms_background).to({alpha: 1},500).call(function(){
	});
	rules = new createjs.Container();
	main_ui.addChild(rules);
	//Draw rules here.
	var rules_brand = new createjs.Bitmap(queue.getResult("wood_brand"));
	var ctn_scalex = $(window).width()/500;
	var ctn_scaley = $(window).height()*0.8/500;
	rules.addChild(rules_brand);
	rules.regX=250;
	rules.regY=250;
	rules.x=$(window).width()/2;
	rules.y=-500*ctn_scaley/2;
	rules.scaleX = ctn_scalex;
	rules.scaleY = ctn_scaley;
	rules.alpha=0;
	createjs.Tween.get(rules).to({alpha: 1,y:500*ctn_scaley/2},500).to({y:460*ctn_scaley/2},200).to({y:500*ctn_scaley/2},250).call(function(){
		calert("hello",function(){
			cconfirm("xx",function(s){
				if(s){
					//alert("t")
				}else{
					//alert("f")
				}
			},"dasdsadas","adsadsadsa")
		})
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
	main_ui.addChild(single_btn);
	var single_btn_shape = new createjs.Shape();
	single_btn_shape.graphics.beginFill("rgba(225,145,51,0.3)").drawRoundRect(0,-13,200,90,25);
	single_btn.addChild(single_btn_shape);
	var single_btn_scale = Math.min($(window).width()*0.8,$(window).height()*0.8)/400;
	single_btn.regX=90;
	single_btn.regY=45;
	single_btn.x=$(window).width()/4;
	single_btn.y=$(window).height()/7*6;
	single_btn.scaleX = single_btn.scaleY = single_btn_scale ;
	var single_btn_text = new createjs.Text(btnsingletext, "50px Arial", "white");
	single_btn.addChild(single_btn_text);
	single_btn.addEventListener("click", function(evt) {
		play("sound_click");
		singlemode();
    });
	var network_btn = new createjs.Container();
	main_ui.addChild(network_btn);
	var network_btn_shape = new createjs.Shape();
	network_btn_shape.graphics.beginFill("rgba(24,161,95,0.5)").drawRoundRect(0,-13,200,90,25);
	network_btn.addChild(network_btn_shape);
	var network_btn_scale = Math.min($(window).width()*0.8,$(window).height()*0.8)/400;
	network_btn.regX=110;
	network_btn.regY=45;
	network_btn.x=$(window).width()/4*3;
	network_btn.y=$(window).height()/7*6;
	network_btn.scaleX = network_btn.scaleY = network_btn_scale ;
	var network_btn_text = new createjs.Text(btnnetworktext, "50px Arial", "white");
	network_btn.addChild(network_btn_text);
	network_btn.addEventListener("click", function(evt) {
		play("sound_click");
		networkmode();
    });
	modeselect.counter++;
}

//Single mode main setting page
function singlemode(){
	//Draw a transparent mask on main_ui and give it click listener to block intractive from expired ui content
	var main_block = new createjs.Shape();
	main_block.alpha = 0;
	main_block.graphics.beginFill("black").drawRect(0, 0, $(window).width(), $(window).height());
	main_ui.addChild(main_block);
	main_block.addEventListener("click",function(){/*This is a blackhole*/});
	//Draw single mode ui here
	
	//ui animation
	mode_ui.x = $(window).width();
	createjs.Tween.get(main_ui).to({x:-$(window).width()},500);
	createjs.Tween.get(mode_ui).to({x:0},500).call(function(){
		main_ui.removeAllChildren();
		main_ui.x = 0;
		calert("Done",0);
	});
}

//Network mode main setting page
function networkmode(){
	calert("Developing,please wait..",0);
}

//Single mode game start
function singlestart(player_num,spy_num,white_num,wordtype){
	
}

//Universal function to get user's head portrait
function getlogo(callbackFunction){
	<?php 
	if(WechatEnabled){
	?>
	//Script to execute if WechatEnabled
	
	<?php 
	}else{
	?>
	//Script to execute if WechatDisabled
	
	<?php 
	}
	?>
}