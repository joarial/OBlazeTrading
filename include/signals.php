<?php

	require_once "../include/db.inc.php";
	require_once "../include/globalVariables.inc.php";

function CreateSignalOtherString($signal)
{
	global $diPlusPlotArray, $diMinusPlotArray, $adxPlotArray, $adxrPlotArray, $uBBArray;
	global $tPriceArray, $lBBArray, $uBBArray, $datesArray, $adjCloseArray;

	if ($signal == "TriBuy")
		$other = "lBBValue: " . strval(number_format($lBBArray[$i], 2));
	elseif ($signal == "TrSell")
		$other = "uBBValue: " . strval(number_format($uBBArray[$i], 2));
	elseif (($signal == "buy") || ($signal == "sell"))
		$other =	"DI+: " . strval(number_format($diPlusPlotArray[$i], 2)) .
					", DI-: " . strval(number_format($diMinusPlotArray[$i], 2)) .
					", ADX: " . strval(number_format($adxPlotArray[$i], 2)) .
					", ADXR: " . strval(number_format($adxrPlotArray[$i], 2));
	else
		$other = "N/A";

	return $other;
}

function IsBuyTrigger($symbol, $i)
{
	global $tPriceArray, $lBBArray, $BBWhenSignalArray, $datesArray, $adjCloseArray;

// trigger when close > lBB and decreasing lBB

		$why =	"Price(" . number_format($tPriceArray[$i], 2) . ") < lBB(" . number_format($lBBArray[$i], 2) .
				") & Price_d-1(" . number_format($tPriceArray[$i-1], 2) . ") > lBB_d-1(" . number_format($lBBArray[$i-1], 2) . ")";

	if (($tPriceArray[$i] < $lBBArray[$i]) && ($tPriceArray[$i-1] > $lBBArray[$i-1]))
	{
		$BBWhenSignalArray[$i] = $lBBArray[$i];
		DoStoreSignal($symbol, $datesArray[$i], $tPriceArray[$i], $adjCloseArray[$i], "TriBuy", $why, $lBBArray[$i]);
		return TRUE;
	}
	else
		return FALSE;
}

function IsBuyConfirm($symbol, $i)
{
	global $tPriceArray, $sMAArray, $mBBArray, $buyArray, $datesArray, $adjCloseArray;
	global $diPlusPlotArray, $diMinusPlotArray, $adxPlotArray, $adxrPlotArray, $uBBArray;

$why = 	"Price(" . number_format($tPriceArray[$i], 2) . ") > Price_d-1(" . number_format($tPriceArray[$i-1], 2) .
		") & Price(" . number_format($tPriceArray[$i], 2) . ") > short_avg(" . number_format($sMAArray[$i], 2) .
		") & short_avg(" . number_format($sMAArray[$i], 2) . ") < med_avg(" . number_format($mBBArray[$i], 2) .
		") & Price(" . number_format($tPriceArray[$i], 2) . ") < uBB(" . number_format($uBBArray[$i], 2) . ")";

$why .= " & diPlus(" . number_format($diPlusPlotArray[$i], 2) . ") >= diMinus(" . number_format($diMinusPlotArray[$i], 2) . ")";

// confirm buy when increasing close and close > sMA and sMA < mBB and close < uBB
	if (($tPriceArray[$i] > $tPriceArray[$i-1]) && ($tPriceArray[$i] > $sMAArray[$i]) && ($sMAArray[$i] < $mBBArray[$i]) && ($tPriceArray[$i] < $uBBArray[$i]))
	{
	// check whether DI+ not < D-
		if ($diPlusPlotArray[$i] < $diMinusPlotArray[$i])
			return 'short';
		else
		{
			$buyArray[$i] = $tPriceArray[$i];
			DoStoreSignal($symbol, $datesArray[$i], $tPriceArray[$i], $adjCloseArray[$i], "buy", $why);
			return 'long';
		}
	}
	else
		return 'short';
}

//number_format($maxScenario + $num, 0, '.', '')

