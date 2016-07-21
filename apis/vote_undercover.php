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
if(!(isset($_POST['target']) && is_numeric($_POST['target']) && $_POST['target']>=0 && $_POST['target']<=12)){
	echo '{"status":"error","errmsg":"param target invalid"}';
	exit;
}
$target = $_POST['target'];
$sql = 'SELECT * FROM `'.TABLE_PREFIX.'_Rooms` WHERE `ID` = '.$roomid;
$res = $mysqli->query($sql);
if($res && $row = $res->fetch_row()){
	@$userarray = unserialize($row[1]);
	@$status = unserialize($row[5]);
	if(!is_array($userarray) || !is_array($status)){
		echo '{"status":"error","errmsg":"string in Database cannot be unserialized"}';
		exit;
	}
	foreach($userarray as $key=>$value){
		if($value != 0 && $value[0] == $_SESSION['openid']){
			$userid = $key+1;
			if($status[0] == 0){
				echo '{"status":"error","errmsg":"room not initiated"}';
				exit;
			}
			if($status[1][$userid]!=0){
				echo '{"status":"error","errmsg":"you have already voted this round"}';
				exit;
			}
			$status[1][$userid] = $target;
			$status = $mysqli->real_escape_string(serialize($status));
			$sql = "UPDATE  `".TABLE_PREFIX."_Rooms` SET  `status` =  '$status' WHERE  `".TABLE_PREFIX."_Rooms`.`ID` = ".$roomid;
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