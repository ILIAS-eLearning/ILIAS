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

$tpl = new Template("tpl.lo_edit_questions.html", false, false);

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("list_of_questions"));

$tpl->setVariable("TXT_ID", $lng->txt("id"));
$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_QUESTION", $lng->txt("question"));
$tpl->setVariable("TXT_TOTAL", $lng->txt("total"));
$tpl->setVariable("TXT_LINKED_PAGES", $lng->txt("linked_pages"));
$tpl->setVariable("TXT_SECTIONS", $lng->txt("sections"));
$tpl->setVariable("TXT_SET", $lng->txt("set"));

include "./include/inc.lo_buttons.php";

$tpl->parseCurrentBlock();

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();
?>