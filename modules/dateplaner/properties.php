<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source															  |
	|	Dateplaner Modul - properties											  |													
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
// include som properties functions
include_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.parse.php');


$tableBorder = 0;
// Generiere Frames
// -----------------------------------------  FEST ---------------------------------//
$minical_show = setMinicalendar($_REQUEST[month],$_REQUEST[year], $DP_Lang, $_REQUEST[app]);
//$keywords_show = showKeywords();
eval ("\$lefttxt = \"".$Gui->getTemplate("menue")."\";");
eval ("\$left = \"".$Gui->getTemplate("left")."\";");
// --------------------------------------  ende Fest -------------------------------//
//*******************************************************************************************************
	
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


// -----------------------------------------  FEST ---------------------------------//
// Frameset
eval ("\$main = \"".$Gui->getTemplate("frames_set")."\";");
// HauptTemplate
eval("doOutput(\"".$Gui->getTemplate("main")."\");"); 
// --------------------------------------  ende Fest -------------------------------//
exit;

?>
