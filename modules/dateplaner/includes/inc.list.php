<?
/**
* Include file List
*
* this file should manage the list functions
* 
* @author Frank Grümmert 
* 
* @version $Id: inc.list.php,v 0.9 2003/06/11 
* @package application
* @access public
*
*/

/**
* 	void function setNavigation($fromtime_ts, $totime_ts)
* 	@description :  stet variables in the setNavigation of list 
* 	@param int fromtime_ts
* 	@param int totime_ts   
* 	@global string $actualtemplate  ( name of actual cscw skin )
* 	@global string $templatefolder  ( name of template folder )
* 	@global Array CSCW_language		( include Languageproperties )
* 	@return string $list_navigation ( contains the output for the setNavigation )
*/
function setNavigation($fromtime_ts, $totime_ts)
	{
		global $templatefolder, $actualtemplate , $CSCW_language;
		$Gui		= new Gui();
		$ttd		= new TimestampToDate;
		$ttd->ttd($fromtime_ts);
		$date2		= $ttd->day_of_month."/".$ttd->monthnumber_long."/".$ttd->year_long ;
		$ttd->ttd($totime_ts);
		$date4		= $ttd->day_of_month."/".$ttd->monthnumber_long."/".$ttd->year_long ;
		$popupcall_1 = '
<script language=JavaScript>
			var cal2 = new CalendarPopup();  
			cal2.showYearNavigation(); cal2.setMonthNames(\''.$CSCW_language[long_01].'\',\''.$CSCW_language[long_02].'\',\''.$CSCW_language[long_03].'\',\''.$CSCW_language[long_04].'\',\''.$CSCW_language[long_05].'\',\''.$CSCW_language[long_06].'\',\''.$CSCW_language[long_07].'\',\''.$CSCW_language[long_08].'\',\''.$CSCW_language[long_09].'\',\''.$CSCW_language[long_10].'\',\''.$CSCW_language[long_11].'\',\''.$CSCW_language[long_12].'\'); 
		    cal2.setDayHeaders(\''.$CSCW_language[Su_short].'\',\''.$CSCW_language[Mo_short].'\',\''.$CSCW_language[Tu_short].'\',\''.$CSCW_language[We_short].'\',\''.$CSCW_language[Th_short].'\',\''.$CSCW_language[Fr_short].'\',\''.$CSCW_language[Sa_short].'\');
			cal2.setWeekStartDay(1);
			cal2.setTodayText("'.$CSCW_language[today].'");
</script>
		';
		$popupcall_2 = '
<script language=JavaScript >
			var cal4 = new CalendarPopup();	cal4.setMonthNames(\''.$CSCW_language[long_01].'\',\''.$CSCW_language[long_02].'\',\''.$CSCW_language[long_03].'\',\''.$CSCW_language[long_04].'\',\''.$CSCW_language[long_05].'\',\''.$CSCW_language[long_06].'\',\''.$CSCW_language[long_07].'\',\''.$CSCW_language[long_08].'\',\''.$CSCW_language[long_09].'\',\''.$CSCW_language[long_10].'\',\''.$CSCW_language[long_11].'\',\''.$CSCW_language[long_12].'\'); 
		    cal4.setDayHeaders(\''.$CSCW_language[Su_short].'\',\''.$CSCW_language[Mo_short].'\',\''.$CSCW_language[Tu_short].'\',\''.$CSCW_language[We_short].'\',\''.$CSCW_language[Th_short].'\',\''.$CSCW_language[Fr_short].'\',\''.$CSCW_language[Sa_short].'\');
			cal4.setWeekStartDay(1);
			cal4.setTodayText("'.$CSCW_language[today].'");			
			cal4.showYearNavigation(); 
</script>
		';

		eval ("\$list_navigation = \"".$Gui->getTemplate("list_navigation")."\";");

		Return $list_navigation;
} // end func

/**
* 	function getContent($start_ts, $end_ts)
* 	@description : get Content for the Week View from the sortdates functions 
* 	@param int start_ts
* 	@param int end_ts
* 	@global string CSCW_UId     ( actual User ID )
* 	@global Array CSCW_Keywords ( actuel Keywords)
* 	@return Array [][][] 
* 			[0]	Dates			( normel Dates )
* 			[1] WholeDates		( one day Dates )
*/
function getContent($start_ts, $end_ts)
{
	global  $CSCW_Keywords, $CSCW_UId;
	$Dates				= getDateList ($CSCW_UId, $start_ts, $end_ts, $CSCW_Keywords);
	$WholeDates			= getWholeDayDateList ($CSCW_UId, $start_ts, $end_ts, $CSCW_Keywords);

	$DATE[0]=$Dates;
	$DATE[1]=$WholeDates;
	return $DATE;
} // end func

/**
* 	function print_viewDate_list()
* 	@description : the second Main function of the list view , to print out the List result
* 	@param int fromtime_ts
* 	@param int totime_ts   
* 	@global array CSCW_language		( include Languageproperties )
* 	@global array CSCW_CSS			( contains CSS Strings from the conf.gui file )
*   @return string list_print_float ( contains the output )
*/
function printDateList($fromtime_ts, $totime_ts)
{
	global $CSCW_language, $CSCW_CSS ;


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

	$DATE				= getContent($fromtime_ts, $totime_ts);
	$Dates				= $DATE[0];
	$WholeDates			= $DATE[1];
	
	// 1st - one day dates , cause more important 
	$list_print_float = $list_print_float."
  <tr>
    <td width=\"100%\" colspan=\"4\" $CSCW_CSS[tblrow1] >".$CSCW_language[extra_dates]."</td>
  </tr>
	";
	if ($WholeDates == False) 
	{
		$list_print_float = $list_print_float."
  <tr>
    <td width=\"100%\" colspan=\"4\" >".$CSCW_language[no_entry]."</td>
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
    <td width=\"100%\" colspan=\"4\" $CSCW_CSS[tblrow1] >".$CSCW_language[main_dates]."</td>
  </tr>
	";

	if ($Dates == False) 
	{
		$list_print_float = $list_print_float."
  <tr>
    <td width=\"100%\" colspan=\"4\" >".$CSCW_language[no_entry]."</td>
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
* 	@description : parse the from / to date strings and retuns messages if they are not valid
* 	@param int fromtime_ts
* 	@param int totime_ts   
* 	@param array Start_date
* 	@param array End_date  
* 	@global Array CSCW_language		( include Languageproperties )
*	@return array $Valid
*/
function  parseData ($fromtime_ts, $totime_ts , $Start_date, $End_date) {
	global  $CSCW_language;

	if($fromtime_ts == "-1")			$Valid[] = $CSCW_language[ERROR_STARTDATE];
	if($totime_ts == "-1")				$Valid[] = $CSCW_language[ERROR_ENDDATE];
	if($fromtime_ts > $totime_ts)		$Valid[] = $CSCW_language[ERROR_END_START]; 

	if(	$Start_date[0] != date ("d", mktime(0,0,0,$Start_date[1],$Start_date[0],$Start_date[2])) or
		$Start_date[1] != date ("m", mktime(0,0,0,$Start_date[1],$Start_date[0],$Start_date[2])) or 
		$Start_date[2] != date ("Y", mktime(0,0,0,$Start_date[1],$Start_date[0],$Start_date[2]))) 
	{
		 $Valid[] = $CSCW_language[ERROR_STARTDATE];
	}
	if(	$End_date and ($End_date[0] != date ("d", mktime(0,0,0,$End_date[1],$End_date[0],$End_date[2])) or
		$End_date[1] != date ("m", mktime(0,0,0,$End_date[1],$End_date[0],$End_date[2])) or 
		$End_date[2] != date ("Y", mktime(0,0,0,$End_date[1],$End_date[0],$End_date[2])))) 
	{
		 $Valid[] = $CSCW_language[ERROR_ENDDATE];
	}

	$Valid[] = "TRUE";
	Return $Valid;

}// end func


/**
* 	void function setDateList()
* 	@description : the first Main function of the list view , to list out the result
* 	@param int fromtime_ts
* 	@param int totime_ts   
* 	@global Array CSCW_language		( include Languageproperties )
* 	@global array CSCW_CSS			( contains CSS Strings from the conf.gui file )
*   @return Array Return
*						[0] $list_navigation	( contains the navigation output )
*						[1] $list_float			( contains the output )
*/
function setDateList($fromtime_ts, $totime_ts)
{
	global   $CSCW_language, $CSCW_CSS;
	$DB					= new Database();
	$ttd				= new TimestampToDate;
	
	if (!session_is_registered("CSCW_fromtime_ts")) {
		session_register ("CSCW_fromtime_ts");
	}
	if (!session_is_registered("CSCW_totime_ts")) {
		session_register ("CSCW_totime_ts");
	}
	$list_navigation = setNavigation($fromtime_ts, $totime_ts);

	if($fromtime_ts == $totime_ts) 
	{
		$ttd->ttd($fromtime_ts);
		$totime_ts = mktime(23,59,59,$ttd->monthnumber,$ttd->day_of_month,$ttd->year_long); 
	}

	$list_float = "";
	$DATE = getContent($fromtime_ts, $totime_ts);
	$Dates		= $DATE[0];
	$WholeDates	= $DATE[1];

	// 1st - one day dates , cause more important 
	$list_float = $list_float."
  <tr ".$CSCW_CSS[tblrow1].">
    <td width=\"100%\" colspan=\"4\"  >".$CSCW_language[extra_dates]."</td>
  </tr>
	";
	if ($WholeDates == False) 
	{
		$list_float = $list_float."
  <tr>
    <td width=\"100%\" colspan=\"4\" ".$CSCW_CSS[tblrow2]." >".$CSCW_language[no_entry]."</td>
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
		<tr ".$CSCW_CSS[tblrow2]." >
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
    <td width=\"100%\" colspan=\"4\" $CSCW_CSS[tblrow1] >".$CSCW_language[main_dates]."</td>
  </tr>
	";

	if ($Dates == False) 
	{
		$list_float = $list_float."
  <tr ".$CSCW_CSS[tblrow2]." >
    <td width=\"100%\" colspan=\"4\" >".$CSCW_language[no_entry]."</td>
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
		<tr ".$CSCW_CSS[tblrow2].">
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