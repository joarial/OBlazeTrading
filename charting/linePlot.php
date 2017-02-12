<?php

/*
 *	usage
 *
 *	linePlot.php?width=width&height=heightt&issuer=issuer&ticker=ticker&fromdate=YYYY-MM-DD&rand=random number&DM=on
 *
 *	http://localhost:8888/mInvestNew/charting/linePlot.php?width=600&height=600&selectedIssuer=AGILENT+TECHNOLOGIES+IN&ticker=A&fromdate=2008-03-27&DM=on&BB=on
 */

	ini_set('memory_limit', 16777216);

	require_once "../include/db.inc.php";
	require_once "../include/globalVariables.inc.php";
	require_once "../include/indicators.php";
	require_once "../include/graph.inc.php";
	require_once "../include/dataArraysSQL.php";
	require_once "../include/signals.php";
	require_once "../signals/findSignals.php";
	require_once "../include/yScaleCallback.inc.php";

	$selectedIssuer = rawurldecode($_GET['issuer']);
	$ticker = $_GET['ticker'];
	$fromDate = $_GET['fromdate'];
	$DM = $_GET["DM"];
	
//	SELECT q.ticker, q.date, q.adjclose/100 as aclose, high/100*(q.adjclose/close) as hi, low/100*(q.adjclose/close) as lo, open/100*(q.adjclose/close) as op, q.volume,
// tprice/100 as price, mavg32/100 as ma32, mavg20/100 as ma20, (mavg32+(2*stdev32))/100 as bbup32, (mavg32-(2*stdev32))/100 as bblow32, s.signal
// FROM quotes as q  LEFT JOIN signals as s ON q.ticker = s.ticker AND q.date = s.date WHERE q.ticker = "A" and q.date >= "2008-03-27" ORDER by q.date asc
		
	$query = "SELECT q.ticker, q.date,
		q.adjclose/100 as aclose,
		high/100*(q.adjclose/close) as hi,
		low/100*(q.adjclose/close) as lo,
		open/100*(q.adjclose/close) as op,
		q.volume,
		tprice/100 as price,
		mavg32/100 as ma32,
		mavg20/100 as ma20,
		(mavg32+(2*stdev32))/100 as bbup32,
		(mavg32-(2*stdev32))/100 as bblow32,
		s.signal,
		s.date
		FROM quotes as q
		LEFT JOIN signals as s
		ON
		 q.ticker = s.ticker
		AND
		 q.date = s.date
		WHERE
		 q.ticker = \"$ticker\" and q.date >= \"$fromDate\"
		ORDER BY
			q.date asc";
			
	$dataRecords = $db->GetAll($query);
	$dataFound = count($dataRecords);
	
//	echo "number records: $dataFound for $ticker and from $fromDate<br/>";
	
	$query = "SELECT count(date) from quotes where ticker = \"$ticker\" and date > \"$fromDate\"";
	
	$rowsFound = $db->GetOne($query);
	
//	echo "records from select: $dataFound and number of dates: $rowsFound<br/>";

/*
 * Need 9 data vector:
 * 	dates
 * 	tprice
 * 	upper BB
 * 	lower BB
 * 	medium MA (also middle of BB)
 * 	slow MA
 * 	buy signal
 * 	sell/out signal
 * 	trigger signal
 * 	volume
 * 
 */
	$datesArray = array();
	$tPriceArray = array();
	$adjCloseArray = array();
	$highArray = array();
	$lowArray = array();
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
	
	$query = "SELECT max(date) from quotes where ticker = \"$ticker\"";
	$maxQuote = &$db->GetOne($query);
	$query = "SELECT max(last_in_signal) from issuers where ticker = \"$ticker\"";
	$maxScenario = $db->GetOne($query);
	
//	echo "higer date is tupple: $maxQuote and from last_in_signals in issuers: $maxScenario<br/>";	
	
//	Did we already update the signals with most recent data
	if ($maxQuote > $maxScenario)
	{
		DoFindSignals($ticker, $fromDate);				// in ../include/signals.php
		$parabolicArray = DoRetrieveArray("SAR");
	}
	else
		$parabolicArray = DoParabolic($ticker, $fromDate);

//	echo "back from Parabolic processing<br/>";	
		
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

		// candlesticks
		if ($rowsFound <= DETAILCHART)
		{
			$dataRow[] = $record["op"]; // $open
			$dataRow[] = $record["aclose"]; // $adjClose
			$dataRow[] = $record["lo"]; // $low
			$dataRow[] = $record["hi"]; // $high
		}
				
		if ($record["signal"] == "buy")
		{
			$buyArray[]= $record["typPrice"];
			$sellArray[] = null;
			$BBWhenSignalArray[]= null;
		}
		elseif ($record["signal"] == "sell" or $record["signal"] == "out")
		{
			$sellArray[]= $record["typPrice"];
			$buyArray[] = null;
			$BBWhenSignalArray[]= null;
		}
		else
		{
			$buyArray[] = NULL;
			$sellArray[] = NULL;
			
			if ($record["signal"] == "TriSell")
				$BBWhenSignalArray[] = $record["typPrice"];
			elseif ($record["signal"] == "TriBuy")
				$BBWhenSignalArray[] = $record["typPrice"];
			else 
				$BBWhenSignalArray[] = null;
		}
	}		
