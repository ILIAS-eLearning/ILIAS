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
* Functions for month.php
*
* Include file month
*
* this file should manage the month functions
*
* @author       Frank Gruemmert <gruemmert@feuerwelt.de>    
* @version		$Id$ 
*/

/**
* 	void function setNavigation($timestamp,$rowSpan)
* 	set variables in the navigation of week 
* 	@param int $timestamp
* 	@global string $month_navigation		( contains the output ) 
*	@global Array $DP_language			( include Languageproperties )
*/

function setNavigation($timestamp)

{

	global $DP_language;

	$Gui		= new Gui();
	$ttd		= new TimestampToDate;
	$rowSpan	= 6;

	$ttd->ttd($timestamp);
	$today		= mktime(0,0,0);
	$lastweek	= strtotime ("last week" , $timestamp) ;
	$nextweek	= strtotime ("+7 days" , $timestamp) ;

	$monthnumber	= $ttd->monthnumber;
	$jahreszahl	= $ttd->year_long;
	$timestamp	= mktime(0,0,0,$monthnumber,1,$jahreszahl);		// first day of the month
	$lastmonth	= strtotime ("last month", $timestamp) ;
	$nextmonth	= strtotime ("+1 month", $timestamp) ;
	$lastyear	= strtotime ("last year" , $timestamp) ;
	$nextyear	= strtotime ("+1 year" , $timestamp) ;

	eval ("\$month_navigation = \"".$Gui->gettemplate("month_navigation")."\";");

	Return $month_navigation;
	
} // end func

/**
* 	function getDayInWeek($week_ts)
* 	get number of a day into a week 
* 	@param int $week_ts
* 	@return string $daynumber
*/
function getDayInWeek($week_ts) 
{

		$ttd		= new TimestampToDate;
		$ttd->ttd($week_ts);

		$daynumber	= $ttd->weekdaynumber ;

		// sunday is day 0 in standart , monday day 1
		// but we need that sunday is day 7 
		if ($daynumber==0) {
			$daynumber=7;
		}

		return $daynumber;
}// end func


/**
* 	function getContent($start_ts, $end_ts)
* 	get Content for the Week View from the sortdates functions 
* 	@param int $begin_ts
* 	@param int $end_ts
* 	@param int $DB			    (object of th db class ) 
* 	@global string $DP_UId		( actual User ID )
* 	@global array $_SESSION		( Array DP_Keywords ( actual Keywords)
* 	@return Array [][][] $DATE
* 			[0]	Dates			( normel Dates )
* 			[1] WholeDates		( one day Dates )
*/
function getContent($start_ts, $end_ts, $DB)

{
	global $DP_UId , $_SESSION ;

	$Dates				= getDateList ($DP_UId, $start_ts, $end_ts, $_SESSION[DP_Keywords], $DB);
	$WholeDates			= getWholeDayDateList ($DP_UId, $start_ts, $end_ts, $_SESSION[DP_Keywords], $DB);
	$DATE[0]=$Dates;
	$DATE[1]=$WholeDates;

	return $DATE;
} // end func

/**
*	void function setDaysInMonth($date_ts, $DATE,  $style)
*	set the Output for normal Dates into the Week view 
*	@param int $date_ts
*	@param Array[][][] $DATE			( Date Data )
*	@param string $style				( to format rows, control variable )
*	@global Array $DP_language			( include Languageproperties )
* 	@global array $DP_CSS				( contains CSS Strings from the conf.gui file )
*	@global Array $_SESSION				( include the Resolution, java script options )
*	@global sting $actualtemplate		( current template )
*	@global string $templatefolder		( current used template folder )
*	@return string $month_float			( contains the output )
*/

function setDaysInMonth($dayinmonth_ts, $DATE, &$style)

