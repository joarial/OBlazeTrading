<?php

/*  Driving parameters of the indicators 
	bbMA = moving average for the bollinger bands
	bbStdev = standard deviation used for the bands under and above the MA
	sMA = shorter term moving average used to confirm a signal triggered by
	      penetration of the bands
*/
	require_once "../include/globalVariables.inc.php";
	require_once "../include/class.Numerical.php";

function DoMovingAverage($datarow, $interval)
{
	$maRow = array();
	$i = 0;
	
	foreach ($datarow as $row)
	{
		$storage[$i % $interval] = @ $row;
        $maRow[] = Numerical::mean($storage);
        $i++;
	}
	DoStoreDataArray("sMA", $maRow);
	return $maRow;
}

function DoMovingStDev($datarow, $interval)
{
	$stDevRow = array();
	$i = 0;
	
	foreach ($datarow as $row)
	{
		$storage[$i % $interval] = @ $row;
		$stDevRow[] = Numerical::standardDeviation($storage);
		$i++;
	}
	return $stDevRow;
}
	
// http://www.stockcharts.com/education/IndicatorAnalysis/indic_Bbands.html

function DoBollingerBand($datarow, $interval, $bb)
{
	$upBBRow = array();
	$lowBBRow = array();
	$midBBRow = array();
	$pctb = array();
	$BandWidth = array();
	$i = 0;
	
	foreach ($datarow as $row)
	{
		$storage[$i % $interval] = @ $row;
	    $ma = Numerical::mean($storage);
//    	$ma /= count($storage);
        $stdev = Numerical::standardDeviation($storage);
        $upBB = $ma + ($bb*$stdev);
        $lowBB = $ma - ($bb*$stdev);
        $BW = ($upBB - $lowBB)/$ma;

        $upBBRow[] = $upBB;
        $lowBBRow[] = $lowBB;
        $midBBRow[] = $ma;

		if ($BW > 0)
		{
	        $pctb[] = @ ($row - $lowBB)/($upBB - $lowBB);
    	    $BandWidth[] = $BW;
    	}
    	else
    	{
	        $pctb[] = "";
    	    $BandWidth[] = "";
		}    	
        $i++;
	}

	DoStoreDataArray("upBB", $upBBRow);
	DoStoreDataArray("lowBB", $lowBBRow);
	DoStoreDataArray("midBB", $midBBRow);
	DoStoreDataArray("%b", $pctb);
	DoStoreDataArray("BandWidth", $BandWidth);
}


/* input must be array(day) of:
   		volume
  		close
  		high
 		low
 		
 		http://www.prophet.net/analyze/popglossary.jsp?studyid=VA

 */
function DoVolumeAccumulation($datarow)
{
	$vac = array();

	foreach ($datarow as $row)
		$vac[] = $row[0] * ((($row[1]-$row[3])-($row[2]-$row[1]))/($row[2]-$row[3]));

	DoStoreDataArray("VAC", $vac);
	return $vac;
}

/* input must be array(day) of:
   		volume
  		close
  		high
 		low
 		
		http://stockcharts.com/education/IndicatorAnalysis/indic_AccumDistLine.html
 */

function DoADLine($datarow)
{
	$ADLine = array();
	
	foreach ($datarow as $row)
	{
		$volume = $row[0];
		$close = $row[1];
		$high = $row[2];
		$low = $row[3];
		$CLV = (($close-$low)-($high-$close))/($high-$low);
		$ADLine[] = $CLV*$volume;
	}
}

// http://www.stockcharts.com/education/IndicatorAnalysis/indic_RSI.html

