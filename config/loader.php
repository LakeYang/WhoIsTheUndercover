<?php 
include dirname(__FILE__).'/config.php';
//loading language packs, Chinese chosen if target language pack not found.
$language=LANGUAGE;
if((@$langfile = file_get_contents(dirname(dirname(__FILE__)).'/languages/'.$language.'.json'))==FALSE){
	@$langfile = file_get_contents(dirname(dirname(__FILE__)).'/languages/zh_CN.json');
	$language = 'zh_CN';
}
$langarray = json_decode($langfile, true) or $langarray = array();
unset($langfile);
//Get url for this page
if(isset($_GET['url'])){
	$url=$_GET['url'];
}else{
	$HTTP_SCHEME = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$url = urlencode($HTTP_SCHEME.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
}
//Translation function
function trans($transtext){
	if(isset($GLOBALS['langarray'][$transtext]))return $GLOBALS['langarray'][$transtext];
	return $transtext;
}
//Error Reporting function
function errorjump($errcode,$errorinfo){
	switch($errcode)
	{
	case 1:
		//Cannot Connect to Database
		include dirname(__FILE__).'/errorpages/database.php';
		exit;
	case 2:
		//Database processing error
		echo $errorinfo;
		exit;		
	default:
		//Other Unknown error
		exit;
	}
}
//Database Connection
@$mysqli = new mysqli(HOSTNAME,DB_USERNAME,DB_PASSWORD,DB_NAME);
if(mysqli_connect_errno())errorjump(1,0);
//Wechat module
if(WechatEnabled){
	//Wechat generating randstr
	$str = "";
	$str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
	$max = strlen($str_pol) - 1;
	for ($i = 0; $i < 16; $i++) {
		$str .= $str_pol[mt_rand(0, $max)];
	}
	//Wechat timestamp
	$timestamp = time();
	//Wechat get Access-token
	$sql = 'SELECT * FROM `'.TABLE_PREFIX.'_AuthToken` WHERE `ID` = 1';
	$res = $mysqli->query($sql);
	if($row = $res->fetch_row()){
		if(time()-$row[3] >= 7000){
			$AccessTokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".APPID."&secret=".SECRET;
			$json = file_get_contents($AccessTokenUrl);
			$AccessTokenRespond = json_decode($json, true);
			$AccessToken = $AccessTokenRespond['access_token'];
			$sql = "UPDATE `".TABLE_PREFIX."_AuthToken` SET `val` = '".$AccessToken."', `stamp` = '".$timestamp."' WHERE `".TABLE_PREFIX."_AuthToken`.`ID` = 1;";
			$res = $mysqli->query($sql);
		}else{
			$AccessToken = $row[2];
		}
	}else{
		errorjump(2,'Error occured when getting data from database'.TABLE_PREFIX.'_AuthToken');
	}
	//Wechat get jsapi-ticket
	$sql = 'SELECT * FROM `'.TABLE_PREFIX.'_AuthToken` WHERE `ID` = 2';
	$res = $mysqli->query($sql);
	if($row = $res->fetch_row()){
		if(time()-$row[3] >= 7000){
			$JsapiTicketUrl="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$AccessToken&type=jsapi";
			$json = file_get_contents($JsapiTicketUrl);
			$JsapiTicketRespond = json_decode($json, true);
			$JsapiTicket = $JsapiTicketRespond['ticket'];
			$res->free();
			$sql = "UPDATE `".TABLE_PREFIX."_AuthToken` SET `val` = '".$JsapiTicket."', `stamp` = '".$timestamp."' WHERE `".TABLE_PREFIX."_AuthToken`.`ID` = 2;";
			$res = $mysqli->query($sql);
		}else{
			$JsapiTicket = $row[2];
		}
	}else{
		errorjump(2,'Error occured when getting data from database'.TABLE_PREFIX.'_AuthToken');
	}
	//Generate Signature
	$timestamp = time();
	$signature = "jsapi_ticket=$JsapiTicket&noncestr=$str&timestamp=$timestamp&url=".$url;
	$signature = sha1($signature);
}




?>