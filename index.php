<?php 
include_once dirname(__FILE__).'/config/loader.php';
$HTTP_SCHEME = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
$SignHost = $_SERVER['HTTP_HOST'];
if(CutPort){
	$SignHost = explode(":",$SignHost);
	$SignHost = $SignHost[0];
}
$url = urlencode($HTTP_SCHEME.$SignHost.$_SERVER['REQUEST_URI']);
if(isset($_GET['code']) && WechatEnabled){
	$json = file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=".APPID."&secret=".SECRET."&code=".urlencode($_GET['code'])."&grant_type=authorization_code");
	$WebAccessTokenRespond = json_decode($json, true);
	$WebAccessToken = $WebAccessTokenRespond['access_token'];
	$UserOpenID = $WebAccessTokenRespond['openid'];
	$json = file_get_contents("https://api.weixin.qq.com/sns/userinfo?access_token=$WebAccessToken&openid=$UserOpenID&lang=zh_CN");
	$UserInfo = json_decode($json, true);
	$UserName = $UserInfo['nickname'];
	$UserImg = $UserInfo['headimgurl'];
	if(!(isset($UserInfo['nickname']) && $UserInfo['nickname']!="")){
		$UserName = "UNSET";
	}
	if(!(isset($UserInfo['headimgurl']) && $UserInfo['headimgurl']!="")){
		$UserImg = "UNSET";
	}
}else if(WechatEnabled){
	header('Location: https://open.weixin.qq.com/connect/oauth2/authorize?appid='.APPID.'&redirect_uri='.$url.'&response_type=code&scope=snsapi_userinfo#wechat_redirect');
	exit;
}
session_start();
$_SESSION['started'] = 1;
$_SESSION['openid'] = $UserOpenID;
$_SESSION['nickname'] = $UserName;
$_SESSION['userimg'] = $UserImg;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0,user-scalable=no" />
<!--
This is an open-source web game by LakeYang
GitHub Page: github.com/LakeYang/WhoIsTheUndercover.git
Copyright LakeYang(hihuyang.com) 2016
Licensed under the Apache License, Version 2.0
-->
<title><?php echo trans('Who is the undercover'); ?></title>
<script src="js/libs/jquery-3.0.0.min.js"></script>
<script src="js/libs/createjs-2015.11.26.min.js"></script>
<script src="js/api.js.php?url=<?php echo $url; ?>"></script>
<script>
play.enabled = 1;
<?php 
if(WechatEnabled){
?>
user_nickname = "<?php echo $UserName; ?>";
user_headimg = "<?php echo $UserImg; ?>";
<?php 
}
?>
</script>
<link href="css/main.css" rel="stylesheet" type="text/css">
</head>

<body>
<div id="top_ui">
<span><?php echo trans('Loading...'); ?></span>
<div class="loading_total">
  <div class="loading_progress"></div>
</div>
<div id="ck-button">
  <label>
    <input id="soundswitch" type="checkbox" onClick="javascript:if(!this.checked){$('.if_sound_enabled').html('<?php echo trans('Sound enabled'); ?>');play.enabled=1;}else{$('.if_sound_enabled').html('<?php echo trans('Sound disabled'); ?>');play.enabled=0;};"><span class="if_sound_enabled"><?php echo trans('Sound enabled'); ?></span>
  </label>
</div>
<br>
<input onClick="init()" class="enter-btn" type="button" name="button" id="button" value="<?php echo trans('Enter anyway'); ?>">
</div>

<?php 

?>
</body>
</html>
