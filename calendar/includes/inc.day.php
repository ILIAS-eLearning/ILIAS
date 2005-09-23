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
* Functions for day.php
*
* Description:
*	These Functions are concerned with the Day view of Dates. They
*	fetch data from the functions in inc.sortdates.php or do basic
*	calculations of timestamps to provide links to other dates or to
*	configure the display size.
*
* Version History:
*	1.0	- release
*
*	0.8	- removed most global variables
*
*	0.7	- fixed a problem with dates with the same start and end time (moments).
*
*	0.6	- added navigation() and getDateForDay(), these provide links for
*		  navigation (lastday, nextday, today) and the date of the day shown.
*		- added getWholeDay(). It fetches Whole Day Dates for a given day.
*	
*	0.5	- added initTS and initDisplayTime. InitTS calculates the timestamps 
*		  for datarequests , initDisplaytime compares the user-set displaytime
*		  against the requirements of a given dataset and adjusts it, so that
*		  all dates of a day can be shown.
*		- DisplayTimes are now read from the Session.
*		- Changed Sequences in generateDay to accomodate initDisplayTime()
*	
*	0.4	- Refactored the whole Code, modularized the padding into padFront(),
*		  padMiddle(), padBack(), fixed padding problems. Output is now
*		  called for by generateDay().
*	
*	0.3	- Added table colorings and time column
*	
*	0.2	- Removed debug code
*	
*	0.1	- Creation of first code with padding and a basic table, debug 
*		  code for evaluation
*
* @author		Jan Hübbers <jan@huebbers.de> 
* @author       Frank Gruemmert <gruemmert@feuerwelt.de>    
* @version		$Id$ 
* @module       inc.day.php                            
* @modulegroup  dateplaner                    
* @package		dateplaner-functions
*/


/**
*	void function navigation($timestamp)
*
*	This function generates the timestamps for the day navigation and passes them to the template day_navigation 
*	
*	@param $timestamp , a required timestamp of sometime during the current day.
*	@global $DP_language , used by the Gui to determine the language of "today" as set in the language file.
*	@return $day_navigation , the output variable of the Gui,
*/
function navigation($timestamp){
			global $DP_language;
			$Gui				= new Gui();

			$today		= mktime(0,0,0);
			$lastday	= strtotime ("-1 day" , $timestamp) ;
			$nextday	= strtotime ("+1 day" , $timestamp) ;
			
			eval ("\$day_navigation = \"".$Gui->getTemplate("day_navigation")."\";");
			
			return $day_navigation;

} // end func

/*-----------------------------------------------------------------------------*/

/**
*	void function getDateForDay($timestamp)
*   This function generates the day string for the day view and passes it to the GUI
*	
*	@param $timestamp , a required timestamp of sometime during the current day.
*	@global $DP_language , used by the Gui to determine the language of "today" as set in the language file.
*	@return $showdate , the output variable of the Gui,
*/
function getDateForDay($timestamp){
	global $DP_language;
	$Date = new TimestampToDate();
	$Date->ttd($timestamp);
	$showdate = $Date->shorttime;
	
	Return $showdate;
	
	
}// end func

/*-----------------------------------------------------------------------------*/

/**
*	void function getWholeDay($timestamp)
*
*   This function queries for whole day dates and generates html code which is passed to the Gui
*	
*	@param $timestamp , a required timestamp of sometime during the current day.
*	@param $DB , a DB class object.
*	@global	$DP_UId reads the User ID from the session
*	@global	array[] $DP_Keywords array containing selected Keywords
*	@global	$_SESSION ( bol $DP_JSscript is 1 if JavaScript disabled)
*	@return $wholeDayDayDates , contains dates from 00:00:00 to 23:59:59 
*			  for the selected day as html, evaluated by the Gui.
*	
*/
function getWholeDay($timestamp, $DB){
	global $DP_UId, $_SESSION;
	$startshow = mktime(0, 0, 0, date("m", $timestamp), date ("d", $timestamp), date("Y", $timestamp));// don't edit
	$endshow   = mktime(23,59,59, date("m", $timestamp), date ("d", $timestamp), date("Y", $timestamp));// don't edit
	$dates = getWholeDayDateList($DP_UId, $startshow, $endshow, $_SESSION[DP_Keywords], $DB);
	if($dates){
		foreach($dates as $date){
			if($_SESSION[DP_JSscript] != 1) {
				$wholeDayDayDates=$wholeDayDayDates."<a href=\"dateplaner.php?app=date&date_id=";
				$wholeDayDayDates=$wholeDayDayDates.$date[0];
				$wholeDayDayDates=$wholeDayDayDates."&PHPSESSID=".$PHPSESSID."\" target=\"_blank\" >";
			
			}else {
				$wholeDayDayDates=$wholeDayDayDates."<a href=\"javascript:popup('dateplaner.php?app=date&date_id=";
				$wholeDayDayDates=$wholeDayDayDates.$date[0];
				$wholeDayDayDates=$wholeDayDayDates."&PHPSESSID=".$PHPSESSID."','Date','width=600,height=600,directories=no,toolbar=no,location=no,menubar=no,scrollbars=yes,status=yes,resizable=yes,dependent=no')\" >";
			}
			$wholeDayDayDates=$wholeDayDayDates.$date[5]."</a> ";
			$wholeDayDayDates=$wholeDayDayDates.$date[6]."<BR>";
		}
	}
	return $wholeDayDayDates;
	

}// end func

