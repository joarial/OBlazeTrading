<?php

echo("Enter globalUpdateDB.php");

	$query = "select ticker, max(date) as maxDate from quotes group by ticker order by ticker asc";
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

	$lastDate = strftime("%Y-%m-%d");
	
	foreach ($records as $record)
	{
		$ticker = $record["ticker"];
		doGetYahooData($ticker, $record["maxDate"], $lastDate);

		$prb->unhide();
		$label = "Processed ... $ticker until $lastDate";
		$prb->setLabelValue('txt1', $label);
		$prb->moveStep($cur_task++);
	}
	
	$prb->setLabelValue('txt1', "Completed");
	$prb->moveStep($cur_task);

?>