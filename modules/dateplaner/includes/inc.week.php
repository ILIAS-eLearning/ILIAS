<?php	
/**
* Include file Week
*
* this file should manage the week functions
* 
* @author Frank Grümmert 
* 
* @version $Id: inc.week.php,v 0.9 2003/06/11 
* @package application
* @access public
*
*/

/**
* 	void function setNavigation($timestamp,$rowSpan)
* 	@description : stet variables in the setNavigation of week 
* 	@param int timestamp
* 	@param string rowSpan   ( to format the Span of rows, control variable )
*	@global Array CSCW_language ( include Languageproperties )
* 	@return string week_navigation   ( contains the output ) 
*/
function setNavigation($timestamp,$rowSpan)
{
	global $CSCW_language;

	$Gui		= new Gui();

	$today		= mktime(0,0,0);
	$lastweek	= strtotime ("last week" , $timestamp) ;
	$nextweek	= strtotime ("next week" , $timestamp) ;
	$rowSpan = $rowSpan+1;

	eval ("\$week_navigation = \"".$Gui->getTemplate("week_navigation")."\";");

	Return $week_navigation;

} // end func


/**
* 	void function setDateInTblHead($timestamp)
* 	@description : set the Stings for the date format in the week view at the table top 
* 	@param int timestamp
* 	@return string S_Datum 
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
* 	@description : get Content for the Week View from the sortdates functions 
* 	@param int begin_ts
* 	@param int end_ts
* 	@global string CSCW_UId     ( actual User ID )
* 	@global Array CSCW_Keywords ( actuel Keywords)
* 	@return Array [][][] 
* 			[0]	Dates			( normel Dates )
* 			[1] WholeDates		( one day Dates )
*/
function getContent($begin_ts, $end_ts)
{

	global $CSCW_UId , $CSCW_Keywords ;

	$Dates				= getDateList ($CSCW_UId, $begin_ts, $end_ts, $CSCW_Keywords);
	$WholeDates			= getWholeDayDateList ($CSCW_UId, $begin_ts, $end_ts, $CSCW_Keywords);
	$DATE[0]=$Dates;
	$DATE[1]=$WholeDates;
	return $DATE;

} // end func

/**
*	void function setDatesInWeek($date_ts, $Dates, $row_height, &$style)
*	@description : set the Output for normal Dates into the Week view 
*	@param int date_ts
*	@param int day_ts			( Day Timestamp )
*	@param Array Dates			( Date Data )
*	@param string style			( to format rows, control variable )
*	@param string row_height	( to format the height of rows, control variable )
*	@global Array CSCW_language ( include Languageproperties )
*	@global	bol $CSCW_JSscript  ( is 0 if JavaScript disabled )
*	@return string week_float   ( contains the output )
*/
function setDatesInWeek($date_ts, $Dates, $day_ts, $row_height, &$style)
{

		global $CSCW_language, $CSCW_CSS, $CSCW_JSscript;

		$ttd = new TimestampToDate;
		$Gui = new Gui();

		$datesperhalfhourmax	= 4 ; // max dates in a day
		$shorttextmax			= 10; // max lenght of shorttext in normal dates
		$today_style			= $CSCW_CSS[tblrow1];

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

				if($CSCW_JSscript != 1 ) {
					$week_float = $week_float."<span ".$CSCW_CSS[small]."><a  TITLE=\"".$alttag."\" href=\"date.php?timestamp=".$day_ts."&date_id=".$Dates[$i][0]."&PHPSESSID=".session_id()."\" target=\"_blank\">".$starttime." - ".$endtime."</a> <br>".$shortext."</span><br>"; 
				}else {
					$week_float = $week_float."<span ".$CSCW_CSS[small]."><a onMouseOver=show('".$id."') onMouseOut=hide('".$id."')  href=\"javascript:popup('date.php?timestamp=".$day_ts."&date_id=".$Dates[$i][0]."&PHPSESSID=".session_id()."','Date','width=600,height=650,directories=no,toolbar=no,location=no,menubar=no,scrollbars=yes,status=yes,resizable=yes,dependent=no')\" >".$starttime." - ".$endtime."</a> <br>".$shortext."</span><br>"; 
					$week_float.= $Gui->setToolTip($starttime, $endtime, $shortext, $text, $id );
				}

			}

			if ($datesperhalfhourmax == $datesperhalfhour) 
			{
				$week_float = $week_float."<span ".$CSCW_CSS[small]."><a href=\"day.php?timestamp=".$date_ts."\">".$CSCW_language[more]."</a> </span>";
				break ;  
			}
		}

		$week_float = $week_float."</td>";	

		Return $week_float;
} // end func