/*-----------------------------------------------------------------------------*/

/**
*	void function generateDay($timestamp)
*
*	this funktion calls the other relevant funktions to fetch dates, reformat them and to generate
*	
*	@param $timestamp ,  a required timestamp of sometime during the current day.
*	@param $DB, a DB class object.
*	@global $DP_UId , Session Variable of the User ID
*	@global $_SESSION  ( $DP_Keywords, array containing selected Keywords, time $DP_Endtime, user set display end time and  time $DP_Starttime, user set display start time)
*	@return $dayString output in HTML format 
*			.
*/




function generateDay($timestamp, $DB){
	global 	$DP_UId, $_SESSION ;
     	$startDisplayTimeInMinutes = 60 * $_SESSION[DP_Starttime]; // convert to minutes, cast time to int
	$endDisplayTimeInMinutes = 60 * $_SESSION[DP_Endtime]; // convert to minutes, cast time to int
	
	$intervall = 15; // intervall of dividers, e.g 15 (min)
	initTS($timestamp, $startshow, $endshow); // get start and end time for the day
	$table_sorted  = getDayList($DP_UId,$startshow , $endshow, $_SESSION[DP_Keywords], $DB); // get streamed dates
	if($table_sorted){ // check for existance of dates
		for($stream=0; $stream<count($table_sorted); $stream++){
			turnToMinutes	($table_sorted[$stream], $startshow); // convert timestamps to minutes and format date
			
		}
		for($stream=0; $stream<count($table_sorted); $stream++){
			initDisplayTime	($startDisplayTimeInMinutes, $endDisplayTimeInMinutes, $table_sorted[$stream], $intervall); //check if user set display time is sufficient
			
		}
		for($stream=0; $stream<count($table_sorted); $stream++){
			//$counter = 0;
			padFront($table_sorted[$stream], $stream, $arrayPadded, $intervall, $startDisplayTimeInMinutes); // generate fields in $arrayPadded, that resemble the free time before the first date.
			for($counter=1; $counter<count($table_sorted[$stream]); $counter++){
				padMiddle($table_sorted[$stream], $counter, $stream, $arrayPadded, $intervall); // generate fields in $arrayPadded, that resemble the free time during dates.
			}
			padBack ($stream, $arrayPadded, $intervall, $endDisplayTimeInMinutes);// generate fields in $arrayPadded, that resemble the free time after the last date.
		}
		$dayString = generateOutput($arrayPadded, $intervall, $startDisplayTimeInMinutes, $endDisplayTimeInMinutes, $startshow, $timestamp);
		
	}
	else{	// in case that table_sorted is empty, create an NULL array_padded for blank rows
		$arrayPadded[0][0][0]="NULL";
		$arrayPadded[0][0][1]=$startDisplayTimeInMinutes;
		$arrayPadded[0][0][2]=$startDisplayTimeInMinutes+$intervall;
		$arrayPadded[0][0][5]=$intervall;
		$arrayPadded[0][0][6]=1;
		padBack(0, $arrayPadded, $intervall, $endDisplayTimeInMinutes);
		$dayString = generateOutput($arrayPadded, $intervall, $startDisplayTimeInMinutes, $endDisplayTimeInMinutes, $startshow, $timestamp);
		
	}
	return $dayString;

}// end func

/*-----------------------------------------------------------------------------*/

