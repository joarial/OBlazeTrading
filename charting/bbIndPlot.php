<?php

	require_once "../include/globalVariables.inc.php";
	require_once "../include/db.inc.php";
	require_once "../include/graph.inc.php";
	require_once "../include/dataArraysSQL.php";

/*
 *	usage
 *
 *	bbIndPlot.php?width=width&height=height&issuer=issuer&ticker=ticker&fromdate=YYYY-MM-DD&rand=random number
 *
 *	http://localhost:8888/mInvestNew/charting/bbIndPlot.php?width=1050&height=100&issuer=AGILENT+TECHNOLOGIES+IN&ticker=A&fromdate=2008-03-27
*/
	
	
	$selectedTicker = $_GET['ticker'];
	$fromDate = $_GET['fromdate'];

	$query = "SELECT
			  adjclose/100 as aclose,
			  tprice/100 as price,
			  mavg32/100 as mavg, 
			  (mavg32+(2*stdev32))/100 as bbup,
			  (mavg32-(2*stdev32))/100 as bblow
			  FROM quotes
			  WHERE ticker = \"$selectedTicker\" and date >= \"$fromDate\" order by date asc";
	$records = $db->GetAll($query);
	
	foreach ($records as $recordIssuer)
	{
		$bbMid = $recordIssuer["mavg"];	// mavg32

		if (($bbMid == NULL) || ($bbMid == 0.00))
		{
        	$pctb[] = "";
   		   	$bandWidth[] = "";
   		   	continue;
   		}

		$adjClose = $recordIssuer["aclose"];
		$tPrice = $recordIssuer["price"];		// tprice
		$bbUp = $recordIssuer["bbup"];		// mavg32 + (2*stdev32) = bbup32
		$bbBottom = $recordIssuer["bblow"];	// mavg32 - (2*stdev32) = bblow32

		$bandWidthArray[] = @ ($bbUp - $bbBottom)/$bbMid;
        $pctbArray[] = @ ($adjClose - $bbBottom)/($bbUp - $bbBottom);
	}

	$lineplotpctb = new LinePlot($pctbArray);
	$lineplotBW = new LinePlot($bandWidthArray);
	
	$minPctb = min($pctbArray) * 1.1;
	$maxPctb = max($pctbArray) * 1.1;
	
	$minBW = min($bandWidthArray) * 1.1;
	$maxBW = max($bandWidthArray) * 1.1;

// General graph
	$graph = new Graph($_GET['width'], $_GET['height'],"auto");
	$graph->legend->Pos(0.05, 0.1,'left','top');
	$graph->img->SetImgFormat( "auto");
	$graph->SetScale("textlin");
//	$graph->SetScale("textlin");
	$graph->img->SetMargin(45,45,5,10);
	$graph->SetShadow();

// x axis
	$graph->xaxis->SetTextTickInterval(22);
	$graph->xaxis->SetPos('min');
	$graph->xaxis->HideLabels(); // Hide xaxis Labels 
 	$graph->xgrid->Show(true);

// y Axis & Grid
	$graph->yaxis->scale->ticks->Set(0.5);
	$graph->yaxis->SetColor('turquoise1');
	$graph->ygrid->SetColor('turquoise1');
	$graph->ygrid->SetLineStyle('solid');
	$graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5'); 
	$graph->ygrid->SetWeight(1);
 	$graph->ygrid->Show(true,false);

 	$lineplotpctb->SetColor('orangered4');
	$lineplotpctb->SetLegend("%b");
	$graph->Add($lineplotpctb);
	
	$lineplotBW->SetColor('red');
	$lineplotBW->SetLegend("BandWidth");
	$graph->Add($lineplotBW);

	$graph->Stroke();
?>