{
		global  $DP_language, $DP_CSS, $templatefolder, $actualtemplate, $_SESSION;

		$ttd		= new TimestampToDate;
		$Gui		= new Gui();
		$Dates		= $DATE[0];
		$WholeDates	= $DATE[1];

		$ttd->ttd($dayinmonth_ts);
		
		// if java script disabeld set standard view
		if($_SESSION[DP_ScreenWith] == "" or !$_SESSION[DP_ScreenWith]) {
			$DP_ScreenHeight	= "768";
			$DP_ScreenWith		= "1024" ;
		}else {
			$DP_ScreenHeight	= $_SESSION[DP_ScreenHeight];
			$DP_ScreenWith		= $_SESSION[DP_ScreenWith] ;
		}

		// change colour of months
		$day		= $ttd->day_of_month ;
		if ("01" == $day)
		{
			if ($style == $DP_CSS[tblrow1])	
			{ 
				$style = $DP_CSS[tblrow2]; 
			}
			else								
			{ 
				$style = $DP_CSS[tblrow1]; 
			}
		}

		// change table height for Browse comatibility
		// it depends on the the screen Height and width 
		if (!$DP_ScreenHeight)  
		{
			$height			="15%"	;
		}
		else 
		{
			$height=(15*($DP_ScreenHeight - 150))/100	;
		}

		if (!$DP_ScreenWith) 
		{
			$shorttextmax2		= 9; // max lenght of shorttext in one day dates
			$shorttextmax		= 5; // max lenght of shorttext in normal dates
			$width				="13%"	;
		}
		else 
		{
			// Height an width dedected
			$width=(13*($DP_ScreenWith - 180))/100	;

			switch ($DP_ScreenWith) 
			{
				case '800':
					$datesperdaymax		= 2 ; // max dates in a day 
					$shorttextmax		= 1 ; // max lenght of shorttext in normal dates
					$shorttextmax2		= 4 ; // max lenght of shorttext in one day dates
					break;
				case '1024':
					$datesperdaymax		= 4 ; // max dates in a day 
					$shorttextmax		= 3 ; // max lenght of shorttext in normal dates
					$shorttextmax2		= 9 ; // max lenght of shorttext in one day dates
					break;
				case '1280':
					$datesperdaymax		= 6 ; // max dates in a day 
					$shorttextmax		= 8 ; // max lenght of shorttext in normal dates
					$shorttextmax2		= 14; // max lenght of shorttext in one day dates
					break;
				case '1600':
					$datesperdaymax		= 8 ; // max dates in a day 
					$shorttextmax		= 13; // max lenght of shorttext in normal dates
					$shorttextmax2		= 18; // max lenght of shorttext in one day dates
					break;
				default :
					$datesperdaymax		= 4 ; // max dates in a day 
					$shorttextmax		= 3 ; // max lenght of shorttext in normal dates
					$shorttextmax2		= 9 ; // max lenght of shorttext in one day dates
			}
		}

		$month_float = $month_float."<td width=\"".$width."\" height=\"".$height."\" valign=\"top\" style=\"border-style: solid;  border-width: 1; background-image:url(.".DATEPLANER_ROOT_DIR.$templatefolder."/".$actualtemplate."/images/".$day.".gif); background-repeat:no-repeat ; background-position:center center \" ";

		// if today change background
		if ($dayinmonth_ts >=  mktime(0,0,0) and $dayinmonth_ts <=  mktime(23,59,59)) 
		{
			$month_float = $month_float."$DP_CSS[tblrow1] >";
			$month_float = $month_float."
<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" style=\"border-collapse: collapse\" width=\"100%\" height=\"100%\">
			";
		}
		else 
		{
			$month_float = $month_float.$style.">";
			$month_float = $month_float."
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"border-collapse: collapse\" width=\"100%\" height=\"100%\">
			";
		}

		// if first day in Month display Month name
		if ("01" == $day) 
		{
			$month_float = $month_float."
	<tr>
		<td height=\"8\" style=\"border-style: solid; border-width: 1\" $DP_CSS[tblheader] ><center><span $DP_CSS[small]>$ttd->monthname</span></center></td>
	</tr>
			";
		}

		$month_float = $month_float.'
	<tr>
	    <td height="*" valign="top">
			';

		// filter Dates of the day out of the array
		$endofday_ts = mktime(23,59,59,$ttd->monthnumber,$ttd->day_of_month,$ttd->year_long);  
		
		// 1st - one day dates , cause more important 
		if($WholeDates) 
		{
		for ($i=0;$i<count($WholeDates);$i++) 
		{
			if ($WholeDates[$i][1] >= $dayinmonth_ts and $WholeDates[$i][1] <= $endofday_ts)  
			{
				$datesperday++;
				$ttd->ttd($WholeDates[$i][1]);

				// count sting lenght
				if (strlen ($WholeDates[$i][5]) >= $shorttextmax2) 
				{
					$shortext = substr($WholeDates[$i][5], 0, $shorttextmax2)." ."; 
				}
				else 
				{
					$shortext = $WholeDates[$i][5];
				}
				$alttag = $WholeDates[$i][5];

				$text = $Dates[$i][6];
				$id = rand(1,100);


				if($_SESSION[DP_JSscript] != 1) {
					$month_float = $month_float."<span ".$DP_CSS[small]."><a TITLE=\"".$alttag."\" href=\"dateplaner.php?app=date&timestamp=".$dayinmonth_ts."&date_id=".$WholeDates[$i][0]."&PHPSESSID=".session_id()."\" target=\"_blank\" >".$shortext."</a> </span><br>"; 
				}else {
					$month_float = $month_float."<span ".$DP_CSS[small]."><a onMouseOver=show('".$id."') onMouseOut=hide('".$id."')  href=\"javascript:popup('dateplaner.php?app=date&timestamp=".$dayinmonth_ts."&date_id=".$WholeDates[$i][0]."&PHPSESSID=".session_id()."','Date','width=600,height=650,directories=no,toolbar=no,location=no,menubar=no,scrollbars=yes,status=yes,resizable=yes,dependent=no')\" >".$shortext."</a> </span><br>"; 
					$month_float.= $Gui->setToolTip($starttime, $endtime, $Dates[$i][5], $text, $id );
				}



			}
			if ($datesperdaymax == $datesperday) 
			{
				$month_float = $month_float."<span ".$DP_CSS[small]."><a href=\"dateplaner.php?app=day&timestamp=".$dayinmonth_ts."\">".$DP_language[more]."</a> </span>";
				break ;  
			}
		}
		}

		// 2nd - normal dates  
		if($Dates) 
		{
		for ($i=0;$i<count($Dates);$i++) 
		{
			if ($Dates[$i][1] >= $dayinmonth_ts and $Dates[$i][1] <= $endofday_ts)  
			{
				$datesperday++;
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
				$text = $Dates[$i][6];
				$id = rand(101,200);
				$starttime	= $ttd->hour_long.":".$ttd->minutes ;
				$ttd->ttd($Dates[$i][2]);
				$endtime	= $ttd->hour_long.":".$ttd->minutes ;
				$alttag = $starttime." ".$DP_language[to]." ".$endtime." [ ".$Dates[$i][5]." ]";

				if($_SESSION[DP_JSscript] != 1) {
					$month_float = $month_float."<span ".$DP_CSS[small]."><a TITLE=\"".$alttag."\" href=\"dateplaner.php?app=date&timestamp=".$dayinmonth_ts."&date_id=".$Dates[$i][0]."&PHPSESSID=".session_id()."\" target=\"_blank\" >".$starttime."</a> - ".$shortext."</a> </span><br>"; 
				}else {
					$month_float = $month_float."<span ".$DP_CSS[small]."><a onMouseOver=show('".$id."') onMouseOut=hide('".$id."')   href=\"javascript:popup('dateplaner.php?app=date&timestamp=".$dayinmonth_ts."&date_id=".$Dates[$i][0]."&PHPSESSID=".session_id()."','Date','width=600,height=650,directories=no,toolbar=no,location=no,menubar=no,scrollbars=yes,status=yes,resizable=yes,dependent=no')\" >".$starttime."</a> - ".$shortext." </span><br>"; 
					$month_float.= $Gui->setToolTip($starttime, $endtime, $Dates[$i][5], $text, $id );
				}

		}
			if ($datesperdaymax == $datesperday) {

				$month_float = $month_float."<span ".$DP_CSS[small]."><a href=\"dateplaner.php?app=day&timestamp=".$dayinmonth_ts."\">".$DP_language[more]."</a> </span>";
				break ;  
			}
		}
		}

		// footer in days  

		$month_float = $month_float."
				</center></strong>
	    </td>
	</tr>
	<tr>
		<td height=\"8\">
			<a href=\"javascript:popup('dateplaner.php?app=date&timestamp=".$dayinmonth_ts."&PHPSESSID=$PHPSESSID','Date','width=600,height=650,directories=no,toolbar=no,location=no,menubar=no,scrollbars=yes,status=yes,resizable=yes,dependent=no')\" TITLE=\"".$DP_language[new_doc]."\" ".$DP_CSS[navi_new]." >
				<img border='0' src='.".DATEPLANER_ROOT_DIR.$templatefolder."/".$actualtemplate."/images/blind_1515.gif' width='15' height='15'  align='left' hspace='0'>
			</a>	
			<a href=\"dateplaner.php?app=day&timestamp=".$dayinmonth_ts."\" TITLE=\"".$DP_language[open_day]."\" ".$DP_CSS[navi_open].">
				<img border='0' src='.".DATEPLANER_ROOT_DIR.$templatefolder."/".$actualtemplate."/images/blind_1515.gif' width='15' height='15' align='left' space='0'>
			</a>
		</td>
	</tr>
</table>
		";
		$month_float = $month_float."</td>";

		Return $month_float;

} // end func



