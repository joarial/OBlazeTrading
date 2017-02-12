<?php
/**************************************************************************************
Originally from http://www.phpbuddy.com/article.php?id=8
Author: Ranjit Kumar (Cheif Editor phpbuddy.com)
Modified by: Ying Zhang (Dodo) http://pure-essence.net
Two main modifications:
1. To check currently saved screen resolution cookie
2. To allow inclusion by other php files

In order to use this file in another php file, use:

$callget_res_page_name = $REQUEST_URI;
$GLOBALS[callget_res_page_name] = $callget_res_page_name;
include("get_resolution.php");
echo $screen_width." is your screen width and ".$screen_height." is your screen height.";

*****************************************************************************************/
?>

<script language="javascript">
<!--
function writeCookie() 
{
	var today = new Date();
 	var the_date = new Date("December 31, 2023");
 	var the_cookie_date = the_date.toGMTString();
 	var the_cookie = "users_res="+ screen.width +"x"+ screen.height;
 	var the_cookie = the_cookie + ";expires=" + the_cookie_date;
 	document.cookie = the_cookie;
 
 	if (document.cookie)
 	{ 
		location = '<?=$GLOBALS['callget_res_page_name']?>';
 	}
}

function checkRes(width, height)
{
	if(width != screen.width || height != screen.height)
		writeCookie();
	else
		return true;
}
//-->
</script>

<?php
if(isset($_COOKIE["users_res"]))
{
	$screen_res = $_COOKIE["users_res"];
	$screen_res_tmp = explode("x", $screen_res);
	$screen_width = $screen_res_tmp[0];
	$screen_height = $screen_res_tmp[1];
?>

<script language="javascript">
<!--
	checkRes(<?=$screen_width?>, <?=$screen_height?>);
//-->
</script>

<?php
}
else //means cookie is not found set it using Javascript
{
?>

<script language="javascript">
<!--
	writeCookie();
//-->
</script>

<?php
}
?>