/**
*	void function initTS($timestamp, &$startshow, &$endshow)
*
*	This function generates the timestamps for the start and endtime of the day. using references (i.e &$var)
*			the result is directly stored in the passed variables.
*	
*	@param $timestamp , a required timestamp of sometime during the current day.
*	@param &$startshow , a required timestamp of the starttime of the day, will be generated an written to the variable.
*	@param &$endshow , a required timestamp of the end of the day, will be generated an written to the variable.
*	
*/

function initTS($timestamp, &$startshow, &$endshow){
	$startshow = mktime(0, 0, 0, date("m", $timestamp), date ("d", $timestamp), date("Y", $timestamp));// don't edit
	$endshow   = mktime(23,59,59, date("m", $timestamp), date ("d", $timestamp), date("Y", $timestamp)); //don't edit
}// end func

/*-----------------------------------------------------------------------------*/

/**
*	void function initDisplayTime(&$startDisplayTimeInMinutes, &$endDisplayTimeInMinutes, $dates)
*
*	this funktion goes through an 2dim array of dates and checks, if the passed
			start and end display time suffice, if not, they are ajusted 
*	
*	@param &$startDisplayTimeInMinutes , a required timestamp of sometime during the current day.
*	@param &$endDisplayTimeInMinutes , a required timestamp of sometime during the current day.
*	@param $dates , a 2-dimensional array of dates.
*	
*/

function initDisplayTime(&$startDisplayTimeInMinutes, &$endDisplayTimeInMinutes, $dates, $intervall){
		foreach($dates as $dim1){
			if( $dim1[1]< $startDisplayTimeInMinutes){
				$startDisplayTimeInMinutes = $dim1[1] - $dim1[1]%$intervall;
				
			}
			
			if( $dim1[2] > $endDisplayTimeInMinutes){
				$endDisplayTimeInMinutes = $dim1[2] + $dim1[2]%$intervall;
				if($endDisplayTimeInMinutes > 1440) $endDisplayTimeInMinutes  = 1440;
			}	
		
		}
		
}// end func

/*-----------------------------------------------------------------------------*/

/**
*	void function turnToMinutes($dateList, $startshow)
*
*	This function reformats the timestamps of start and end to minutes of the day.
*			Additionally, the timestamps are formated as a date string, as to prepare data for output
*
*	@param	array[][] $dateList , a 2-dimensional array of dates
*	@param	int $startshow , timestamp of the start of the day
*
*	@global	array[] $DP_language , an array containung language dependant information
*	
*/
function turnToMinutes(&$dateList, $startshow){
	global $DP_language;
	$format = $DP_language[date_format];
	for($date=0; $date<count($dateList); $date++){
		$dateList[$date][7]= date($format, $dateList[$date][1]); // save the date and start time readeable
		$dateList[$date][8]= date($format, $dateList[$date][2]); // save the date and end time readeable
		$dateList[$date][1]= ($dateList[$date][1]-$startshow)/60; // (1)calculate timelength of beginning of from start of day in seconds and divide my 60 to acquire minutes
		if($dateList[$date][1]< 0){$dateList[$date][1]=0;}	  // (2) if date started previous to this day, set start display time to start of day	
		$dateList[$date][2]= ($dateList[$date][2]-$startshow)/60; // analogous to (1) for end of date
		if($dateList[$date][2]> 1440){$dateList[$date][2]=1440;} // analogous to (2) for subsequent end of date, set to end of day
	}
}// end func

/*-----------------------------------------------------------------------------*/

/**
*	void function testoutput($dim3)
*	
*	echos a 3 dimensional array to the screen, used for debug purposes,
*			might distort normal table output if existent
*	
*	@param array[][][] $dim3 , a 3 dimensional array with data
*	
*/
function testOutput($dim3){
	foreach($dim3 as $key => $dim2){
		echo "Stream: ".$key."<br>";
		foreach($dim2 as $dim1){
			foreach($dim1 as $v){
				echo ":".$v." ";
			}
		echo "<br>";	
		}
		echo "<br>";
	}
}// end func

/*-----------------------------------------------------------------------------*/

/**
*	void function testOutput2($dim2)
*	
*	echos a 2 dimensional array to the screen, used for debug purposes,
*			might distort normal table output if existent
*	
*	@param array[][] $dim2 , a 2 dimensional array with data
*	
*/
function testOutput2($dim2){
		foreach($dim2 as $dim1){
			foreach($dim1 as $v){
				echo ":".$v." ";
			}
		echo "<br>";	
		}
		echo "<br>";
}// end func

