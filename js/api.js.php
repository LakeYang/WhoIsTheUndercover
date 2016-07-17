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
		{id:"gear1", src:"assets/image/gear1.png"},
		{id:"gear2", src:"assets/image/gear2.png"},
		{id:"gear3", src:"assets/image/gear3.png"},
		{id:"gear4", src:"assets/image/gear4.png"},
		{id:"gear5", src:"assets/image/gear5.png"},
		{id:"card_background", src:"assets/image/card_background.png"},
		{id:"paper1", src:"assets/audio/paper1.mp3"},
		{id:"paper2", src:"assets/audio/paper2.mp3"},
		{id:"stamp", src:"assets/audio/stamp.mp3"},
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
 	singlemode_bg.scaleX = stage_width/752;
	singlemode_bg.scaleY = stage_height/1000;
	singlemode_ui.alpha = 0;
	createjs.Tween.get(singlemode_ui).to({alpha: 1},500).call(function(){}); 
	var gear = new createjs.Container();
	singlemode_ui.addChild(gear);
	var gear1 = new createjs.Bitmap(queue.getResult("gear1"));
	var gear2 = new createjs.Bitmap(queue.getResult("gear2"));
	var gear3 = new createjs.Bitmap(queue.getResult("gear3"));
	var gear4 = new createjs.Bitmap(queue.getResult("gear4"));
	var gear5 = new createjs.Bitmap(queue.getResult("gear5"));
	gear.addChild(gear1,gear2,gear3,gear4,gear5);
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
	gear.x = -58;
	gear.y = -222;
	var gear1_speed = 20000;
	var gear2_speed = gear1_speed/(gear1.regX/gear2.regX);
	var gear3_speed = gear2_speed/(gear2.regX/gear3.regX);
	var gear4_speed = gear3_speed/(gear3.regX/gear4.regX);
	var gear5_speed = gear4_speed/(gear4.regX/gear5.regX);
 	createjs.Tween.get(gear1,{loop:true}).to({rotation:360},gear1_speed).call(function(){});
	createjs.Tween.get(gear2,{loop:true}).to({rotation:-360},gear2_speed).call(function(){});
	createjs.Tween.get(gear3,{loop:true}).to({rotation:360},gear3_speed).call(function(){});
	createjs.Tween.get(gear4,{loop:true}).to({rotation:-360},gear4_speed).call(function(){});
	createjs.Tween.get(gear5,{loop:true}).to({rotation:360},gear5_speed).call(function(){}); 
	//ui animation
	mode_ui.x = stage_width;
	createjs.Tween.get(main_ui).to({x:-stage_width},500);
	createjs.Tween.get(mode_ui).to({x:0},500).call(function(){
		main_ui.removeAllChildren();
		calert("Done",function(){
			wordssss=['辣椒','芥末'];
			singlestart(3,1,0,wordssss,1);
		});
	});
}

//Network mode main setting page
function networkmode(){
	calert("Developing,please wait..",0);
}

//Single mode game start
function singlestart(player_num,spy_num,white_num,wordarr,mustphoto){
	var folk_num = player_num - spy_num - white_num;
	//block first
	var main_block = new createjs.Shape();
	main_block.alpha = 0;
	main_block.graphics.beginFill("black").drawRect(0, 0, stage_width, stage_height);
	mode_ui.addChild(main_block);
	main_block.addEventListener("click",function(){/*This is a blackhole*/});
	//Animation first
	var ctn_back = new createjs.Bitmap(queue.getResult("modeselect_background"));
	ctn_back.scaleX = stage_width/360;
	ctn_back.scaleY = stage_height/640;
	game_ui.addChild(ctn_back);
	
	game_ui.x = stage_width;
	createjs.Tween.get(mode_ui).to({x:-stage_width},500);
	createjs.Tween.get(game_ui).to({x:0},500).call(function(){
		mode_ui.removeAllChildren();
		mode_ui.x = 0;
		//Remove headimgquery if defined
		if(headimgquery){
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
					//Add mouse event listener
					for(var i=1;i<=player_num;i++){
						shapelist[i].no = i;
						shapelist[i].addEventListener("click", function(evt){
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
												var endingtxt = "<?php echo trans('Undercovers win! Undercovers: Player %1'); ?>".replace(/%1/,123);
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
		});
	});
}

//function used in singlestart() used to take photo.
function takephotos(num,callbackFunction,nownum,lastarray){
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
					takephotos(num,callbackFunction,nownum+1,lastarray);
				}else{
					cconfirm("<?php echo trans('Player %1 failed to take a photo. Retry or use random head portrait?'); ?>".replace(/%1/,nownum),function(s){
						if(s){
							takephotos(num,callbackFunction,nownum,lastarray);
						}else{
							lastarray[nownum] = "random";
							takephotos(num,callbackFunction,nownum+1,lastarray);
						}
					},"<?php echo trans('Retry'); ?>","<?php echo trans('Random'); ?>");
				}
			})
		});
	}else{
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

//Who will be the next?
