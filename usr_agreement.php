<?php
/**
 * display user agreement
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias
 * @version $Id$
 */

include("./include/ilias_header.inc");
include("./include/inc.main.php");

$lng = new Language($ilias->account->data["language"]);

$tplmain->setVariable("TXT_PAGETITLE","ILIAS - ".$lng->txt("usr_agreement"));

$tplbtn = new IntegratedTemplate($TPLPATH);
$tplbtn->loadTemplateFile("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","usr_profile.php");
$tplbtn->setVariable("BTN_TXT",$lng->txt("personal_profile"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","usr_password.php");
$tplbtn->setVariable("BTN_TXT",$lng->txt("chg_password"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","usr_agreement.php");
$tplbtn->setVariable("BTN_TXT",$lng->txt("usr_agreement"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl = new IntegratedTemplate($TPLPATH);
$tpl->loadTemplateFile("tpl.usr_agreement.html", true, true);
$tpl->setVariable("BUTTONS",$tplbtn->get());
$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("usr_agreement"));
$tpl->setVariable("TXT_AGREEMENT", $lng->txt("usr_agreement"));
$tpl->setVariable("TXT_USR_AGREEMENT", $lng->getUserAgreement()); 
$tpl->setVariable("TXT_ACCEPT", $lng->txt("accept_usr_agreement"));
$tpl->setVariable("TXT_YES", $lng->txt("yes"));
$tpl->setVariable("TXT_NO", $lng->txt("no"));
$tpl->setVariable("TXT_SUBMIT", $lng->txt("save"));

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>
