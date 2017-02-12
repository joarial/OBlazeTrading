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

	$lastDate = strftime("%Y-%m-%d");
	$fromDate = "2005-01-01";
	
	foreach ($results as $result)
	{
		$ticker = $result["ticker"];
		$issuer = $result["issuer"] . " ($ticker)";
			
		initiateSignalsDataArrays($ticker, $fromDate);
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

		$prb->unhide();
		$label = "Processed ... $issuer";
		$prb->setLabelValue('txt1', $label);
		$prb->moveStep($cur_task++);
	}
	$prb->setLabelValue('txt1', "Recalculation completed");
	$prb->moveStep($cur_task);
?>