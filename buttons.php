<?php
/**
* button bar in main screen
* adapted from ilias 2
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias-layout
*/
require_once "./include/ilias_header.inc";

include("./include/inc.mainmenu.php");

//user stylehandling
if ($ilias->account->prefs["style_".$ilias->account->prefs["skin"]] != "")
{
	$style = $ilias->account->prefs["style_".$ilias->account->prefs["skin"]].".css";
}
else
{
	$style = "style.css";
}

$tplnav->setVariable("LOCATION_STYLESHEET", $tplmain->tplPath."/".$style);

$tplnav->show();
?>