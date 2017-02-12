<?php
	require_once "../include/db.inc.php";
	require_once "../include/globalVariables.inc.php";

function DoStoreSignal($ticker, $date, $tPrice, $adjClose, $signal, $comment="None", $trigger = 0)
{
	global $db;
	
	$ret = $db->Replace('signals',
			array('ticker'=>$ticker, 'date'=>$date,
			'typPrice'=>number_format($tPrice, 2),
			'adjClose'=>number_format($adjClose, 2),
			'signal'=>$signal,
			'comment'=>$comment,
			'trigg'=>number_format($trigger, 2)),
			array('ticker', 'date'),
			$autoquote = true);

//	echo "Replace $ticker at $tPrice for a $trigg because of $comment :$ret - Updating ". $db->ErrorMsg() . "<br/>";
}

function DoFindSignals($ticker, $position = 'short', $buyPrice = 0.0, $buyTrigger = FALSE, $sellTrigger = FALSE, $scenario = "current")
{
	global $db;
/*
	global $uBBArray, $lBBArray, $mBBArray, $sMAArray, $RSIArray,
		   $tPriceArray, $adjCloseArray, $datesArray,
		   $buyTrigger, $sellTrigger, $volumeArray,
		   $buyArray, $sellArray, $BBWhenSignalArray,
		   $highArray;
	global $diPlusPlotArray, $diMinusPlotArray, $adxPlotArray, $adxrPlotArray;
*/		   
	$query = "select * from issuers where ticker = \"$ticker\"";
	$issuer = $db->GetRow($query);
	
//	$query = "SELECT max(last_in_signal) from issuers where ticker = \"$ticker\"";
//	$lastSignal = $db->GetOne($query);
	
	$position = $issuer["current_position"];
	$buyPrice = $issuer["buy_price"];
	$buyTrigger = $issuer["buy_trigger"];
	$sellTrigger = $issuer["sell_trigger"];
	$fromDate = $issuer["last_in_signal"];
	
	$db->SetFetchMode(ADODB_FETCH_ASSOC);

    $query = "SELECT ticker, date,
			  adjclose/100 as aclose,
			  high/100*(adjclose/close) as hi,
			  low/100*(adjclose/close) as lo,
			  open/100*(adjclose/close) as op,
			  volume,
			  tprice/100 as price,
			  mavg32/100 as ma32,
			  mavg20/100 as ma20,
			  (mavg32+(2*stdev32))/100 as bbup32,
			  (mavg32-(2*stdev32))/100 as bblow32
			  FROM quotes
			  WHERE ticker = \"$ticker\" and date > \"$fromDate\" order by date asc";

	$dataRecords = $db->Execute($query);
 	$rowsFound = $dataRecords->RecordCount();

//	echo "in findSignals.php: found $rowsFound tupples for quotes > $fromDate<br/>";
		
/*
 * Need data vector:
 * 	dates
 *	adjusted close
 * 	tprice
 * 	upper BB
 * 	lower BB
 * 	medium MA (also middle of BB)
 * 	slow MA
 * 	buy signal
 * 	sell/out signal
 * 	trigger signal
 *	high
 *	low
 *	open
 * 	volume
 * 
 */
	$datesArray = array();
	$tPriceArray = array();
	$adjCloseArray = array();
	$highArray = array();
	$lowArray = array();
	$openArray = array();
	$volumeArray = array();
	$sMAArray = array();
	$uBBArray = array();
	$lBBArray = array();
	$mBBArray = array();
	$buyArray = array();
	$sellArray = array();
	$triggerArray = array();
	$dataRow = array();
	$BBWhenSignalArray = array();
	$recordArray = array();
	
	foreach ($dataRecords as $record)
	{
		$datesArray[] = $record["date"];
		$tPriceArray[] = $record["price"]; // $tPrice
		$adjCloseArray[] = $record["aclose"]; // $adjClose
		$highArray[] = $record["hi"];
		$lowArray[] = $record["lo"];
		$volumeArray[] = $record["volume"];
		$sMAArray[] = $record["ma20"];
		$mBBArray[] = $record["ma32"]; // $bbMid32
		$uBBArray[] = $record["bbup32"]; // $bbUp32
		$lBBArray[] = $record["bblow32"]; // $bbBottom32

		$recordArray[] = $record;
	}
	
//	While computing parabolic, dmi is required and calculated as well
	$sarArray = DoParabolic($ticker, $fromDate);
	
	$diMinusArray = DoRetrieveArray("DI-");
	$diPlusArray = DoRetrieveArray("DI+");
	$adxArray = DoRetrieveArray("ADX");
	$adxrArray = DoRetrieveArray("ADXR");
	$excessDailyRateArray = array();
	
	$sarHigher = 0;
	$sarLower = 0;

	for ($i=1; $i<$rowsFound; $i++)
	{
//		echo "for with $i until $rowsFound - Date is " . $datesArray[$i] . "<br/>";
		
		if (($sarArray[$i] >= $tPriceArray[$i]) && ($sarArray[$i-1] >= $tPriceArray[$i]))
			$sarHigher++;
		elseif (($sarArray[$i] >= $tPriceArray[$i]) && ($sarArray[$i-1] < $tPriceArray[$i]))
		{
			$sarHigher = 1;
			$sarLower = 0;
		}
		elseif (($sarArray[$i] < $tPriceArray[$i]) && ($sarArray[$i-1] < $tPriceArray[$i]))
			$sarLower++;
		elseif (($sarArray[$i] < $tPriceArray[$i]) && ($sarArray[$i-1] >= $tPriceArray[$i]))
		{
			$sarLower = 1;
			$sarHigher = 0;
		}
		
// Populating vectors to calculate Sharpe Ratio

//		$dailyRate = ($tPriceArray[$i] - $tPriceArray[$i-1])/$tPriceArray[$i-1];
//		$excessDailyRateArray[] = ($dailyRate - $averageRiskFree)/$numberTradingDays;

		if ($position == 'short')
		{
			if (!$buyTrigger)
			{
			// trigger when close crosses below lower BB and lower BB is decreasing
			
				if (($tPriceArray[$i] < $lBBArray[$i]) &&
					($tPriceArray[$i-1] > $lBBArray[$i-1]))
				{
					$BBWhenSignalArray[$i] = $lBBArray[$i];
					$why =	"Price(" . number_format($tPriceArray[$i], 2) . ") < lBB(" . number_format($lBBArray[$i], 2) .
							") & Price_d-1(" . number_format($tPriceArray[$i-1], 2) . ") > lBB_d-1(" . number_format($lBBArray[$i-1], 2) . ")";
					DoStoreSignal($ticker, $datesArray[$i], $tPriceArray[$i], $adjCloseArray[$i], "TriBuy", $why, $lBBArray[$i]);
					$buyTrigger = TRUE;
				}
				continue;
			}
			elseif ((	// if buy signals was triggered previously
			
					($tPriceArray[$i] > $tPriceArray[$i-1]) && // if 2 succesives higher typical price
					($tPriceArray[$i-1] > $tPriceArray[$i-2]) &&
					($tPriceArray[$i] >= $sMAArray[$i]) && // and typical price above short moving average
					($sMAArray[$i] < $mBBArray[$i]) &&	// if  short moving average still lower than medium moving average
					($tPriceArray[$i] <= $mBBArray[$i]))	// if typical price is lower than medium moving average
					||
					(($sMAArray[$i-1] < $mBBArray[$i-1]) &&
					($sMAArray[$i] > $mBBArray[$i])))
			
				if ($diPlusArray[$i] < $diMinusArray[$i]) // does not trigger a buy if DI+ lower than DI-
					continue;
				else
				{
					$buyArray[$i] = $tPriceArray[$i];
					DoStoreSignal($ticker, $datesArray[$i], $tPriceArray[$i], $adjCloseArray[$i], "buy");
					$position = 'long';
					$buyTrigger = FALSE;
					$buyPrice = $tPriceArray[$i];
				}
			}
			continue;	// not worth going further
		}

		if ($position == 'long')		// purchased some
		{
			// if wrong entry better get out after limited damage: 10% loss  --> OUT
		
			if (($tPriceArray[$i] < ($buyPrice * 0.9)) && ($tPriceArray[$i] > 0))
			{
				$why = "Price(" . number_format($tPriceArray[$i], 2) . ") < 90% of buy_price(" . number_format($buyPrice, 2) . ")";
				$sellArray[$i] = $tPriceArray[$i];
				$sellTrigger = FALSE;
				$position = 'short';
				$buyPrice = 0.0;
				DoStoreSignal($ticker, $datesArray[$i], $tPriceArray[$i], $adjCloseArray[$i], "out", $why);
			}
			elseif (	// even without a trigger if the close passes below the short moving average better sell if SAR is not supporting trend
					(!$sellTrigger) &
					($tPriceArray[$i-1] >= $sMAArray[$i-1]) &&
					($tPriceArray[$i] < $sMAArray[$i]) &&
					($sarArray[$i] >= $tPriceArray[$i]))
			{
					$sellArray[$i] = $tPriceArray[$i];
					$sellTrigger = FALSE;
					$position = 'short';
					$buyPrice = 0.0;
					DoStoreSignal($ticker, $datesArray[$i], $tPriceArray[$i], $adjCloseArray[$i], "sell");
			}
			elseif (!$sellTrigger)
			{
			// trigger when close crosses above upper BB and upper BB is on the increase

				if (($tPriceArray[$i] > $uBBArray[$i]) &&
					($uBBArray[$i] > $uBBArray[$i-1]))
					{
						$BBWhenSignalArray[$i] = $uBBArray[$i];
						DoStoreSignal($ticker, $datesArray[$i], $tPriceArray[$i], $adjCloseArray[$i], "TrSell", "", $uBBArray[$i]);
						$sellTrigger = TRUE;
					}
			}
			elseif (	// confirm sell when a declining tprice is passing below short moving average which is above the medium one

					($tPriceArray[$i-1] >= $mBBArray[$i-1]) &&
					($tPriceArray[$i] < $mBBArray[$i]) &&
					($sMAArray[$i] > $mBBArray[$i]))
			{
						// check whether DI+ < D- and keep position if Positive > Negative and/or real trend
				if (
					(($diPlusArray[$i] <= $diMinusArray[$i]) ||
					 ($adxrArray[$i] <= $adxArray[$i])))
				{
					$sellArray[$i] = $tPriceArray[$i];
					$sellTrigger = FALSE;
					$position = 'short';
					$buyPrice = 0.0;
					DoStoreSignal($ticker, $datesArray[$i], $tPriceArray[$i], $adjCloseArray[$i], "sell");
				}
/*				elseif (	// test whether there is not a new buy trigger on the positin we just shortened
					($tPriceArray[$i] < $lBBArray[$i]) &
					($tPriceArray[$i-1] > $lBBArray[$i-1]))
				{
					$BBWhenSignalArray[$i] = $lBBArray[$i];
					DoStoreSignal($symbol, $datesArray[$i], $tPriceArray[$i], $adjCloseArray[$i], "TriBuy", $why, $lBBArray[$i]);
					$buyTrigger = TRUE;
					DoStoreSignal($symbol, $datesArray[$i], $tPriceArray[$i], $adjCloseArray[$i], "out", "Buy trigger reached. Could also be possible to reduce average buy price");
				} */
			}
		}
		
//		$sharpeRatio = (sqrt(252) * mean($excessDailyRateArray)) / standardDeviation($excessDailyRateArray);
	
//		$issuer = array();
//		$issuer["last_scenario"] = "current";
//		$issuer["last_in_signal"] = max($datesArray);
//		$issuer["current_position"] = $position;
//		$issuer["buy_price"] = $buyPrice;
//		$issuer["buy_trigger"] = $buyTrigger;
//		$issuer["sell_trigger"] = $sellTrigger;
//		$query = "select ticker, last_scenario, last_in_signal, current_position, buy_price, buy_trigger, sell_trigger from issuers where ticker = \"$ticker\"";
//		$rs = $db->Execute($query);
//		$insertSQL = $db->GetUpdateSQL($rs, $issuer);
//		$db->Execute($insertSQL);
//		$update_id = "ticker=\"$ticker\"";
//		$updateSQL = $db->AutoExecute('issuers', $issuer, 'UPDATE', $update_id);

		$ret = $db->Replace('issuers',
			array('ticker'=>$ticker, 'last_in_signal'=>max($datesArray),
			'current_position'=>$position,
			'buy_price'=>$buyPrice,
			'buy_trigger'=>$buyTrigger,
			'sell_trigger'=>$sellTrigger),
			'ticker',
			$autoquote = true);
			
		if ($ret == 0) {
    		echo "Error updating $ticker: ". $db->ErrorMsg() . "<br/>";
		}
}
?>