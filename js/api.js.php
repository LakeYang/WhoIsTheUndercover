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
	background = new createjs.Shape();
	background.alpha=0;
	background.graphics.beginFill("rgb(187,209,232)").drawRect(0, 0, $(window).width(), $(window).height());
	stage.addChild(background);
	createjs.Tween.get(background).to({alpha: 1}, 1000).call(function(){$("#top_ui").remove()});
	createjs.Ticker.setFPS(30);
	createjs.Ticker.addEventListener("tick", stage);
}