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
// include DP month functions
include_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.month.php');
		
// Generiere Frames
// -----------------------------------------  FEST ---------------------------------//
$minical_show = setMinicalendar($_REQUEST[month],$_REQUEST[year], $DP_Lang, $_REQUEST[app]);
$keywords_float	= showKeywords($_REQUEST[S_Keywords], $DB);
eval ("\$keywords_show = \"".$Gui->getTemplate("menue_keyword")."\";");
eval ("\$lefttxt = \"".$Gui->getTemplate("menue")."\";");
eval ("\$left = \"".$Gui->getTemplate("left")."\";");
// --------------------------------------  ende Fest -------------------------------//

if ($_REQUEST["timestamp"] ) {
	 $Return = setMonthView($_REQUEST["timestamp"], $_REQUEST["week_s"], False, $DB);
}else {
	 $first_change = true;
	 $Return = setMonthView(mktime(0,0,0), $_REQUEST["week_s"], $_REQUEST["first_change"], $DB);
}
$month_navigation	= $Return[0];
$month_float		= $Return[1];
$month_string		= $Return[2];
eval ("\$centertxt = \"".$Gui->getTemplate("month_main")."\";");

// -----------------------------------------  FEST ---------------------------------//
// Frameset
eval ("\$main = \"".$Gui->getTemplate("frames_set")."\";");
// HauptTemplate
eval("doOutput(\"".$Gui->getTemplate("main")."\");"); 
// --------------------------------------  ende Fest -------------------------------//
exit;

?>
