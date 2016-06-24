<?php 
include_once dirname(__FILE__).'/config/loader.php';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<!--
This is an open-source web game by LakeYang
GitHub Page: githun.com/LakeYang/WhoIsTheUndercover.git
Copyright LakeYang(hihuyang.com) 2016
Licensed under the Apache License, Version 2.0
-->
<title><?php echo trans('Who is the undercover'); ?></title>
<script src="js/libs/jquery-3.0.0.min.js"></script>
<script src="js/main.js.php"></script>
<?php 
if(WechatEnabled){
?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<?php 
}
?>
<script>
var WechatEnabled=<?php if(WechatEnabled)echo 1;else echo 0; ?>;
</script>
<link href="css/main.css" rel="stylesheet" type="text/css">
</head>

<body>
<div id="top_ui">
<span><?php echo trans('Loading...'); ?></span>
<div class="loading_total">
  <div class="loading_progress"></div>
  <span><?php  ?></span>
</div>

</div>

<?php 

?>
</body>
</html>