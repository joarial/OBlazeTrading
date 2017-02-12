<?php

	$query = "select ticker, issuer from issuers";
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

	$q1 = "select max(date) from quotes";
	$r1 = $db->GetRow($q1);
	$lastQuoteDate = $r1[0];

	$lastDate = strftime("%Y-%m-%d");

	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
		
	foreach ($results as $recordIssuer)
	{
		$symbol = $recordIssuer["ticker"];
		$issuer = $recordIssuer["issuer"];
			
		$query = "select max(date) from quotes where ticker = \"$symbol\"";
		$fDate = $db->GetOne($query);
		
		if ($fDate <> $lastQuoteDate)
			echo "<br/>$symbol - $issuer: no new quotes since $fDate ($lastQuoteDate)";

		$prb->unhide();
		$label = "Processed ... $symbol from $fDate to $lastDate";
		$prb->setLabelValue('txt1', $label);
		$prb->moveStep($cur_task++);
	}
	$prb->setLabelValue('txt1', "Completed");
	$prb->moveStep($cur_task);

?>