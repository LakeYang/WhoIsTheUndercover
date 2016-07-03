<?php 
include_once dirname(__FILE__).'/config/loader.php';
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
<script src="js/api.js.php"></script>
<script src="js/main.js.php"></script>
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
</div>
<div id="ck-button">
  <label>
    <input type="checkbox" onClick="javascript:if(!this.checked){$('.if_sound_enabled').html('<?php echo trans('Sound enabled'); ?>');}else{$('.if_sound_enabled').html('<?php echo trans('Sound disabled'); ?>');};"><span class="if_sound_enabled"><?php echo trans('Sound enabled'); ?></span>
  </label>
</div>
<br>
<input onClick="init()" class="enter-btn" type="button" name="button" id="button" value="<?php echo trans('Enter anyway'); ?>">
</div>

<?php 

?>
</body>
</html>
