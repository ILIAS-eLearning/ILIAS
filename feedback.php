<?php
/**
* feedback
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.feedback.html");

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("feedback"));
$tpl->setVariable("TXT_MSG_DEFAULT",  $lng->txt("type_your_message_here"));
$tpl->setVariable("TXT_MSG_TO", $lng->txt("message_to"));
$tpl->setVariable("TXT_YOUR_MSG",  $lng->txt("your_message"));
$tpl->setVariable("TXT_SEND",  $lng->txt("send"));
$tpl->parseCurrentBlock();
$tpl->show();
?>