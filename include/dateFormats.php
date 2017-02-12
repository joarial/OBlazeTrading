<?php

// switch from mysql to UK format  yyyy-mm-dd hh:ii:ss  to dd/mm/yyyy hh:ii:ss (time is optional)
// allows use of non-numeric seperators so 2000X12X25 is a valid date

function uk_datetime_from_mysql($timestamp, $date_seperator="/", $time_seperator=":", $seperator=" ")
{
	ereg ("([0-9]{2,4})[^0-9]([0-9]{1,2})[^0-9]([0-9]{1,2})", $timestamp, $regs);
	ereg ("([0-9]{2,4})[^0-9]([0-9]{1,2})[^0-9]([0-9]{1,2})[^0-9]([0-9]{1,2})[^0-9]([0-9]{1,2})[^0-9]([0-9]{1,2})", $timestamp, $regs);

	if (sizeof($regs)>1)
	{
		if (sizeof($regs)>4)
		{
			$date=$regs[3].$date_seperator.$regs[2].$date_seperator.$regs[1].$seperator.$regs[4].$time_seperator.$regs[5].$time_seperator.$regs[6];
		}
		else
		{
			$date=$regs[3].$date_seperator.$regs[2].$date_seperator.$regs[1];
		}
	}
	else
	{
		$date="unavailable";
	}
	return $date;
}

// create unix timestamp from mysql datetime - careful of pre-1970
function timestamp_from_mysql($timestamp)
{
	ereg ("([0-9]{2,4})[^0-9]([0-9]{1,2})[^0-9]([0-9]{1,2})", $timestamp, $regs);
	ereg ("([0-9]{2,4})[^0-9]([0-9]{1,2})[^0-9]([0-9]{1,2})[^0-9]([0-9]{1,2})[^0-9]([0-9]{1,2})[^0-9]([0-9]{1,2})", $timestamp, $regs);

	if (sizeof($regs)>1)
	{
		if (sizeof($regs)>4)
		{
			$date=mktime($regs[4],$regs[5],$regs[6],$regs[2],$regs[3],$regs[1]);
		}
		else
		{
			$date=mktime(0,0,0,$regs[2],$regs[3],$regs[1]);
		}
	}
	else
	{
		$date=0;
	}
	return $date;
}

// use the php date function to generate a date/time from a mysql datetime
// basically converts it to a unix datestamp, which date() uses.
function format_mysql_datetime($format, $mysql_datetime)
{
	$unix=timestamp_from_mysql($mysql_datetime);

	$date="&nbsp;";
	if($unix>0)
	{
		$date=date($format, $unix);
	}
	return $date;
}

// converts a uk date format to mysql - uk date must be dd/mm/yyyy with optional time hh:ii:ss
function mysql_datetime_from_uk($timestamp, $date_seperator="-", $time_seperator=":", $seperator=" ")
{
	// dd/mm/yyyy
	ereg ("([0-9]{1,2})[^0-9]([0-9]{1,2})[^0-9]([0-9]{2,4})", $timestamp, $regs);
	// dd/mm/yyyy HH:MM
	ereg ("([0-9]{1,2})[^0-9]([0-9]{1,2})[^0-9]([0-9]{2,4})[^0-9]([0-9]{1,2})[^0-9]([0-9]{1,2})", $timestamp, $regs);
	// dd/mm/yyyy HH:MM:SS
	ereg ("([0-9]{1,2})[^0-9]([0-9]{1,2})[^0-9]([0-9]{1,2})[^0-9]([0-9]{2,4})[^0-9]([0-9]{1,2})[^0-9]([0-9]{1,2})", $timestamp, $regs);

	if (sizeof($regs)>1)
	{
		if (sizeof($regs)>4)
		{
			$date=$regs[3].$date_seperator.$regs[2].$date_seperator.$regs[1].$seperator.$regs[4].$time_seperator.$regs[5].$time_seperator;
			if(empty($regs[6]))
			{
				$date.="00";
			}
			else
			{
				$date.=$regs[6];
			}
		}
		else
		{
			$date=$regs[3].$date_seperator.$regs[2].$date_seperator.$regs[1];
		}
	}
	else
	{
		$date="0";
	}
	return $date;
}

?>