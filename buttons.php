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

$tpl = new Template("tpl.main_buttons.html", false, false);

//user stylehandling
if ($ilias->account->prefs["style_".$ilias->account->prefs["skin"]] != "")
{
	$style = $ilias->account->prefs["style_".$ilias->account->prefs["skin"]].".css";
}
else
{
	$style = "style.css";
}
$tpl->setVariable("LOCATION_STYLESHEET", $tplmain->tplPath."/".$style);

$tpl->show();
?>