<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 'PHP_INI_ALL');

	require_once "../adodb5/adodb.inc.php";

//using MAMP locally
	$h = 'localhost';
//production
//	$d = 'uks13350mInvest';
//	$u = 'uks13350ajj';
//	$p = '273295';

//test local
	$d = 'minvest';
	$u = 'root';
	$p = 'Isolde13';

	$dsn = "mysql://$u:$p@$h/$d?persist";
	$db = NewADOConnection($dsn);

//	$db->debug = true;
	$db->debug = false;

//range to be used
	$defaultDateRangeChart = date('Y-m-d', strtotime('-24 months'));

    if (!$db) die("Connection failed");
?>