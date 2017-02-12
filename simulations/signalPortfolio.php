<?php
	ini_set('max_execution_time', 3600);
	ini_set('memory_limit', 116777216);

	require_once "../include/db.inc.php";
	require_once "../include/globalVariables.inc.php";
	require_once "../include/indicators.php";
	require_once "../include/dataArraysSQL.php";
	require_once "../include/signals.php";
	require_once "../signals/findSignals.php";
	require_once "../include/class.progressbar.php";
	require_once "../include/class.HtmlTagMaker.php";
	require_once "../include/class.TableMaker.php";
	require_once "../include/yScaleCallback.inc.php";

	$signalTable = new TableMaker();

// Define elements with class attributes
	$signalTable -> defineHeader('trHead', array('class' => 'trHead'));
	$signalTable -> defineRow ('trGain', array('class' => 'trGain'));
	$signalTable -> defineRow ('trLoss', array('class' => 'trLoss'));
	
	$signalTable -> defineDataCell ('tdFigure', array('class' => 'tdFigure'));
	$signalTable -> defineDataCell ('tdName', array('class' => 'tdName'));
	$signalTable -> defineDataCell ('tdDate', array('class' => 'tdDate'));
	$signalTable -> defineDataCell ('tdCenter', array('class' => 'tdCenter'));

// Add a caption
	$signalTable -> addCaption ("Portfolio performance since 01/01/2005", array('class' => 'captionStyle'));

// Add Header
	$signalTable -> addHeader('ticker', 'trHead');
	$signalTable -> addHeader('issuer', 'trHead');
	$signalTable -> addHeader('Buys', 'trHead');
	$signalTable -> addHeader('Sales', 'trHead');
	$signalTable -> addHeader('Gain/loss', 'trHead');
	$signalTable -> addHeader('Return', 'trHead');
	$signalTable -> addHeader('# buys', 'trHead');
	$signalTable -> addHeader('# sells', 'trHead');
	$signalTable -> addHeader('# outs', 'trHead');

// Place a horizontal ruler above the first row
	$signalTable -> addRow();
	$signalTable -> addData('<hr>', 'ruler', array('colspan' => 13));
	
	$sharesTraded = 0;
	$portfolio = 0;
	$totInvest = 0;
	$totReturn = 0;
	$totTransactions = 0;
/**
 * Read records by date
 * Purchase on the first and subsequent buys until all the cash available has vanished
 * 
 * 						cashInHand
 * 
 * Requires arrays:		allTransactionsArray --> key is ticker ==> numBuy, numSell, numOut, totalBuy, totalSell 
 * 						sharesPortfolio --> key is ticker, date_purchased, quantity, price_payed
 */

	define("INVESTMENT", 100000);

	$cashInHand = INVESTMENT;
	$maxTransaction = 1500;
	$transactionsArray = array();
	$sharesPortfolio = array();
	$allTransactionsArray = array();

	$query = "select ticker, issuer from issuers order by ticker asc";
	$results = $db->GetAll($query);
	$num_tasks = $rowsFound = count($results);
	
	$prb = new ProgressBar(400,70);
	$prb->left = 320;
	$prb->top = 120;
	$prb->border = 2;
	$prb->color = '#ff6633';
	$prb->bgr_color = 'yellow';
	$prb->setBarDirection('right');
	$prb->addLabel('text','txt1');
	$prb->addLabel('percent','pct1');
	$prb->setLabelPosition('pct1',320,105+23,400,70,'center');
	$prb->setLabelFont('pct1',48);
	$prb->min = 1;
	$prb->max = $num_tasks;
	$cur_task = 0;
	$prb->show();
