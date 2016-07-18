<?php 
include_once dirname(dirname(__FILE__)).'/config/loader.php';
header('Content-Type: application/json');
session_start();
if(!isset($_SESSION['started'])){
	echo '{"status":"error","errmsg":"session is not started yet"}';
	exit;
}
if(!(is_numeric($_POST['roomid']) && $_POST['roomid']>=0)){
	echo '{"status":"error","errmsg":"param roomid invalid"}';
	exit;
}
$roomid = $_POST['roomid'];
if(!isset($_POST['password'])){
	$password = "";
}else{
	$password = $_POST['password'];
}
$sql = 'SELECT * FROM `'.TABLE_PREFIX.'_Rooms` WHERE `ID` = '.$roomid;
$res = $mysqli->query($sql);
if($row = $res->fetch_row()){
	@$userarray = $row[1];
	@$config = $row[3];
	if(!$userarray || !$config){
		echo '{"status":"error","errmsg":"string in Database cannot be unserialized"}';
		exit;
	}
	if($config["password"] != $password){
		echo '{"status":"fail","failcode":1,"failmsg":"invalid password"}';
		exit;
	}
	foreach($userarray as $value){
		if($value != 0 && $value[0] == $_SESSION['openid']){
			echo '{"status":"ok","roomid":'.$roomid.',"position":'.($key+1).'}';
			exit;
		}
	}
	foreach($userarray as $key=>$value){
		if($value == 0){
			$userarray[$key]=array($_SESSION['openid'],$_SESSION['nickname'],$_SESSION['userimg']);
			$userarray = serialize($userarray);
			$sql = "UPDATE  `".TABLE_PREFIX."_Rooms` SET  `users` =  '$userarray' WHERE  `".TABLE_PREFIX."_Rooms`.`ID` = ".$roomid;
			if($mysqli->query($sql) === TRUE){
				echo '{"status":"ok","roomid":'.$roomid.',"position":'.($key+1).'}';
				exit;
			}
			echo '{"status":"error","errmsg":"Database update failure"}';
			exit;
		}
	}
	echo '{"status":"error","errmsg":"unknown error"}';
	exit;
}else{
	echo '{"status":"fail","failcode":0,"failmsg":"room not found"}';
	exit;
}
?>