function DoRSI($datarow, $n)
{
	$RSI = array();
	$firstRS = TRUE;
	$totLoss = array();
	$totGain = array();
	$prevGain = $prevLoss = 0;
	
	$numrows = count($datarow);
	
	for($i=1; $i<$numrows; $i++)
	{
		if ($datarow[$i]<$datarow[$i-1])
		{
			$totLoss[] = $datarow[$i-1] - $datarow[$i];
			$totGain[] = 0;
		}
		else
		{
			$totGain[] = $datarow[$i] - $datarow[$i-1];
			$totLoss[] = 0;
		}

		if ($i > $n)
		{
			$gain = array_sum($totGain);
			$loss = array_sum($totLoss);

			if ($firstRS)
				$RSI[] = 100-(100/(1+($gain/$n)/($loss/$n)));
			else
				$RSI[] = 100-(100/(1+((($prevGain*($n-1))+$gain)/$n)/((($prevLoss*($n-1))+$loss)/$n)));
			
			$firstRS = FALSE;
			$prevGain = $gain;
			$prevLoss = $loss;
			$dummy = array_shift($totGain);
			$dummy = array_shift($totLoss);
		}
		else
			$RSI[] = '';
	}
	DoStoreDataArray("RSI", $RSI);
	return $RSI;
}

// http://stockcharts.com/education/IndicatorAnalysis/indic-obv.htm

/* input must be array(day) of:
	volume
	close
*/

function DoOBV($volumeArray, $adjCloseArray)
{
	$OBV = array();
	$precOBV = $volumeArray[0];
	$prevClose = $adjCloseArray[0];
	$numrows = count($volumeArray);
	
	for ($i=1; $i<$numrows; $i++)
	{
		$adjClose = $adjCloseArray[$i];
		$volume = $volumeArray[$i];
	
		if ($adjClose < $prevClose)
			$precOBV = $precOBV - $volume;
		elseif ($adjClose > $prevClose)
			$precOBV = $precOBV + $volume;

		$OBV[] = $precOBV;
		$prevClose = $adjClose;
	}
	DoStoreDataArray("OBV", $OBV);
	return $OBV;
}

function DoMFI($volumeArray, $tPriceArray)
{
	$MFI = array();
	$numrows = count($volumeArray);
	
	for ($i=0; $i<14; $i++)
		$MFI[] = "";

	for (; $i<$numrows; $i++)
	{
		for ($j=13; $j>0;$j--)
		{
			if ($tPriceArray[$i-$j] > $tPriceArray[$i-$j-1])
				$denPos = $tPriceArray[$i-$j] * $volumeArray[$i-$j];
			else
				$denNeg = $tPriceArray[$i-$j] * $volumeArray[$i-$j];
		}
		$MFI[] = 100-(100/(1+($denPos/$denNeg)));
	}
	DoStoreDataArray("MFI", $MFI);
	return $MFI;
}

function DoNormaliseVolume($interval)
{
	global $volumeArray;
	
	$maRow = array();
	$i = 0;
	
	foreach ($volumeArray as $row)
	{
		$storage[$i % $interval] = @ $row;
        $ma = array_sum($storage);
        $ma /= count($storage);
        $maRow[] = ($row/$ma)*100;
        $i++;
	}
	return $maRow;
}