/**
 * Ensure we have an updated signals table
 */
	foreach ($results as $result)
	{
		$ticker = $result["ticker"];
		$issuer = $result["issuer"];
		
		$sq1 = "select max(date) from quotes where ticker = \"$ticker\"";
		$r1 = $db->GetRow($sq1);
		$maxQuote = $r1[0];
		
		$sq1 = "SELECT max(last_in_signal) from issuers where ticker = \"$ticker\"";
		$r1 = $db->GetRow($sq1);
		$maxScenario = $r1[0];
		
		if ($maxQuote > $maxScenario)	// did not yet run simulation with most recent data
		{
			initiateSignalsDataArrays($ticker, "2005-01-01");
			DoDmi($ticker);
			DoParabolic($ticker, $fromDate);
			DoFindSignals($ticker);		// in ../include/signals.php

			array_splice($uBBArray, 0);
			array_splice($lBBArray, 0);
			array_splice($mBBArray, 0);
			array_splice($sMAArray, 0);
			array_splice($RSIArray, 0);
			array_splice($tPriceArray, 0);
			array_splice($adjCloseArray, 0);
			array_splice($datesArray, 0);
			array_splice($buyArray, 0);
			array_splice($sellArray, 0);
			array_splice($BBWhenSignalArray, 0);
			array_splice($volumeArray, 0);
			array_splice($highArray, 0);
			array_splice($lowArray, 0);
			array_splice($dataRow, 0);
			array_splice($pctbArray, 0);
			array_splice($bandWidthArray, 0);
			array_splice($diPlusPlotArray, 0);
			array_splice($diMinusPlotArray, 0);
			array_splice($adxPlotArray, 0);
			array_splice($adxrPlotArray, 0);
		}
		$prb->unhide();
		$label = "Processed ... $issuer";
		$prb->setLabelValue('txt1', $label);
		$prb->moveStep($cur_task++);
	}
	$prb->setLabelValue('txt1', "Update Signal Completed");
	$prb->moveStep($cur_task);
	$prb->hide();

	$query = "select s.ticker, i.issuer, s.date, s.signal, s.adjclose, s.typPrice, s.trigg, s.comment from signals as s, issuers as i
			  where s.ticker = i.ticker order by date asc";
	$records = $db->GetAll($query);
	$num_tasks = $rowsFound = count($records);
	
	$prb = new ProgressBar(400,70);
	$prb->left = 320;
	$prb->top = 120;
	$prb->border = 2;
	$prb->color = '#ff6633';
	$prb->bgr_color = 'yellow';
	$prb->setBarDirection('right');
	$prb->addLabel('text','txt1');
	$prb->addLabel('percent','pct1');
	$prb->setLabelPosition('pct1',320,105+23,400,70,'center');
	$prb->setLabelFont('pct1',48);
	$prb->min = 1;
	$prb->max = $num_tasks;
	$cur_task = 0;
	$prb->show();
		
	foreach ($records as $record)
	{
		$ticker = $record["ticker"];
		$issuer = $record["issuer"] . " ($ticker)";
		$signalDate = $record["date"];
/**
 * Requires arrays:		allTransactionsArray --> key is ticker ==> numBuy, numSell, numOut, totalBuy, totalSell 
 * 						sharesPortfolio --> key is ticker, date_purchased, quantity, price_payed
 */
 		if ($record["signal"] == "buy")
		{
			$query = "select ticker, date, open/100*(adjclose/close) as op, tprice/100 as tp from quotes where ticker=\"$ticker\" and date > \"$signalDate\" order by date asc limit 1" ;
			$rs = $db->GetRow($query);
			if ($rs)
			{
				$open = $rs["op"];
				$shares = floor(($maxTransaction - FEE) / $open);
				$valueTransaction = ($shares * $open) + FEE;
				
				
				if ($cashInHand > $valueTransaction)
				{
//					echo "Buy $ticker on $signalDate: $shares shares @ $open <br/>";
					$sharesPortfolio[$ticker]["date"]= $signalDate;
					$sharesPortfolio[$ticker]["quantity"] = $shares;
					$sharesPortfolio[$ticker]["price"] = $open;
					
					if (array_key_exists($ticker, $allTransactionsArray))
					{
						$allTransactionsArray[$ticker]["numBuy"] = 	$allTransactionsArray[$ticker]["numBuy"] + 1;
						$allTransactionsArray[$ticker]["totalBuy"] = $allTransactionsArray[$ticker]["totalBuy"] + $valueTransaction;
					}
					else
					{
						$allTransactionsArray[$ticker]["numBuy"] = 1;
						$allTransactionsArray[$ticker]["numSell"] =  0;
						$allTransactionsArray[$ticker]["numBuy"] = 0;
						$allTransactionsArray[$ticker]["totalBuy"] = $valueTransaction;
						$allTransactionsArray[$ticker]["totalSell"] = 0;
					}
					$cashInHand -= $valueTransaction;
				}
			}
		}
		elseif ($record["signal"] == "sell")
		{
			if (array_key_exists($ticker, $sharesPortfolio))
			{
				$query = "select ticker, date, open/100*(adjclose/close) as op from quotes where ticker=\"$ticker\" and date > \"$signalDate\" order by date asc limit 1" ;
				$rs = $db->GetRow($query);
				if ($rs)
				{
					$open = $rs["op"];
					$shares = $sharesPortfolio[$ticker]["quantity"];
					$price = $sharesPortfolio[$ticker]["price"];
//					echo "Sell $ticker on $signalDate: $shares shares @ $open vs. $price<br/>";
					$valueTransaction = $sharesPortfolio[$ticker]["quantity"] * $open - FEE;
					$allTransactionsArray[$ticker]["numSell"] = 	$allTransactionsArray[$ticker]["numSell"] + 1;
					$allTransactionsArray[$ticker]["totalSell"] = $allTransactionsArray[$ticker]["totalSell"] + $valueTransaction;
					$cashInHand += $valueTransaction;
					unset($sharesPortfolio[$ticker]);
				}
			}
			elseif ($record["signal"] == "out")
			{
				if (array_key_exists($ticker, $sharesPortfolio))
				{
					$query = "select ticker, date, open/100*(adjclose/close) as op from quotes where ticker=\"$ticker\" and date > \"$signalDate\" order by date asc limit 1" ;
					$rs = $db->GetRow($query);
					if ($rs)
					{
						$open = $rs["op"];
						$shares = $sharesPortfolio[$ticker]["quantity"];
						$price = $sharesPortfolio[$ticker]["price"];
//						echo "Sell $ticker on $signalDate: $shares shares @ $open vs. $price<br/>";
						$valueTransaction = $sharesPortfolio[$ticker]["quantity"] * $open - FEE;
						$allTransactionsArray[$ticker]["numOut"] = 	$allTransactionsArray[$ticker]["numOut"] + 1;
						$allTransactionsArray[$ticker]["totalSell"] = $allTransactionsArray[$ticker]["totalSell"] + $valueTransaction;
						$cashInHand += $valueTransaction;
						unset($sharesPortfolio[$ticker]);
					}
				}
			}
		}
		$prb->unhide();
		$prb->setLabelValue('txt1', "Establishing transactions");
		$prb->moveStep($cur_task++);
	}
	$prb->moveStep($cur_task);
	$prb->hide();

	$arrayKeys = array_keys($sharesPortfolio);
		
	foreach ($arrayKeys as $arrayKey)
	{
		$ticker = $arrayKey;
		$quantity = $sharesPortfolio[$arrayKey]["quantity"];
 		$query = "select ticker, adjclose/100 as clo from quotes where ticker=\"$ticker\" order by date desc limit 1" ;
		$rs = $db->GetRow($query);
		$close = $rs["clo"];
		$valueTransaction = $quantity * $close - FEE;
		$allTransactionsArray[$ticker]["totalSell"] = $allTransactionsArray[$ticker]["totalSell"] + $valueTransaction;
		$cashInHand += $valueTransaction;
		
//		echo "After $ticker @ $close for $quantity shares in portfolio we have a theoretical portfolio value of $cashInHand<br/>";

	}

	$totReturn = 0;
	$totTransactions = 0;
	$totBuy = 0;
	$totSell = 0;
	
	$arrayKeys = array_keys($allTransactionsArray);

	foreach ($arrayKeys as $arrayKey)
	{
		$ticker = $arrayKey;
		$numBuy = $allTransactionsArray[$ticker]["numBuy"];
		$numSell = $allTransactionsArray[$ticker]["numSell"];
		$numOut = $allTransactionsArray[$ticker]["numOut"];
		$valueBuy = $allTransactionsArray[$ticker]["totalBuy"];
		$valueSell = $allTransactionsArray[$ticker]["totalSell"];

		$sharesTraded++;
		$returnValue = $valueSell - $valueBuy;
		$returnRate = ($returnValue / $valueBuy) * 100;
		$totReturn += $returnValue;
		$totBuy += $valueBuy;
		$totSell += $valueSell;

		$totTransactions += ($numBuy + $numSell + $numOut);
	
		if ($returnValue > 0)
			$rowClass = 'trGain';
		else
			$rowClass = 'trLoss';
				
		$sq1 = "select issuer from issuers where ticker = \"$ticker\"";
		$issuer = $db->GetOne($sq1);

		$link = "<a href=\"" . "../charting/charting.php?selectedIssuer=" . urlencode($issuer) . "&fromdate=" . urlencode($defaultDateRangeChart) . "&DM=on&BB=on\">$ticker</a>";
		$signalTable -> addRow($rowClass);
		$signalTable -> addData($link, 'tdName');
		$signalTable -> addData($issuer, 'tdName');
		$signalTable -> addData(number_format($valueBuy, 2), 'tdFigure');
		$signalTable -> addData(number_format($valueSell, 2), 'tdFigure');
		$signalTable -> addData(number_format($returnValue, 2), 'tdFigure');
		$signalTable -> addData(number_format($returnRate, 2), 'tdCenter');
		$signalTable -> addData(number_format($numBuy, 0), 'tdCenter');
		$signalTable -> addData(number_format($numSell, 0), 'tdCenter');
		$signalTable -> addData(number_format($numOut, 0), 'tdCenter');
/*
		$i_record = array();
		$i_dateCreation = strftime("%Y-%m-%d %H:%M:%S", strtotime("now"));
		$i_record["date_created"] = $i_dateCreation;
		$i_record["scenario"] = "current";
		$i_record["ticker"] = $ticker;
		$i_record["shares_held"] = $shares;
		$i_record["value_shares"] = number_format($valueShares, 2);
		$i_record["last_quotation"] = $date;
		$i_record["last_typ_price"] = number_format($open, 2);
		$i_record["cash"] = number_format($curInvest - $valueShares, 2);
		$i_record["portfolio_value"] = number_format($curInvest, 2);
		$i_record["gain_loss"] = number_format($returnValue, 2);
		$i_record["returns"] = number_format($returnRate, 2);
		$i_record["buys"] = $numBuy;
		$i_record["sells"] = $numSell;
		$i_record["outs"] = $numOut;
			
		$insertSQL = $db->AutoExecute("scenarios", $i_record, 'INSERT'); 
*/	
	}

// Add a horizontal ruler below the table body
	$signalTable -> addRow();
	$signalTable -> addData('<hr>', 'ruler');

	$signalTable -> writeTable();
	
	echo "Initial investment:\t" . number_format(INVESTMENT, 2) . "<br />";
	echo "Total return:\t" . number_format($totReturn, 2) . "<br />";
	$format = "Return rate:\t %01.2f%s<br/>";
	printf($format, $totReturn/(INVESTMENT*100), "%");
	echo "Cost of operations:\t" . number_format($totTransactions * FEE, 2);
?>