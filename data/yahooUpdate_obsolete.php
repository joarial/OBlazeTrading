<?php
	ini_set('max_execution_time', 1200);

	require_once "../include/db.inc.php";
	require_once "../include/date_validate.inc.php";
	require_once "../include/dataArraysSQL.php";
	require_once "../include/class.Yahoo.php";
	require_once "../include/indicators.php";
	require_once "../include/class.progressbar.php";
	require_once "../include/class.Numerical.php";
	require_once "../include/yahooFunctions.inc.php";

	$fromDate = $_POST['ymd'];
	
	if ($fromDate)
	{
		$fDate = date_validate($fromDate);
		if (substr($fDate, 0, 5) == "Error")
			die ($fDate);
	}
	
	$ticker = $_POST['ticker'];
	
// Global Update
// read all tickers and update from the latest Date in database

	if ($_POST['globalUpdateDB'])
	{
		include("globalUpdateDB.php");
	}
	
// Reload quotes for a specific ticker	
	
	else if ($_POST['oneTickerReloadDB'])
	{
		include("oneTickerReloadDB.php");
	}

// Load fundamentals for all quotes 

	else if ($_POST['loadFundamentals'])
	{
		include("loadFundamentals.php");
	}

// Identify those ticker for whihc the data is not up to date.  Requires further manual checks 

	else if ($_POST['checkIntegrity'])
	{
		include("checkIntegrity.php");		
	}
// Recalculate the signals in the database, for instance when the algorithm changes

	else if ($_POST['recalculateSignals'])
	{
		include("recalculateSignals.php");		
	}
	else	
		die("Wrong action selected");
	
	echo "\n<br /><br /><br /><br />";
?>