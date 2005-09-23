<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source															  |
	|	Dateplaner Modul - inbox												  |													
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
* @author		Bartosz Tyrakowski <tyra@freenet.de> 
* @author		Frank Gruemmert <gruemmert@feuerwelt.de>
* @version		$Id$
* @module       date.php                            
* @modulegroup  dateplaner                   
* @package		dateplaner-frontend
*/ 

// include DP date functions
include_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.dates.php');

/* ------------------------------------  generate frames --------------------------- */
// -----------------------------------------  fixed ---------------------------------//
// the left frame is not used 
$left	= '';

// right frame is curently not used
$right	= '';

// the up frame is detect by the interface 

// down frame is curently not used
$downtext = '';
// --------------------------------------  end fixed  -------------------------------//

// kein Timpestamp vorhanden !
$DateValues				= $_REQUEST[DateValues];
$DateValues[date2]		= $_REQUEST["date2"] ;
$DateValues[date4]		= $_REQUEST["date4"] ;
$DateValues[date6]		= $_REQUEST["date6"] ;
$DateValues[group_id]	= $_REQUEST["DateValuesGroup_id"];
$DateValues[rotation]	= $_REQUEST["DateValuesRotation"];
$DateValues[whole_day]	= $_REQUEST["DateValuesWhole_day"];

// aktionen
if($_REQUEST["dateaction"]) {
	switch($_REQUEST["dateaction"]) {
		case 'insert':
			$msg = setInsertAction($_REQUEST["date2"], $_REQUEST["date4"], $_REQUEST["date6"], $DateValues, $DB);
			if($msg) {
				eval ("\$dateContent = \"".$Gui->getTemplate("date_msg")."\";");
			}else {
				echo '<script language=JavaScript> opener.location.reload(); window.close(); </script>';
			}
			break; 
		case $DP_language[dv_button_update]:
			$msg = setUpdateAction($_REQUEST["date2"], $_REQUEST["date4"], $_REQUEST["date6"], $DateValues, $DB);
			if($msg) {
				eval ("\$dateContent = \"".$Gui->getTemplate("date_msg")."\";");
			}else {
				echo '<script language=JavaScript> opener.location.reload(); window.close(); </script>';
			}
			
			break;
		case $DP_language[dv_button_delete]:
			$msg = setDeleteAction($DateValues, $DB);
			if($msg) {
				eval ("\$dateContent = \"".$Gui->getTemplate("date_msg")."\";");
			}else {
				echo '<script language=JavaScript> opener.location.reload(); window.close(); </script>';
			}
			break;
	}
}else {
	if ((!$_REQUEST["date_id"] and !$DateValues[date_id]) or $_REQUEST["dateview"] == "insert"  )
	// neuer Termin soll eingetragen werden
	{
		if($_REQUEST["dateview"] == "freetime") {
			if($timestamp != "") {
				$ttd					= new TimestampToDate;
				$ttd->ttd($timestamp);
				$DateValues[date2] 		= $DateValues[date4]	= $ttd->day_of_month."/".$ttd->monthnumber_long."/".$ttd->year_long ;
				$DateValues[begin_h]	= $DateValues[end_h]	= $ttd->hour_long ;
				$DateValues[begin_min]	= $DateValues[end_min] 	= $ttd->minutes ;
			}
		}
		if (!$timestamp = $_REQUEST["timestamp"] ) $timestamp = (int)mktime(0,0,0);
		$dateContent = setInsertDate($timestamp, $DateValues, $DB);
		$jscriptboddy = "onLoad=\"HideElements('textOne','textTwo','textThree', 'textFour'); HideThingsRotation(); HideThingsGroup()\"";
	}else 
	{
		if (!$timestamp ) $timestamp = (int)mktime(0,0,0);
		$DateArray[old_keyword_id] = $DateArray[keyword_id];
		$dateContent = setUpdateDeleteDate($timestamp, $date_id, $DateArray, $DateValues, $DB );

		if($js != "ro") 
		{
			$jscriptboddy = "onLoad=\"HideElements('textOne','textTwo','textThree', 'textFour'); HideThingsRotation()\"";
		}
		else 
		{
			$jscriptboddy = "";
		}


	}
}

eval ("\$main = \"".$Gui->getTemplate("date_main")."\";");
 
// -----------------------------------------  fixed ---------------------------------//
// frameset template not used
// main template
eval("doOutput(\"".$Gui->getTemplate("main")."\");"); 
// --------------------------------------  end fixed --------------------------------//
echo ("<noscript>".$DP_language[ERROR_JAVASCRIPT]." </noscript>");
exit;
?>