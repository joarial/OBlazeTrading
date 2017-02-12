<?php
	ini_set('max_execution_time', 3600);
	ini_set('memory_limit', 16777216);

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
	$caption = "Portfolio performance since $defaultDateRangeChart";
	$signalTable -> addCaption ($caption, array('class' => 'captionStyle'));

// Add Header
	$signalTable -> addHeader('ticker', 'trHead');
	$signalTable -> addHeader('issuer', 'trHead');
	$signalTable -> addHeader('shares held', 'trHead');
	$signalTable -> addHeader('value shares', 'trHead');
	$signalTable -> addHeader('last quotation date', 'trHead');
	$signalTable -> addHeader('last typical price', 'trHead');
	$signalTable -> addHeader('cash in hand', 'trHead');
	$signalTable -> addHeader('portfolio value', 'trHead');
	$signalTable -> addHeader('gain/loss', 'trHead');
	$signalTable -> addHeader('return', 'trHead');
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
 * Requires arrays:		transationsArray --> ticker, date, type
 * 						sharesPortfolio --> ticker, date_purchased, quantity, price_payed
 */	
	$query = "select ticker, issuer from issuers order by issuer asc";
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
	
	foreach ($results as $result)
	{
		$ticker = $result["ticker"];
		
		$query = "SELECT max(date) from quotes where ticker = \"$ticker\"";
		$maxQuote = $db->GetOne($query);
		$query = "SELECT last_in_signal from issuers where ticker = \"$ticker\"";
		$maxScenario = $db->GetOne($query);
		
//	Did we already update the signals with most recent data
		if ($maxQuote > $maxScenario)
		{
			DoFindSignals($ticker);				// in ../include/signals.php
		}
		
		$issuer = $result["issuer"] . " ($ticker)";
		$curInvest = $curValue = $initialValue = 1000;
		$shares = $numBuy = $numSell = $numOut = $valueShares = 0;
		$date = "";
		
		$query = "SELECT 
			open/100*(q.adjclose/q.close) as op,
			signal
			FROM signals as s
			LEFT JOIN quotes as q
			ON q.ticker=s.ticker AND q.date=s.date
			WHERE q.ticker = \"$ticker\" and q.date >= \"$defaultDateRangeChart\"
			ORDER by q.date asc";

		$dataRecords = $db->GetAll($query);
		$dataFound = count($dataRecords);
		
		foreach ($dataRecords as $record)
		{
			if ($record["signal"] == "buy")
			{
				$open = $record["op"];
				$shares = floor(($curInvest - FEE) / $open);
				$curInvest -= ($shares * $open) + FEE;
				$curValue = $curInvest + ($shares * $open) + FEE; // avoid to get out because of the difference given by the FEE
				$numBuy++;
			}
			elseif ($record["signal"] == "sell")
			{
				$open = $record["op"];
				$curInvest += ($shares * $open) - FEE;
				$curValue = $curInvest;
				$shares = 0;
				$numSell++;
			}
			elseif ($record["signal"] == "out")
			{
				$open = $record["op"];
				$curInvest += ($shares * $open) - FEE;
				$curValue = $curInvest;
				$shares = 0;
				$numOut++;
			}
		}
		
		if ($shares > 0)
		{
			$query = "select date, adjclose/100 as clo from quotes where ticker=\"$ticker\" order by date desc limit 1" ;
			$rs = $db->GetRow($query);
			$close = $rs["clo"];
			$date = $rs["date"];
			$curInvest += ($shares * $close) - FEE;
			$valueShares = $shares * $close;
		}
			
		if ($numBuy > 0)
		{
			$sharesTraded++;
			$portfolio += $curInvest;
			$returnValue = $curInvest - $initialValue;
			$returnRate = ($returnValue / $initialValue)*100;
			$totReturn += $returnValue;
			$totTransactions += $numBuy + $numSell + $numOut;
	
			if ($returnValue > 0)
				$rowClass = 'trGain';
			else
				$rowClass = 'trLoss';

			$link = "<a href=\"" . "../charting/charting.php?selectedIssuer=" . urlencode($issuer) . "&fromdate=" . urlencode($defaultDateRangeChart) . "&DM=on&BB=on\">$ticker</a>";
			$signalTable -> addRow($rowClass);
			$signalTable -> addData($link, 'tdName');
			$signalTable -> addData($issuer, 'tdName');
			$signalTable -> addData(number_format($shares, 0), 'tdCenter');
			$signalTable -> addData(number_format($valueShares, 2), 'tdFigure');
			$signalTable -> addData($date, 'tdDate');
			$signalTable -> addData(number_format($open, 2), 'tdFigure');
			$signalTable -> addData(number_format($curInvest - $valueShares, 2), 'tdFigure');
			$signalTable -> addData(number_format($curInvest, 2), 'tdFigure');
			$signalTable -> addData(number_format($returnValue, 2), 'tdFigure');
			$signalTable -> addData(number_format($returnRate, 2), 'tdCenter');
			$signalTable -> addData(number_format($numBuy, 0), 'tdCenter');
			$signalTable -> addData(number_format($numSell, 0), 'tdCenter');
			$signalTable -> addData(number_format($numOut, 0), 'tdCenter');
		}

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

		$prb->unhide();
		$label = "Processed ... $issuer";
		$prb->setLabelValue('txt1', $label);
		$prb->moveStep($cur_task++);
	}
	$prb->setLabelValue('txt1', "Completed");
	$prb->moveStep($cur_task);
	$prb->hide();

// Add a horizontal ruler below the table body
	$signalTable -> addRow();
	$signalTable -> addData('<hr>', 'ruler');

	echo "Total investment:\t" . number_format($sharesTraded * $initialValue, 2) . "<br/>";
	echo "Total net return:\t\t" . number_format($totReturn, 2) . "<br/>";
	$format = "Return net rate:\t\t %01.2f%s<br/><br/>";
	printf($format, ($totReturn/($sharesTraded * $initialValue))*100, "%");

	echo "Number of operations: $totTransactions<br/>";
	echo "Number of shares traded: $sharesTraded<br/>";
	echo "Number of issuers in database: $rowsFound<br/>";
	echo "Cost of operations:\t" . number_format($totTransactions * FEE, 2) . "<br/><br/>";
	
	
	$signalTable -> writeTable();
	
?>