/**
* 	void function setDayDatesInWeek($date_ts, $Dates, $row_height, &$style)
* 	@description : set the Output for one Day Dates into the Week view 
* 	@param int date_ts
* 	@param Array Dates			( Date Data )
* 	@param string style		( to format rows, control variable )
* 	@param string row_height   ( to format the height of rows, control variable )
* 	@global Array CSCW_language ( include Languageproperties )
* 	@global array CSCW_CSS		( contains CSS Strings from the conf.gui file )
*	@global	bol $CSCW_JSscript ( is 0 if JavaScript disabled )
* 	@return string week_float   ( contains the output )
*/
function setDayDatesInWeek($date_ts, $Dates, $row_height, &$style)
{
		global $CSCW_language, $CSCW_CSS, $CSCW_JSscript;
		$ttd = new TimestampToDate;
		$Gui = new Gui();

		$datesperhalfhourmax	= 4 ; // maximale Termine pro Tag
		$shorttextmax			= 12; // maximale Länge des shorttextes

		$today_style			= $CSCW_CSS[tblrow1];

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


				if($CSCW_JSscript != 1 ) {
					$week_float = $week_float."<span ".$CSCW_CSS[small]."><a  TITLE=\"".$alttag."\" href=\"date.php?timestamp=".$day_ts."&date_id=".$Dates[$i][0]."&PHPSESSID=".session_id()."\" target=\"_blank\"><b>".$shortext."</b></a> </span><br>"; 
				}else {
					$week_float = $week_float."<span ".$CSCW_CSS[small]."><a onMouseOver=show('".$id."') onMouseOut=hide('".$id."')  href=\"javascript:popup('date.php?timestamp=".$date_ts."&date_id=".$Dates[$i][0]."&PHPSESSID=".session_id()."','Date','width=600,height=650,directories=no,toolbar=no,location=no,menubar=no,scrollbars=yes,status=yes,resizable=yes,dependent=no')\" ><b>".$shortext."</b></a></span><br>"; 
					$week_float.= $Gui->setToolTip($starttime, $endtime, $shortext, $text, $id );
				}



			}
			if ($datesperhalfhourmax == $datesperhalfhour) 
			{
				$week_float = $week_float."<span ".$CSCW_CSS[small]."><a href=\"day.php?timestamp=".$date_ts."\">".$CSCW_language[more]."</a> </span>";
				break ;  
			}
		}

		$week_float = $week_float."</td>";
		Return $week_float;
} // end func

/**
* 	void function setWeekView($week_ts)
* 	@description : the Main function of the week view
* 	@description : called from the executed file
* 	@param int week_ts				( one timestamp in the week, which should be shown ) 
* 	@global array S_Datum			( contains Date from Table Top )
* 	@global array CSCW_language		( include Languageproperties )
* 	@global array CSCW_CSS			( contains CSS Strings from the conf.gui file )
* 	@global string CSCW_Starttime	( include Start Time of during on day in week view )
* 	@global string CSCW_Endtime		( include End Time of during on day in week view )
*   @return Array Return
*						[0] string week_navigation	( contains the navigation output )
*						[1] string week_float		( contains the output )
*						[2] array S_Datum			( contains Date from Table Top )
*/
function setWeekView($week_ts)

{
	global $CSCW_language, $CSCW_CSS, $CSCW_Starttime, $CSCW_Endtime;

	srand(microtime()*1000000);

	// time period for view

	$from_time	= $CSCW_Starttime; 
	$to_time	= $CSCW_Endtime;

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
	$DATE		= getContent($start_ts, $end_ts);
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

	$week_navigation = setNavigation($week_ts,$rows);
	$S_Datum = setDateInTblHead($week_ts) ;

	$monatstag_ts	= $week_ts;
    $style			= $CSCW_CSS[tblrow2];

	// 1st - one day dates , cause more important 
	$c_rows	= 0;
	for ($i=0;$i<=7;$i++) 
	{

		if ($i==0) 
		{
			$week_float = $week_float."<tr >\n";
			$week_float = $week_float."<td width=\"4%\" height=\"".$row_height."%\" style=\"border-style: solid;  border-width: 1\" $CSCW_CSS[tblrow2] ><center>".$CSCW_language[o_day_date]."<center></td>";
		}
		else 
		{
			$week_float = $week_float.setDayDatesInWeek($monatstag_ts, $WholeDates, $row_height, &$style);
			$monatstag_ts = strtotime ("+1 day", $monatstag_ts );
		}
			$ttd->ttd($monatstag_ts);
	}

	$week_float		= $week_float."</tr>\n";
	$c_rows++;
	$ttd->ttd($week_ts);
	$week_ts		= mktime($from_time,0,0,$ttd->monthnumber,$ttd->day_of_month,$ttd->year_long);
	$monatstag_ts	= $week_ts;
	$style			= $CSCW_CSS[tblrow2];
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
				$week_float = $week_float."<td width=\"4%\" height=\"".$row_height."%\" style=\"border-style: solid;  border-width: 1\" $CSCW_CSS[tblrow2] ><center>".$time."<center></td>";
			}
			else 
			{
				$week_float	= $week_float.setDatesInWeek($monatstag_ts, $Dates, $day_ts, $row_height, &$style);
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
