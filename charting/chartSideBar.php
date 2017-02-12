<?php
 	require_once("../include/yahooClass.php");
 	require_once "../include/db.inc.php";
 			
 	function chartSideBar($symbol)
 	{
	  $quote = new yahoo;
        $quote->get_detail_last_stock_quote($symbol);
        if ($quote->symbol)
        {
	        echo ("\n<table>");
			echo ("\n\t<tr>");
			echo ("\n\t\t<td>Date/Time</td>\n\t\t<td>$quote->date ($quote->time)</td>");
			echo ("\n\t</tr>");
			echo ("\n\t<tr>");
			echo ("\n\t\t<td>Last</td>\n\t\t<td>$quote->last</td>");
			echo ("\n\t</tr>");
		 	echo ("\n\t<tr>");
			echo ("\n\t\t<td>Change</td>\n\t\t<td>$quote->changeValue ($quote->changePercent)</td>");
			echo ("\n\t</tr>");
			echo ("\n\t<tr>");
			echo ("\n\t\t<td>Volume</td>\n\t\t<td>" . number_format($quote->volume,0) . "</td>");
			echo ("\n\t</tr>");
			echo ("\n\t<tr>");
			echo ("\n\t\t<td>Average volume</td>\n\t\t<td>" . number_format($quote->averageVolume,0) . "</td>");
			echo ("\n\t</tr>");
			echo ("\n\t<tr>");
			echo ("\n\t\t<td>Day range</td>\n\t\t<td>$quote->dayLow - $quote->dayHigh</td>");
			echo ("\n\t</tr>");
			echo ("\n\t<tr>");
			echo ("\n\t\t<td><a href=\"http://www.investopedia.com/terms/e/eps.asp\" onclick=\"return popup(this,'EPS')\">EPS</a></td>\n\t\t<td>$quote->EPS</td>");
			echo ("\n\t</tr>");
	 		echo ("\n\t<tr>");
		 	echo ("\n\t\t<td><a href=\"http://www.investopedia.com/terms/p/price-earningsratio.asp\" onclick=\"return popup(this,'PE')\">PE</a></td>\n\t\t<td>$quote->PE</td>");
		 	echo ("\n\t</tr>");
			echo ("\n\t<tr>");
		 	echo ("\n\t\t<td>Market cap.</td><td>$quote->marketCap</td>");
			echo ("\n\t</tr>");
			$quote->get_stock_fundamentals($symbol);
			echo ("\n\t<tr>");
			echo ("\n\t\t<td><a href=\"http://www.investopedia.com/terms/s/shortinterestratio.asp\" onclick=\"return popup(this,'Short Ratio')\">Short ratio</a></td>\n\t\t<td>$quote->shortRatio</td>");
			echo ("\n\t</tr>");
		 	echo ("\n\t<tr>");
		 	echo ("\n\t\t<td><a href=\"http://www.investopedia.com/terms/f/float.asp\" onclick=\"return popup(this,'Float share')\">Float share</a></td>\n\t\t<td>" . number_format($quote->floatShare,0) . "</td>");
		 	echo ("\n\t</tr>");
		 	echo ("\n\t<tr>");
	 		echo ("\n\t\t<td>TargetPrice</td>\n\t\t<td>$quote->targetPrice</td>");
		 	echo ("\n\t</tr>");
		 	echo ("\n\t<tr>");
	 		echo ("\n\t\t<td>EPS current year</td>\n\t\t<td>$quote->EPSCurrentYear</td>");
		 	echo ("\n\t</tr>");
		 	echo ("\n\t<tr>");
	 		echo ("\n\t\t<td>EPS next year</td>\n\t\t<td>$quote->EPSNextYear</td>");
		 	echo ("\n\t</tr>");
		 	echo ("\n\t<tr>");
	 		echo ("\n\t\t<td>Price/EPS current year</td>\n\t\t<td>$quote->priceEPSCurrentYear</td>");
		 	echo ("\n\t</tr>");
		 	echo ("\n\t<tr>");
	 		echo ("\n\t\t<td>Price/EPS next year</td>\n\t\t<td>$quote->priceEPSNextYear</td>");
		 	echo ("\n\t</tr>");
		 	echo ("\n\t<tr>");
	 		echo ("\n\t\t<td><a href=\"http://www.investopedia.com/terms/p/pegratio.asp\" onclick=\"return popup(this,'PEG')\">PEG</a></td>\n\t\t<td>$quote->PEG</td>");
		 	echo ("\n\t</tr>");
		 	echo ("\n\t<tr>");
	 		echo ("\n\t\t<td>Book value</td>\n\t\t<td>$quote->bookValue</td>");
		 	echo ("\n\t</tr>");
		 	echo ("\n\t<tr>");
	 		echo ("\n\t\t<td><a href=\"http://www.investopedia.com/terms/b/bookvalue.asp\" onclick=\"return popup(this,'P/B')\">Price/book value</a></td>\n\t\t<td>$quote->priceBook</td>");
		 	echo ("\n\t</tr>");
		 	echo ("\n\t<tr>");
	 		echo ("\n\t\t<td><a href=\"http://www.investopedia.com/terms/e/ebitda.asp\" onclick=\"return popup(this,'EBITDA')\">EBITDA</a></td>\n\t\t<td>$quote->EBITDA</td>");
		 	echo ("\n\t</tr>");
			echo ("\n</table>");
		}
	}
	
	global $db;
	
	if (!empty($_GET['selectedIssuer']))
	{
		$selection = $_GET['selectedIssuer'];
		$selection = stripslashes($selection);
		$pos = strpos($selection, '(');
		$len = strlen($selection);
		$selectedTicker = substr($selection,$pos+1,$len-1-$pos-1);
		$selectedIssuer = substr($selection,0,$pos-1);

//		chartSideBar($selectedTicker);
		
// Find previous & next stock in the list
// Provide navigation
// Button to mark manually either buy or sell guess		
// http://localhost/MInvestNew/charting/charting.php?selectedIssuer=ALBERTO+CULVER+CO+%28ACV%29&fromdate=2006%2F10%2F17&DM=on&BB=on
		
		$sql = "select concat(issuer, ' (', ticker, ')') from issuers where issuer > \"$selectedIssuer\" order by issuer asc limit 1";
		$nextIssuer = $db->GetOne($sql);
		
		$sql = "select concat(issuer, ' (', ticker, ')') from issuers where issuer < \"$selectedIssuer\" order by issuer desc limit 1";
		$previousIssuer = $db->GetOne($sql);
?>
		<br />
		<center>
		
		<input type="button" value="Previous" onclick="window.location.href='../charting/charting.php?selectedIssuer=<?php echo $previousIssuer; ?>&fromdate=2006%2F10%2F17&DM=on&BB=on'">
		<input type="button" value="Next" onclick="window.location.href='../charting/charting.php?selectedIssuer=<?php echo $nextIssuer; ?>&fromdate=2006%2F10%2F17&DM=on&BB=on'">
		<br />
		<br />
<?php
			$yahooString = "http://finance.yahoo.com/q?s=$selectedTicker";
?>
		<button type="submit" onclick="location.href='<?php echo $yahooString; ?>'"><img src="../images/ma_fi_1.gif" width="120" alt="Yahoo News"></button>
		</form>
		</center>
<?php
	}
?>