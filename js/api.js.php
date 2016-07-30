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
	headimgquery = new createjs.LoadQueue(false);
	queue.installPlugin(createjs.Sound);
	queue.on("complete",function(){
		$("#loadingtext").text("<?php echo trans('Loading complete'); ?>");
<?php if(WechatEnabled){ ?>
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
<?php } ?>
		$(".enter-btn").css("background","#3498db");
		$(".enter-btn").val("<?php echo trans('Enter'); ?>");
	}, this);
	queue.on("progress", function(){
		$(".loading_progress").css("width",queue.progress*100+"%");
	});
	queue.loadManifest([
<?php if(WechatEnabled){ ?>
		{id:"jweixin", src:"js/libs/jweixin-1.0.0.js"},
<?php } ?>
		{id:"words", src:"resources/words/<?php echo $language; ?>.json"},
		{id:"punishments", src:"resources/penalties/<?php echo $language; ?>.json"},
		{id:"popup_background", src:"assets/image/old_scroll.png"},
		{id:"red_stamp", src:"assets/image/red_wax_stamp.png"},
		{id:"blue_stamp", src:"assets/image/blue_wax_stamp.png"},
		{id:"modeselect_background", src:"assets/image/modeselect_bg.png"},
		{id:"wood_brand", src:"assets/image/rules.png"},
		{id:"singlemode_background", src:"assets/image/singlemode_background.png"},
		{id:"singlemode_bottom", src:"assets/image/singlemode_bottom.png"},
		{id:"gear1", src:"assets/image/gear1.png"},
		{id:"gear2", src:"assets/image/gear2.png"},
		{id:"gear3", src:"assets/image/gear3.png"},
		{id:"gear4", src:"assets/image/gear4.png"},
		{id:"gear5", src:"assets/image/gear5.png"},
		{id:"scrollbar_bg",src:"assets/image/scrollbar_bg.png"},
		{id:"listbutton",src:"assets/image/list.png"},
		{id:"list0",src:"assets/image/list0.png"},
		{id:"switchbg",src:"assets/image/switchbg.png"},
		{id:"switchbtn",src:"assets/image/switchbtn.png"},
		{id:"playbutton",src:"assets/image/playbutton.png"},
		{id:"card_background", src:"assets/image/card_background.png"},
		{id:"paper1", src:"assets/audio/paper1.mp3"},
		{id:"paper2", src:"assets/audio/paper2.mp3"},
		{id:"stamp", src:"assets/audio/stamp.mp3"},
		{id:"scroll", src:"assets/audio/scroll.mp3"},
		{id:"flipcard", src:"assets/audio/flipcard.ogg"},
		{id:"sound_click", src:"assets/audio/click.ogg"}
	]);
});


