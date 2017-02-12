<?php

	ini_set('max_execution_time', 1200);
	ini_set('display_errors', "1");
//	ini_set('include_path', '.:..:/Library/WebServer/Documents:/Library/WebServer/Documents/mInvest:/Library/WebServer/Documents:/Library/WebServer/Documents/mInvest:/include');
	
	require_once "../adodb/adodb.inc.php";

	$h = '127.0.0.1';
	$d = 'mInvest';
	$u = 'root';
	$p = '';

$dsn = "mysql://$u:$p@$h/$d?persist";
$db = NewADOConnection($dsn);
$db->debug = false;

if (!$db) die("Connection failed");  

require_once "/Library/WebServer/Documents/mInvest/include/class.Numerical.php";
require_once "/Library/WebServer/Documents/mInvest/include/indicators.php";
require_once "/Library/WebServer/Documents/mInvest/include/yahooFunctions.inc.php";

$query = "select ticker from issuers";
$results = $db->GetAll($query);
$num_tasks = $rowsFound = count($results);

$lastDate = strftime("%Y-%m-%d");
	
foreach ($results as $recordIssuer)
{
	$symbol = $recordIssuer["ticker"];
			
	if (!$fDate)
	{
		$query = "select max(date) from quotes where ticker = \"$symbol\"";
		$fDate = $db->GetOne($query);
	}
	
	$ret = doGetYahooData($symbol, $fDate, $lastDate);
	echo "$symbol: $ret\n";
}
?>