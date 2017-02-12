<?php

	if (empty($_GET['action']))
	{
?>
	<br />
    <form action="data.php" method="GET">
    	<u>Update database</u>
    	<br />
    	<br />
    	using Yahoo data from the lastest date in the database up to last quote available on-line
    	<input type="submit" value="global" name="action" />
    	<br />
    	<br />
    	using Yahoo starting from 1995/06/01 up to the last quote available on-line:
    	<input type="text" name="ymd" size="10" maxlength="10" value="1995/06/01"/>
    	<input type="submit" value="reload" name="action" />
    	<br />
    	<br />
    	Reload all quotes from Yahoo data for a given ticker:
    	<input type="text" name="ticker" size="10" maxlength="10" />
    	<input type="submit" value="reloadTicker" name="action" />
    	<br />
    	<br />
    	<u>Fundamental data</u>
    	<br />
    	<br />
    	Load fundamentals data for the current week from Yahoo
    	<input type="submit" value="fundamentals" name="action" />
    	<br />
    	<br />
    	<u>Data checks and processing</u>
    	<br />
    	<br />
    	Check integrity of data (missing or changed ticker in Yahoo)
    	<input type="submit" value="integrity" name="action" />
    	<br />
    	<br />
    	<br />
    	Recalculate all signals for the database
    	<input type="submit" value="signals" name="action" />
	</form>
 <?php
	}
	else
	{
		$action = $_GET['action'];
		
		$fromDate = $_GET['ymd'];
		$ticker = $_GET['ticker'];
		ini_set('max_execution_time', 1200);
		require_once "../include/db.inc.php";
		require_once "../include/dataArraysSQL.php";
		require_once "../include/class.Yahoo.php";
		require_once "../include/indicators.php";
		require_once "../include/class.progressbar.php";
		require_once "../include/class.Numerical.php";
		require_once "../include/yahooFunctions.inc.php";

		switch ($_GET['action'])
		{
// Global Update
// read all tickers and update from the latest Date in database
			case "global":
			{
				include("globalUpdateDB.php");
				break;
			}
			case "reload":
			{
				require_once "../include/date_validate.inc.php";
				$fDate = date_validate($fromDate);
				if (substr($fDate, 0, 5) == "Error")
					die ($fDate);
				include("globalReloadDB.php");
				break;
			}
// Reload quotes for a specific ticker	
			case "reloadTicker":
			{
				include("oneTickerReloadDB.php");
				break;
			}
// Load fundamentals for all quotes 
			case "fundamentals":
			{
				include("loadFundamentals.php");
				break;
			}
// Identify those ticker for whihc the data is not up to date.  Requires further manual checks 
			case "integrity":
			{
				include("checkIntegrity.php");
				break;
			}
// Recalculate the signals in the database, for instance when the algorithm changes
			case "signals":
			{
				include("recalculateSignals.php");
				break;
			}
			default:
				die("Wrong action selected");
		}
	}
?>