function IsSellTrigger($symbol, $i)
{
	global $tPriceArray, $uBBArray, $lBBArray, $BBWhenSignalArray, $datesArray, $adjCloseArray;

$why = "Price(" . number_format($tPriceArray[$i], 2) . ") > uBB(" . number_format($uBBArray[$i], 2) . ") & uBB(" . number_format($uBBArray[$i], 2) . ") > uBB_d-1(" . number_format($uBBArray[$i-1], 2) . ")";


// trigger when close > uBB and increasing uBB
	if (($tPriceArray[$i] > $uBBArray[$i]) && ($uBBArray[$i] > $uBBArray[$i-1]))
	{
		$BBWhenSignalArray[$i] = $uBBArray[$i];
		DoStoreSignal($symbol, $datesArray[$i], $tPriceArray[$i], $adjCloseArray[$i], "TrSell", $why, $uBBArray[$i]);
		return TRUE;
	}
	else
		return FALSE;
}

function IsSellConfirm($symbol, $i)
{
	global $tPriceArray, $sMAArray, $sellArray, $datesArray, $adjCloseArray, $mBBArray, $lBBArray;
	global $diPlusPlotArray, $diMinusPlotArray, $adxPlotArray, $adxrPlotArray;

// confirm sell when a declining tprice is passing below mid BB

	$why = "Price(" . number_format($tPriceArray[$i], 2) . ") < med_avg(" . number_format($mBBArray[$i], 2) . ")";

	if ($tPriceArray[$i] < $mBBArray[$i])
	{
// check whether DI+ < D- and keep position if Positive > Negative and/or real trend
		if (($diPlusPlotArray[$i] > $diMinusPlotArray[$i]) || ($adxrPlotArray[$i] > $adxPlotArray[$i]))
			return 'long';
// keep position if is there is no trend
//		elseif ($adxrArray[$i] > $adxArray[$i])
//			return TRUE;
		else
		{
			$why .= " & diPlus(" . number_format($diPlusPlotArray[$i], 2) . ") <= diMinus(" . number_format($diMinusPlotArray[$i], 2) .
					") or adxr(" . number_format($adxrPlotArray[$i], 2) . ") <= adx(" . number_format($adxPlotArray[$i], 2) . ")";

			$sellArray[$i] = $tPriceArray[$i];
			DoStoreSignal($symbol, $datesArray[$i], $tPriceArray[$i], $adjCloseArray[$i], "sell", $why);
			return 'short';
		}
	}
	elseif ($tPriceArray[$i] < $lBBArray[$i])
	{
		$why .= " & Price(" . number_format($tPriceArray[$i], 2) . ") < lBB(" . number_format($lBBArray[$i], 2) . ")";

		$sellArray[$i] = $tPriceArray[$i];
		DoStoreSignal($symbol, $datesArray[$i], $tPriceArray[$i], $adjCloseArray[$i], "sell", $why);
		return 'short';
	}
	else
		return 'long';
}

function initiateSignalsDataArrays($ticker, $fromDate)
{
	global $db;

	global $uBBArray, $lBBArray, $mBBArray, $sMAArray,
		   $tPriceArray, $adjCloseArray, $datesArray, $highArray, $lowArray, $dataRow,
		   $volumeArray;

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
			  WHERE ticker = \"$ticker\" order by date asc";

	$records = $db->GetAll($query);
	$rowsFound = count($records);

	foreach ($records as $record)
	{
		$datesArray[] = $record["date"];
		$adjCloseArray[] = $record["aclose"]; // $adjClose
		$highArray[] = $record["hi"];
		$lowArray[] = $record["lo"];
		$tPriceArray[] = $record["price"]; // $tPrice
		$volumeArray[] = $record["volume"];
		$sMAArray[] = $record["ma20"];

		$mBBArray[] = $record["ma32"]; // $bbMid32
		$uBBArray[] = $record["bbup32"]; // $bbUp32
		$lBBArray[] = $record["bblow32"]; // $bbBottom32

		// candlesticks
		if ($rowsFound <= DETAILCHART)
		{
			$dataRow[] = $record["op"]; // $open
			$dataRow[] = $record["aclose"]; // $adjClose
			$dataRow[] = $record["lo"]; // $low
			$dataRow[] = $record["hi"]; // $high
		}
	}
	return $rowsFound;
}

?>