/*-----------------------------------------------------------------------------*/

/**
*	void function padFront($dateList, $stream, $arrayPadded, $intervall, $startDisplayTimeInMinutes)
*	
*	This funktion inserts what will be displayed as blank "<TD></TD>" into $arrayPadded,
*			until the first date is reached.
*	
*	@param array[][] $dateList , a two dimensional array with ascending dates.
*	@param int $stream , used to determine, in which part of the first dimension of $arrayPadded dates are inserted.
*	@param array[][][] $arrayPadded , it is the result array, which contains all data used for displaying and formating,
*				to be passed as a reference
*	
*	@param string $intervall , this value holds the size of the subdivisions (i.e. 15) 
*	@param $startDisplayTimeInMinutes , the display starting time in minutes
			
*/	
function padFront($dateList, $stream, &$arrayPadded, $intervall, $startDisplayTimeInMinutes){
	$padStart= $startDisplayTimeInMinutes;
	$padEnd = $startDisplayTimeInMinutes + $intervall;
	$start=$dateList[0][1];
	$end=$dateList[0][2];
	$row=0;
	while($padEnd<=$start){ //while the padding of one subdivision (i.e length of $intervall) does not overlap a date, keep padding
		$arrayPadded[$stream][$row][0]="NULL";
		$arrayPadded[$stream][$row][1]=$padStart;
		$arrayPadded[$stream][$row][2]=$padEnd;
		$arrayPadded[$stream][$row][5]=$intervall;
		$arrayPadded[$stream][$row][6]=1;
		$padStart=$padEnd;
		$padEnd=$padEnd+$intervall;
		$row++;	
	}
	if($padStart < $start){ //try to fit part of an intervall as padding in front of the date.
		$arrayPadded[$stream][$row][0]="NULL";
		$arrayPadded[$stream][$row][1]=$padStart;
		$arrayPadded[$stream][$row][2]=$start;
		$arrayPadded[$stream][$row][5]=$start - $padStart;
		$arrayPadded[$stream][$row][6]=1;
		$padEnd=$padEnd+$intervall;
		$row++;	
	}
	//add the date itself
	$arrayPadded[$stream][$row][0]=$dateList[0][0];
	$arrayPadded[$stream][$row][1]=$dateList[0][1];
	$arrayPadded[$stream][$row][2]=$dateList[0][2];
	if($arrayPadded[$stream][$row][2]==$arrayPadded[$stream][$row][1])$arrayPadded[$stream][$row][2]++; // adjust dates with no length, otherwise HTML gets crazy
	$arrayPadded[$stream][$row][3]=$dateList[0][3];
	$arrayPadded[$stream][$row][4]=$dateList[0][4];
	$arrayPadded[$stream][$row][5]=$end - $start;
	$arrayPadded[$stream][$row][6]=1;
	$arrayPadded[$stream][$row][7]=$dateList[0][7];
	$arrayPadded[$stream][$row][8]=$dateList[0][8];
	$row++;
}// end func

/*-----------------------------------------------------------------------------*/