//	echo "Processing fron $fromDate --> $maxQuote - rows from SQL: $rowsFound / $dataFound - size arrays: " . count($tPriceArray) . " - latest run: $maxScenario<br />";
//	General graph
		$graph = new Graph($_GET['width'], $_GET['height'],"auto");
		$graph->img->SetImgFormat( "auto");

		$graph->SetScale("textlin");
//		$graph->SetScale("textlog");
		$graph->SetTickDensity(TICKD_NORMAL);

		$graph->img->SetMargin(45,45,40,55);
		$graph->SetShadow();
		$graph->title->Set("$selectedIssuer ($ticker)");
		$graph->title->SetFont(FF_VERA,FS_BOLD);
		$graph->legend->SetAbsPos(80,70,'left','top');

// y Axis & Grid
		$graph->yaxis->SetColor('blue');
		$graph->ygrid->SetColor('blue');
		$graph->ygrid->SetLineStyle('dotted');
		$graph->ygrid->SetWeight(1);
 		$graph->ygrid->Show(true,false);
 		$graph ->xgrid->Show( true);

// y2 Axis & Grid
		$graph->SetY2Scale("lin");
		$graph->y2axis->scale->SetGrace(400);
 		$graph->y2axis->Hide();
 		$graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5'); 
		$graph->ygrid->Show(); 
		$graph->xgrid->Show(); 

// x Axis
		$graph->xaxis->SetTickLabels($datesArray);
		$graph->xaxis->SetTextTickInterval(22);
		$graph->xaxis->SetTickSide(SIDE_BOTTOM);
		$graph->xaxis->SetFont(FF_VERA,FS_NORMAL,7);
		$graph->xaxis->SetLabelAngle(45);
//		$graph->xaxis->title->SetFont(FF_VERA,FS_BOLD);
		$graph->xaxis->SetPos('min');

// Close
		if ($rowsFound <= DETAILCHART)
		{
			$lineplot0 = new StockPlot($dataRow);
			$lineplot0->SetWidth(3);
			$graph->Add($lineplot0);
		}
		else
		{
		 	$lineplot = new LinePlot($tPriceArray);
			$lineplot->SetColor('blue');
			$lineplot->SetLegend("Typical Price");
			$lineplot->SetStepStyle(true);
			$graph->Add($lineplot);
		}

		$lineplot2 = new LinePlot($mBBArray);
		$lineplot2a = new LinePlot($uBBArray);
		$lineplot2b = new LinePlot($lBBArray);
		$legend = BBMA . "-day MA";
		$lineplot2->SetColor('red');
		$lineplot2->SetLegend($legend);
		$graph->Add($lineplot2);
		$lineplot2a->SetColor('violetred2');
		$graph->Add($lineplot2a);
		$lineplot2b->SetColor('violetred2');
		$graph->Add($lineplot2b);

// shorter term MA
		$lineplot3 = new LinePlot($sMAArray);
		$lineplot3->SetColor('green');
		$legend = SMA . "-day MA";
		$lineplot3->SetLegend($legend);
		$graph->Add($lineplot3);

// volume
		$bplot = new BarPlot(DoNormaliseVolume(50));
		$bplot->SetFillColor("orange");
		$graph->AddY2($bplot);
		$volRefLine = array();
		$volRefLine = array_pad($volRefLine, $rowsFound, 100);
		$volRef = new LinePlot($volRefLine);
		$volRef->SetColor("orange");
		$graph->AddY2($volRef);
		
// Parabolic
		$pPlot = new LinePlot($parabolicArray);
		$pPlot->SetColor('black');
		$pPlot->SetStyle("dashed");
		$legend = "Parabolic T/P";
		$pPlot->SetLegend($legend);
		$graph->Add($pPlot);


		$lineplotBuy = new LinePlot($buyArray);
		$lineplotBuy->value->SetColor('forestgreen');
		$lineplotBuy->value->SetFont(FF_FONT1,FS_BOLD);
		$lineplotBuy->value->SetFormat("$%0.1f");
		$lineplotBuy->value->Show();
		$lineplotBuy->mark->SetType(MARK_CIRCLE);
		$lineplotBuy->mark->SetColor('forestgreen');
		$graph->Add($lineplotBuy);

		$lineplotSell = new LinePlot($sellArray);
		$lineplotSell->value->SetColor('darkred');
		$lineplotSell->value->SetFont(FF_FONT1,FS_BOLD);
		$lineplotSell->value->SetFormat("$%0.1f");
		$lineplotSell->value->Show();
		$lineplotSell->mark->SetType(MARK_CIRCLE);
		$lineplotSell->mark->SetColor('darkred');
		$graph->Add($lineplotSell);

		$lineplotuBB = new LinePlot($BBWhenSignalArray);
		$lineplotuBB->mark->SetType(MARK_CIRCLE, 'purple');
//		$lineplotuBB->mark->SetColor('grey8');
		$graph->Add($lineplotuBB);

		$graph->Stroke();
?>