<?php

require_once	('./classes/class.interface.php');

//get variables from ilias

$interface				= new Interface;

$CSCW_UId				= $interface->getUId();
$CSCW_Lang				= $interface->getLang();
$CSCW_Skin				= $interface->getSkin();
$CSCW_Style				= $interface->getStyle();
$CSCW_StyleFname		= $interface->getStyleFname();

$CSCW_GroupIds			= $interface->getGroupIds();

/* -----------------------------   Session Initialisierung -----------------------------------*/

// include CSCW Header 
require_once	('./includes/inc.cscw.header.php');

$DB					= new Database();

require_once	('./includes/inc.session.php');

// uncoment for ilias 2.3.8 Session Handler 
//db_session_write(session_id(),session_encode());

require_once	('./includes/inc.output.php');
require_once	('./includes/inc.inbox.php');

// Objects
$db			= new Database();
$Gui		= new Gui($db);

$PAGETITLE	= "Inbox";


// Generiere Frames
// -----------------------------------------  FEST ---------------------------------//
$minical_show = setMinicalendar($month,$year,$CSCW_Lang);
//$keywords_show = showKeywords();
eval ("\$lefttxt = \"".$Gui->getTemplate("menue")."\";");
eval ("\$left = \"".$Gui->getTemplate("left")."\";");


// rechter Frame wird nicht benötigt
$right	= '';
// oberer Frame automatisch über Interface bestimmt. 

// unterer Frame wird nicht benötigt
$downtext = '';

// --------------------------------------  ende Fest -------------------------------//

if ( isset( $btn_accept) ) 		// Wurde Button gedrückt
{
	$i = 0;

	while ( isset($$i) )		// Solange noch weitere Termine anstehen
	{
		$array = explode("-",$$i);	// String in Array Elemente aufteilen

		switch ($array[0])		// Welchewr Radiobutton wurde angewählt?
		{
			
			case 'ok':
				$db->applyChangedDate ($CSCW_UId, $array[1], $array[2] );
				break;
			case 'del':
				$db->discardChangedDate ($CSCW_UId, $array[1], $array[2] );
				break;
			case 'noChange':
				// Nichts tun
				break;
		}

		$i++;
	}
}
// Get Dates from Database
$newDates = $db->getchangedDates($CSCW_UId, 0);
$changedDates = $db->getchangedDates($CSCW_UId, 1);
$deletedDates = $db->getchangedDates($CSCW_UId, 2);

//*******************************************************************************************************
$DateID = 0;
// Tabelle mit neuen Terminen erstellen
if ($newDates != false)
{
	
	$array = createTable($newDates, $DateID, $Gui, $db, 1);
	$DateID = $array[0];
	$neueTermine = $array[1];
}
else
{
	$neueTermine = "<tr class='tblrow2'><td align='center' colspan=7 >$CSCW_language[no_entry]</td></tr>";
}

//*******************************************************************************************************
// Tabelle mit geänderten Terminen erstellen
if ($changedDates != false)
{
	$array = createTable($changedDates,$DateID, $Gui, $db, 1);
	$DateID = $array[0];
	$geänderteTermine = $array[1];
}
else
{
	$geänderteTermine = "<tr class='tblrow2'><td align='center' colspan=7 >$CSCW_language[no_entry]</td></tr>";
}
//*******************************************************************************************************
// Tabelle mit gelöschten Terminen erstellen
if ($deletedDates != false)
{
	$array = createTable($deletedDates,$DateID, $Gui, $db, 0);
	$DateID = $array[0];
	$gelöschteTermine = $array[1];
}
else
{
	$gelöschteTermine = "<tr class='tblrow2'><td align='center' colspan=7 >$CSCW_language[no_entry]</td></tr>";
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