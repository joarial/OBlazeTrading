<?php

Class yahoo
{
	function get_last_stock_quote($symbol)
	{
	 	$url = sprintf("http://finance.yahoo.com/d/quotes.csv?s=%s&f=sl1d1t1c1ohgv" ,$symbol);
		$fp = @ fopen($url, "r");
		if($fp)
		{
			$array = fgetcsv($fp, 4096 , ',');
			fclose($fp);
			$this->symbol = $array[0];
			$this->last = $array[1];
			$this->date = $array[2];
			$this->time = $array[3];
			$this->changeValue = $array[4];
			$this->open = $array[5];
			$this->dayHigh = $array[6];
			$this->dayLow = $array[7];
			$this->volume = $array[8];
		}
	}
	
	function get_detail_last_stock_quote($symbol)
	{
		$url = sprintf("http://finance.yahoo.com/d/quotes.csv?s=%s&f=snl1d1t1c1p2va2bapomwerr1dyj1", $symbol);
// s n l1 d1 t1 c1 p2 v a2 b a p o m w e r r1 d y j1

		$fp = @ fopen($url, "r");
		if($fp)
		{
			$array = fgetcsv($fp, 4096, ',');
			fclose($fp);
			foreach ($array as $token)
				$token = trim(str_replace("\"", "", $token));
		
			$this->symbol=$array[0];
			$this->name=$array[1];
			$this->last=$array[2];
			$this->date=$array[3];
			$this->time=$array[4];
			$this->changeValue=$array[5];
			$this->changePercent=$array[6];
			$this->volume=$array[7];
			$this->averageVolume=$array[8];
			$this->lastBid=$array[9];
			$this->lastAsk=$array[10];
			$this->previousClose=$array[11];
			$this->open=$array[12];
			$day = split("-",$array[13], 2);
			$this->dayHigh = $day[1];
			$this->dayLow = $day[0];
			$year = split("-",$array[14], 2);
			$this->yearHigh = $year[1];
			$this->yearLow = $year[0];
			$this->EPS=$array[15];
			$this->PE=$array[16];
			$this->divDate=$array[17];
			$this->yield=$array[19];
			$this->divShare=$array[18];
			$this->marketCap=$array[20];
		}
		else
			$this->symbol = "";
	}
	
	function get_afterhours_stock_quote($symbol)
	{
		$url = sprintf("http://finance.yahoo.com/d/quotes.csv?s=%s&f=st5l9c6p4b1a3", $symbol);
		$fp = @ fopen($url, "r");
		if($fp)
		{
			$array = fgetcsv($fp, 4096, ',');
			fclose($fp);
			foreach ($array as $token)
				$token = trim(str_replace("\"", "", $token));
		
			$this->symbol=$array[0];
			$this->date=$array[1];
			$this->ast=$array[2];
			$this->changeValue=$array[3];
			$this->changePercent=$array[4];
			$this->lastBid=$array[5];
			$this->lastAsk=$array[6];
		}
	 }
	function get_stock_fundamentals($symbol)
	{
		$url = sprintf("http://finance.yahoo.com/d/quotes.csv?s=%s&f=st7rs7f6t8e7e8r6r7r5b4p6j4e", $symbol);
// s t7 r s7 f6 t8 e7 e8 r6 r7 r5 b4 p6 j4 e

		$fp = @ fopen($url, "r");
		if($fp)
		{
			$array = fgetcsv($fp, 4096, ',');
			fclose($fp);
			foreach ($array as $token)
				$token = htmlentities(trim(str_replace("\"", "", $token)));
			
			$numtoken = count($array);
	
			$this->symbol=$array[0];
			$this->tickerTrend=$array[1];
			$this->PE=$array[2];
			$this->shortRatio=$array[3];
		
			if ($numtoken == 17)
			{
				$i=7;
				$this->floatShare=$array[4]*1000000+$array[5]*1000+$array[6];
			}
			else if ($numtoken == 18)
			{
				$i=8;
				$this->floatShare=$array[4]*1000000000+$array[5]*1000000+$array[6]*1000+$array[7];
			}
			else if ($numtoken == 16)
			{
				$i=6;
				$this->floatShare=$array[4]*1000+$array[5];
			}
			else if ($numtoken == 15)
			{
				$i=5;
				$this->floatShare=$array[4];
			}
		
			$this->targetPrice=$array[$i++];
			$this->EPSCurrentYear=$array[$i++];
			$this->EPSNextYear=$array[$i++];
			$this->priceEPSCurrentYear=$array[$i++];
			$this->priceEPSNextYear=$array[$i++];
			$this->PEG=$array[$i++];
			$this->bookValue=$array[$i++];
			$this->priceBook=$array[$i++];
			$this->EBITDA=$array[$i++];
			$this->EPS=$array[$i];
		}
	 }
	 
	 function get_historical_stock_quotes($symbol, $fromDate, $toDate)
	 {
	 	$from = explode("-", $fdate);
		$to = explode("-", $ldate);

		$url =	 sprintf("http://ichart.yahoo.com/table.csv?s=%s&d=%s&e=%s&f=%s&g=d&a=%s&b=%s&c=%s&ignore=.csv", $symbol,$to[1]-1,$to[2], $to[0], $from[1]-1, $from[2], $from[0]);

		$fp = @ fopen($url, "r");
		if($fp)
		{
			$num = count($fp);

			for ($i=$num-1;$i>0;$i--)
			{
				$array = explode(',', $fp[$i]);
				$this->open = round($array[1]*100);
				$this->high = round($array[2]*100);
				$this->low = round($array[3]*100);
				$this->close = round($array[4]*100);
				$this->adjclose = round($array[6]*100); 
				$this->ticker = $symbol;
				$this->date = date("Y-m-d", strtotime($array[0]));
				$this->volume = $array[5];
				$this->tPrice = round((($this->open+$this->low+$this->high+$this->close)/4)*($this->adjclose/$this->close));
				$record["date"] = $date;
				$record["open"] = round($this->open*100);
				$record["high"] = round($this->high*100);
				$record["low"]  = round($this->low*100);
				$record["close"] = round($this->close*100);
				$record["volume"] = $this->volume;
				$record["adjclose"] = $this->adjclose;
				$record["tprice"] = $this->tPrice;
			}
		}
	}
}