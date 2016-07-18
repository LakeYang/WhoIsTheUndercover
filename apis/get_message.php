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
if(!(isset($_POST['since']) && is_numeric($_POST['since']) && $_POST['since']>=0)){
	$since = 0;
}
$since = $_POST['since'];
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
			$outputarray = array();
			$key = $chatsarray[0];
			while(count($outputarray)<100){			
				if(isset($chatsarray[$key][2]) && $chatsarray[$key][2]>=$since){
					array_push($outputarray,$chatsarray[$key]);
					$key--;
					if($key <= 0){
						$key = 100;
					}
				}else{
					break;
				}
			}
			$outputarray = json_encode($outputarray);
			echo '{"status":"ok","messages":'.$outputarray.'}';
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