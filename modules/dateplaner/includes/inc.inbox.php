<?php
/**
* Functions for inbox.php
* 
* @author Stefan Stahlkopf <mail@stefan-stahlkopf.de> 
* @module inbox_php
* @modulegroup cscw
* 
* @version $Id$: inc.inbox.php,v 1.0 2003/06/20 
*
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
* @author Stefan Stahlkopf <mail@stefan-stahlkopf.de> 
* 
* @version $Id$: inc.inbox.php,v 1.0 2003/06/20 
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
* @return array 	$retArray		  
* @access public
* @global array 	$DP_language	Array for labels
* 
* @author Stefan Stahlkopf <mail@stefan-stahlkopf.de> 
* 
* @version $Id$: inc.inbox.php,v 1.0 2003/06/20 
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
?>