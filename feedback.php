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

$tpl = new IntegratedTemplate($TPLPATH);
$tpl->loadTemplateFile("tpl.feedback.html", false, false);
$tpl->setVariable("TXT_PAGEHEADLINE","_Feedback");

$tpl->setVariable("TXT_MSG_DEFAULT", "_Type your message here");
$tpl->setVariable("TXT_MSG_TO", "_Message to");
$tpl->setVariable("TXT_YOUR_MSG", "_Your message");
$tpl->setVariable("TXT_SEND", "_send");

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>