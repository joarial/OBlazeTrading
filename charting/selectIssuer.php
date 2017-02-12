<?php
	require_once "../include/db.inc.php";

	$selection = $_GET['selectedIssuer'];
	
	if (empty($selection))
	{ 
?>
	    Select Listed company: <form action="charting.php" method="get">
<?php
		$sql = "select concat(issuer, ' (', ticker, ')') from issuers order by issuer asc";
		$rs = $db->Execute($sql);
		print $rs->GetMenu('selectedIssuer');
?>
        <br />
        Start date (blank is all data available) yyyy/mm/dd: <input type="text" name="fromdate" size="10" maxlength="10" value="<?php echo($defaultDateRangeChart); ?>"><br/><br/>
        <b>Type of indicator
        </b>
        <br/>
        <input type="checkbox" checked="checked" name="DM" /> Directional Movement (J. Welles Wilder Jr.)<br/>
        <input type="checkbox" checked="checked" name="BB" /> The Squeeze (J. Bollinger)<br/>
        <input type="checkbox" name="OT" /> Others (Money Flow Index - Relative Strength Index)<br/><br/>
        <input type="submit" value="Select">
<?php
	}
	else
	{
		$fromDate = $_GET["fromdate"];
		$DM = $_GET["DM"];
		$BB = $_GET["BB"];
		$OT = $_GET["OT"];

		$selection = stripslashes($selection);
		$pos = strpos($selection, '(');
		$len = strlen($selection);
		$selectedTicker = substr($selection,$pos+1,$len-1-$pos-1);
		$selectedIssuer = substr($selection,0,$pos-1);

		if (!$fromDate)
		{
			$query = "SELECT min(date) from quotes where ticker = \"$selectedTicker\"";
			$result = $db->Execute($query);
			$fromDate = $result->fields[0];
		}

		$query = "SELECT ticker from quotes where ticker = \"$selectedTicker\"";
		$result = $db->Execute($query);
		 
		if ($result)
		{
			$query = "delete from data_arrays";
			$result = $db->Execute($query);

			$callget_res_page_name = $REQUEST_URI;
			$GLOBALS['callget_res_page_name'] = $callget_res_page_name;

			include_once "../include/get_resolution.inc.php";

			$ranNum = rand($screen_width > $screen_height ? $screen_height : $screen_width, $screen_width >= $screen_height ? $screen_width : $screen_height);

			$generalWidth = ($screen_width*0.90) - 180;
			$linePlotHeight = ($screen_height*0.60) - 65;
			$otherPlotHeight = ($screen_height*0.15) - 65;
			
			$img1 = "linePlot.php?width=$generalWidth&amp;height=$linePlotHeight&amp;issuer=".urlencode($selectedIssuer)."&amp;ticker=$selectedTicker&amp;fromdate=$fromDate&amp;rand=$ranNum&amp;DM=$DM";
?>
			<img src="<?php echo $img1; ?>" alt="Price place" border="0"/>
<?php
			if ($DM == "on")
			{
				$img2 = "dmiPlot.php?width=$generalWidth&amp;height=$otherPlotHeight&amp;issuer=".urlencode($selectedIssuer)."&amp;ticker=$selectedTicker&amp;fromdate=$fromDate&amp;rand=$ranNum";
?>
				<img src="<?php echo $img2; ?>" alt="DMI System" border="0"/>
<?php
			}
			
			if ($BB == "on")
			{
				$img3 = "bbIndPlot.php?width=$generalWidth&amp;height=$otherPlotHeight&amp;issuer=".urlencode($selectedIssuer)."&amp;ticker=$selectedTicker&amp;fromdate=$fromDate&amp;rand=$ranNum";
?>
				<img src="<?php echo $img3; ?>" alt="Bollinger System" border="0"/>
<?php
			}
			
			if ($OT == "on")
			{
				$img4 = "vacPlot.php?width=$generalWidth&amp;height=$otherPlotHeight&amp;issuer=".urlencode($selectedIssuer)."&amp;ticker=$selectedTicker&amp;fromdate=$fromDate&amp;rand=$ranNum";
?>
				<img src="<?php echo $img4; ?>" alt="Additional Indicators" border="0"/>
<?php
			}
?>		
	    <form action="charting.php" method="get">
        <input type="hidden" name="selectedIssuer" value="<?php echo $selection; ?>" />
		<input type="submit" value="ZOOM" />
		<input type="text" name="fromdate" size="10" maxlength="10" value="<?php echo ($defaultDateRangeChart);?>" />
		<b>Type of indicator</b>
        <input type="checkbox" checked="checked" name="DM" /> Directional Movement
        <input type="checkbox" checked="checked" name="BB" /> The Squeeze
        <input type="checkbox" name="OT" /> MFI - RSI
<?php
		}else
			echo "No data for $selectedIssuer <br />";
	}
?>