//User clicked enter, initiate canvas.
function init(){
	//Parse json to array first
	words = [];
	var temp = queue.getResult("words");
	for(var x in temp){
		temp[x].splice(0,0,x)
		words.push(temp[x]);
	}
	punishments = [];
	temp = queue.getResult("punishments");
	for(var x in temp){
		punishments.push(temp[x]);
	}
	//Pixel ratio for most mobile is 2
	var PixelRatio = 2;
	stage_height = $(window).height()*PixelRatio;
	stage_width = $(window).width()*PixelRatio;
	$("body").append('<canvas id="mainCanvas" height="'+stage_height+'px" width="'+stage_width+'px" style="position: absolute; left: 0px; top: 0px; width: '+$(window).width()+'px; height: '+$(window).height()+'px;"></canvas>');
	stage = new createjs.Stage("mainCanvas");
	createjs.Touch.enable(stage);
	play("sound_click");
	//Draw background that fade in
	background = new createjs.Shape();
	background.alpha=0;
	background.graphics.beginFill("rgb(187,209,232)").drawRect(0, 0, stage_width, stage_height);
	stage.addChild(background);
	//ui container for the first mode page
	mode_ui = new createjs.Container();
	stage.addChild(mode_ui);
	//UI FOR GAME
	game_ui = new createjs.Container();
	stage.addChild(game_ui);
	//ui container for main page
	main_ui = new createjs.Container();
	stage.addChild(main_ui);
	//Top ui grey mask
	ui_mask = new createjs.Container();
	stage.addChild(ui_mask);
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

//top mask manager class
function top_mask(){
	var _self = this;
	if(!top_mask.nowid){
		top_mask.nowid = 0;
		top_mask.state = [];
		top_mask.now = 0;
	}
	top_mask.state[top_mask.nowid] = 1;
	if(!top_mask.now){
		top_mask.now = 1;
		var topmask = new createjs.Shape();
		topmask.graphics.beginFill("rgba(0,0,0,0.5)").drawRect(0, 0,stage_width,stage_height);
		ui_mask.addChild(topmask);
		topmask.addEventListener("click",function(){});
	}
	top_mask.nowid++;
	_self.removeMask = function(id){
		top_mask.now = 0;
		ui_mask.removeAllChildren();
		if(typeof(id)!="undefined"){
			top_mask.state = [];
			top_mask.nowid = 0;
		}else{
			top_mask.state[_self.id] = 0;
			$.each(top_mask.state,function(key,val){
				if(val){
					var topmask = new createjs.Shape();
					topmask.graphics.beginFill("rgba(0,0,0,0.5)").drawRect(0, 0,stage_width,stage_height);
					ui_mask.addChild(topmask);
					topmask.addEventListener("click",function(){});
					top_mask.now = 1;
					return false;
				}
			});
		}		
	}
	_self.id = top_mask.nowid-1;
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
	var calertmask = new top_mask();
	calert.alertlist[calert.counter] = new createjs.Container();
	top_ui.addChild(calert.alertlist[calert.counter]);
	//Draw things here.
	var ctn_scale = Math.min(stage_width*0.8,stage_height*0.75*0.773)/773;
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
		calertmask.removeMask();
		play("paper2");
		createjs.Tween.get(evt.target.parent.parent).to({y:stage_height/2-50},100).to({alpha:0,y:stage_height/2+100},100).call(function(){
			top_ui.removeChild(evt.target.parent.parent);
		});
		evt.remove();
    });
	calert.alertlist[calert.counter].regX=386;
	calert.alertlist[calert.counter].regY=500;
	calert.alertlist[calert.counter].scaleY=calert.alertlist[calert.counter].scaleX = ctn_scale;
	calert.alertlist[calert.counter].x=stage_width/2;
	calert.alertlist[calert.counter].y=stage_height/2-60;
	calert.alertlist[calert.counter].alpha=0;
	play("paper1");
	createjs.Tween.get(calert.alertlist[calert.counter]).to({alpha: 1,y:stage_height/2},500).call(function(){
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
	var cconfirmmask = new top_mask();
	cconfirm.alertlist[cconfirm.counter] = new createjs.Container();
	top_ui.addChild(cconfirm.alertlist[cconfirm.counter]);
	//Draw things here.
	var ctn_scale = Math.min(stage_width*0.8,stage_height*0.75*0.773)/773;
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
	ok_btn.y = 750;
	ok_btn.x = 225;
	ok_btn.addChild(ok_btn_text);
	ok_btn.addEventListener("click", function(evt) {
		callbackFunction(1);
		cconfirmmask.removeMask();
		play("paper2");
		createjs.Tween.get(evt.target.parent.parent).to({y:stage_height/2-50},100).to({alpha:0,y:stage_height/2+100},100).call(function(){
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
	cancel_btn.y = 750;
	cancel_btn.x = 568;
	cancel_btn.addChild(cancel_btn_text);
	cancel_btn.addEventListener("click", function(evt) {
		callbackFunction(0);
		cconfirmmask.removeMask();
		play("paper2");
		createjs.Tween.get(evt.target.parent.parent).to({y:stage_height/2-50},100).to({alpha:0,y:stage_height/2+100},100).call(function(){
			top_ui.removeChild(evt.target.parent.parent);
		});
		evt.remove();
    });
	
	cconfirm.alertlist[cconfirm.counter].regX=386;
	cconfirm.alertlist[cconfirm.counter].regY=500;
	cconfirm.alertlist[cconfirm.counter].scaleY=cconfirm.alertlist[cconfirm.counter].scaleX = ctn_scale;
	cconfirm.alertlist[cconfirm.counter].x=stage_width/2;
	cconfirm.alertlist[cconfirm.counter].y=stage_height/2-60;
	cconfirm.alertlist[cconfirm.counter].alpha=0;
	play("paper1");
	createjs.Tween.get(cconfirm.alertlist[cconfirm.counter]).to({alpha: 1,y:stage_height/2},500).call(function(){
	});
	cconfirm.counter++;
}

//canvas scrollbar function
function scrollbar(min_num, max_num,x,y,scale,onStateChange){
	if(!onStateChange){
		var onStateChange=function(){}
	}
	var offset = 0;
	var num = [];
	for(var i = min_num ; i <= max_num ; i++)
		num[i-min_num] =  i;
	var scrollbar_ui = new createjs.Container();
	scrollbar_ui.key_num = min_num;
	scrollbar_ui.x = x;
	scrollbar_ui.y = y;
	scrollbar_ui.regX = scrollbar_ui.regY = 0;
	scrollbar_ui.scaleX = scale;
	scrollbar_ui.scaleY = 55/50*scale;
	var scrollbar_bg = new createjs.Bitmap(queue.getResult("scrollbar_bg"));
	scrollbar_ui.addChild(scrollbar_bg);
	scrollbar_bg.scaleX = 110/150;
	scrollbar_bg.scaleY = 65/38;
	var bar = new createjs.Container();
	scrollbar_ui.addChild(bar);
	var bar_bg = new createjs.Shape();
	bar.addChild(bar_bg);
	bar_bg.graphics.beginFill("rgba(0,0,0,0.2)").drawRect(-100,8,40+55*(10-min_num)+80*(max_num-10)+85+150,50);
	var bar_text = [];
	for(var i = min_num; i <= max_num; i++){
		bar_text[i-min_num] = new createjs.Text(i, "50px Arial", "white");
		bar_text[i-min_num].y = 4;
		if(i<=10)
			bar_text[i-min_num].x = 40+55*(i-min_num);
		if(i>10)
			bar_text[i-min_num].x = 40+55*(10-min_num)+80*(i-10);
		bar.addChild(bar_text[i-min_num]);
	}
	var bar_mask = new createjs.Shape();
	scrollbar_ui.addChild(bar_mask);
	bar_mask.graphics.beginFill("rgba(0,0,0,0)").drawRect(5,0,100,65);
	bar.mask = bar_mask;
	bar.on("mousedown",function(evt){
		offset = this.x - evt.stageX/scale;
		createjs.Tween.removeTweens(this);
	});
	var bar_cover1 = new createjs.Shape();
	var bar_cover2 = new createjs.Shape();
	scrollbar_ui.addChild(bar_cover1);
	scrollbar_ui.addChild(bar_cover2);
	bar_cover1.graphics.beginLinearGradientFill(["rgba(0,0,0,0.7)","rgba(0,0,0,0.0)"], [0, 1], 0, 0, 35, 0).drawRect(5,5,50,55);
	bar_cover2.graphics.beginLinearGradientFill(["rgba(0,0,0,0)","rgba(0,0,0,0.7)"], [0, 1], 65, 0, 100, 0).drawRect(55,5,50,55);
	bar.on("pressmove",function(evt){
		this.x = evt.stageX/scale + offset;
		if(this.x < -(40+55*(10-min_num)+80*(max_num-11)+55+70))
			this.x = -(40+55*(10-min_num)+80*(max_num-11)+55);
		if(this.x > 70)
			this.x = 0;
		if(this.x > -40)
			play("scroll");
		for(var i = min_num; i < max_num; i++){
			if(-(bar_text[i-min_num+1].x) < this.x && this.x < -(bar_text[i-min_num].x)){
				play("scroll");
			}
		}
	});
	bar.on("pressup",function(evt){
		if(this.x > -40){
			createjs.Tween.get(this).to({x:0},500,createjs.Ease.circOut);
			evt.target.parent.key_num = 3;
		}
		for(var i = min_num; i < 9; i++){
			if(-(bar_text[i-min_num+1].x) < this.x && this.x < -(bar_text[i-min_num].x)){
				createjs.Tween.get(this).to({x:-(bar_text[i-min_num+1].x-40)},500,createjs.Ease.circOut);
				evt.target.parent.key_num = i+1;
				break;
			}
		}
		for(var i = 9; i < max_num; i++){
			if(-(bar_text[i-min_num+1].x) < this.x && this.x < -(bar_text[i-min_num].x)){
				createjs.Tween.get(this).to({x:-(bar_text[i-min_num+1].x-25)},500,createjs.Ease.circOut);
				evt.target.parent.key_num = i+1;
				break;
			}
			if(this.x < -(bar_text[max_num-min_num].x)){
				createjs.Tween.get(this).to({x:-(bar_text[max_num-min_num].x-25)},500,createjs.Ease.circOut);
		}
		}
		onStateChange(evt.target.parent.key_num);
	});
	return scrollbar_ui;
}

//canvas dropdownlist function
function dropdownlist(item,x,y,scale,onStateChange){
	if(!onStateChange){
		var onStateChange=function(){}
	}
	var dplist = new createjs.Container();
	var list = [];
	var list_bg =[];
	var list_text = [];
	var list_num = item.length;
	var list_text_length = item[0].length;
	dplist.state = 0;
	dplist.choose = 0;
	for(var i = 0; i< list_num;i++){
		if(item[i].length > list_text_length)
			list_text_length = item[i].length;
	}
	for(var i = list_num-1; i>= 0; i--){
		list[i] = new createjs.Container;
		dplist.addChild(list[i]);
		list[i].x = x;
		list[i].y = y;
		list[i].scaleX = list[i].scaleY = scale;
		list[i].num = i;
		list_bg[i] = new createjs.Bitmap(queue.getResult("listbutton"));
		list_bg[0] = new createjs.Bitmap(queue.getResult("list0"));
		list_bg[i].scaleX = list_text_length*50*2/912;
		list_bg[i].scaleY = 100/79;
		list[i].addChild(list_bg[i]);
		list_text[i] = new createjs.Text(item[i], "50px Arial", "white");
		list_text[i].x = list_text_length*50;
		list_text[i].y = 8;
		list_text[i].textAlign = "center";
		list[i].addChild(list_text[i]);
	}
	for(var i = 0; i<list_num; i++){
		list[i].on("click",function(evt){
			if(dplist.state == 0){
				for(var i = 0; i< list_num; i++){
					createjs.Tween.get(list[i]).to({y : y+100*scale*i},500,createjs.Ease.circOut);
					list[i].addChild(list_text[i]);
				}
				dplist.state = 1;
			}
			else{
				for(var i = 0; i<list_num; i++)
					createjs.Tween.get(list[i]).to({y : y},500,createjs.Ease.circOut)
				dplist.state = 0;
				list[0].addChild(list_bg[0]);
				list[0].addChild(list_text[this.num]);
				this.parent.choose = this.num;
			}
			onStateChange(this.parent.choose);		
		});
	}
	return dplist;
}

//canvas switchbutton function
function switchbutton(x,y,scale,onStateChange){
	if(!onStateChange){
		var onStateChange=function(){}
	}
	var switchbtn = new createjs.Container();
	switchbtn.state = 0;
	switchbtn.x = x;
	switchbtn.y = y;
	switchbtn.scaleX = switchbtn.scaleY = scale;
	var switch_bg = new createjs.Bitmap(queue.getResult("switchbg"));
	switchbtn.addChild(switch_bg);
	var switch_btn = new createjs.Bitmap(queue.getResult("switchbtn"));
	switchbtn.addChild(switch_btn);
	switch_btn.scaleX = switch_btn.scaleY = 100/109;
	switch_btn.x = 151;
	switch_btn.y = 10;
	switch_btn.on("mousedown",function(evt){
		if(switchbtn.state == 0){
			createjs.Tween.get(this).to({x:10},500,createjs.Ease.circOut);
			switchbtn.state = 1;
		}
		else{
			createjs.Tween.get(this).to({x:151},500,createjs.Ease.circOut);
			switchbtn.state = 0;
		}
		onStateChange(switchbtn.state);
	});
	return switchbtn;
}

//canvas randomwords function
function randomwords(select){
	var m;
	var n;
	var max;
	if(select == 0){
		n = 0;
		max = words[0].length - 1;
		}
	else if(select == 1){
		n = 1;
		max = words[1].length - 1;
		}
	else{
		n = Math.floor(Math.random()*2);
		max = words[n].length - 1;
	}
	m = Math.floor(Math.random()*max + 1);
	return words[n][m];
}

//canvas modeselect function
function modeselect(){
	var btnsingletext = "<?php echo trans('Single'); ?>";
	var btnnetworktext = "<?php echo trans('Network'); ?>";
	var info = "<?php echo trans('Rules'); ?>";
	main_ui.x = 0;
	var ms_background = new createjs.Container();
	main_ui.addChild(ms_background);
	//Draw background here.
	var ctn_back = new createjs.Bitmap(queue.getResult("modeselect_background"));
	ms_background.addChild(ctn_back);
	ms_background.regX=180;
	ms_background.regY=320;
	ms_background.scaleX = stage_width/360;
	ms_background.scaleY = stage_height/640;
	ms_background.x=stage_width/2;
	ms_background.y=stage_height/2;
	ms_background.alpha=0;
	createjs.Tween.get(ms_background).to({alpha: 1},500).call(function(){
		game_ui.removeAllChildren();
	});
	var rules = new createjs.Container();
	main_ui.addChild(rules);
	//Draw rules here.
	var rules_brand = new createjs.Bitmap(queue.getResult("wood_brand"));
	var ctn_scalex = stage_width/440;
	var ctn_scaley = stage_height*0.8/450;
	rules.addChild(rules_brand);
	rules.regX=250;
	rules.regY=250;
	rules.x=stage_width/2;
	rules.y=-500*ctn_scaley/2;
	rules.scaleX = ctn_scalex;
	rules.scaleY = ctn_scaley;
	rules.alpha=0;
	createjs.Tween.get(rules).to({alpha: 1,y:500*ctn_scaley/2},500).to({y:460*ctn_scaley/2},200).to({y:500*ctn_scaley/2},250).call(function(){
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
	var single_btn_scale = Math.min(stage_width*0.8,stage_height*0.8)/400;
	single_btn.regX=90;
	single_btn.regY=45;
	single_btn.x=stage_width/4;
	single_btn.y=stage_height/7*6;
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
	var network_btn_scale = Math.min(stage_width*0.8,stage_height*0.8)/400;
	network_btn.regX=110;
	network_btn.regY=45;
	network_btn.x=stage_width/4*3;
	network_btn.y=stage_height/7*6;
	network_btn.scaleX = network_btn.scaleY = network_btn_scale ;
	var network_btn_text = new createjs.Text(btnnetworktext, "50px Arial", "white");
	network_btn.addChild(network_btn_text);
	network_btn.addEventListener("click", function(evt) {
		play("sound_click");
		networkmode();
    });
}

//Single mode main setting page
function singlemode(){
	//Draw a transparent mask on main_ui and give it click listener to block intractive from expired ui content
	var main_block = new createjs.Shape();
	main_block.alpha = 0;
	main_block.graphics.beginFill("black").drawRect(0, 0, stage_width, stage_height);
	main_ui.addChild(main_block);
	main_block.addEventListener("click",function(){/*This is a blackhole*/});
	//Draw single mode ui here
	var singlemode_ui = new createjs.Container();
	mode_ui.addChild(singlemode_ui);
	var singlemode_bg = new createjs.Bitmap(queue.getResult("singlemode_background"));
	singlemode_ui.addChild(singlemode_bg);
 	singlemode_bg.scaleX = stage_width/500;
	singlemode_bg.scaleY = stage_height/665;
	singlemode_ui.alpha = 0;
	createjs.Tween.get(singlemode_ui).to({alpha: 1},500).call(function(){}); 
	var singlemode_bottom = new createjs.Bitmap(queue.getResult("singlemode_bottom"));
	singlemode_ui.addChild(singlemode_bottom);
	singlemode_bottom.scaleX = singlemode_bottom.scaleY = stage_width/1383*1.7;
	singlemode_bottom.x = -0.35*stage_width;
	singlemode_bottom.y = stage_height - 206*singlemode_bottom.scaleY;
	var gear = new createjs.Container();
	singlemode_ui.addChild(gear);
	var gear1 = new createjs.Bitmap(queue.getResult("gear1"));
	var gear2 = new createjs.Bitmap(queue.getResult("gear2"));
	var gear3 = new createjs.Bitmap(queue.getResult("gear3"));
	var gear4 = new createjs.Bitmap(queue.getResult("gear4"));
	var gear5 = new createjs.Bitmap(queue.getResult("gear5"));
	gear.addChild(gear1,gear2,gear3,gear4,gear5);
	gear.scaleX = gear.scaleY = stage_width/880*1.2;
	gear1.regX = gear1.regY = 147.5;
	gear2.regX = gear2.regY = 105;
	gear3.regX = 66.5;
	gear3.regY = 67;
	gear4.regX = gear4.regY = 94;
	gear5.regX = gear5.regY = 77.5;
	gear1.x = 150.5;
	gear1.y = 147.5;
	gear2.x = 331.5;
	gear2.y = 315.5;
	gear3.x = 482.5;
	gear3.y = 231.5;
	gear4.x = 642;
	gear4.y = 253;
	gear5.x = 802.5;
	gear5.y = 192.5;
	gear.x = -stage_width*0.15;
	gear.y = -420*gear.scaleY*0.6;
	var gear1_speed = 20000;
	var gear2_speed = gear1_speed/(gear1.regX/gear2.regX);
	var gear3_speed = gear2_speed/(gear2.regX/gear3.regX);
	var gear4_speed = gear3_speed/(gear3.regX/gear4.regX);
	var gear5_speed = gear4_speed/(gear4.regX/gear5.regX);
 	createjs.Tween.get(gear1,{loop:true}).to({rotation:360},gear1_speed);
	createjs.Tween.get(gear2,{loop:true}).to({rotation:-360},gear2_speed);
	createjs.Tween.get(gear3,{loop:true}).to({rotation:360},gear3_speed);
	createjs.Tween.get(gear4,{loop:true}).to({rotation:-360},gear4_speed);
	createjs.Tween.get(gear5,{loop:true}).to({rotation:360},gear5_speed);
	//Draw gamesettings here
	var gamesettings = new createjs.Container();
	mode_ui.addChild(gamesettings);
	gamesettings.regX = 330;
	gamesettings.x = stage_width/2;
	gamesettings.y = stage_height/6;
	var gamesettingstext = "<?php echo trans('Gamesettings'); ?>";
	var player_numtext = "<?php echo trans('Playernum'); ?>";
	var gameclasstext = "<?php echo trans('Word type'); ?>";
	var	gamesettings_text = new createjs.Text(gamesettingstext, "bold 60px 微软雅黑", "rgb(236,236,236)");
	gamesettings.addChild(gamesettings_text);
	gamesettings_text.x = 330;
	gamesettings_text.textAlign = "center";
	var gamesettings_line_top = [];
	for(i = 0 ;i <= 22; i++){
		gamesettings_line_top[i] = new createjs.Shape();
		gamesettings.addChild(gamesettings_line_top[i]);
		gamesettings_line_top[i].graphics.beginFill("rgba(236,236,236,0.5)").drawPolyStar(30*i, 100, 10, 6, 0.5, -90);
	}
	var gamesettings_line_bottom = [];
	for(i = 0 ;i <= 22; i++){
		gamesettings_line_bottom[i] = new createjs.Shape();
		gamesettings.addChild(gamesettings_line_bottom[i]);
		gamesettings_line_bottom[i].graphics.beginFill("rgba(236,236,236,0.5)").drawPolyStar(30*i, 670, 10, 6, 0.5, -90);
	}
	//Draw play button here
	var playbtn = new createjs.Bitmap(queue.getResult("playbutton"));
	gamesettings.addChild(playbtn);
	playbtn.scaleX = playbtn.scaleY = 340/160;
	playbtn.x = 160;
	playbtn.y = 710;
	playbtn.addEventListener("click",function(){
		main_ui.removeAllChildren();
		var word = randomwords(gamesettings.gameclass);
		singlestart(gamesettings_scrollbar.key_num,spy_text.text,blank_switchbutton.state,word);
	});
	//Draw playnum here
	var player_num_text = new createjs.Text(player_numtext + "：", "bold 50px 微软雅黑 ", "rgb(324,117,110)");
	gamesettings.addChild(player_num_text);
	player_num_text.x = 20;
	player_num_text.y = 165;
	gamesettings_scrollbar = new scrollbar(3,12,370,140,1.5,function(key_num){
		if(key_num <=7){
			spy_text.text = 1;
			civilian_text.text = key_num - spy_text.text;
		}
		else{
			spy_text.text = 2;
			civilian_text.text = key_num - spy_text.text;
		}
		gamesettings_scrollbar.key_num = key_num;
		});
	gamesettings.addChild(gamesettings_scrollbar);
	//Draw civilian_num here
	var civilian = new createjs.Text("<?php echo trans('FOLK'); ?>"+"x", "45px 微软雅黑", "rgb(232, 232, 255)" );
	gamesettings.addChild(civilian); 
	civilian.x = 50;
	civilian.y = 300;
	var civilian_text = new createjs.Text(2, "bold 80px 微软雅黑", "rgb(237,175,114)");
	gamesettings.addChild(civilian_text);
	civilian_text.x = 190;
	civilian_text.y = 280;
	//Draw spy_num here
	var spy = new createjs.Text("<?php echo trans('SPY'); ?>"+"x", "45px 微软雅黑", "rgb(232, 232, 255)" );
	gamesettings.addChild(spy); 
	spy.x = 50;
	spy.y = 410;
	var spy_text = new createjs.Text(1, "bold 80px 微软雅黑", "rgb(237,175,114)");
	gamesettings.addChild(spy_text);
	spy_text.x = 190;
	spy_text.y = 390;
	//Draw blank here
	var blank = new createjs.Text("<?php echo trans('BLANK'); ?>", "45px 微软雅黑", "rgb(232, 232, 255)" );
	gamesettings.addChild(blank);
	blank.x = 340;
	blank.y = 350;
	var blank_switchbutton =new switchbutton(440,340,0.6,function(state){
		if(state == 1){
			civilian_text.text--;
		}
		else{
			civilian_text.text++;
		}
	});
	gamesettings.addChild(blank_switchbutton);
	//Draw gameclass here
	var gameclass_text = new createjs.Text(gameclasstext + "：", "bold 50px 微软雅黑 ", "rgb(150,206,203)");
	gamesettings.addChild(gameclass_text);
	gameclass_text.x = 20;
	gameclass_text.y = 550;
	gamesettings.gameclass = 0;
	var gamesettings_dropdownlist = new dropdownlist(["重口味","小清新","随机"],310,520,1,function(choose){
		gamesettings.gameclass = choose;
	});
	gamesettings.addChild(gamesettings_dropdownlist);
	//ui animation
	mode_ui.x = stage_width;
	createjs.Tween.get(main_ui).to({x:-stage_width},500);
	createjs.Tween.get(mode_ui).to({x:0},500);
}

//Network mode main setting page
function networkmode(){
	var tt = new pending("跳转至创房");
	setTimeout(function(){
		tt.clear();
		netcreateroom();
	},700);
	setTimeout(function(){
		tt.changeText("wait");
	},500);
	//calert("Developing,please wait..",0);
}

//Single mode game start
function singlestart(player_num,spy_num,white_num,wordarr,restarted){
	game_ui.removeAllChildren();
	var folk_num = player_num - spy_num - white_num;
	var ctn_back = new createjs.Bitmap(queue.getResult("modeselect_background"));
	ctn_back.scaleX = stage_width/360;
	ctn_back.scaleY = stage_height/640;
	game_ui.addChild(ctn_back);
	if(!restarted){
		game_ui.x = stage_width;
		//block first
		var main_block = new createjs.Shape();
		main_block.alpha = 0;
		main_block.graphics.beginFill("black").drawRect(0, 0, stage_width, stage_height);
		mode_ui.addChild(main_block);
		main_block.addEventListener("click",function(){/*This is a blackhole*/});
		//Animation first
		createjs.Tween.get(game_ui).to({x:0},500);
	}
	createjs.Tween.get(mode_ui).to({x:-stage_width},500).call(function(){
		mode_ui.removeAllChildren();
		mode_ui.x = 0;
		//Remove headimgquery if defined
		if(headimgquery && !restarted){
			headimgquery.removeAll();
		}
		//Start distribute card and take photos
		takephotos(player_num,function(photoarray){
			if(stage_width<=stage_height*0.76875){
				var card_height = stage_width*4/17;
			}else{
				var card_height = stage_height/4;
			}			
			var card_interspace_x = (stage_width-3*card_height)/5;
			var card_interspace_y = (stage_height*0.75-3*card_height)/4;
			var row_num = Math.ceil(player_num/4);
			var ypos_list = [];
			if(row_num == 1){
				ypos_list[0] = stage_height*0.375-card_height/2;
			}else if(row_num == 2){
				ypos_list[0] = stage_height*0.375-card_height-card_interspace_y/2;
				ypos_list[1] = stage_height*0.375+card_interspace_y/2;
			}else{
				ypos_list[0] = stage_height*0.375-card_height*3/2-card_interspace_y;
				ypos_list[1] = stage_height*0.375-card_height/2;
				ypos_list[2] = stage_height*0.375+card_height/2+card_interspace_y;
			}
			var xpos_list = [];
			if(player_num%4 == 1){
				xpos_list[0] = stage_width/2-card_height*0.375;
			}else if(player_num%4 == 2){
				xpos_list[0] = stage_width/2-card_height*0.75-card_interspace_x/2;
				xpos_list[1] = stage_width/2+card_interspace_x/2;
			}else{
				xpos_list[0] = stage_width/2-card_height*9/8-card_interspace_x;
				xpos_list[1] = stage_width/2-card_height*3/8;
				xpos_list[2] = stage_width/2+card_height*3/8+card_interspace_x;
			}
			
			var cardlist = [];
			var shapelist = [];
			var imglist = [];
			var playerlist = [];
			var masklist = [];
			for(var i=1;i<=player_num;i++){
				cardlist[i] =  new createjs.Container();
				var now_no = i%4;
				if(now_no == 0){
					now_no = 4;
				}
				if(row_num == 1 && player_num%4 != 0){
					cardlist[i].x = xpos_list[now_no-1];
				}else{
					cardlist[i].x = (now_no)*card_interspace_x+(now_no-1)*0.75*card_height;
				}
				game_ui.addChild(cardlist[i]);
				shapelist[i] = new createjs.Shape();
				shapelist[i].graphics.beginFill("white").drawRoundRect(0,0,300,400,30);
				cardlist[i].addChild(shapelist[i]);
				if(photoarray[i] == "random"){
					imglist[i] = randomshape();
					imglist[i].x = 10;
					imglist[i].y = 20;
				}else{
					imglist[i] = new createjs.Bitmap(headimgquery.getResult(photoarray[i]));
					if(imglist[i].getBounds().width >= imglist[i].getBounds().height){
						imglist[i].scaleY = imglist[i].scaleX = 280/imglist[i].getBounds().height;
						imglist[i].x = -imglist[i].getBounds().width*imglist[i].scaleX/2+150;
						imglist[i].y = 20;
					}else{
						imglist[i].scaleY = imglist[i].scaleX = 280/imglist[i].getBounds().width;
						imglist[i].x = 10;
						imglist[i].y = -imglist[i].getBounds().height*imglist[i].scaleY/2+160;
					}
					masklist[i] = new createjs.Shape();
					masklist[i].graphics.beginFill("rgba(0,0,0,0)").drawRect(10,20,280,280);
					cardlist[i].addChild(masklist[i]);
					imglist[i].mask = masklist[i];
				}
				cardlist[i].addChild(imglist[i]);
				playerlist[i] = new createjs.Text("<?php echo trans('Player'); ?> #"+i, "60px Arial", "black");
				playerlist[i].x = 15;
				playerlist[i].y = 305;
				playerlist[i].maxWidth = 270;
				playerlist[i].textBaseline = "top";
				cardlist[i].addChild(playerlist[i]);
				cardlist[i].y = ypos_list[Math.ceil(i/4)-1];
				cardlist[i].scaleY = cardlist[i].scaleX = card_height/400;
				if(i%4 == 0){
					row_num--;
				}
			}
			//Distribute words randomly
			var wordsarray = [];
			for(var i=0;i<=player_num;i++){
				wordsarray.push("");
			}
			var spyword = wordarr[0];
			var folkword = wordarr[1];
			if(Math.round(Math.random())){
				var temp = spyword;
				spyword = folkword;
				folkword = temp;
			}
			wordsarray[0] = spyword;
			var nonwhitearray = arrayselect(player_num-white_num,player_num);
			var spyarray = [];
			$.each(arrayselect(spy_num,player_num-white_num),function(key,val){
				spyarray.push(nonwhitearray[val-1]);
			});
			for(var i=1;i<=player_num;i++){
				if($.inArray(i,nonwhitearray) != -1){
					//not white
					if($.inArray(i,spyarray) == -1){
						wordsarray[i] = folkword;
					}else{
						wordsarray[i] = spyword;
					}
				}
			}
			showcardword(cardlist,wordsarray,function(){
				calert("<?php echo trans('Please describe the words by order. When a round is complete, click the player card to vote him(her) as an Undercover.'); ?>",function(){
					//Draw function button
					var forget_btn = new createjs.Container();
					game_ui.addChild(forget_btn);
					var forget_btn_shape = new createjs.Shape();
					forget_btn_shape.graphics.beginFill("#965632").drawRoundRect(70,50,450,160,25);
					forget_btn.addChild(forget_btn_shape);
					var forget_btn_stamp = new createjs.Bitmap(queue.getResult("blue_stamp"));
					forget_btn.addChild(forget_btn_stamp);
					var forget_btn_text = new createjs.Text("<?php echo trans('Forget Word'); ?>", "130px Arial", "white");
					forget_btn_text.x = 200;
					forget_btn_text.y = 130;
					forget_btn_text.maxWidth = 320;
					forget_btn_text.textBaseline = "middle";
					forget_btn.regX = 295;
					forget_btn.scaleY=forget_btn.scaleX = Math.min(stage_width,stage_height)/2200;
					forget_btn.y = stage_height-forget_btn.scaleX*315;
					forget_btn.x = stage_width/2;
					forget_btn.addChild(forget_btn_text);
					singlestart.forget = 0;
					forget_btn.addEventListener("click", function(evt){
						if(singlestart.forget){
							singlestart.forget = 0;
							forget_btn_text.text = "<?php echo trans('Forget Word'); ?>";
						}else{
							calert("<?php echo trans('Click the player to reveal the word'); ?>",function(){evt.target.active = 1;
								forget_btn_text.text = "<?php echo trans('Cancel'); ?>";
								singlestart.forget = 1;
							})
						}	
					});
					//Add mouse event listener
					for(var i=1;i<=player_num;i++){
						shapelist[i].no = i;
						shapelist[i].addEventListener("click", function(evt){
							if(singlestart.forget){
								showcardword([0,evt.target.parent],[0,wordsarray[evt.target.no]],function(){
									forget_btn_text.text = "<?php echo trans('Forget Word'); ?>";
									singlestart.forget = 0;
								});
								return 0;
							}
							cconfirm("<?php echo trans('Confirm to vote Player #%1 as an undercover?'); ?>".replace(/%1/,evt.target.no),function(s){
								if(s){
									evt.target.removeAllEventListeners("click");
									evt.target.parent.lastx = evt.target.parent.x;
									evt.target.parent.lasty = evt.target.parent.y;
									evt.target.parent.scale = evt.target.parent.scaleX;
									var showscale = Math.min(stage_width,stage_height*0.75)*0.6/300;
									var idstamp = new createjs.Container();
									var shapeline = new createjs.Shape();
									if(wordsarray[0] == wordsarray[evt.target.no]){
										shapeline.graphics.setStrokeStyle(20,'square','square').beginStroke("#993300").moveTo(0,0).lineTo(0,150).lineTo(330,150).lineTo(330,0).lineTo(0,0);
										var idtext = new createjs.Text("<?php echo trans('SPY'); ?>", "80px Arial", "#993300");
										spy_num--;
									}else if(wordsarray[evt.target.no] != ""){
										shapeline.graphics.setStrokeStyle(20,'round').beginStroke("#993300").drawCircle(165,75,180);
										var idtext = new createjs.Text("<?php echo trans('FOLK'); ?>", "80px Arial", "#993300");
										folk_num--;
									}else{
										shapeline.graphics.setStrokeStyle(20,'round').beginStroke("#993300").drawRoundRect(0,0,330,150,30);
										var idtext = new createjs.Text("<?php echo trans('BLANK'); ?>", "80px Arial", "#993300");
										white_num--;
									}
									if(((player_num <= 6) && spy_num+folk_num+white_num == 2) || ((player_num > 6) && spy_num+folk_num+white_num == 3)){
										if(spy_num){
											//Spy win
											var flag = 1;
										}else if(white_num){
											//White win
											var flag = 2;
										}else{
											//Folk win
											var flag = 3;
										}
									}else if(!spy_num){
										if(white_num){
											//White win
											var flag = 2;
										}else{
											//Folk win
											var flag = 3;
										}
									}else if(spy_num >= folk_num){
										//Spy win
										var flag = 1;
									}else{
										var flag = 0;
									}
									if(flag){
										switch(flag){
											case 1:
												var endtextshow = "";
												for(var i=1;i<=player_num;i++){
													if(wordsarray[0] == wordsarray[i]){
														endtextshow += i+", ";
													}
												}
												for(var i=1;i<=player_num;i++){
													if(wordsarray[i] == ""){
														endtextshow += "  <?php echo trans('Blanks: Player'); ?> ";
														break;
													}
												}
												for(var i=1;i<=player_num;i++){
													if(wordsarray[i] == ""){
														endtextshow += i+", ";
													}
												}
												var endingtxt = "<?php echo trans('Undercovers win! Undercovers: Player %1'); ?>".replace(/%1/,endtextshow);
												break;
											case 2:
												var endingtxt = "<?php echo trans('Blanks win! Undercovers have been eliminated.'); ?>";
												break;
											case 3:
												var endingtxt = "<?php echo trans('Folks win! Undercovers have been eliminated.'); ?>";
												break;
										}
										setTimeout(function(){
											cconfirm(endingtxt,function(s){
												if(s){
													//Punish,generating a person to accept punishment
													var ranpeople = 0;
													var looping = 1;
													do{
														ranpeople = Math.ceil(Math.random()*player_num);
														switch(flag){
															case 1:
																if(wordsarray[0] != wordsarray[ranpeople]){
																	looping = 0;
																}
																break;
															case 2:
																if(wordsarray[ranpeople] != ""){
																	looping = 0;
																}
																break;
															case 3:
																if(wordsarray[ranpeople] == wordsarray[0] || wordsarray[ranpeople] == ""){
																	looping = 0;
																}
																break;
														}
													}while(looping);
													shapelist[ranpeople].removeAllEventListeners("click");
													$.each(cardlist,function(key,val){
														if(key != ranpeople && key){
															createjs.Tween.get(cardlist[key]).to({},500).to({alpha:0},600);
														}
													})
													createjs.Tween.get(cardlist[ranpeople]).to({},1000).to({x:stage_width/2-150*showcardword.showscale,y:stage_height/2-200*showcardword.showscale,scaleX:showcardword.showscale,scaleY:showcardword.showscale},1000,createjs.Ease.cubicOut).call(function(){
														playerlist[ranpeople].text += " <?php echo trans('Obediently Punished'); ?>";
														shapelist[ranpeople].addEventListener("click", function(evt){
															cconfirm(punishments[Math.floor(Math.random()*punishments.length)],function(s){
																if(s){
																	singlestart(player_num,spy_num,white_num,wordarr,1);
																}else{
																	modeselect();
																}
															},"<?php echo trans('Play again'); ?>","<?php echo trans('Main menu'); ?>");
															evt.remove();
														});
													});
												}else{
													modeselect();
												}
											},"<?php echo trans('Punishment'); ?>","<?php echo trans('Main menu'); ?>");
										},1000);
										
									}
									idtext.y = 75;
									idtext.x = 165;
									idtext.textAlign = "center"
									idtext.textBaseline = "middle";
									idstamp.addChild(idtext);
									idstamp.addChild(shapeline);
									idstamp.regX = 165;
									idstamp.regY = 75;
									idstamp.rotation = -25;
									idstamp.y = 200;
									idstamp.x = 150;
									idstamp.alpha = 0;
									idstamp.scaleY = idstamp.scaleX = 10;
									evt.target.parent.addChild(idstamp);
									play("stamp");
									createjs.Tween.get(idstamp).to({scaleX:1,scaleY:1,alpha:1},700,createjs.Ease.quintIn);
								}
							},"<?php echo trans('Assuredly'); ?>","<?php echo trans('Maybe..not'); ?>");
						});
					}
				});
			});
		},restarted);
	});
}

//netMode create room step
function netcreateroom(){
	//Draw a transparent mask on main_ui and give it click listener to block intractive from expired ui content
	var main_block = new createjs.Shape();
	main_block.alpha = 0;
	main_block.graphics.beginFill("black").drawRect(0,0,stage_width,stage_height);
	main_ui.addChild(main_block);
	main_block.addEventListener("click",function(){/*This is a blackhole*/});
	//Draw single mode ui here
	var singlemode_ui = new createjs.Container();
	mode_ui.addChild(singlemode_ui);
	var singlemode_bg = new createjs.Bitmap(queue.getResult("singlemode_background"));
	singlemode_ui.addChild(singlemode_bg);
 	singlemode_bg.scaleX = stage_width/500;
	singlemode_bg.scaleY = stage_height/665;
	singlemode_ui.alpha = 0;
	createjs.Tween.get(singlemode_ui).to({alpha: 1},500).call(function(){}); 
	var singlemode_bottom = new createjs.Bitmap(queue.getResult("singlemode_bottom"));
	singlemode_ui.addChild(singlemode_bottom);
	singlemode_bottom.scaleX = singlemode_bottom.scaleY = stage_width/1383*1.7;
	singlemode_bottom.x = -0.35*stage_width;
	singlemode_bottom.y = stage_height - 206*singlemode_bottom.scaleY;
	//Draw gamesettings here
	var gamesettings = new createjs.Container();
	mode_ui.addChild(gamesettings);
	gamesettings.regX = 330;
	gamesettings.x = stage_width/2;
	gamesettings.y = stage_height/12;
	var gamesettingstext = "<?php echo trans('Room settings'); ?>";
	var player_numtext = "<?php echo trans('Playernum'); ?>";
	var gameclasstext = "<?php echo trans('Word type'); ?>";
	var password = "";
	var	gamesettings_text = new createjs.Text(gamesettingstext, "bold 60px 微软雅黑", "rgb(236,236,236)");
	gamesettings.addChild(gamesettings_text);
	gamesettings_text.x = 330;
	gamesettings_text.textAlign = "center";
	var gamesettings_line_top = [];
	for(i = 0 ;i <= 22; i++){
		gamesettings_line_top[i] = new createjs.Shape();
		gamesettings.addChild(gamesettings_line_top[i]);
		gamesettings_line_top[i].graphics.beginFill("rgba(236,236,236,0.5)").drawPolyStar(30*i, 100, 10, 6, 0.5, -90);
	}
	var gamesettings_line_bottom = [];
	for(i = 0 ;i <= 22; i++){
		gamesettings_line_bottom[i] = new createjs.Shape();
		gamesettings.addChild(gamesettings_line_bottom[i]);
		gamesettings_line_bottom[i].graphics.beginFill("rgba(236,236,236,0.5)").drawPolyStar(30*i, 770, 10, 6, 0.5, -90);
	}
	//Draw play button here
	var playbtn = new createjs.Bitmap(queue.getResult("playbutton"));
	gamesettings.addChild(playbtn);
	playbtn.scaleX = playbtn.scaleY = 340/160;
	playbtn.x = 160;
	playbtn.y = 810;
	playbtn.addEventListener("click",function(){
		main_ui.removeAllChildren();
		var word = randomwords(gamesettings.gameclass);
		//singlestart(gamesettings_scrollbar.key_num,spy_text.text,blank_switchbutton.state,word);
		var roompending = new pending("<?php echo trans('Creating Room'); ?>");
		netconn("create_room",{"spynum":spy_text.text,"usernum":gamesettings_scrollbar.key_num,"whitenum":blank_switchbutton.state,"password":password,"words":word},function(r){
			var roomid = r.roomid;
			roompending.clear();
			netstart(roomid);
		})
	});
	//Draw playnum here
	var player_num_text = new createjs.Text(player_numtext + "：", "bold 50px 微软雅黑 ", "rgb(324,117,110)");
	gamesettings.addChild(player_num_text);
	player_num_text.x = 20;
	player_num_text.y = 165;
	gamesettings_scrollbar = new scrollbar(3,12,370,140,1.5,function(key_num){
		if(key_num <=7){
			spy_text.text = 1;
			civilian_text.text = key_num - spy_text.text;
		}
		else{
			spy_text.text = 2;
			civilian_text.text = key_num - spy_text.text;
		}
		gamesettings_scrollbar.key_num = key_num;
		});
	gamesettings.addChild(gamesettings_scrollbar);
	//Draw civilian_num here
	var civilian = new createjs.Text("<?php echo trans('FOLK'); ?>"+"x", "45px 微软雅黑", "rgb(232, 232, 255)" );
	gamesettings.addChild(civilian); 
	civilian.x = 50;
	civilian.y = 300;
	var civilian_text = new createjs.Text(2, "bold 80px 微软雅黑", "rgb(237,175,114)");
	gamesettings.addChild(civilian_text);
	civilian_text.x = 190;
	civilian_text.y = 280;
	//Draw spy_num here
	var spy = new createjs.Text("<?php echo trans('SPY'); ?>"+"x", "45px 微软雅黑", "rgb(232, 232, 255)" );
	gamesettings.addChild(spy); 
	spy.x = 50;
	spy.y = 410;
	var spy_text = new createjs.Text(1, "bold 80px 微软雅黑", "rgb(237,175,114)");
	gamesettings.addChild(spy_text);
	spy_text.x = 190;
	spy_text.y = 390;
	//Draw blank here
	var blank = new createjs.Text("<?php echo trans('BLANK'); ?>", "45px 微软雅黑", "rgb(232, 232, 255)" );
	gamesettings.addChild(blank);
	blank.x = 340;
	blank.y = 350;
	var blank_switchbutton =new switchbutton(440,340,0.6,function(state){
		if(state == 1){
			civilian_text.text--;
		}
		else{
			civilian_text.text++;
		}
	});
	gamesettings.addChild(blank_switchbutton);
	//Draw gameclass here
	var gameclass_text = new createjs.Text(gameclasstext + "：", "bold 50px 微软雅黑 ", "rgb(150,206,203)");
	gamesettings.addChild(gameclass_text);
	gameclass_text.x = 20;
	gameclass_text.y = 550;
	gamesettings.gameclass = 0;
	var gamepass_text = new createjs.Text("<?php echo trans('Password'); ?>" + "：", "bold 50px 微软雅黑 ", "rgb(150,206,203)");
	gamesettings.addChild(gamepass_text);
	gamepass_text.x = 20;
	gamepass_text.y = 650;
	var pass_text = new createjs.Text("<?php echo trans('Unset'); ?>", "50px 微软雅黑 ", "rgb(150,206,203)");
	gamesettings.addChild(pass_text);
	pass_text.x = 300;
	pass_text.y = 650;
	var pass_hitarea = new createjs.Shape();
	pass_hitarea.graphics.beginFill("rgba(0,0,0,0.01)").drawRect(300,650,pass_text.getMeasuredWidth()*1.5,70);
	gamesettings.addChild(pass_hitarea);
	pass_hitarea.addEventListener("click",function(){
		var newpass = prompt("<?php echo trans('Set password'); ?> :",password);
		if (newpass!=null && newpass!=""){
			pass_text.text = "<?php echo trans('Setted'); ?>";
			password = newpass;
		}
		if(newpass==""){
			password = "";
			pass_text.text = "<?php echo trans('Unset'); ?>";
		}
	});
	var gamesettings_dropdownlist = new dropdownlist(["重口味","小清新","随机"],310,520,1,function(choose){
		gamesettings.gameclass = choose;
	});
	gamesettings.addChild(gamesettings_dropdownlist);
	//ui animation
	mode_ui.x = stage_width;
	createjs.Tween.get(main_ui).to({x:-stage_width},500);
	createjs.Tween.get(mode_ui).to({x:0},500);
}

//Network mode game start
function netstart(roomid){
	game_ui.removeAllChildren();
	
	
}

//function used in singlestart() used to take photo.
function takephotos(num,callbackFunction,immjump,nownum,lastarray){
	if(immjump){
		callbackFunction(takephotos.lasttemp);
		return 0;
	}
	if(!takephotos.phptoid){
		takephotos.phptoid = 0;
	}
	takephotos.phptoid++;
	if(!nownum){
		nownum = 1;
		lastarray=[];
	}
	if(nownum<=num){
		calert("<?php echo trans('Player %1 ,It is your turn to take a photo now.'); ?>".replace(/%1/,nownum),function(){
			getlogo(function(res){
				if(res){
					headimgquery.loadFile({id:"userheadimg"+takephotos.phptoid,src:res,type:createjs.AbstractLoader.IMAGE});
					lastarray[nownum] = "userheadimg"+takephotos.phptoid;
					takephotos(num,callbackFunction,immjump,nownum+1,lastarray);
				}else{
					cconfirm("<?php echo trans('Player %1 failed to take a photo. Retry or use random head portrait?'); ?>".replace(/%1/,nownum),function(s){
						if(s){
							takephotos(num,callbackFunction,immjump,nownum,lastarray);
						}else{
							lastarray[nownum] = "random";
							takephotos(num,callbackFunction,immjump,nownum+1,lastarray);
						}
					},"<?php echo trans('Retry'); ?>","<?php echo trans('Random'); ?>");
				}
			})
		});
	}else{
		takephotos.lasttemp = lastarray;
		if(headimgquery.getItems(false).length){
			if(headimgquery.loaded){
				callbackFunction(lastarray);
			}else{
				headimgquery.on("complete",function(){
					callbackFunction(lastarray);
				},this);
			}
		}else{
			callbackFunction(lastarray);
		}
	}
}

//Pending Action
function pending(showtext){
	var _self = this;
	var pendingmask = new top_mask();
	var pendctn = new createjs.Container();
	top_ui.addChild(pendctn);
	var pendshape = new createjs.Shape();
	pendshape.graphics.beginFill("rgba(0,0,0,0.5)").drawRoundRect(0,0,180,100,10);
	pendctn.addChild(pendshape);
	var pendtxt = new createjs.Text(showtext, "30px Arial", "#FFFFFF");
	pendtxt.textBaseline = "top";
	pendtxt.textAlign = "center";
	pendtxt.x = 90;
	pendtxt.y = 10;
	pendctn.addChild(pendtxt);
	var pendcirc = new createjs.Shape();
	pendcirc.graphics.beginFill("rgb(72,145,227)").drawCircle(0,0,6);
	pendctn.addChild(pendcirc);
	pendcirc.x = 25;
	pendcirc.y = 75;
	createjs.Tween.get(pendcirc,{loop:true}).to({x:155},600,createjs.Ease.cubicInOut).to({x:25},600,createjs.Ease.cubicInOut);
	pendctn.regX = 90;
	pendctn.regY = 50;
	pendctn.x = stage_width/2;
	pendctn.y = stage_height/2;
	pendctn.scaleY = pendctn.scaleX = Math.min(stage_width,stage_height)*0.4/140;
	_self.clear = function(){
		pendingmask.removeMask();
		top_ui.removeChild(pendctn);
	}
	_self.changeText = function(text){
		pendtxt.text = text;
	}
}

//Universal function to get user's head portrait
function getlogo(callbackFunction){
<?php 
if(WechatEnabled){
?>
	//Script to execute if WechatEnabled
	wx.chooseImage({
		count: 1,
		sizeType: 'original',
		sourceType: ['album', 'camera'],
		success: function(res){
			callbackFunction(res.localIds);
		},
		cancel: function(){
			callbackFunction(false);
		},
		fail: function(){
			callbackFunction(false);
		},
	});
<?php 
}else{
?>
	//Script to execute if WechatDisabled
	callbackFunction(false);
<?php 
}
?>
}

//Generating random head portrait
function randomshape(){
	var shape = new createjs.Shape();
	var backcolor = "rgb("+Math.floor(Math.random()*256)+","+Math.floor(Math.random()*256)+","+Math.floor(Math.random()*256)+")"; 
	var frontcolor = "rgb("+Math.floor(Math.random()*256)+","+Math.floor(Math.random()*256)+","+Math.floor(Math.random()*256)+")"; 
	for(var i=0;i<3;i++){
		for(var n=0;n<5;n++){
			if(Math.round(Math.random()) == 1){
				shape.graphics.beginFill(backcolor).drawRect(56*i,56*n,57,57);
				shape.graphics.beginFill(backcolor).drawRect(56*(4-i),56*n,57,57);
			}else{
				shape.graphics.beginFill(frontcolor).drawRect(56*i,56*n,57,57);
				shape.graphics.beginFill(frontcolor).drawRect(56*(4-i),56*n,57,57);
			}
		}
	}
	shape.cache(0, 0, 280, 310);
	return shape;
}

//Displaying words cards
function showcardword(cardlist,wordsarray,callbackFunction,currenti){
	if(!currenti){
		showcardword.nowkey = 1;
		showcardword.showscale = Math.min(stage_width,stage_height*0.75)*0.6/300;
	}else{
		showcardword.nowkey++;
	}
	if(cardlist[showcardword.nowkey] != undefined){
		showcardword.lastx = cardlist[showcardword.nowkey].x;
		showcardword.lasty = cardlist[showcardword.nowkey].y;
		showcardword.lastscale = cardlist[showcardword.nowkey].scaleX;
		showcardword.lastclone = cardlist[showcardword.nowkey].clone(true);
		//Because of Chrome security issue, cross-origin image may not respond to click event correctly. Use an almost transparent mask to solve it.
		var clickmask = new createjs.Shape();
		clickmask.graphics.beginFill("rgba(0,0,0,0.01)").drawRect(0,0,300,400);
		showcardword.lastclone.addChild(clickmask);
		cardlist[showcardword.nowkey].alpha = 0;
		game_ui.addChild(showcardword.lastclone);
		createjs.Tween.get(showcardword.lastclone).to({},500).to({x:stage_width/2-150*showcardword.showscale,y:stage_height/2-200*showcardword.showscale,scaleX:showcardword.showscale,scaleY:showcardword.showscale},500,createjs.Ease.backOut).call(function(){
			clickmask.addEventListener("click", function(evt){
				play("flipcard");
				createjs.Tween.get(showcardword.lastclone).to({x:stage_width/2,scaleX:0},200).call(function(){
					var card_backimg = new createjs.Bitmap(queue.getResult("card_background"));
					card_backimg.scaleY = card_backimg.scaleX = 2;
					showcardword.lastclone.addChild(card_backimg);
					var wordtext = new createjs.Text(wordsarray[showcardword.nowkey], "60px Arial", "black");
					wordtext.x = 150;
					wordtext.y= 30;
					wordtext.maxWidth = 300;
					wordtext.textAlign = "center";
					wordtext.textBaseline = "top";
					showcardword.lastclone.addChild(wordtext);
					if(wordsarray[showcardword.nowkey] == ""){
						var hinttext = new createjs.Text("<?php echo trans('You just picked up a blank card. Try to guess others` words and confuse others to protect yourself. '); ?><?php echo trans('Click again and pass the phone to the next.'); ?>", "20px Arial", "black");
					}else{
						var hinttext = new createjs.Text("<?php echo trans('Remember your word and do not tell it to others. When you finish, '); ?><?php echo trans('Click again and pass the phone to the next.'); ?>", "20px Arial", "black");
					}
					hinttext.x = 20;
					hinttext.y = 200;
					hinttext.maxWidth = 260;
					hinttext.lineWidth = 260;
					hinttext.textBaseline = "top";
					showcardword.lastclone.addChild(hinttext);
					createjs.Tween.get(showcardword.lastclone).to({x:stage_width/2-150*showcardword.showscale,y:stage_height/2-200*showcardword.showscale,scaleX:showcardword.showscale,scaleY:showcardword.showscale},200).call(function(){
						clickmask.addEventListener("click", function(evt){
							play("flipcard");
							createjs.Tween.get(showcardword.lastclone).to({x:stage_width/2,scaleX:0},200).call(function(){
								showcardword.lastclone.removeChild(hinttext,wordtext,card_backimg);
								createjs.Tween.get(showcardword.lastclone).to({x:stage_width/2-150*showcardword.showscale,y:stage_height/2-200*showcardword.showscale,scaleX:showcardword.showscale,scaleY:showcardword.showscale},200).to({x:showcardword.lastx,y:showcardword.lasty,scaleX:showcardword.lastscale,scaleY:showcardword.lastscale},500).call(function(){
									showcardword.lastclone.parent.removeChild(showcardword.lastclone);
									cardlist[showcardword.nowkey].alpha = 1;
									showcardword(cardlist,wordsarray,callbackFunction,1);
								});
							});
							evt.remove();
						});
					});
				});
				evt.remove();
			});
		});
	}else{
		callbackFunction();
	}
}

//Random selection
function arrayselect(pickups,numbers){
	var outputarr = [];
	outputarr.push(Math.ceil(Math.random()*numbers));
	var found = 0;
	for(var i=2;i<=pickups;i++){
		do{
			found = 0;
			var tmp = Math.ceil(Math.random()*numbers);
			$.each(outputarr,function(key,value){
				if(tmp == value){
					found++;
					return false;
				}
			});	
		}while(found);	
		outputarr.push(tmp);
	}
	return outputarr.sort();
}

//API connection function
function netconn(TargetName,postData,callbackFunction){
	$.ajax({
		type: "POST",
		url: "apis/"+TargetName+".php",
		data: postData,
		success: function(ReturnData,Status){
			if(typeof(ReturnData)!="string"){
				if(ReturnData.status=="error"){
					if(confirm("<?php echo trans('Server returned an error.'); ?>\n"+"<?php echo trans('Target'); ?> : apis/"+TargetName+".php\n<?php echo trans('Error Message'); ?> : \n"+ReturnData.errmsg+"\n<?php echo trans('Retry'); ?>?")){
						netconn(TargetName,postData,callbackFunction);
					}else{
						callbackFunction(false);
					}
					return 0;
				}
				callbackFunction(ReturnData);
				return 0;
			}
			var parsed = JSON.parse(ReturnData);
			try{
				var parsed = JSON.parse(ReturnData);
			}catch(e){
				if(confirm("<?php echo trans('Server have returned a non-JSON response. The most likely reason is the administrator incorrect configured.'); ?>\n<?php echo trans('Server respond was'); ?> : \n"+ReturnData+"\n<?php echo trans('Retry'); ?>?")){
					netconn(TargetName,postData,callbackFunction);
				}else{
					callbackFunction(false);
				}
				return 0;
			}
			callbackFunction(parsed);
		},
		error: function(xhr,textStatus,errorThrown){
			if(confirm("<?php echo trans('An error occured when requesting and processing data from server'); ?>"+"\n"+"<?php echo trans('Target'); ?> : apis/"+TargetName+".php\n<?php echo trans('Error'); ?> : "+errorThrown+"\n<?php echo trans('Retry'); ?>?")){
				netconn(TargetName,postData,callbackFunction);
			}else{
				callbackFunction(false);
			}
		}
	});
}

//net EventListener Class
function netEventListener(netevent,callBackFunction,param){
	
}

//Who will be the next?
