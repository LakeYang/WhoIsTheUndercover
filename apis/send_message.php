<?php 
include_once dirname(dirname(__FILE__)).'/config/loader.php';
header('Content-Type: application/json');
session_start();
if(!isset($_SESSION['started'])){
	echo '{"status":"error","errmsg":"session is not started yet"}';
	exit;
}
if(!(isset($_POST['roomid']) && is_numeric($_POST['roomid']) && $_POST['roomid']>=0)){
	echo '{"status":"error","errmsg":"param roomid invalid"}';
	exit;
}
$roomid = $_POST['roomid'];
if(!(isset($_POST['message']) && $_POST['message']!="")){
	echo '{"status":"error","errmsg":"param message invalid"}';
	exit;
}
$sql = 'SELECT * FROM `'.TABLE_PREFIX.'_Rooms` WHERE `ID` = '.$roomid;
$res = $mysqli->query($sql);
if($res && $row = $res->fetch_row()){
	@$userarray = unserialize($row[1]);
	@$chatsarray = unserialize($row[4]);
	if(!$userarray || !$chatsarray){
		echo '{"status":"error","errmsg":"string in Database cannot be unserialized"}';
		exit;
	}
	foreach($userarray as $value){
		if($value != 0 && $value[0] == $_SESSION['openid']){
			if($chatsarray[0] == 100){
				$chatsarray[0] = 1;
				$chatsarray[1] = array($_SESSION['nickname'],$_POST['message'],time());
			}else{
				$chatsarray[0]++;
				$chatsarray[$chatsarray[0]] = array($_SESSION['nickname'],$_POST['message'],time());
			}
			$chatsarray = $mysqli->real_escape_string(serialize($chatsarray));
			$sql = "UPDATE  `".TABLE_PREFIX."_Rooms` SET  `chats` =  '$chatsarray' WHERE  `".TABLE_PREFIX."_Rooms`.`ID` = ".$roomid;
			if($mysqli->query($sql) === TRUE){
				echo '{"status":"ok"}';
				exit;
			}
			echo '{"status":"error","errmsg":"Database update failure"}';
			exit;
		}
	}
	echo '{"status":"error","errmsg":"you are not in this room"}';
	exit;
}else{
	echo '{"status":"error","errmsg":"target room not found"}';
	exit;
}
?>