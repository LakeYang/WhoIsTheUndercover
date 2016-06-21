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
//Translation function
function trans($transtext){
	if(isset($GLOBALS['langarray'][$transtext]))return $GLOBALS['langarray'][$transtext];
	return $transtext;
}
//Error Reporting function
function errorjump($errcode){
	switch($errcode)
	{
	case 1:
		//Cannot Connect to Database
		header('Location: errorpages/database.php');
		exit;
	case 2:
		//Database processing error
		exit;		
	default:
		//Other Unknown error
		exit;
	}
}
//Database Connection
@$mysqli = new mysqli(HOSTNAME,DB_USERNAME,DB_PASSWORD,DB_NAME);
//if(mysqli_connect_errno())errorjump(1);

?>