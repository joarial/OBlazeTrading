<?php
// From J. Welles Widder Jr. Directional Movement Index System

function YLabelFormat ($aLabel)
{
	$aLabel /= 1000000;
	return number_format($aLabel);
}

/*
 *	usage
 *
 *	dmiPlot.php?width=width&height=height&issuer=issuer&ticker=ticker&fromdate=YYYY-MM-DD&rand=random number
 *
 *	http://localhost:8888/mInvestNew/charting/dmiPlot.php?width=1050&height=100&issuer=AGILENT+TECHNOLOGIES+IN&ticker=A&fromdate=2008-03-27
*/

	require_once "../include/db.inc.php";
	require_once "../include/graph.inc.php";
	require_once "../include/indicators.php";
	require_once "../include/dataArraysSQL.php";
	
	$ticker = $_GET['ticker'];
	$fromDate = $_GET['fromdate'];

// General graph
	$graph = new Graph($_GET['width'], $_GET['height'],"auto");
	$graph->legend->Pos(0.05, 0,'left','top');
	$graph->img->SetImgFormat( "auto");
	$graph->SetScale("textlin");
	$graph->img->SetMargin(45,45,5,10);
	$graph->SetShadow();

// x axis
	$graph->xaxis->SetTextTickInterval(22);
	$graph->xaxis->SetPos('min');
	$graph->xaxis->HideLabels(); // Hide xaxis Labels 
 	$graph->xgrid->Show(true);
	$graph->ygrid->SetLineStyle('solid');
	$graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5'); 
	$graph->ygrid->SetWeight(1);
 	$graph->ygrid->Show(true,true);
		
// Parabolic Time/Price System
// Directional Index
	DoDmi($ticker, $fromDate);

//DIPlus
	$lineplot1 = new LinePlot(DoRetrieveArray("DI+"));
	$lineplot1->SetColor("green");
	$lineplot1->SetLegend("DI+");
	$graph->Add($lineplot1);
	
//DIMinus
	$lineplot2 = new LinePlot(DoRetrieveArray("DI-"));
	$lineplot2->SetColor("red");
	$lineplot2->SetLegend("DI-");
	$graph->Add($lineplot2);
	
// ADX
	$lineplot3 = new LinePlot(DoRetrieveArray("ADX"));
	$lineplot3->SetColor("orange");
	$lineplot3->SetLegend("ADX");
	$graph->Add($lineplot3);

// ADXR
	$lineplot4 = new LinePlot(DoRetrieveArray("ADXR"));
	$lineplot4->SetColor("orangered4");
	$lineplot4->SetStyle("dashed");
	$lineplot4->SetLegend("ADXR");
	$graph->Add($lineplot4);
	
	$graph->Stroke();
?>