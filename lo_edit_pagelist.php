<?php
/**
* editor view
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/ilias_header.inc";

$tpl = new Template("tpl.lo_edit_pagelist.html", false, false);

$tpl->setVariable("TXT_ID", $lng->txt("id"));
$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_STATUS", $lng->txt("status"));

include "./include/inc.lo_buttons.php";

$tpl->parseCurrentBlock();

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();
?>