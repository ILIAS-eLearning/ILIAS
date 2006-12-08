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
* Functions for inbox.php
*
* this file should manage the inbox functions
*
* @author		Stefan Stahlkopf <mail@stefan-stahlkopf.de> 
* @author       Frank Gruemmert <gruemmert@feuerwelt.de>    
* @version		$Id$ 
*/

//******************************************************************************
/**
* function formatDate
*
*	Displays a timespace 
*
* @param timestamp $timestampStart	Starttime 
* @param timestamp $timestampEnd	Endtime
* @return string $timestring		  
* @access public
* 
*/
function formatDate($timestampStart, $timestampEnd, $singleRotation)
{
	
	$start = new TimestampToDate();
	$end = new TimestampToDate();

	$start->ttd( $timestampStart );
	$end->ttd( $timestampEnd );
			 
	if ($start->day_of_year == $end->day_of_year)
	{
		// Start and End of date on the same day
		// hour:minute-hour:minute day. month year
		
		$timestring =	"$start->hour_long:$start->minutes-$end->hour_long:$end->minutes 
						$start->day_of_month. $start->monthname $start->year_long";
	}
	else
	{
		if ( $start->monthnumber == $end->monthnumber)
		{
			// Start and End of date in the same month
			// hour:minute-hour:minute
			// day. - day. month year
		
			$timestring = 	"$start->hour_long:$start->minutes-$end->hour_long:$end->minutes<br>
							 $start->day_of_month. - $end->day_of_month. $start->monthname $start->year_long";
		}
		else
		{
			// other cases
			// hour:minute-hour:minute
			// day. month year -
			// day. month year
			
			$timestring = 	"$start->hour_long:$start->minutes-$end->hour_long:$end->minutes<br>
							 $start->day_of_month. $start->monthname $end->year_long - <br>
							 $end->day_of_month. $end->monthname $start->year_long";
		
		}
	}
	if ($singleRotation)
	{
		$timestring = 	"$start->hour_long:$end->minutes-$end->hour_long:$end->minutes $end->day_of_month. $end->monthname $end->year_long";
				
	}
	 
	
	return $timestring;
}
//******************************************************************************
/**
* function createTable
*
*	Creates a table with dates for display in inbox 
*
*	html template file:	1_inbox_dates.htm		
*
* @param array 		$dates			Starttime 
* @param integer 	$timestampEnd	Endtime
* @param gui		$Gui			Object of class Gui
* @param db			$db				Object of class db
* @param mode		$mode			defines radio buttons
*						0: 	buttons for "deleted dates"
*						other:	
* @global array 	$DP_language	Array for labels
* @return array 	$retArray		  
* @access public
* 
*/
function createTable($Dates,$DateID, $Gui , $db, $mode)
{
global $DP_language;
for ( $i = 0; $i < sizeof($Dates); $i++ )
{
	if ( $Dates[$i][6] == 0 )
	{
		// Not a single rotation date
		$formattedtime = formatDate($Dates[$i][1], $Dates[$i][2], false);
	}
	else
	{
		// single rotation date
		$formattedtime = formatDate($Dates[$i][6], $Dates[$i][2], true);
	}
	
	$timestamp = $Dates[$i][6];
	$id = $Dates[$i][0];
	if ($mode != "0")
	{
		// view for new and changed dates
		
		$col1 = "<input type='radio' name=$DateID value='ok-$id-$timestamp'>";
	}
	else
	{
		// view for deleted dates
		$col1 = "";
	}
	$col2 = "<input type='radio' name=$DateID value='del-$id-$timestamp'>";
	$col3 = "<input type='radio' checked name=$DateID value='noChange-$id-$timestamp'>";	
	$col4 = "$formattedtime";// Time / Date 
	$col5 = $Dates[$i][3];// Shorttext 
	$col6 = $db->getGroupName($Dates[$i][4]);// Groupname 
	
	if ( $Dates[$i][6] == 0 )
	{
		// not a single rotation date	
		switch ($Dates[$i][5])
		{
			// type of rotation
			case 0:
				$col7 = $DP_language[r_nonrecurring];
				break;
			case 1:
				$col7 = $DP_language[r_day];
				break;
			case 2:
				$col7 = $DP_language[r_week];
				break;
			case 3:
				$col7 = $DP_language[r_14];
				break;
			case 4:
				$col7 = $DP_language[r_4_weeks];
				break;
			case 5:
				$col7 = $DP_language[r_month];
				break;
			case 6:
				$col7 = $DP_language[r_halfyear];
				break;
			case 7:
				$col7 = $DP_language[r_year];
				break;
			default:
				$col7 = " ";
		}		
	}
	else
	{
		// single rotation date
		$col7 = $DP_language[singleDate];
			
	}
	
			
	eval ("\$Termine = \"".$Gui->getTemplate("inbox_dates")."\";");
	$x = $x.$Termine;	// attach row
	
	$DateID++;
}
$Termine = $x;
$retArray = array($DateID, $Termine); 
return $retArray;	
}

