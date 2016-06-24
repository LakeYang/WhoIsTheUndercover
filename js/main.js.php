<?php 
include_once dirname(dirname(__FILE__)).'/config/loader.php';
header('Cache-Control: max-age=43200');
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
	$("#top_ui").hide();
});