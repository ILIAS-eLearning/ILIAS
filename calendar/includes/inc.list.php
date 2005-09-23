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
* Functions for list.php
*
* Include file List
*
* this file should manage the list functions
*
* @author       Frank Gruemmert <gruemmert@feuerwelt.de>    
* @version		$Id$ 
* @module       inc.list.php                            
* @modulegroup  dateplaner                    
* @package		dateplaner-functions
*/

/**
* 	void function setNavigation($fromtime_ts, $totime_ts)
* 	 stet variables in the setNavigation of list 
* 	@param int $fromtime_ts
* 	@param int $totime_ts   
* 	@global string $actualtemplate  ( name of actual skin )
* 	@global string $templatefolder  ( name of template folder )
* 	@global Array $DP_language		( include Languageproperties )
* 	@return string $list_navigation ( contains the output for the setNavigation )
*/
function setNavigation($fromtime_ts, $totime_ts)
	{
		global $templatefolder, $actualtemplate , $DP_language;
		$Gui		= new Gui();
		$ttd		= new TimestampToDate;
		$ttd->ttd($fromtime_ts);
		$date2		= $ttd->day_of_month."/".$ttd->monthnumber_long."/".$ttd->year_long ;
		$ttd->ttd($totime_ts);
		$date4		= $ttd->day_of_month."/".$ttd->monthnumber_long."/".$ttd->year_long ;
		$popupcall_1 = '
<script language=JavaScript>
			var cal2 = new CalendarPopup();  
			cal2.showYearNavigation(); cal2.setMonthNames(\''.$DP_language[long_01].'\',\''.$DP_language[long_02].'\',\''.$DP_language[long_03].'\',\''.$DP_language[long_04].'\',\''.$DP_language[long_05].'\',\''.$DP_language[long_06].'\',\''.$DP_language[long_07].'\',\''.$DP_language[long_08].'\',\''.$DP_language[long_09].'\',\''.$DP_language[long_10].'\',\''.$DP_language[long_11].'\',\''.$DP_language[long_12].'\'); 
		    cal2.setDayHeaders(\''.$DP_language[Su_short].'\',\''.$DP_language[Mo_short].'\',\''.$DP_language[Tu_short].'\',\''.$DP_language[We_short].'\',\''.$DP_language[Th_short].'\',\''.$DP_language[Fr_short].'\',\''.$DP_language[Sa_short].'\');
			cal2.setWeekStartDay(1);
			cal2.setTodayText("'.$DP_language[today].'");
</script>
		';
		$popupcall_2 = '
<script language=JavaScript >
			var cal4 = new CalendarPopup();	cal4.setMonthNames(\''.$DP_language[long_01].'\',\''.$DP_language[long_02].'\',\''.$DP_language[long_03].'\',\''.$DP_language[long_04].'\',\''.$DP_language[long_05].'\',\''.$DP_language[long_06].'\',\''.$DP_language[long_07].'\',\''.$DP_language[long_08].'\',\''.$DP_language[long_09].'\',\''.$DP_language[long_10].'\',\''.$DP_language[long_11].'\',\''.$DP_language[long_12].'\'); 
		    cal4.setDayHeaders(\''.$DP_language[Su_short].'\',\''.$DP_language[Mo_short].'\',\''.$DP_language[Tu_short].'\',\''.$DP_language[We_short].'\',\''.$DP_language[Th_short].'\',\''.$DP_language[Fr_short].'\',\''.$DP_language[Sa_short].'\');
			cal4.setWeekStartDay(1);
			cal4.setTodayText("'.$DP_language[today].'");			
			cal4.showYearNavigation(); 
</script>
		';

		eval ("\$list_navigation = \"".$Gui->getTemplate("list_navigation")."\";");

		Return $list_navigation;
} // end func

/**
* 	function getContent($start_ts, $end_ts)
* 	get Content for the Week View from the sortdates functions 
* 	@param int $start_ts
* 	@param int $end_ts
* 	@global string $DP_UId     ( actual User ID )
* 	@global array $_SESSION		--> Array DP_Keywords ( current Keywords)
* 	@return Array[][][] $DATE
* 			[0]	Dates			( normel Dates )
* 			[1] WholeDates		( one day Dates )
*/
function getContent($start_ts, $end_ts, $DB)
{
	global  $_SESSION, $DP_UId;
	$Dates				= getDateList ($DP_UId, $start_ts, $end_ts, $_SESSION[DP_Keywords], $DB);
	$WholeDates			= getWholeDayDateList ($DP_UId, $start_ts, $end_ts, $_SESSION[DP_Keywords], $DB);

	$DATE[0]=$Dates;
	$DATE[1]=$WholeDates;
	return $DATE;
} // end func

/**
* 	function print_viewDate_list()
* 	the second Main function of the list view , to print out the List result
* 	@param int $fromtime_ts
* 	@param int $totime_ts   
* 	@global array $DP_language		( include Languageproperties )
* 	@global array $DP_CSS			( contains CSS Strings from the conf.gui file )
*   @return string $list_print_float ( contains the output )
*/
function printDateList($fromtime_ts, $totime_ts, $DB)
{
	global $DP_language, $DP_CSS ;


	$ttd				= new TimestampToDate;
	$list_print_float	= "";
	

	if ($totime_ts == False) {
		$totime_ts = mktime(23,59,59);	
	}

	if($fromtime_ts == $totime_ts) 
	{
		$ttd->ttd($fromtime_ts);
		$totime_ts = mktime(23,59,59,$ttd->monthnumber,$ttd->day_of_month,$ttd->year_long); 
	}

	$DATE				= getContent($fromtime_ts, $totime_ts, $DB);
	$Dates				= $DATE[0];
	$WholeDates			= $DATE[1];
	
	// 1st - one day dates , cause more important 
	$list_print_float = $list_print_float."
  <tr>
    <td width=\"100%\" colspan=\"4\" $DP_CSS[tblrow1] >".$DP_language[extra_dates]."</td>
  </tr>
	";
	if ($WholeDates == False) 
	{
		$list_print_float = $list_print_float."
  <tr>
    <td width=\"100%\" colspan=\"4\" >".$DP_language[no_entry]."</td>
  </tr>
		";
	}
	else 
	{
		for ($i=0;$i<count($WholeDates);$i++) 
		{
			$list_print_float = $list_print_float."
		<tr>
			<td width=\"10%\">
			";
			$ttd->ttd($WholeDates[$i][1]);
			$list_print_float = $list_print_float.$ttd->day_of_month.".".$ttd->monthname;
			$list_print_float = $list_print_float."
			</td><td width=\"10%\">
			";
			$ttd->ttd($WholeDates[$i][2]);
			$list_print_float = $list_print_float.$ttd->day_of_month.".".$ttd->monthname;
			$list_print_float = $list_print_float."
			</td><td width=\"10%\">
			";

			$list_print_float = $list_print_float.$WholeDates[$i][5];
			$list_print_float = $list_print_float."
			</td><td width=\"10%\">
			";
			$list_print_float = $list_print_float.$WholeDates[$i][6];
			$list_print_float = $list_print_float."
			</td>
		</tr>
			";
		}
	}
	// end onde day dates

	// 2nd - normal dates  
	$list_print_float = $list_print_float."
  <tr>
    <td width=\"100%\" colspan=\"4\" $DP_CSS[tblrow1] >".$DP_language[main_dates]."</td>
  </tr>
	";

	if ($Dates == False) 
	{
		$list_print_float = $list_print_float."
  <tr>
    <td width=\"100%\" colspan=\"4\" >".$DP_language[no_entry]."</td>
  </tr>
		";
	}
	else 
	{
		for ($i=0;$i<count($Dates);$i++) 
		{
			$list_print_float = $list_print_float."
		<tr>
			<td width=\"10%\">
			";
			$ttd->ttd($Dates[$i][1]);
			$list_print_float = $list_print_float.$ttd->day_of_month.".".$ttd->monthname." - ".$ttd->hour_long.":".$ttd->minutes ;
			$list_print_float = $list_print_float."
			</td><td width=\"10%\">
			";
			$ttd->ttd($Dates[$i][2]);
			$list_print_float = $list_print_float.$ttd->day_of_month.".".$ttd->monthname." - ".$ttd->hour_long.":".$ttd->minutes ;
			$list_print_float = $list_print_float."
			</td><td width=\"10%\">
			";
			$list_print_float = $list_print_float.$Dates[$i][5];
			$list_print_float = $list_print_float."
			</td><td width=\"10%\">
			";
			$list_print_float = $list_print_float.$Dates[$i][6];
			$list_print_float = $list_print_float."
			</td>
		</tr>
			";
		}
	}

	return $list_print_float;
} // end func

/**
* 	void function parseData ()
* 	parse the from / to date strings and retuns messages if they are not valid
* 	@param int $fromtime_ts
* 	@param int $totime_ts   
* 	@param array $Start_date
* 	@param array $End_date  
* 	@global Array $DP_language		( include Languageproperties )
*	@return array $Valid
*/
function  parseData ($fromtime_ts, $totime_ts , $Start_date, $End_date) {
	global  $DP_language;

	if($fromtime_ts == "-1")			$Valid[] = $DP_language[ERROR_STARTDATE];
	if($totime_ts == "-1")				$Valid[] = $DP_language[ERROR_ENDDATE];
	if($fromtime_ts > $totime_ts)		$Valid[] = $DP_language[ERROR_END_START]; 

	if(	$Start_date[0] != date ("d", mktime(0,0,0,$Start_date[1],$Start_date[0],$Start_date[2])) or
		$Start_date[1] != date ("m", mktime(0,0,0,$Start_date[1],$Start_date[0],$Start_date[2])) or 
		$Start_date[2] != date ("Y", mktime(0,0,0,$Start_date[1],$Start_date[0],$Start_date[2]))) 
	{
		 $Valid[] = $DP_language[ERROR_STARTDATE];
	}
	if(	$End_date and ($End_date[0] != date ("d", mktime(0,0,0,$End_date[1],$End_date[0],$End_date[2])) or
		$End_date[1] != date ("m", mktime(0,0,0,$End_date[1],$End_date[0],$End_date[2])) or 
		$End_date[2] != date ("Y", mktime(0,0,0,$End_date[1],$End_date[0],$End_date[2])))) 
	{
		 $Valid[] = $DP_language[ERROR_ENDDATE];
	}

	$Valid[] = "TRUE";
	Return $Valid;

}// end func


/**
* 	void function setDateList()
* 	the first Main function of the list view , to list out the result
* 	@param int $fromtime_ts
* 	@param int $totime_ts   
* 	@global Array $DP_language		( include Languageproperties )
* 	@global array $DP_CSS			( contains CSS Strings from the conf.gui file )
*   @return Array $Return
*						[0] $list_navigation	( contains the navigation output )
*						[1] $list_float			( contains the output )
*/
function setDateList($fromtime_ts, $totime_ts, $DB)
{
	global   $DP_language, $DP_CSS;
	$ttd				= new TimestampToDate;
	
	$list_navigation = setNavigation($fromtime_ts, $totime_ts);

	if($fromtime_ts == $totime_ts) 
	{
		$ttd->ttd($fromtime_ts);
		$totime_ts = mktime(23,59,59,$ttd->monthnumber,$ttd->day_of_month,$ttd->year_long); 
	}

	$list_float = "";
	$DATE = getContent($fromtime_ts, $totime_ts, $DB);
	$Dates		= $DATE[0];
	$WholeDates	= $DATE[1];

	// 1st - one day dates , cause more important 
	$list_float = $list_float."
  <tr ".$DP_CSS[tblrow1].">
    <td width=\"100%\" colspan=\"4\"  >".$DP_language[extra_dates]."</td>
  </tr>
	";
	if ($WholeDates == False) 
	{
		$list_float = $list_float."
  <tr>
    <td width=\"100%\" colspan=\"4\" ".$DP_CSS[tblrow2]." >".$DP_language[no_entry]."</td>
  </tr>
		";
	}
	else 
	{
		if (!$pointer) 
		{
			$pointer =0;
		}
		for ($i=0;$i<count($WholeDates);$i++) 
		{
			$list_float = $list_float."
		<tr ".$DP_CSS[tblrow2]." >
			<td width=\"10%\" >
			";
			$ttd->ttd($WholeDates[$i][1]);
			$list_float = $list_float.$ttd->day_of_month.".".$ttd->monthname ;
			$list_float = $list_float."
			</td><td width=\"10%\">
			";
			$ttd->ttd($WholeDates[$i][2]);
			$list_float = $list_float.$ttd->day_of_month.".".$ttd->monthname ;
			$list_float = $list_float."
			</td><td width=\"10%\">
			";
			$list_float = $list_float.$WholeDates[$i][5];
			$list_float = $list_float."
			</td><td width=\"10%\">
			";
			$list_float = $list_float.$WholeDates[$i][6];
			$list_float = $list_float."
			</td>
		</tr>
			";
			$pointer++;
		}
	}

	// end onde day dates

	// 2nd - normal dates  
	$list_float = $list_float."
  <tr>
    <td width=\"100%\" colspan=\"4\" $DP_CSS[tblrow1] >".$DP_language[main_dates]."</td>
  </tr>
	";

	if ($Dates == False) 
	{
		$list_float = $list_float."
  <tr ".$DP_CSS[tblrow2]." >
    <td width=\"100%\" colspan=\"4\" >".$DP_language[no_entry]."</td>
  </tr>
		";
	}
	else 
	{
		if (!$pointer) 
		{
			$pointer =0;
		}

		for ($i=0;$i<count($Dates);$i++) 
		{

			$list_float = $list_float."
		<tr ".$DP_CSS[tblrow2].">
			<td width=\"10%\">
			";
			$ttd->ttd($Dates[$i][1]);
			$list_float = $list_float.$ttd->day_of_month.".".$ttd->monthname." - ".$ttd->hour_long.":".$ttd->minutes ;
			$list_float = $list_float."
			</td><td width=\"10%\">
			";
			$ttd->ttd($Dates[$i][2]);
			$list_float = $list_float.$ttd->day_of_month.".".$ttd->monthname." - ".$ttd->hour_long.":".$ttd->minutes ;
			$list_float = $list_float."
			</td><td width=\"10%\">
			";
			$list_float = $list_float.$Dates[$i][5];
			$list_float = $list_float."
			</td><td width=\"10%\">
			";
			$list_float = $list_float.$Dates[$i][6];
			$list_float = $list_float."
			</td>
		</tr>
			";
			$pointer++;

		}
	}
	
	$Return[0] = $list_navigation;
	$Return[1] = $list_float;
	Return $Return;
} // end func
?>