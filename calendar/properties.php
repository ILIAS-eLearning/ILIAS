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
* @author		Stefan Stahlkopf <mail@stefan-stahlkopf.de> 
* @author		Frank Gruemmert <gruemmert@feuerwelt.de>
* @version		$Id$
*/ 

// include som properties functions
include_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.parse.php');


/* ------------------------------------  generate frames --------------------------- */
// -----------------------------------------  fixed ---------------------------------//
if($_GET[action] == "next"){
		$_GET[year] = $_GET[year] + 1;
		$minical_show = setMinicalendar($_GET[month], $_GET[year], $DP_Lang, $_GET[app]);
}elseif($_GET[action] == "last"){
		$_GET[year] = $_GET[year] - 1;
		$minical_show = setMinicalendar($_GET[month], $_GET[year], $DP_Lang, $_GET[app]);
}else{
	$minical_show = setMinicalendar($_REQUEST[month],$_REQUEST[year], $DP_Lang, $_REQUEST[app]);
}
eval ("\$lefttxt = \"".$Gui->getTemplate("menue")."\";");
eval ("\$left = \"".$Gui->getTemplate("left")."\";");

// right frame is curently not used
$right	= '';

// the up frame is detect by the interface 

// down frame is curently not used
$downtext = '';
// --------------------------------------  end fixed  -------------------------------//

//*******************************************************************************************************

$tableBorder = 0;
	
if ($_REQUEST[btn_accept] == "OK" )
{
	if ($_REQUEST[newKeyword] != "")
	{
		$DB->addKeyword( $DP_UId, $_REQUEST[newKeyword] );
	}

	if ($_REQUEST[keyword] != "" AND $_REQUEST[changedKeyword] != "")
	{	
		$DB->updateKeyword( $_REQUEST[keyword], $_REQUEST[changedKeyword] );
	}
	
}

if ( $_REQUEST[btn_delete] == "$DP_language[delete]" )
{
		$DB->delKeyword( $_REQUEST[keyword] );
}

if ( $_REQUEST[btn_time] == "OK" )
{
	$startEnd = $DB->getStartEnd( $DP_UId );
	if ( $startEnd[0] != "" )
	{	// Es ist schon ein Eintrag vorhanden
		$DB->updateStartEnd( $startEnd[0], $_REQUEST[starttime], $endtime );
		
	}
	else
	{
		// Noch kein Eintrag vorhanden
		$DB->addStartEnd( $DP_UId, $_REQUEST[starttime], $_REQUEST[endtime] );
		
	}
	$_SESSION[DP_Starttime]	= $_REQUEST[starttime];
	$_SESSION[DP_Endtime]	= $_REQUEST[endtime];		
}



$keywords = $DB->getKeywords($DP_UId);
$x="";
for ($i = 0; $i < count($keywords); $i++)
{
	$keywordText = $keywords[$i][1];
	$keywordID = $keywords[$i][0];
	$options ="<option value='$keywordID'>$keywordText</option>";
	$x = $x.$options;
}
$optionBox = $x;


//*******************************************************************************************************
if($_FILES) {
	$parsedata = parse($DB,$_FILES);
}

eval ("\$centertxt = \"".$Gui->getTemplate("properties_main")."\";");


// -----------------------------------------  fixed ---------------------------------//
// frameset template
eval ("\$main = \"".$Gui->getTemplate("frames_set")."\";");
// main template
eval("doOutput(\"".$Gui->getTemplate("main")."\");"); 
// --------------------------------------  end fixed --------------------------------//
exit;
?>