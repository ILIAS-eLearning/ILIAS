<?php
/**
* mail
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/ilias_header.inc";

$tpl = new Template("tpl.mail_options.html", false, false);

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("mail"));

include "./include/inc.mail_buttons.php";

$tpl->setVariable("TXT_LINEBREAK", $lng->txt("linebreak"));
$tpl->setVariable("TXT_SIGNATURE", $lng->txt("signature"));
$tpl->setVariable("TXT_SAVE", $lng->txt("save"));

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();
?>