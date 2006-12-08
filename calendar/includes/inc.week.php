<?php	
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source															  |
	|	Dateplaner Modul														  |													
	+-----------------------------------------------------------------------------+
	| Copyright (c) 2004 ILIAS open source & University of Applied Sciences Bremen|
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

/**
* Functions for week.php
*
* Include file Week
*
* this file should manage the week functions
*
* @author       Frank Gruemmert <gruemmert@feuerwelt.de>    
* @version		$Id$ 
*/

/**
* 	void function setNavigation($timestamp,$rowSpan)
* 	set variables in the setNavigation of week 
* 	@param int $timestamp
* 	@param string $rowSpan				( to format the Span of rows, control variable )
*	@global Array $DP_language			( include languageproperties )
* 	@return string $week_navigation		( contains the output ) 
*/
function setNavigation($timestamp,$rowSpan)
{
	global $DP_language;

	$Gui		= new Gui();

	$today		= mktime(0,0,0);
	$lastweek	= strtotime ("last week" , $timestamp) ;
	$nextweek	= strtotime ("+7 days" , $timestamp) ;
	$rowSpan = $rowSpan+1;

	eval ("\$week_navigation = \"".$Gui->getTemplate("week_navigation")."\";");

	Return $week_navigation;

} // end func


/**
* 	void function setDateInTblHead($timestamp)
* 	set the Stings for the date format in the week view at the table top 
* 	@param int $timestamp
* 	@return string $S_Datum 
*/
function setDateInTblHead($timestamp)
{
	$ttd			= new TimestampToDate;

	$ttd->ttd($timestamp);

	$S_Datum[monday_full]		= $ttd->shorttime ;
	$S_Datum[monday_link]		= $timestamp ;
	$S_Datum[week]				= $ttd->weeknumber;

	$ttd->ttd(strtotime ("+1 day" , $timestamp));
	$S_Datum[tuesday_full]		= $ttd->shorttime ;
	$S_Datum[tuesday_link]		= strtotime ("+1 day" , $timestamp) ;

	$ttd->ttd(strtotime ("+2 day" , $timestamp));
	$S_Datum[wednesday_full]	= $ttd->shorttime ;
	$S_Datum[wednesday_link]	= strtotime ("+2 day" , $timestamp) ;

	$ttd->ttd(strtotime ("+3 day" , $timestamp));
	$S_Datum[thursday_full]		= $ttd->shorttime ;
	$S_Datum[thursday_link]		= strtotime ("+3 day" , $timestamp) ;

	$ttd->ttd(strtotime ("+4 day" , $timestamp));
	$S_Datum[friday_full]		= $ttd->shorttime ;
	$S_Datum[friday_link]		= strtotime ("+4 day" , $timestamp) ;

	$ttd->ttd(strtotime ("+5 day" , $timestamp));
	$S_Datum[saturday_full]		= $ttd->shorttime ;
	$S_Datum[saturday_link]		= strtotime ("+5 day" , $timestamp) ;

	$ttd->ttd(strtotime ("+6 day" , $timestamp));
	$S_Datum[sunday_full]		= $ttd->shorttime ;
	$S_Datum[sunday_link]		= strtotime ("+6 day" , $timestamp) ;

	Return	$S_Datum; 

} // end func

/**
* 	function getContent($begin_ts, $end_ts)
* 	get Content for the Week View from the sortdates functions 
* 	@param int $begin_ts
* 	@param int $end_ts
* 	@param int $DB (object of th db class ) 
* 	@global string $DP_UId     ( current User ID )
* 	@global Array $DP_Keywords ( current Keywords)
* 	@return Array [][][] $DATE
* 			[0]	$Dates			( normal Dates )
* 			[1] $WholeDates		( one day Dates )
*/
function getContent($begin_ts, $end_ts, $DB)
{

	global $DP_UId , $_SESSION ;

	$Dates				= getDateList ($DP_UId, $begin_ts, $end_ts, $_SESSION[DP_Keywords], $DB);
	$WholeDates			= getWholeDayDateList ($DP_UId, $begin_ts, $end_ts, $_SESSION[DP_Keywords], $DB);
	$DATE[0]=$Dates;
	$DATE[1]=$WholeDates;
	return $DATE;

} // end func