/**
* 	void function setMonthView($week_ts)
* 	the Main function of the month view
* 	called from the executed file
* 	@param int $week_ts				( one timestamp in the week, which should be shown ) 
* 	@param string $first_change		( control variable )
* 	@param string $week_s			( control variable , identify the action source)
* 	@param int $DB					(object of th db class ) 
* 	@global string $S_Datum			( contains Date from Table Top )
* 	@global string $style			( to format rows, control variable )
* 	@global array DP_CSS			( contains CSS Strings from the conf.gui file )
*   @return Array $Return
*						[0] string month_navigation	( contains the navigation output )
*						[1] string month_float		( contains the output )
*						[2] string month_string		( contains the month / year name for the output )
*/
function setMonthView($week_ts, $week_s, $first_change, $DB)
{

	global $DP_CSS ;
	srand(microtime()*1000000);

	$ttd					= new TimestampToDate;
	$ttd->ttd($week_ts);
	$weeknumber				= $ttd->weeknumber;
	$monthnumber			= $ttd->monthnumber;
	$jahreszahl				= $ttd->year_long;
	$firstDayInMonth_ts		= mktime(0,0,0,$monthnumber,1,$jahreszahl);		// first day of the month
	$month_string			= $ttd->monthname." ".$ttd->year_long;

	// if there 31 days in month and the 1st is a saturday/sunday 
	// or if there 30 days in month and the 1st is a sunday than we have 6 weeks
	if (($ttd->anzahl_der_tage >= 30 and getDayInWeek($firstDayInMonth_ts) == 7) or 
			($ttd->anzahl_der_tage >= 31 and getDayInWeek($firstDayInMonth_ts) >= 6)) 
	{
		$weeks = 6;
	}
	else 
	{
		$weeks = 5;
	}
	

	// if the first change
	if ($first_change) {
		$month_navigation = setNavigation($firstDayInMonth_ts);
	}else {
		$month_navigation = setNavigation($week_ts);
	}

	$ttd->ttd($week_ts);

	// if the timestamp into the week , set to the first day 
	if ($ttd->weekdaynumber != 1 ) {										
		$week_ts = strtotime ("last Monday", $week_ts );
		$ttd->ttd($week_ts);
		// set 00:00.00 time
		$week_ts = mktime(0,0,0,$ttd->monthnumber,$ttd->day_of_month,$ttd->year_long);  
		$ttd->ttd($week_ts);
	}

	// catch Modnay before, to set month view weekly
	// if the navigation not used, take an other monday before
	if (!$week_s) 
	{												
		$ttd->ttd($firstDayInMonth_ts);
		// if the timestamp into the week , set to the first day 
		if ($ttd->weekdaynumber != 1 ) 
		{										
			$mondaybefore_ts = strtotime ("last Monday", $firstDayInMonth_ts );
		}
		else 
		{
			$mondaybefore_ts = $firstDayInMonth_ts; 
		}
	}
	else 
	{
		$mondaybefore_ts = $week_ts;
	}


	$ttd->ttd($mondaybefore_ts);
	$weeknumber = $ttd->weeknumber;

	$dayinmonth_ts	= $mondaybefore_ts ;
	$style			= $DP_CSS[tblrow1];

				
	// set Month view start and End 
	$start_ts		= $dayinmonth_ts;
	$end_ts			= strtotime ("+".$weeks." week", $week_ts );	
	$DATE			= getContent($start_ts, $end_ts, $DB);

	
	// gerate Data for Output .. pass the month
	while($weeks > 0)
	{ 
		for ($i=0;$i<=7;$i++) 
		{
			if ($i==0) 
			{
				$month_float = $month_float."<tr>\n";
				$month_float = $month_float."<td width=\"4%\" style=\"border-style: solid; border-width: 1\" $DP_CSS[tblrow2] ><center><a href=\"dateplaner.php?app=week&timestamp=".$dayinmonth_ts."\">".$weeknumber."</a><center></td>";
			}
			else 
			{
				$month_float = $month_float.setDaysInMonth($dayinmonth_ts, $DATE, $style);
				$dayinmonth_ts = strtotime ("+1 day", $dayinmonth_ts );
			}
			$ttd->ttd($dayinmonth_ts);
		}
		$weeknumber = $ttd->weeknumber;
		$month_float = $month_float."</tr>\n";
		$weeks--;
	}
	$month_float = $month_float."</tr>\n";

	$Return[0] = $month_navigation;
	$Return[1] = $month_float;
	$Return[2] = $month_string;

	Return  $Return;

}// end func
?>