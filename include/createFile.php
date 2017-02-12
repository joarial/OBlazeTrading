<?php

	include 'db.inc';

	if (!($connection = @ mysql_connect($hostname, $username, $password)))
		die("Cannot connect");

	if (!(mysql_select_db($databaseName, $connection)))
		showerror();
		
	$query = "SELECT ticker, date, adjclose from quotes where ticker = \"AAPL\" order by date";
		
	if (!($result = @ mysql_query($query, $connection)))
		showerror();

	$rowsFound = @ mysql_num_rows($result);

	if ($rowsFound != 0)
	{
        $txtFile="../files/fileProcessing.txt";
        $fp = fopen($txtFile,"w");
        
        for ($i = 0; $i < $rowsFound; $i++)
        {
			$row = @ mysql_fetch_array($result);
			$date = $row["date"];
			$adjClose = $row['adjclose'];
			$str .= $date . "," . $adjClose . "\n";
		}
		
		fwrite ($fp, $str);
		fclose ($fp);

//        header("Content-type: text/htm");
//		header("Content-Disposition:attachment;filename=testing.DAT");
//   	readfile($txtFile);
	}
	
	mysql_close($connection);

?>