/**
*	void function setDatesInWeek($date_ts, $Dates, $row_height, &$style)
*	set the Output for normal Dates into the Week view 
*	@param int $date_ts
*	@param int $day_ts			( Day Timestamp )
*	@param Array $Dates			( Date Data )
*	@param string $style		( to format rows, control variable )
*	@param string $row_height	( to format the height of rows, control variable )
*	@global Array $DP_language  ( include Languageproperties )
*	@global	Array $_SESSION		( DP_JSscript is 0 if JavaScript disabled )
*	@return string $week_float  ( contains the output )
*/
function setDatesInWeek($date_ts, $Dates, $day_ts, $row_height, &$style)
{

		global $DP_language, $DP_CSS, $_SESSION;

		$ttd = new TimestampToDate;
		$Gui = new Gui();

		$datesperhalfhourmax	= 4 ; // max dates in a day
		$shorttextmax			= 10; // max lenght of shorttext in normal dates
		$today_style			= $DP_CSS[tblrow1];

		$ttd->ttd($date_ts);
		$day = $ttd->day_of_month ;

		$week_float = $week_float."<td width=\"11%\" height=\"".$row_height."%\" style=\"border-style: solid;  border-width: 1 \"  ";

		// if today change background
		if ($date_ts >=  mktime(0,0,0) and $date_ts <=  mktime(23,59,59)) 
		{
			$week_float = $week_float.$today_style."  >";
		}
		else 
		{
			$week_float = $week_float.$style.">";
		}

		// filter Dates of the day out of the array
		$datebeginend_ts = strtotime ("+30 minutes", $date_ts); 
		for ($i=0;$i< count($Dates);$i++) 
		{
			if ($Dates[$i][1] >= $date_ts and $Dates[$i][1] < $datebeginend_ts)  
			{
				$datesperhalfhour++;
				$ttd->ttd($Dates[$i][1]);
				// count sting lenght
				if (strlen ($Dates[$i][5]) >= $shorttextmax) 
				{
					$shortext = substr($Dates[$i][5], 0, $shorttextmax)." .."; 
				}
				else 
				{
					$shortext = $Dates[$i][5];
				}
				$starttime	= $ttd->hour_long.":".$ttd->minutes ;

				$ttd->ttd($Dates[$i][2]);
				$endtime	= $ttd->hour_long.":".$ttd->minutes ;

				$alttag		= $starttime." bis ".$endtime." [ ".$Dates[$i][5]." ]";

				$text = $Dates[$i][6];
				$id = rand(1,100);

				if($_SESSION[DP_JSscript] != 1 ) {
					$week_float = $week_float."<span ".$DP_CSS[small]."><a  TITLE=\"".$alttag."\" href=\"dateplaner.php?app=date&timestamp=".$day_ts."&date_id=".$Dates[$i][0]."&PHPSESSID=".session_id()."\" target=\"_blank\">".$starttime." - ".$endtime."</a> <br>".$shortext."</span><br>"; 
				}else {
					$week_float = $week_float."<span ".$DP_CSS[small]."><a onMouseOver=show('".$id."') onMouseOut=hide('".$id."')  href=\"javascript:popup('dateplaner.php?app=date&timestamp=".$day_ts."&date_id=".$Dates[$i][0]."&PHPSESSID=".session_id()."','Date','width=600,height=650,directories=no,toolbar=no,location=no,menubar=no,scrollbars=yes,status=yes,resizable=yes,dependent=no')\" >".$starttime." - ".$endtime."</a> <br>".$shortext."</span><br>"; 
					$week_float.= $Gui->setToolTip($starttime, $endtime, $Dates[$i][5], $text, $id );
				}

			}

			if ($datesperhalfhourmax == $datesperhalfhour) 
			{
				$week_float = $week_float."<span ".$DP_CSS[small]."><a href=\"day.php?timestamp=".$date_ts."\">".$DP_language[more]."</a> </span>";
				break ;  
			}
		}

		$week_float = $week_float."</td>";	

		Return $week_float;
} // end func

