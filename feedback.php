<?php
/**
 * feedback
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include("./include/inc.main.php");

$tpl = new Template("tpl.feedback.html", false, false);
$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("feedback"));

$tpl->setVariable("TXT_MSG_DEFAULT",  $lng->txt("type_your_message_here"));
$tpl->setVariable("TXT_MSG_TO", $lng->txt("message_to"));
$tpl->setVariable("TXT_YOUR_MSG",  $lng->txt("your_message"));
$tpl->setVariable("TXT_SEND",  $lng->txt("send"));

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>