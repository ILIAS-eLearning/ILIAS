<?php
/**
* mail
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";



$tpl->addBlockFile("CONTENT", "content", "tpl.mail_options.html");


include "./include/inc.mail_buttons.php";

$tpl->setVariable("TXT_MAIL", $lng->txt("mail"));

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_LINEBREAK", $lng->txt("linebreak"));
$tpl->setVariable("TXT_SIGNATURE", $lng->txt("signature"));
$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
$tpl->parseCurrentBlock();

$tpl->show();
?>