function DoParabolic($ticker, $fromDate)
{
	global $db;
	
//	global $highArray, $lowArray, $tPriceArray;
	
	$query = "SELECT 
			  high/100*(adjclose/close) as hi,
			  low/100*(adjclose/close) as lo,
			  tprice/100*(adjclose/close) as tprice
			  FROM quotes
			  WHERE ticker = \"$ticker\" and date >= \"$fromDate\" order by date asc";
	

	$dataRecords = $db->GetAll($query);
	$rowsFound = count($dataRecords);

	$highArray = array();
	$lowArray = array();
	$tPriceArray = array();

	foreach ($dataRecords as $record)
	{
		$highArray[] = $record["hi"];
		$lowArray[] = $record["lo"];
		$tPriceArray[] = $record["tprice"];
	}
	
	DoDmi($ticker, $fromDate);
	$diPlusArray = DoRetrieveArray("DI+");
	$diMinusArray = DoRetrieveArray("DI-");
	
	$sar = array_fill(0, $rowsFound, "");
	$position = "";
	
	for ($i=0; $i<$rowsFound; $i++)
	{
//		echo "$i/$numRows: from " . $lowArray[$i] . " to " . $highArray[$i] . " with DI- " . $diMinusArray[$i]. " and D+ " . $diPlusArray[$i] . "<br />";
	
// entry point will be as soon as we have a DI+ or DI- giving a long or short indication
		if ($diPlusArray[$i] <> "")
		{
	// identify whether we enter Long or short at the begining
			if ($position == "")
			{
				if ($diPlusArray[$i] >= $diMinusArray[$i]) // entering long
				{
//					$sar[$i] = min(array_slice($lowArray, 0, $i+1));
					$sar[$i] = min(array_slice($tPriceArray, 0, $i+1));
					$af = 0.02;
//					$epTrade = $highArray[$i];
					$epTrade = $tPriceArray[$i];
					$sar[$i+1] = $sar[$i] + ($af * ($epTrade - $sar[$i]));
					$position = "long";
					$inTrade = $i;
//					echo "(1st)LONG -> sar[i]: " . $sar[$i] . " - sar[i+1]: " . $sar[$i+1] . "<br />";
				}
				else // entering short
				{
//					$sar[$i] = max(array_slice($highArray, 0, $i+1));
					$sar[$i] = max(array_slice($tPriceArray, 0, $i+1));
					$af = 0.02;
//					$epTrade = $lowArray[$i];
					$epTrade = $tPriceArray[$i];
					$sar[$i+1] = $sar[$i] - ($af * ($sar[$i] - $epTrade));
					$position = "short";
					$inTrade = $i;
//					echo "(1st)SHORT -> sar[i]: " . $sar[$i] . " - sar[i+1]: " . $sar[$i+1] . "<br />";
		}
			}
			elseif ($position == "long")
			{
	// do we have a SAR?
//				if ($lowArray[$i] < $sar[$i])
				if ($tPriceArray[$i] < $sar[$i])
				{
//					$sar[$i] = max(array_slice($highArray, $inTrade, $i-$inTrade+1));
					$sar[$i] = max(array_slice($tPriceArray, $inTrade, $i-$inTrade+1));
					$af = 0.02;
//					$epTrade = $lowArray[$i];
					$epTrade = $tPriceArray[$i];
					$sar[$i+1] = $sar[$i] - ($af * ($sar[$i] - $epTrade));
//					echo "SAR LONG -> SHORT since $inTrade - sar[i]: " . $sar[$i] . " - sar[i+1]: " . $sar[$i+1] . "<br />";
					$sar[$i] = "";
					$position = "short";
					$inTrade = $i;
				}
	// if not stay in position
				else
				{
	// acceleration factor increases by 0.2 if new high, but never more than 0.2
//					if ($highArray[$i] > $sar[$i-1])
					if ($tPriceArray[$i] > $sar[$i-1])
					{
						$af = min($af + 0.02, 0.20);
//						$epTrade = $highArray[$i];
						$epTrade = $tPriceArray[$i];
					}
					$sar[$i+1] = $sar[$i] + ($af * ($epTrade - $sar[$i]));
	// cannot move the SAR in in yesterday's or today's low				
//					if (($sar[$i+1] > $lowArray[$i]) or ($sar[$i+1] > $lowArray[$i-1]))
//						$sar[$i+1] = min($lowArray[$i], $lowArray[$i-1]);
					if (($sar[$i+1] > $tPriceArray[$i]) or ($sar[$i+1] > $tPriceArray[$i-1]))
						$sar[$i+1] = min($tPriceArray[$i], $tPriceArray[$i-1]);
//					echo "LONG -> sar[i]: " . $sar[$i] . " - sar[i+1]: " . $sar[$i+1] . "<br />";
				}
			}
	// position == short
			else
			{
	// do we have a SAR?
//				if ($highArray[$i] > $sar[$i])
				if ($tPriceArray[$i] > $sar[$i])
				{
//					$sar[$i] = min(array_slice($lowArray, $inTrade, $i-$inTrade+1));
					$sar[$i] = min(array_slice($tPriceArray, $inTrade, $i-$inTrade+1));
					$af = 0.02;
//					$epTrade = $highArray[$i];
					$epTrade = $tPriceArray[$i];
					$sar[$i+1] = $sar[$i] + ($af * ($epTrade - $sar[$i]));
//					echo "SAR SHORT-> LONG since $inTrade -  sar[i]: " . $sar[$i] . " - sar[i+1]: " . $sar[$i+1] . "<br />";
					$sar[$i] = "";
					$position = "long";
					$inTrade = $i;
				}
	// stay in position
				else
				{
//					if ($lowArray[$i] < $sar[$i-1])
					if ($tPriceArray[$i] < $sar[$i-1])
					{
						$af = min($af + 0.02, 0.20);
//						$epTrade = $lowArray[$i];
						$epTrade = $tPriceArray[$i];
					}
				
					$sar[$i+1] = $sar[$i] - ($af * ($sar[$i] - $epTrade));
	// cannot move the SAR in in yesterday's or today's high				
//					if (($sar[$i+1] < $highArray[$i]) or ($sar[$i+1] < $highArray[$i-1]))
//						$sar[$i+1] = max($highArray[$i], $highArray[$i-1]);
					if (($sar[$i+1] < $tPriceArray[$i]) or ($sar[$i+1] < $tPriceArray[$i-1]))
						$sar[$i+1] = max($tPriceArray[$i], $tPriceArray[$i-1]);
//					echo "SHORT -> sar[i]: " . $sar[$i] . " - sar[i+1]: " . $sar[$i+1] . "<br />";
				}
			}
		}		
		else
			$sar[$i] = "";
	}
	
	DoStoreDataArray("SAR", $sar);
	return $sar;
}

