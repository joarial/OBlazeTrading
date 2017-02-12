<?php
	ini_set('max_execution_time', 3600);
	ini_set('memory_limit', 16777216);

	require_once "../include/db.inc.php";
	require_once "../include/globalVariables.inc.php";
	require_once "../include/indicators.php";
	require_once "../include/dataArraysSQL.php";
	require_once "../include/signals.php";
	require_once "../include/class.progressbar.php";
	require_once "../include/class.HtmlTagMaker.php";
	require_once "../include/class.TableMaker.php";
	require_once "../include/yScaleCallback.inc.php";

	$signalTable = new TableMaker();
	$fromDate = $defaultDateRangeChart;

// Define elements with class attributes
	$signalTable -> defineHeader('trHead', array('class' => 'trHead'));
	$signalTable -> defineRow ('trBuy', array('class' => 'trBuy'));
	$signalTable -> defineRow ('trSell', array('class' => 'trSell'));
	$signalTable -> defineRow ('trOut', array('class' => 'trOut'));
	$signalTable -> defineRow ('trTrigger', array('class' => 'trTrigger'));
	
	$signalTable -> defineDataCell ('tdFigure', array('class' => 'tdFigure'));
	$signalTable -> defineDataCell ('tdName', array('class' => 'tdName'));
	$signalTable -> defineDataCell ('tdDate', array('class' => 'tdDate'));

// Add a caption
	$sinceDate = date('Y/m/d', strtotime('-2 weeks'));
	$title = "Signals generated since $sinceDate";
	$signalTable -> addCaption ($title, array('class' => 'captionStyle'));

// Add Header
	$signalTable -> addHeader('ticker', 'trHead');
	$signalTable -> addHeader('issuer', 'trHead');
	$signalTable -> addHeader('date', 'trHead');
	$signalTable -> addHeader('type', 'trHead');
	$signalTable -> addHeader('aadjusted close', 'trHead');
	$signalTable -> addHeader('typical price', 'trHead');
	$signalTable -> addHeader('technical reason', 'trHead');

	// Place a horizontal ruler above the first row
	$signalTable -> addRow();
	$signalTable -> addData('<hr>', 'ruler', array('colspan' => 13));
	
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
		$link = "<a href=\"" . "../charting/charting.php?selectedIssuer=" . urlencode($issuer) . "&fromdate=" . urlencode($defaultDateRangeChart) . "&DM=on&BB=on\">$ticker</a>";
					
		$query = "select s.ticker, i.issuer, s.date, s.signal, s.adjclose, s.typPrice, s.comment from signals as s, issuers as i
				  where s.ticker = i.ticker and s.ticker = \"$ticker\" and date >= \"$sinceDate\" order by date asc";
		$records = $db->GetAll($query);
	
		foreach ($records as $record)
		{
			$signal = $record[3];
			$typPrice = $record[5];

			if ($signal == "buy")
				$rowClass = 'trBuy';
			else if ($signal == "sell")
				$rowClass = 'trSell';
			else if ($signal == "out")
				$rowClass = 'trOut';
			else
				continue;

			$signalTable -> addRow($rowClass);
			$signalTable -> addData($link, 'tdName');
			$signalTable -> addData($issuer, 'tdName');
			$signalTable -> addData($record[2], 'tdName');
			$signalTable -> addData($record[3], 'tdName');
			$signalTable -> addData($record[4], 'tdFigure');
			$signalTable -> addData($record[5], 'tdFigure');
			$signalTable -> addData($record[6], 'tdName');
		}
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

	$signalTable -> writeTable();
	
?>