/**
* 	void function setDayDatesInWeek($date_ts, $Dates, $row_height, &$style)
* 	set the Output for one Day Dates into the Week view 
* 	@param int $date_ts
* 	@param Array $Dates			( Date Data )
* 	@param string $style		( to format rows, control variable )
* 	@param string $row_height	( to format the height of rows, control variable )
* 	@global Array $DP_language  ( include Languageproperties )
* 	@global array $DP_CSS		( contains CSS Strings from the conf.gui file )
*	@global	Array $_SESSION		( $DP_JSscriptis 0 if JavaScript disabled )
* 	@return string $week_float  ( contains the output )
*/
function setDayDatesInWeek($date_ts, $Dates, $row_height, &$style)
{
		global $DP_language, $DP_CSS, $_SESSION;
		$ttd = new TimestampToDate;
		$Gui = new Gui();

		$datesperhalfhourmax	= 4 ; // maximale Termine pro Tag
		$shorttextmax			= 12; // maximale L�nge des shorttextes

		$today_style			= $DP_CSS[tblrow1];

		$ttd->ttd($date_ts);
		$day = $ttd->day_of_month ;

		$week_float = $week_float."<td width=\"11%\" height=\"".$row_height."%\" style=\"border-style: solid;  border-width: 1 \" ";

		// if today change background
		if ($date_ts >=  mktime(0,0,0) and $date_ts <=  mktime(23,59,59)) 
		{
			$week_float = $week_float.$today_style."  >";
		}
		else 
		{
			$week_float = $week_float.$style.">";
		}

		// filter Dates of the day out of the array
		$dayend_ts = strtotime ("+23 hours +59 minutes +59 seconds", $date_ts); 
		for ($i=0;$i< count($Dates);$i++) 
		{
			if ($Dates[$i][1] >= $date_ts and $Dates[$i][2] <= $dayend_ts)  
			{
				$datesperhalfhour++;
				$ttd->ttd($Dates[$i][1]);

				// count sting lenght
				if (strlen ($Dates[$i][5]) >= $shorttextmax) 
				{
					$shortext = substr($Dates[$i][5], 0, $shorttextmax)." .."; 
				}
				else 
				{
					$shortext = $Dates[$i][5];
				}
				$alttag		= " [ ".$Dates[$i][5]." ]";

				$text = $Dates[$i][6];
				$id = rand(100,200);


				if($_SESSION[DP_JSscript] != 1 ) {
					$week_float = $week_float."<span ".$DP_CSS[small]."><a  TITLE=\"".$alttag."\" href=\"dateplaner.php?app=date&timestamp=".$day_ts."&date_id=".$Dates[$i][0]."&PHPSESSID=".session_id()."\" target=\"_blank\"><b>".$shortext."</b></a> </span><br>"; 
				}else {
					$week_float = $week_float."<span ".$DP_CSS[small]."><a onMouseOver=show('".$id."') onMouseOut=hide('".$id."')  href=\"javascript:popup('dateplaner.php?app=date&timestamp=".$date_ts."&date_id=".$Dates[$i][0]."&PHPSESSID=".session_id()."','Date','width=600,height=650,directories=no,toolbar=no,location=no,menubar=no,scrollbars=yes,status=yes,resizable=yes,dependent=no')\" ><b>".$shortext."</b></a></span><br>"; 
					$week_float.= $Gui->setToolTip($starttime, $endtime, $Dates[$i][5], $text, $id );
				}



			}
			if ($datesperhalfhourmax == $datesperhalfhour) 
			{
				$week_float = $week_float."<span ".$DP_CSS[small]."><a href=\"day.php?timestamp=".$date_ts."\">".$DP_language[more]."</a> </span>";
				break ;  
			}
		}

		$week_float = $week_float."</td>";
		Return $week_float;
} // end func

/**
* 	void function setWeekView($week_ts, $DB)
* 	the Main function of the week view
* 	called from the executed file
* 	@param int $week_ts				( one timestamp in the week, which should be shown ) 
* 	@param int $DB					(object of th db class ) 
* 	@global array $S_Datum			( contains Date from Table Top )
* 	@global array $DP_language		( include Languageproperties )
* 	@global array $DP_CSS			( contains CSS Strings from the conf.gui file )
* 	@global array $_SESSION 		( DP_Starttime include Start Time of during on day in week view and 
*                                     DP_Endtimeinclude End Time of during on day in week view)
*   @return Array $Return
*						[0] string week_navigation	( contains the navigation output )
*						[1] string week_float		( contains the output )
*						[2] array S_Datum			( contains Date from Table Top )
*/
function setWeekView($week_ts, $DB)

