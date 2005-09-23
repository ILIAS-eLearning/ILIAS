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
* @author		Stefan Stahlkopf <mail@stefan-stahlkopf.de> 
* @author		Frank Gruemmert <gruemmert@feuerwelt.de>
* @version		$Id$
* @module       inbox.php                            
* @modulegroup  dateplaner                   
* @package		dateplaner-frontend
*/ 

// include DP inbox functions
include_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.inbox.php');

/* ------------------------------------  generate frames --------------------------- */
// -----------------------------------------  fixed ---------------------------------//
/*
if($_GET[action] == "next"){
		$_GET[year] = $_GET[year] + 1;
		$minical_show = setMinicalendar($_GET[month], $_GET[year], $DP_Lang, $_GET[app]);
}elseif($_GET[action] == "last"){
		$_GET[year] = $_GET[year] - 1;
		$minical_show = setMinicalendar($_GET[month], $_GET[year], $DP_Lang, $_GET[app]);
}else{
	$minical_show = setMinicalendar($_REQUEST[month],$_REQUEST[year], $DP_Lang, $_REQUEST[app]);
}*/

eval ("\$lefttxt = \"".$Gui->getTemplate("menue")."\";");
eval ("\$left = \"".$Gui->getTemplate("left")."\";");

// right frame is curently not used
$right	= '';

// the up frame is detect by the interface 

// down frame is curently not used
$downtext = '';
// --------------------------------------  end fixed  -------------------------------//

if ( isset($_POST[btn_accept]) ) 								// it the btton pressed
{
	$i = 0;

	while ( isset($_POST[$i]) )									// while dates exists
	{
		$array = explode("-",$_POST[$i]);						// catch string elements as an array

		switch ($array[0])										// witch radio button was pressed ?
		{
			
			case 'ok':
				$DB->applyChangedDate ($DP_UId, $array[1], $array[2] );
				break;
			case 'del':
				$DB->discardChangedDate ($DP_UId, $array[1], $array[2] );
				break;
			case 'noChange':
				// Nichts tun
				break;
		}

		$i++;
	}
}

$centertxt = setInboxView($Gui, $DB);
//echo htmlentities($centertxt); exit;
// -----------------------------------------  fixed ---------------------------------//
// frameset template
eval ("\$main = \"".$Gui->getTemplate("frames_set")."\";");
// main template
eval("doOutput(\"".$Gui->getTemplate("main")."\");");
//doOutput($Gui->getTemplate("main"));
// --------------------------------------  end fixed --------------------------------//
exit;
?>
