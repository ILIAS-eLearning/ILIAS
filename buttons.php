<?php
/**
 * button bar in main screen
 * adapted from ilias 2
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias-layout
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include_once("./include/inc.main.php");

$lng = new Language($ilias->account->data["language"]);

$tpl = new Template("tpl.main_buttons.html", false, false);
$tpl->show();
//$tplmain->setVariable("PAGECONTENT",$tpl->get());
//$tplmain->show();

?>