{
	global $DP_language, $DP_CSS, $_SESSION;

	srand(microtime()*1000000);

	// time period for view

	$from_time	= $_SESSION[DP_Starttime]; 
	$to_time	= $_SESSION[DP_Endtime];

	$ttd = new TimestampToDate;

	$ttd->ttd($week_ts);

	// if the timestamp into the week , set to the first day 
	if ($ttd->weekdaynumber != 1 ) 
	{										
		$week_ts = strtotime ("last Monday", $week_ts );
		$ttd->ttd($week_ts);
	}

	$week_ts = mktime(0,0,0,$ttd->monthnumber,$ttd->day_of_month,$ttd->year_long);  
	$ttd->ttd($week_ts);

	// set Week view start and End time
	$start_ts	= $week_ts;
	$end_ts		= strtotime ("+6 days 23 hours 59 minutes 59 seconds", $week_ts );	
	$DATE		= getContent($start_ts, $end_ts, $DB);
	$Dates		= $DATE[0];
	$WholeDates	= $DATE[1];

	// take care that no date begin or end after time zone above
	// if it, enhance time zone
	if($Dates) 
	{
	for ($i=0;$i< count($Dates);$i++) 
	{
		$ttdend = new TimestampToDate;
		$ttdend->ttd($Dates[$i][2]);

		$end = $ttdend->hour_long.":".$ttdend->minutes.":".$ttdend->seconds;

		$ttd->ttd($Dates[$i][1]);
		$begin = $ttd->hour_long.":".$ttd->minutes.":".$ttd->seconds;

		if ($ttd->hour_short <= (int)$from_time )  
		{
			$from_time = $ttd->hour_short;
		}
		if ($ttdend->hour_short >= (int)$to_time) 
		{
			$to_time = $ttdend->hour_short+1;
		}
	}
	}

	// count rows for view
	$rows		= (($to_time - $from_time)*2)+1;
	// count rows height , best compatibility
	$row_height = 100/$rows;

	$week_navigation	= setNavigation($week_ts,$rows);
	$S_Datum			= setDateInTblHead($week_ts) ;

	$monatstag_ts	= $week_ts;
    $style			= $DP_CSS[tblrow2];

	// 1st - one day dates , cause more important 
	$c_rows	= 0;
	for ($i=0;$i<=7;$i++) 
	{

		if ($i==0) 
		{
			$week_float = $week_float."<tr >\n";
			$week_float = $week_float."<td width=\"4%\" height=\"".$row_height."%\" style=\"border-style: solid;  border-width: 1\" $DP_CSS[tblrow2] ><center>".$DP_language[o_day_date]."<center></td>";
		}
		else 
		{
			$week_float = $week_float.setDayDatesInWeek($monatstag_ts, $WholeDates, $row_height, $style);
			$monatstag_ts = strtotime ("+1 day", $monatstag_ts );
		}
			$ttd->ttd($monatstag_ts);
	}

	$week_float		= $week_float."</tr>\n";
	$c_rows++;
	$ttd->ttd($week_ts);
	$week_ts		= mktime($from_time,0,0,$ttd->monthnumber,$ttd->day_of_month,$ttd->year_long);
	$monatstag_ts	= $week_ts;
	$style			= $DP_CSS[tblrow2];
	$day_ts			= mktime(0,0,0,$ttd->monthnumber,$ttd->day_of_month,$ttd->year_long);
	
	// 2nd - normal dates  
	while($c_rows < $rows)
	{ 
		for ($i=0;$i<=7;$i++) 
		{
			if ($i==0) 
			{
			// set time in left row
				$time = (($c_rows-1)/2)+$from_time;
				if (floor($time)!=0) 
				{
					if (($time/floor($time)) > 1) 
					{
						$time = floor ($time).":30";
					}
					else 
					{
						$time = $time.":00";
					}

				}
				else 
				{
					if (round ($time)==1) 
					{
						$time = "0:30";
					}
					else 
					{
						$time = "0:00";
					}
				}
				$week_float = $week_float."<tr >\n";
				$week_float = $week_float."<td width=\"4%\" height=\"".$row_height."%\" style=\"border-style: solid;  border-width: 1\" $DP_CSS[tblrow2] ><center>".$time."<center></td>";
			}
			else 
			{
				$week_float	= $week_float.setDatesInWeek($monatstag_ts, $Dates, $day_ts, $row_height, $style);
				$monatstag_ts = strtotime ("+1 day", $monatstag_ts );

			}
			$ttd->ttd($monatstag_ts);
			$day_ts			= mktime(0,0,0,$ttd->monthnumber,$ttd->day_of_month,$ttd->year_long);
		}
		$week_ts		= strtotime ("+30 minutes", $week_ts);	
		$monatstag_ts	= $week_ts ;
		$week_float		= $week_float."</tr>\n";
		$c_rows++;
	}
	$week_float = $week_float."</tr>\n";

	$Return[0] = $week_navigation;
	$Return[1] = $week_float;
	$Return[2] = $S_Datum;

	Return $Return;
}//end func
?>