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
// include DP inbox functions
include_once	('.'.DATEPLANER_ROOT_DIR.'/includes/inc.inbox.php');
// Generiere Frames
// -----------------------------------------  FEST ---------------------------------//
$minical_show = setMinicalendar($_REQUEST[month],$_REQUEST[year], $DP_Lang, $_REQUEST[app]);
eval ("\$lefttxt = \"".$Gui->getTemplate("menue")."\";");
eval ("\$left = \"".$Gui->getTemplate("left")."\";");

// rechter Frame wird nicht benötigt
$right	= '';
// oberer Frame automatisch über Interface bestimmt. 

// unterer Frame wird nicht benötigt
$downtext = '';

// --------------------------------------  ende Fest -------------------------------//

if ( isset($_POST[btn_accept]) ) 		// Wurde Button gedrückt
{
	$i = 0;

	while ( isset($_POST[$i]) )		// Solange noch weitere Termine anstehen
	{
		$array = explode("-",$_POST[$i]);	// String in Array Elemente aufteilen

		switch ($array[0])		// Welchewr Radiobutton wurde angewählt?
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
// Get Dates from Database
$newDates		= $DB->getchangedDates($DP_UId, 0);
$changedDates	= $DB->getchangedDates($DP_UId, 1);
$deletedDates	= $DB->getchangedDates($DP_UId, 2);

//*******************************************************************************************************
$DateID = 0;
// Tabelle mit neuen Terminen erstellen
if ($newDates != false)
{
	
	$array = createTable($newDates, $DateID, $Gui, $DB, 1);
	$DateID = $array[0];
	$neueTermine = $array[1];
}
else
{
	$neueTermine = "<tr class='tblrow2'><td align='center' colspan=7 >$DP_language[no_entry]</td></tr>";
}

//*******************************************************************************************************
// Tabelle mit geänderten Terminen erstellen
if ($changedDates != false)
{
	$array = createTable($changedDates,$DateID, $Gui, $DB, 1);
	$DateID = $array[0];
	$geänderteTermine = $array[1];
}
else
{
	$geänderteTermine = "<tr class='tblrow2'><td align='center' colspan=7 >$DP_language[no_entry]</td></tr>";
}
//*******************************************************************************************************
// Tabelle mit gelöschten Terminen erstellen
if ($deletedDates != false)
{
	$array = createTable($deletedDates,$DateID, $Gui, $DB, 0);
	$DateID = $array[0];
	$gelöschteTermine = $array[1];
}
else
{
	$gelöschteTermine = "<tr class='tblrow2'><td align='center' colspan=7 >$DP_language[no_entry]</td></tr>";
}
//*******************************************************************************************************
$tableBorder = 1;

eval ("\$centertxt = \"".$Gui->getTemplate("inbox_main")."\";");


// -----------------------------------------  FEST ---------------------------------//
// Frameset
eval ("\$main = \"".$Gui->getTemplate("frames_set")."\";");
// HauptTemplate
eval("doOutput(\"".$Gui->getTemplate("main")."\");"); 
// --------------------------------------  ende Fest -------------------------------//




//*******************************************************************************************************
?>