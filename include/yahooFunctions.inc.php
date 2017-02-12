<?php
function doCalculateColumns($scenarios, &$record)
{
	global $db;
	
	foreach ($scenarios as $interval)
	{
		$query = "select tprice from quotes where ticker = \"" . $record["ticker"] . "\" order by date desc limit 0, " . number_format($interval-1);
		$rs = $db->Execute($query);
		$storage = array();

		while (!$rs->EOF)
		{
			$storage[] = $rs->fields[0];
			$rs->MoveNext();
		}
		$storage[] = $record["tPrice"];
		$dataLabel = "mavg$interval";
		$record[$dataLabel] = round(Numerical::mean($storage));
 		$dataLabel = "stdev$interval";
		$record[$dataLabel] = round(Numerical::standardDeviation($storage));
	}
}

function doStoreRow($record, $query)
{
	global $db;
	
	$rs = $db->Execute($query);
	$rowsFound = $rs->RecordCount();

	if ($rowsFound != 1)
		$insertSQL = $db->GetInsertSQL($rs, $record);
	else
		$insertSQL = $db->GetUpdateSQL($rs, $record);

	$db->Execute($insertSQL);
}


function doDownloadYahooData($symbol, $fDate, $lDate)
{
	global $db;
	
	$num = 0;
	$record = array();
	
	$from = explode("-", $fDate);
	$to = explode("-", $lDate);
	
// http://ichart.yahoo.com/table.csv?s=ADPT&d=3&e=14&f=2006&g=d&a=0&b=1&c=2000&ignore=.csv	
	
	$url =	"http://ichart.yahoo.com/table.csv?s=" . $symbol . "&d=" . number_format($to[1]-1) . "&e=" . $to[2] . "&f=" . $to[0] . "&g=d&a=" . number_format($from[1]-1) . "&b=" . $from[2] . "&c=" . $from[0] . "&ignore=.csv";
	$yahoo = @ file($url);
	
	if ($yahoo)
	{
		$num = count($yahoo);
//		echo "number of records downloaded for $symbol: $num<br />";
		$record["ticker"] = $symbol;
// skip first line with column names
		
		for ($j = 1; $j < $num; $j++)
		{
			$array = explode(',', $yahoo[$j]);
			$record["date"] = date("Y-m-d", strtotime($array[0]));
			$record["ticker"] = $symbol;
			$record["open"] = round($array[1]*100);
			$record["high"] = round($array[2]*100);
			$record["low"] = round($array[3]*100);
			$record["close"] = round($array[4]*100);
			$record["adjclose"] = round($array[6]*100); 
			$record["volume"] = $array[5];
			$adjustingRatio = $record["adjclose"]/$record["close"];
			$record["tPrice"] = round((($record["open"]+$record["low"]+$record["high"]+$record["close"])/4)*($adjustingRatio));
			$query = "select * from quotes where ticker = \"$symbol\" and date = \"" . $record["date"] . "\"";
//			echo "$query<br />";
			$rs = $db->Execute($query);
			$insertSQL = $db->GetInsertSQL($rs, $record);
			$db->Execute($insertSQL);
		}
		return $num;
	}
	else
	{
		echo "No data for $symbol <br />";
	}
}

function doGetYahooData($symbol, $fDate, $lDate)
{
	global $db;

	if (($numNewRows = doDownloadYahooData($symbol, $fDate, $lDate)) == 0)
		return FALSE;

	$scenarios = array(15, 20, 32, 50);
	$maxScenario = 50;
//	
//	$query = "select ticker, date, tprice from quotes where ticker = \"$symbol\" order by date desc limit 0, " . number_format($maxScenario + $numNewRows, 0, '.', '');
//	$records = $db->GetAll($query);
//	echo "$query<br />";
//	var_dump($records);
//	echo "<br />";

	$query = "select date from quotes where ticker = \"$symbol\" order by date desc limit 0, " . number_format($maxScenario + $numNewRows, 0, '.', '');
	$recDates = $db->GetCol($query);

//		$query .= ", sum(if(date = \"$column[0]\", tprice, NULL)) as \"$column[0]\"\n";
//		
//	$query .= "from quotes where ticker = \"$symbol\" group by ticker";
//
	$query = "select tprice from quotes where ticker = \"$symbol\" order by date desc limit 0, " . number_format($maxScenario + $numNewRows, 0, '.', '');
	$recTprices = $db->GetCol($query);
	
	$record = array();

	for ($i=1;$i<$numNewRows;$i++)
	{
		foreach ($scenarios as $interval)
		{
			$storage = array_slice($recTprices, $i, $interval);
			
			$dataLabel = "mavg$interval";
			$record[$dataLabel] = round(Numerical::mean($storage));
 			$dataLabel = "stdev$interval";
			$record[$dataLabel] = round(Numerical::standardDeviation($storage));
		}

		$query = "update quotes set " .
				 "mavg15=" . strval($record["mavg15"]) . ", " .
				 "stdev15=" . strval($record["stdev15"]) . ", " .
				 "mavg20=" . strval($record["mavg20"]) . ", " .
				 "stdev20=" . strval($record["stdev20"]) . ", " .
				 "mavg32=" . strval($record["mavg32"]) . ", " .
				 "stdev32=" . strval($record["stdev32"]) . ", " .
				 "mavg50=" . strval($record["mavg50"]) . ", " .
				 "stdev50=" . strval($record["stdev50"]) . " where ticker=\"$symbol\" and date=\"" . $recDates[$i-1] . "\"";

		$OKUpdate = $db->Execute($query);
	}
	return $numNewRows;
}

?>