/**
*	void function padMiddle($dateList, $counter, $stream, $arrayPadded, $intervall)
*	
*	This funktion inserts what will be displayed as blank "<TD></TD>" into $arrayPadded, by
*			trying to fill up untill the next intervall and consecutive intervalls untill 
*			and/or the next date.
*			
*	@param array[][] $dateList , a two dimensional array with ascending dates.
*	@param int $counter , points at the current to be padded date in $dateList.
*	@param int $stream , used to determine, in which part of the first dimension of $arrayPadded dates are inserted.
*	@param array[][][] $arrayPadded , it is the result array, which contains all data used for displaying and formating,
*				to be passed as a reference
*	@param int $intervall , this value holds the size of the subdivisions (i.e. 15) 
*	
*/
function padMiddle($dateList, $counter, $stream, &$arrayPadded, $intervall){
	$lastElement=count($arrayPadded[$stream])-1;
	$lastEnd=$arrayPadded[$stream][$lastElement][2];
	if($dateList[$counter]){ // is not last date
		$nextStart = $dateList[$counter][1]; //start time in minutes
		$nextIntervall=$lastEnd+$intervall-$lastEnd%$intervall; // calculate next interval
		while($nextIntervall < $nextStart){ // insert blank until next interval
			$lastElement++;
			$arrayPadded[$stream][$lastElement][0]="NULL";
			$arrayPadded[$stream][$lastElement][1]=$lastEnd;
			$arrayPadded[$stream][$lastElement][2]=$nextIntervall;
			$arrayPadded[$stream][$lastElement][5]=$nextIntervall-$lastEnd;
			$arrayPadded[$stream][$lastElement][6]=1;
			$lastEnd = $nextIntervall; // inc $lastEnd
			$nextIntervall = $nextIntervall + $intervall; // inc $nextInterval
		}
		if($nextStart-$lastEnd>0){ // pad between intervalls
			$lastElement++;
			$arrayPadded[$stream][$lastElement][0]="NULL";
			$arrayPadded[$stream][$lastElement][1]=$lastEnd;
			$arrayPadded[$stream][$lastElement][2]=$nextStart;
			$arrayPadded[$stream][$lastElement][5]=$nextStart-$lastEnd;
			$arrayPadded[$stream][$lastElement][6]=1;
		}
		$lastElement++; // insert date
		$arrayPadded[$stream][$lastElement][0]=$dateList[$counter][0];
		$arrayPadded[$stream][$lastElement][1]=$dateList[$counter][1];
		$arrayPadded[$stream][$lastElement][2]=$dateList[$counter][2];
		if($arrayPadded[$stream][$lastElement][1]==$arrayPadded[$stream][$lastElement][2])$arrayPadded[$stream][$lastElement][2]++; // adjust dates with no length,  otherwise HTML gets crazy
		$arrayPadded[$stream][$lastElement][3]=$dateList[$counter][3];
		$arrayPadded[$stream][$lastElement][4]=$dateList[$counter][4];
		$arrayPadded[$stream][$lastElement][5]=$dateList[$counter][2] - $nextStart;
		$arrayPadded[$stream][$lastElement][6]=1;
		$arrayPadded[$stream][$lastElement][7]=$dateList[$counter][7];
		$arrayPadded[$stream][$lastElement][8]=$dateList[$counter][8];
		
	}
}// end func back to loop


/*-----------------------------------------------------------------------------*/


/**
*	void function padBack($stream, $arrayPadded, $intervall, $endDisplayTimeInMinutes)
*	
*	This funktion inserts what will be displayed as blank "<TD></TD>" into $arrayPadded, starting from the last date	
*			untill $endDisplayTimeInMinutes is reached
*			
*	@param int $stream , used to determine, in which part of the first dimension of $arrayPadded dates are inserted.
*	@param array[][][] $arrayPadded , it is the result array, which contains all data used for displaying and formating,
*				to be passed as a reference
*	@param int $endDisplayTimeInMinutes , time when the padding has to stop
*	@param int $intervall , this value holds the size of the subdivisions (i.e. 15) 
*	
*/
function padBack($stream, &$arrayPadded, $intervall, $endDisplayTimeInMinutes){
	$lastElement=count($arrayPadded[$stream])-1;
	$lastEnd=$arrayPadded[$stream][$lastElement][2]; // start padding here
	$nextStart=$lastEnd;
	$nextEnd = ($nextStart - $nextStart%$intervall)+$intervall;
	while($endDisplayTimeInMinutes+15>=$nextEnd){
		$lastElement++;
		$arrayPadded[$stream][$lastElement][0]="NULL";
		$arrayPadded[$stream][$lastElement][1]=$nextStart;
		$arrayPadded[$stream][$lastElement][2]=$nextEnd;
		$arrayPadded[$stream][$lastElement][5]=$nextEnd-$nextStart;
		$arrayPadded[$stream][$lastElement][6]=1;
		$nextStart=$nextEnd;
		$nextEnd=$nextEnd+$intervall;
	}
}// end func

/*-----------------------------------------------------------------------------*/

