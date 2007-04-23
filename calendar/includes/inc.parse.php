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
* Functions for properties.php
*
* Description:
*	These Functions are to convert CSV files to integrate it into the dateplaner
*
*
* @author		Jan H�bbers <jan@huebbers.de> 
* @author       Frank Gruemmert <gruemmert@feuerwelt.de>    
* @version		$Id$ 
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
function getCSV($file){
	$handle = fopen ($file,"r"); 
	while ($data = fgetcsv ($handle, 1000, ",")) { // Daten werden aus der Datei
		
		if (is_array($data))
		{
			foreach($data as $k => $v)
			{
				$data[$k] = strip_tags($data[$k]);
			}
		}

	    $array[] = $data;                           // in ein Array $data gelesen
	}
	fclose ($handle);
	return $array;
}

/**
*	void function showArray($array)
*
*	@param $array 
*	@global $DP_language , used by the Gui to determine the language of "today" as set in the language file.
*	@return $parsedata , the output variable of the Gui,
*/
function showArray($array){
	global $DP_language;
	$format = $DP_language[date_format];
	$parsedata.= "<b>$DP_language[insertImportDates]</b> <br><hr>";
	foreach($array as $date){
		$parsedata.= "<table><tr><td><b>$DP_language[timeslice]</b></td><td>".date($format, $date[begin])." - ".date($format, $date[end])."</td></tr>";
		$parsedata.= "<tr><td valign='top'><b>$DP_language[shorttext]:</b></td><td>".$date[short]."</td></tr>";
		$parsedata.= "<tr><td valign='top'><b>$DP_language[Text]:</b></td><td>".$date[text]."</td></tr></table>";
		
		$parsedata.= "<hr>";
	}
	return $parsedata;
}


/**
*	void function convertToDateFormat($a)
*
*	@param $a , a required timestamp of sometime during the current day.
*	@global	int $DP_UId reads the User ID from the session
*	@return array $dates 
*/
function convertToDateFormat($a){
	global $DP_UId;
	for($i=1; $i<count($a); $i++){
		$j = $i-1;
		if($a[$i][5]=="Aus"){//ganztagestermin?
			$dates[$j][begin] 	= makeTimestamp($a[$i][1], $a[$i][2]);
			$dates[$j][end]		= makeTimestamp($a[$i][3], $a[$i][4]);
			$dates[$j][user_ID]	= $DP_UId;
			$dates[$j][short]	= $a[$i][0];
			if($a[$i][16]!="") {$dates[$j][short].= " (".$a[$i][16].")";}//Ort?
			$dates[$j][text]	= $a[$i][14];
		}else{
			$dates[$j][begin] 	= makeTimestamp($a[$i][1], "00:00:00");
			$dates[$j][end]		= makeTimestamp($a[$i][3], "23:59:59");
			$dates[$j][user_ID]	= $DP_UId;
			$dates[$j][short]	= $a[$i][0];
			if($a[$i][16]!="") {$dates[$j][short].= " (".$a[$i][16].")";}//Ort?
			$dates[$j][text]	= $a[$i][14];
		}
		
	}
	return $dates;		
}

/**
*	void function makeTimestamp($day, $time)
*
*	@param $day 
*	@param $time 
*	@return $timestamp 
*/
function makeTimestamp($day, $time){
	$d = explode(".", $day );
	$t = explode(":", $time);
	$timestamp = mktime($t[0],$t[1],$t[2],$d[1],$d[0],$d[2]);
	return	$timestamp;
}

/**
*	void function parse($db, $_FILES)
*
*	@param $_FILES 
*	@param $DB , a DB class object.
*	@global $DP_language , used by the Gui to determine the language of "today" as set in the language file.
*	@return 
*/
function parse($db, $_FILES){
	global $DP_language;
	$file = $_FILES['Datei'];
	if($file[tmp_name]){		
		$array = getCSV($file[tmp_name]);
		$dates = convertToDateFormat($array);
		for($j=0; $j<count($dates);$j++){
			$return = $db->addDate (	$dates[$j][begin],
										$dates[$j][end], 
										0, 
										$dates[$j][user_ID], 
										mktime(),
										0, 
										0, 
										$dates[$j][short], 
										$dates[$j][text], 
										0, 
										$dates[$j][user_ID]);
		}
		return showArray($dates);
	}
	else{
		return $DP_language[ERROR_FILE_CSV_MSG];
	}

}
?>