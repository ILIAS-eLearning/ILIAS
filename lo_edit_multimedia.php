<?php
/**
* editor view
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";

$tpl = new ilTemplate("tpl.lo_edit_multimedia.html", false, false);

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("multimedia"));

$tpl->setVariable("TXT_NEW", $lng->txt("new"));
$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_NO_OBJECTS", $lng->txt("no_objects"));
$tpl->setVariable("TXT_NAME", $lng->txt("name"));

include "./include/inc.lo_buttons.php";

$tpl->parseCurrentBlock();

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();
?>
