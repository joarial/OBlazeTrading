<?php
		
	$query = "select ticker from issuers";
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
	$prb->setLabelPosition('pct1',320,120+23,400,70,'center');
	$prb->setLabelFont('pct1',48);
	$prb->min = 1;
	$prb->max = $num_tasks;
	$cur_task = 0;
	$prb->show();

	foreach ($results as $recordIssuer)
	{
		$symbol = $recordIssuer["ticker"];
		$record = array();
			
		$quote = new yahoo;
		$quote->get_detail_last_stock_quote($symbol);
		$record["ticker"] = $symbol;
		$record["week"] = strftime("%W", strtotime($quote->date));
		$record["PE"] = $quote->PE == 'N/A' ? NULL : $quote->PE;
		$record["avgVolume"] = $quote->averageVolume;
		$record["yearLow"] = $quote->yearLow;
		$record["yearHigh"] = $quote->yearHigh;
		$record["EPS"] = $quote->EPS;
		$record["marketCap"] = $quote->marketCap;
		$quote->get_stock_fundamentals($symbol);
		$record["EPSCurrent"] = $quote->EPSCurrentYear;
		$record["EPSNext"] = $quote->EPSNextYear;
		$record["PEG"] = $quote->PEG == 'N/A' ? NULL : $quote->PEG;
		$record["targetPrice"] = $quote->targetPrice;
		$record["bookValue"] = $quote->bookValue;
				
		$query = "select * from fundamentals where ticker = \"$symbol\" and week = \"" . $record["week"] . "\"";
		$rs = $db->Execute($query);
		$rowsFound = $rs->RecordCount();

		if ($rowsFound != 1)
			$insertSQL = $db->GetInsertSQL($rs, $record);
		else
			$insertSQL = $db->GetUpdateSQL($rs, $record);

		$db->Execute($insertSQL);
		$prb->unhide();
		$label = "Processed ... $symbol";
		$prb->setLabelValue('txt1', $label);
		$prb->moveStep($cur_task++);
	}
	$prb->setLabelValue('txt1', "Completed");
	$prb->moveStep($cur_task);
	$prb->hide();
?>