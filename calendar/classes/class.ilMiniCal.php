<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source						              |
	|	Dateplaner Modul						      |													
	+-----------------------------------------------------------------------------+
	| Copyright (c) 2005 ILIAS open source 					      |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+

*/


// get ilias conectivity 

class ilMiniCal {
	

	var $ilias;
	var $tpl;
	var $lng;

	var $cal_month; // private variable containing the month that will be displayed
	var $cal_year;
	var $cal;

	
	function ilMiniCal()
	{
		global $ilias, $tpl, $lng;

		$this->ilias =& $ilias;
		$this->tpl = & $tpl;
		$this->lng =& $lng;
	}

	
	function show($month, $year)
	{
	
		$cal_month = $month;
		$cal_year = $year;
		$cal_day = date(j);
	
		$lastday		= strftime("%d", mktime (0,0,0,$cal_month,0,$cal_year));	// was last month's last day the 30rd, 31st, ...?
		$firstday		= strftime ("%u", mktime(0,0,0,$cal_month,1,$cal_year)) - 2;	// which day is this month's first day ? (Monday,...)
		
		$startday = $lastday - $firstday;
	
		if ($lastday != 31 || $cal_month == "8" || $cal_month == "1")
		{
			$days_in_month = 31;				// decide how many days the actual month has
		}
		else
		{	
			if ($cal_month != 02)
			{
				$days_in_month = 30;
			}
			else
			{
				if ($cal_year % 4 == 0)
				{
					$days_in_month = 29;		// decide we're about to display a leap year and need to adjust the number of days
				}	
				else
				{
					$days_in_month = 28;
				}
			}
	
		}
		$next = $cal_year + 1;
		$last = $cal_year - 1;
		$next_ts = mktime (0,0,0,$cal_month,1,$next);			// timestamps for displaying day views for next and last year button
		$last_ts = mktime (0,0,0,$cal_month,1,$last);
	
		// beginning of html-code for table 	
		$cal = "<table tableborder='1' rules='none' frame='box' bgcolor='#FFFFFF' style='margin-right: 10px;'><tr><td class=\"il_CalNextMonth\">".
			"<a href=\"dateplaner.php?app=".$_GET["app"]."&month=$cal_month&year=$last&timestamp=$last_ts\">".
			" < </a></td> <td align=\"center\" class=\"il_CalMonth\" colspan=\"6\">".
			$this->lng->txt("short_".$cal_month)." $cal_year </td><td class=\"il_CalNextMonth\"> <a href=\"dateplaner.php?app=".
			$_GET["app"]."&month=$cal_month&year=$next&timestamp=$next_ts\"> > </a> </td></tr>\n";
		$cal = $cal."<tr><td></td>";
		for($i = 1; $i <= 12; $i++)
		{
			if ($i <= 9)
			{
				$tmp = "0".$i;     	// standardizes content of $tmp to two digits representing months
			}
			else
			{	
				$tmp = $i;
			}
			$ts = mktime (0,0,0,$tmp,1,$cal_year);		// create timestamps to enable dayviews for months in actual year
			$cal = $cal."<td class=\"il_CalShortMonth\"><a href=\"dateplaner.php?app=".$_GET["app"]."&month=".$tmp."&year=".$cal_year."&timestamp=".$ts."\">".$this->lng->txt("short_".$tmp)."</td>";
			if ($i == 6) $cal = $cal."<td></td></tr> \n <tr><td></td>";
			
		}
		$cal = $cal."<td></td></tr> \n ";
		// html code for displaying days of week using ilias lng-module
		$cal = $cal."<tr bgcolor='#AAAAAA'><td class=\"il_CalShortDayOfWeek\">".$this->lng->txt("wk_short").
			"</td><td class=\"il_CalShortDayOfWeek\">".$this->lng->txt("Mo_short")."</td><td class=\"il_CalShortDayOfWeek\">".$this->lng->txt("Tu_short").
			"</td><td class=\"il_CalShortDayOfWeek\">".$this->lng->txt("We_short")."</td>";
		$cal = $cal."<td class=\"il_CalShortDayOfWeek\">".$this->lng->txt("Th_short").
			"</td><td class=\"il_CalShortDayOfWeek\">".$this->lng->txt("Fr_short").
			"</td><td class=\"il_CalShortDayOfWeek\">".$this->lng->txt("Sa_short").
			"</td><td class=\"il_CalShortDayOfWeek\">".$this->lng->txt("Su_short")."</td></tr>";
		
		$kw = strftime ("%V", mktime(0,0,0,$cal_month,1,$cal_year)); // get actual number of week to start with
	
		$counter = 1; // general counter; calendar will always dislpay 42 days
		$day = 1;	// variable needed for displaying first week, if first week is split into different month
		$ts_week = mktime (0,0,0,$cal_month,1,$cal_year); // timestamp that contains the week and is updated every loop inside brackets
	
		// loop to generate html-code for actual weeks and days in month
		for ($x = 1; $x <= 6; $x++)
		{		
				$cal = $cal. " <tr><td bgcolor='#AAAAAA' class=\"il_CalShortWeek\"><a href=\"dateplaner.php?app=week&month=".$cal_month."&year=".$cal_year."&timestamp=".$ts_week."\">$kw</td> ";
				$ts_week = strtotime ("+1 week", $ts_week );
	
				if ($x == 1)
				{				
					$tmp = $startday;
					while ($tmp <= $lastday)
					{
						$cal = $cal." <td class=\"il_CalDay\"> $tmp</td> "; // displays days in old month if first day in month isn't Monday
						$tmp++;
						$counter++;
					}
					
					$empty_string ="";
					while ($counter <=7)
					{
						$ts_day = mktime (0,0,0, $cal_month, $day, $cal_year);  // timestamps to display different day views
						if ($day == $cal_day&& $cal_month == date (m) && $cal_year == date (Y)) $empty_string = " bgcolor='FF8000'";
	
						// html-code for table entry and according link
						$cal = $cal." <td class=\"il_CalDay\" ".$empty_string."><a href=\"dateplaner.php?app=day&month=".$cal_month."&year=".$cal_year."&timestamp=".$ts_day."\">$day</a></td> ";
						$day++;
						$tmp++;
						$counter++;
						$empty_string ="";
							
					}
					if ($kw >= 52 ) $kw = 0; // adjusts week numbers if you display january or december 
					$day = 1; // resets variable...
					
				}
				else			
				{
					for ($c = 7 - $firstday + ($x-2) * 7; $c <= 6 - $firstday + ($x-1) *7 && $counter <= 42; $c++) // c represents the actual day in month
					{
						if ($c <= $days_in_month)
						{
							$ts_day = mktime (0,0,0, $cal_month, $c, $cal_year);  // timestamps to display different day views
							if ($c == $cal_day && $cal_month == date (m) && $cal_year == date (Y)) $empty_string = " bgcolor='FF8000'";
													
							// html-code for table entry and according link
							$cal = $cal. "<td class=\"il_CalDay\" ".$empty_string."><a href=\"dateplaner.php?app=day&month=".$cal_month."&year=".$cal_year."&timestamp=".$ts_day."\"> $c</a></td>";
							$counter++;
							$empty_string ="";
						}
						else  // clause displays days that belong to next month
						{
							$cal = $cal. "<td class=\"il_CalDay\"> $day</td>";
							$day++;
							$counter++;
						}								
					} 
				}			
				$cal = $cal. "</tr> \n "; // complete table row
				$kw++;					// update weeknumber
		}
		$cal = $cal." </table>"; // complete table
		
		return $cal;
	
	}

}

?>