/**
*	void function generateOutput($arrayPadded, $intervall, $startDisplayTimeInMinutes, $endDisplayTimeInMinutes, $startshow)
*	
*		This function generates a HTML string. Basically it counts up the minutes from 
*		$startDisplayTime to $endDisplayTime. For each minute a new <tr> is created.
*		The starting times of dates and blanks in $arrayPadded are compared to the current
*		time and if they match, a new <TD> with a blank or a date is insterted.
*		With dates, the date information is displayed and a link to date.php is provided as
*		a javascript pop up.
*		 
*	@param array[][][] $arrayPadded , a 3-dimensional array holding the dates formated for output.	
*				The first dimension is used for distinguishing colliding dates.
*				The second dimension holds a single date.
*				The third dimesion holds the attributes of the date, which are ordered as followed:
*				0=> ID or NULL; 1=> startTimeInMinutes; 2=> endTimeInMinutes; 3=> shortText; 4=> longText; 5=> rowspan; 6=> colspan(not used); 7=> formated date&time of start for output; 8=> formated date&time of end for output;
*	@param int $startDisplayTimeInMinutes , start of display time
*	@param int $endDisplayTimeInMinutes , end of display time
*	@param int $intervall , the displayed subdivisions (i.e. 15)
*	@param int $startshow , a timestamp of the start of the day (00:00 hours)
*
*	@global string $templatefolder , holds the variable to the folder containing template files
*	@global string $actualtemplate , defines the used template
*	@global array[] $DP_CSS, an array containing CSS information
*	@global	$_SESSION  ( bol $DP_JSscript is 1 if JavaScript disabled)
*
*	@return $dayString , the output variable of the Gui
*	
*/
function generateOutput($arrayPadded, $intervall, $startDisplayTimeInMinutes, $endDisplayTimeInMinutes, $startshow, $timestamp){
	global $DP_CSS,$DP_language, $templatefolder, $actualtemplate, $_SESSION;
	
    	$ttd		= new TimestampToDate;

		$stream=count($arrayPadded)-1;
    	for($i=0; $i<=$stream; $i++){
    		$streamCounter[$i]=0;
    	}
    	for($time=$startDisplayTimeInMinutes; $time< $endDisplayTimeInMinutes; $time++){
    		$dayString=$dayString. "<TR >";
        	if($time%15==0) {
    			$dayString=$dayString. "<TD valign='top' width='10' rowspan='".$intervall."'  ".$DP_CSS[tblrow2]."  style='border-style: solid; border-width: 1'  ".$DP_CSS[small]." ><span  ".$DP_CSS[small]." >";
        		$Stunde = ($time-$time%60)/60 ;
        		$Viertel= $time%60;
        		if ($Viertel==0) $Viertel="00";
			$ttd->ttd($timestamp);
			$new_ts = mktime ( $Stunde , $Viertel , 0, $ttd->monthnumber_long , $ttd->day_of_month, $ttd->year_long );
			$dayString=$dayString."<a href=\"javascript:popup('dateplaner.php?app=date&timestamp=".$new_ts."&PHPSESSID=$PHPSESSID','Date','width=600,height=650,directories=no,toolbar=no,location=no,menubar=no,scrollbars=yes,status=yes,resizable=yes,dependent=no')\" TITLE=\"".$DP_language[new_doc]."\"  >".$Stunde.":".$Viertel."
			</a>";	
			$dayString=$dayString. "</span></TD>";  
		}
		for($i=0; $i<=$stream; $i++){
			if($arrayPadded[$i][$streamCounter[$i]][1]==$time){
				while($arrayPadded[$i][$streamCounter[$i]][1]==$time){
					if($arrayPadded[$i][$streamCounter[$i]][0]!="NULL"){
						$dayString=$dayString."<TD valign='top' rowspan=".$arrayPadded[$i][$streamCounter[$i]][5]." ".$DP_CSS[tblrow2]." style='border-style: solid; border-width: 1' ".$DP_CSS[small]." ><span ".$DP_CSS[small]." >";
						if($_SESSION[DP_JSscript] != 1) {
							$dayString=$dayString."<a href=\"dateplaner.php?app=date&date_id=";
							$dayString=$dayString.$arrayPadded[$i][$streamCounter[$i]][0];
							$dayString=$dayString."&PHPSESSID=".$session_id."&timestamp=".$startshow."\" target=\"_blank\" >";
						}else {
							$dayString=$dayString."<a href=\"javascript:popup('dateplaner.php?app=date&date_id=";
							$dayString=$dayString.$arrayPadded[$i][$streamCounter[$i]][0];
							$dayString=$dayString."&PHPSESSID=".$session_id."&timestamp=".$startshow."','Date','width=600,height=600,directories=no,toolbar=no,location=no,menubar=no,scrollbars=yes,status=yes,resizable=yes,dependent=no')\" >";
						}

						$dayString=$dayString.$arrayPadded[$i][$streamCounter[$i]][7];

						if($arrayPadded[$i][$streamCounter[$i]][7] != $arrayPadded[$i][$streamCounter[$i]][8] ) 
						{  
							$dayString=$dayString." - ".$arrayPadded[$i][$streamCounter[$i]][8];
						}
						$DateValues[shorttext] = $arrayPadded[$i][$streamCounter[$i]][3] ;
						$DateValues[text] = $arrayPadded[$i][$streamCounter[$i]][4];

						$DateValues = parseDataForOutput ($DateValues);
						$dayString=$dayString."</a> - <b>".$DateValues[shorttext]."</b>";
						$dayString=$dayString."<BR>".$DateValues[text];
						$dayString=$dayString."</span></TD>";
						$streamCounter[$i]++;
					}else{
						$dayString=$dayString."<TD rowspan=".$arrayPadded[$i][$streamCounter[$i]][5]."  ".$DP_CSS[tblrow4]."style='border-style: solid; border-width: 1' ><span ".$DP_CSS[small]. " >";
						$dayString=$dayString."<img src='".$templatefolder."/".$actualtemplate."/images/blind.gif' height='1' width='1'>";
						$dayString=$dayString."</span></TD>";
						$streamCounter[$i]++;
					}
				}
			}
		}
		$dayString=$dayString. "</TR> \n";    		
    	}
    	return $dayString;
}// end func

