<?php
/**
* editor view
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
include_once "./include/inc.header.php";

$tpl = new Template("tpl.lo_edit_glossary.html", false, false);

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("glossary"));
$tpl->setVariable("TXT_ITEM", $lng->txt("item"));

include "./include/inc.lo_buttons.php";

$tpl->parseCurrentBlock();

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();
?>