/**
* 	function getContent($begin_ts, $end_ts)
* 	get Content for the inbox about group dates 
* 	@param $DB (object of th db class ) 
* 	@global string $DP_UId     ( actual User ID )
* 	@return Array [][][] $DATE
* 			[0]	newDates			( new Dates )
* 			[1] changedDates		( changed Dates )
* 			[2]	deletedDates		( deleted Dates )
*/
function getContent($DB)
{

	global $DP_UId;

	// Get Dates from Database
	$newDates			= $DB->getchangedDates($DP_UId, 0);
	$changedDates		= $DB->getchangedDates($DP_UId, 1);
	$deletedDates		= $DB->getchangedDates($DP_UId, 2);

	$DATE[0]=$newDates;
	$DATE[1]=$changedDates;
	$DATE[2]=$deletedDates;
	return $DATE;

} // end func

/**
* 	function setInboxView($radio_button, $DB)
* 	the Main function of the week view
* 	called from the executed file
* 	@param int $Gui					(object of the gui class ) 
* 	@param int $DB					(object of the db class ) 
* 	@global array $DP_language		( include Languageproperties )
* 	@global array $DP_CSS			( contains CSS Strings from the conf.gui file )
* 	@global array $_SESSION 		( DP_Starttime include Start Time of during on day in week view and 
*                                     DP_Endtimeinclude End Time of during on day in week view)
*	@global sting $actualtemplate		( current template )
*	@global string $templatefolder		( current used template folder )
*   @return Array Return
*						[0] string week_navigation	( contains the navigation output )
*						[1] string week_float		( contains the output )
*						[2] array S_Datum			( contains Date from Table Top )
*/
function setInboxView($Gui, $DB) 
{
	global $DP_language, $DP_CSS, $_SESSION, $templatefolder , $actualtemplate;

	$DATE			= getContent($DB);
	$newDates		= $DATE[0];
	$changedDates	= $DATE[1];
	$deletedDates	= $DATE[2];

	//*******************************************************************************************************
    $DateID = 0;
	// fill table with new dates 
	if ($newDates != false)
	{
		
    	$array	= createTable($newDates, $DateID, $Gui, $DB, 1);
    	$DateID = $array[0];
    	$neueTermine = $array[1];
		eval ("\$tblhead_newdates = \"".$Gui->getTemplate("inbox_tblheadnewdates")."\";");
    }
    else
    {
    	$neueTermine = "<tr class='tblrow2'><td align='center' colspan=7 >$DP_language[no_entry]</td></tr>";
    }
    
    //*******************************************************************************************************
    // fill table with changed dates 
    if ($changedDates != false)
    {
    	$array	= createTable($changedDates,$DateID, $Gui, $DB, 1);
    	$DateID = $array[0];
    	$geaenderteTermine = $array[1];
 		eval ("\$tblhead_changeddates = \"".$Gui->getTemplate("inbox_tblheadchangeddates")."\";");
    }
    else
    {
   		$geaenderteTermine = "<tr class='tblrow2'><td align='center' colspan=7 >$DP_language[no_entry]</td></tr>";
    }
    //*******************************************************************************************************
    // fill table with deletet dates 
    if ($deletedDates != false)
    {
    	$array	= createTable($deletedDates,$DateID, $Gui, $DB, 0);
    	$DateID = $array[0];
    	$geloeschteTermine = $array[1];
 		eval ("\$tblhead_deleteddates = \"".$Gui->getTemplate("inbox_tblheaddeleteddates")."\";");
    }
    else
    {
   		$geloeschteTermine = "<tr class='tblrow2'><td align='center' colspan=7 >$DP_language[no_entry]</td></tr>";
    }
    //*******************************************************************************************************
    $tableBorder = 1;

	if($tblhead_deleteddates or $tblhead_changeddates or $tblhead_newdates) {
 		eval ("\$execute = \"".$Gui->getTemplate("inbox_btnexecute")."\";");
	}
    
    eval ("\$centertxt = \"".$Gui->getTemplate("inbox_main")."\";");
    
	Return $centertxt;
}
?>