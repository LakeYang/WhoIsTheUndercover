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

$sql = 'SELECT * FROM `'.TABLE_PREFIX.'_Rooms` WHERE `ID` = '.$roomid;
$res = $mysqli->query($sql);
if($res && $row = $res->fetch_row()){
	@$status = unserialize($row[5]);
	@$userarray = unserialize($row[1]);
	@$chatsarray = unserialize($row[4]);
	if(!is_array($userarray) || !is_array($chatsarray) || !is_array($status)){
		echo '{"status":"error","errmsg":"string in Database cannot be unserialized"}';
		exit;
	}
	$usernum = count($userarray);
	if($status[0] == 0){
		//Room master initiate the game
		if($userarray[0][0] == $_SESSION['openid']){
			$status[0] = 1;//rounds
			$status[1] = array();//votes
			$status[2] = array();//user live or die
			for($i=0;$i<$usernum;$i++){
				array_push($status[1],0);
				array_push($status[2],1);
			}
			if($chatsarray[0] == 100){
				$chatsarray[0] = 1;
				$chatsarray[1] = array($_SESSION['nickname'],trans('Room master began the game'),time());
			}else{
				$chatsarray[0]++;
				$chatsarray[$chatsarray[0]] = array($_SESSION['nickname'],trans('Room master began the game'),time());
			}
			$chatsarray = $mysqli->real_escape_string(serialize($chatsarray));
			
		}
		echo '{"status":"error","errmsg":"you are not the master of this room"}';
		exit;
	}
}else{
	echo '{"status":"error","errmsg":"target room not found"}';
	exit;
}

?>