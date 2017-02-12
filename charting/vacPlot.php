<?php

function YLabelFormat ($aLabel)
{
	$aLabel /= 1000000;
	return number_format($aLabel);
}
	$dir = $_SERVER['DOCUMENT_ROOT'];

	require_once "../include/db.inc.php";
	require_once "../include/indicators.php";
	require_once "../include/dataArraysSQL.php";
	require_once "../include/db.inc.php";
	require_once "../include/graph.inc.php";

	$graphWidth = $HTTP_GET_VARS['width'];
	$graphHeight = $HTTP_GET_VARS['height'];
//	$selectedIssuer = rawurldecode($HTTP_GET_VARS['issuer']);
	$selectedTicker = $HTTP_GET_VARS['ticker'];
	$fromDate = $HTTP_GET_VARS['fromdate'];

	$query = "SELECT
			  adjclose/100,
			  high/100*(adjclose/close), low/100*(adjclose/close), open/100*(adjclose/close),
			  volume, tprice/100
			  FROM quotes
			  WHERE ticker = \"$selectedTicker\" and date >= \"$fromDate\" order by date asc";
	
	$result = $db->Execute($query);
	$rowsFound = $result->RecordCount();
	
	$vacArray = array();
	$adjCloseArray = array();
	$volumeArray = array();
	$tPriceArray = array();
			
	while (!$result->EOF)
	{
		$adjClose = $result->fields[0];
		$high = $result->fields[1];
		$low = $result->fields[2];
		$volume = $result->fields[4];
			
		$vacArray[] = $volume * ((($adjClose-$low)-($high-$adjClose))/($high-$low));
		$volumeArray[] = $volume;
		$adjCloseArray[] = $adjClose;
		$tPriceArray[] = $result->fields[5];
   		$result->MoveNext();
	}

// General graph
	$graph = new Graph($graphWidth, $graphHeight,"auto");

	$graph->legend->SetAbsPos(80,($screen_height*0.15)-50,'left','bottom');
	$graph->img->SetImgFormat( "auto");
	$graph->SetScale("textlin");
	$graph->img->SetMargin(45,45,5,10);
	$graph->SetShadow();

// x axis
	$graph->xaxis->SetTextTickInterval(22);
	$graph->xaxis->SetPos('min');
	$graph->xaxis->HideLabels(); // Hide xaxis Labels 
 	$graph->xgrid->Show(true);

// y Axis & Grid
	$graph->yaxis->SetLabelFormatCallback('yLabelFormat'); 
//	$graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5');
// same color as the values plotted here
	$graph->yaxis->SetColor('green');
	$graph->ygrid->SetColor('green');
	$graph->ygrid->SetLineStyle('solid');
	$graph->ygrid->SetWeight(1);
 	$graph->ygrid->Show(true,false);
		
// y2 Axis & Grid	
	$graph->SetY2Scale("lin");
// same color as the values plotted here
	$graph->y2axis->SetColor('orange');
	$graph->y2grid->SetColor('orange');
	$graph->y2grid->SetLineStyle('dotted');
	$graph->y2grid->SetWeight(1);
 	$graph->y2grid->Show(false,false);

/* VAC
	$lineplot = new LinePlot($vacArray);
	$lineplot->SetColor('blue');
	$lineplot->SetLegend("VAC");
	$graph->Add($lineplot);
	DoStoreDataArray($db, "vac", $vac);
*/

//MFI
	$lineplot3 = new LinePlot(DoMFI($volumeArray, $tPriceArray));
	$lineplot3->SetColor("green");
	$lineplot3->SetLegend("MFI");
	$graph->Add($lineplot3);

//OBV		
//	$lineplot1 = new LinePlot(DoOBV($volumeArray, $adjCloseArray));
//	$lineplot1->SetColor('green');
//	$lineplot1->SetLegend("OBV");
//	$graph->Add($lineplot1);

// RSI
	$lineplot2 = new LinePlot(DoRSI($adjCloseArray, 14));
	$lineplot2->SetColor("orange");
	$lineplot2->SetLegend("RSI");
	$graph->AddY2($lineplot2);

	
	$graph->Stroke();
?>