function DoDmi($ticker, $fromDate)
{
	global $db;

	$query = "SELECT 
			  high/100*(adjclose/close) as hi,
			  low/100*(adjclose/close) as lo
			  FROM quotes
			  WHERE ticker = \"$ticker\" and date >= \"$fromDate\" order by date asc";

	$dataRecords = $db->GetAll($query);
	$rowsFound = count($dataRecords);

	$highArray = array();
	$lowArray = array();
	$diMinusArray = array();
	$diPlusArray = array();
	$adxArray = array();
	$adxrArray = array();
	$adxTransitArray = array();

	foreach ($dataRecords as $record)
	{
		$highArray[] = $record["hi"];
		$lowArray[] = $record["lo"];
	}
	
	$tr14 = 0.0;
	$dmPlus14 = 0.0;
	$dmMinus14 = 0.0;
	$adx = 0.0;

	for ($i=1; $i<$rowsFound; $i++)
	{
		$dmPlus = $highArray[$i] - $highArray[$i-1];
		$dmMinus = $lowArray[$i-1] - $lowArray[$i];
		$trueRange = $highArray[$i] - $lowArray[$i];
		
		if ($dmPlus > $dmMinus)
			$dmMinus = 0;
		elseif ($dmPlus < $dmMinus)
			$dmPlus = 0;
		else
			$dmPlus = $dmMinus = 0;

		if ($i < 14)
		{
			$tr14 += $trueRange;
			$dmPlus14 += $dmPlus;
			$dmMinus14 += $dmMinus;
			$diPlusArray[] = $diMinusArray[] = $adxArray[] = $adxrArray[] = "";
		}
		else
		{
			$tr14 = ($tr14 * 13/14) + $trueRange;
			$dmPlus14 = ($dmPlus14 * 13/14) + $dmPlus;
			$dmMinus14 = ($dmMinus14 * 13/14) + $dmMinus;
			$diPlusArray[] = $diPlus = @ ($dmPlus14/$tr14) * 100;
			$diMinusArray[] = $diMinus = @ ($dmMinus14/$tr14) * 100;
			$diDifference = abs($diPlus - $diMinus);
			$dx = @ (($diDifference / ($diPlus + $diMinus)) * 100);  // Directional Movement Index (DX) - +25 to see better
			
			if ($i < 29)
			{
				$adx += $dx /14;
				$adxrArray[] = $adxArray[] = "";
			}
			elseif ($i < 43)
			{
				$adx = (($adx * 13) + $dx) /14;
				$adxTransitArray[] = $adxArray[] = $adx;
				$adxrArray[] = "";
			}
			else
			{
				$adx = (($adx * 13) + $dx) /14;
				$adxr = array_shift($adxTransitArray);
				$adxTransitArray[] = $adxArray[] = $adx;
				$adxr = ($adx + $adxr) / 2;
				$adxrArray[] = $adxr;
			}
		}
	}
	DoStoreDataArray("DI+", $diPlusArray);
	DoStoreDataArray("DI-", $diMinusArray);
	DoStoreDataArray("ADX", $adxArray);
	DoStoreDataArray("ADXR", $adxrArray);
}
?>