<?php 
include_once dirname(dirname(__FILE__)).'/config/loader.php';
header('Content-Type: application/json');
session_start();
if(!isset($_SESSION['started'])){
	echo '{"status":"error","errmsg":"session is not started yet"}';
	exit;
}
if(!(is_numeric($_POST['usernum']) && $_POST['usernum']>=3 && $_POST['usernum']<=12)){
	echo '{"status":"error","errmsg":"param usernum invalid"}';
	exit;
}
$usernum = $_POST['usernum'];
if(!(is_numeric($_POST['spynum']) && $_POST['spynum']>=0 && $_POST['spynum']<=12)){
	echo '{"status":"error","errmsg":"param spynum invalid"}';
	exit;
}
$spynum = $_POST['spynum'];
if(!(is_numeric($_POST['whitenum']) && $_POST['whitenum']>=0 && $_POST['whitenum']<=12)){
	$whitenum = 0;
}else{
	$whitenum = $_POST['whitenum'];
}
if(!(is_numeric($_POST['usernum']) && $_POST['usernum']>=3 && $_POST['usernum']<=12)){
	echo '{"status":"error","errmsg":"param usernum invalid"}';
	exit;
}
$usernum = $_POST['usernum'];
$openid = $_SESSION['openid'];
if(!isset($_POST['password'])){
	$password = "";
}else{
	$password = $_POST['password'];
}
if(!isset($_POST['wordtype'])){
	echo '{"status":"error","errmsg":"param wordtype undefined"}';
	exit;
}
$userArray = array();
array_push($userArray,array($openid,$_SESSION['nickname'],$_SESSION['userimg']));
for($i=1;$i<$usernum;$i++){
	array_push($userArray,0);
}
$userArray = $mysqli->real_escape_string(serialize($userArray));
$timestamp = time();
$config = array("password"=>$password,"wordtype"=>"","spynum"=>$spynum,"whitenum"=>$whitenum);
$config = $mysqli->real_escape_string(serialize($config));
$sql = 'INSERT INTO `'.TABLE_PREFIX."_Rooms` (`ID`, `users`, `createtime`, `config`) VALUES (NULL, '$userArray', '$timestamp', '$config');";
if(!$mysqli->query($sql)){
	echo '{"status":"error","errmsg":"Database insert failure"}';
	//$mysqli->sqlstate
	exit;
}
echo '{"status":"ok","roomid":"'.$mysqli->insert_id.'"}';
?>