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
* @author		Frank Gruemmert <gruemmert@feuerwelt.de>
* @version		$Id$
*/ 

// include DP month functions
include_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.week.php');

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

if ($_REQUEST[timestamp]) {
	$Return = setWeekView($_REQUEST[timestamp], $DB);
}else {
	$Return = setWeekView(mktime(0,0,0), $DB);
}

$week_navigation	= $Return[0];
$week_float			= $Return[1];
$S_Datum 			= $Return[2];
eval ("\$centertxt = \"".$Gui->getTemplate("week_main")."\";");

// -----------------------------------------  fixed ---------------------------------//
// frameset template
eval ("\$main = \"".$Gui->getTemplate("frames_set")."\";");
// main template
eval("doOutput(\"".$Gui->getTemplate("main")."\");"); 
// --------------------------------------  end fixed --------------------------------//
exit;
?>