/**
* 	void function parseData (Array DateValues)
* 	
* 	Formate the user Date Date for the html Output.
*
* 	@param		array $DateValues
*	@return		string $Valid
*/
function  parseDataForOutput ($DateValues) {
	
	// parse text for html links and e-mail adresses
	$text = $DateValues[text];
	$urlsearch[]="/([^]_a-z0-9-=\"'\/])((https?|ftp):\/\/|www\.)([^ \r\n\(\)\*\^\$!`\"'\|\[\]\{\};<>]*)/si";
	$urlsearch[]="/^((https?|ftp):\/\/|www\.)([^ \r\n\(\)\*\^\$!`\"'\|\[\]\{\};<>]*)/si";
	$urlreplace[]="\\1<A HREF='\\2\\4' target='_blank'>\\2\\4</A>";
	$urlreplace[]="<A HREF='\\1\\3' target='_blank'>\\1\\3</A>";
	$emailsearch[]="/([\s])([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))/si";
	$emailsearch[]="/^([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))/si";
	$emailreplace[]="\\1<a href='mailto:\\2'>\\2</a>";
	$emailreplace[]="<a href='mailto:\\0'>\\0</a>";
	$text = preg_replace($urlsearch, $urlreplace, $text);
	if (strpos($text, "@")) $text = preg_replace($emailsearch, $emailreplace, $text);
	// parse text for line breaks
	$text = str_replace("\r\n","<br>" , $text);
	// parse text for images links
	$text = preg_replace("!\[img\](.*)\[/img\]!U","<img alt='\\1' src='\\1'>",$text);
	$DateValues[text] = $text;
	// parse text for html links and e-mail adresses

	$text = $DateValues[shorttext];
	// parse shorttext for html links and e-mail adresses
	$urlsearch[]="/([^]_a-z0-9-=\"'\/])((https?|ftp):\/\/|www\.)([^ \r\n\(\)\*\^\$!`\"'\|\[\]\{\};<>]*)/si";
	$urlsearch[]="/^((https?|ftp):\/\/|www\.)([^ \r\n\(\)\*\^\$!`\"'\|\[\]\{\};<>]*)/si";
	$urlreplace[]="\\1<A HREF='\\2\\4' target='_blank'>\\2\\4</A>";
	$urlreplace[]="<A HREF='\\1\\3' target='_blank'>\\1\\3</A>";
	$emailsearch[]="/([\s])([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))/si";
	$emailsearch[]="/^([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,}))/si";
	$emailreplace[]="\\1<a href='mailto:\\2'>\\2</a>";
	$emailreplace[]="<a href='mailto:\\0'>\\0</a>";
	$text = preg_replace($urlsearch, $urlreplace, $text);
	if (strpos($text, "@")) $text = preg_replace($emailsearch, $emailreplace, $text);
	$text = str_replace("\r\n","<br>" , $text);
	$DateValues[shorttext] = $text;

	Return $DateValues;

}// end func
?>