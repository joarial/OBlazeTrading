<?php
	if ($ticker == "")
		die("Error: Missing ticker");
			
	$query = "delete from quotes where ticker =\"$ticker\"";
	$rs =& $db->Execute($query);
	$fDate = "1995-06-1";
	$lastDate = strftime("%Y-%m-%d");
	
	doGetYahooData($ticker, $fDate, $lastDate);
		
	echo "<br/>Completed<br/>";
?>