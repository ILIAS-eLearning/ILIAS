<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source															  |
	|	Dateplaner Modul - month												  |
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
* @author		Frank Gruemmert <gruemmert@feuerwelt.de>
* @version		$Id$
*/ 

// include DP list functions
include_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.list.php');
		
if ($_REQUEST[action]=="print") { // if printview

	if($_SESSION[DP_fromtime_ts])	$fromtime_ts	= $_SESSION[DP_fromtime_ts]; 
	if($_SESSION[DP_totime_ts])		$totime_ts		= $_SESSION[DP_totime_ts];
	if($_REQUEST[fromtime_ts])		$fromtime_ts	= $_SESSION[fromtime_ts]; 
	if($_REQUEST[totime_ts])		$totime_ts		= $_REQUEST[totime_ts];
	$list_print_float = printDateList($fromtime_ts,$totime_ts, $DB);
	eval("doOutput(\"".$Gui->getTemplate("list_print")."\");"); 
}
else // if not printview
{
/* ------------------------------------  generate frames --------------------------- */
// -----------------------------------------  fixed ---------------------------------//
/*if($_GET[action] == "next"){
		$_GET[year] = $_GET[year] + 1;
		$minical_show = setMinicalendar($_GET[month], $_GET[year], $DP_Lang, $_GET[app]);
}elseif($_GET[action] == "last"){
		$_GET[year] = $_GET[year] - 1;
		$minical_show = setMinicalendar($_GET[month], $_GET[year], $DP_Lang, $_GET[app]);
}else{
	$minical_show = setMinicalendar($_REQUEST[month],$_REQUEST[year], $DP_Lang, $_REQUEST[app]);
}*/
$keywords_float	= showKeywords($_REQUEST[S_Keywords], $DB);
eval ("\$keywords_show = \"".$Gui->getTemplate("menue_keyword")."\";");
eval ("\$lefttxt = \"".$Gui->getTemplate("menue")."\";");
eval ("\$left = \"".$Gui->getTemplate("left")."\";");

// right frame is curently not used
$right	= '';

// the up frame is detect by the interface 

// down frame is curently not used
$downtext = '';
// --------------------------------------  end fixed  -------------------------------//

if ($_REQUEST[action]=="list") {
	if($_REQUEST[outdata]==True) {
		$fromtime_ts	= $_REQUEST[timestamp]; 
		$totime_ts		= $_REQUEST[timestamp2];
		$Valid[0]		= "TRUE" ;
	}
	else 
	{
		$Start_date		= explode ("/",$_REQUEST[date2]);
		$timestamp		= mktime(0,0,0,$Start_date[1],$Start_date[0],$Start_date[2]);
		$End_date		= explode ("/",$_REQUEST[date4]);
		$timestamp2		= mktime(23,59,59,$End_date[1],$End_date[0],$End_date[2]);
		$fromtime_ts	= $timestamp; 
		$totime_ts		= $timestamp2 ;
		$Valid = parseData ($fromtime_ts, $totime_ts , $Start_date, $End_date);
	}
}
if($Valid[0] == "TRUE" or !$Valid) {

	if ($fromtime_ts and $fromtime_ts != "-1") {
		$_SESSION[DP_fromtime_ts]	= $fromtime_ts;
		$_SESSION[DP_totime_ts]		= $totime_ts;
		$DP_fromtime_ts				= $fromtime_ts;
		$DP_totime_ts				= $totime_ts;
		
		if (!session_is_registered("DP_fromtime_ts")) {
			session_register ("DP_fromtime_ts");
		}
		if (!session_is_registered("DP_totime_ts")) {
			session_register ("DP_totime_ts");
		}

		$Return						= setDateList($fromtime_ts,$totime_ts, $DB);
	}
	else
	{

		if($_SESSION[DP_fromtime_ts])	$fromtime_ts	= $_SESSION[DP_fromtime_ts]; 
		if($_SESSION[DP_totime_ts])		$totime_ts		= $_SESSION[DP_totime_ts];
		
		if (!$fromtime_ts or $fromtime_ts == "-1") 
		{
			$fromtime_ts	= mktime(0,0,0);
			session_unregister ("DP_totime_ts");
			$totime_ts		= $fromtime_ts;
		} 

		$Return				= setDateList($fromtime_ts,$totime_ts, $DB);
	}
}
else 
{
	
	for($i=0; $i<(count($Valid)-1); $i++) {
		$list_navigation = $list_navigation.$Valid[$i]."<br>";
	}
	$list_navigation = $list_navigation.'
			<a href="dateplaner.php?app=list&action=list&outdata=1&timestamp='.$timestamp.'&timestamp2='.$timestamp2.'">'.$DP_language[back].'</a><br>
			';
}

$list_navigation	= $Return[0];
$list_float			= $Return[1];

eval ("\$centertxt = \"".$Gui->getTemplate("list_main")."\";");

// -----------------------------------------  fixed ---------------------------------//
// frameset template
eval ("\$main = \"".$Gui->getTemplate("frames_set")."\";");
// main template
eval("doOutput(\"".$Gui->getTemplate("main")."\");"); 
// --------------------------------------  end fixed --------------------------------//
}// end if not printview
exit;
?>
