<?php
/**
* display user agreement
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/

require_once "./include/ilias_header.inc";

$tpl->addBlockFile("CONTENT", "content", "tpl.usr_agreement.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

//display buttons
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","usr_profile.php");
$tpl->setVariable("BTN_TXT",$lng->txt("personal_profile"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","usr_password.php");
$tpl->setVariable("BTN_TXT",$lng->txt("chg_password"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","usr_agreement.php");
$tpl->setVariable("BTN_TXT",$lng->txt("usr_agreement"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_row");
$tpl->parseCurrentBlock();

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("usr_agreement"));
$tpl->setVariable("TXT_AGREEMENT", $lng->txt("usr_agreement"));
$tpl->setVariable("TXT_USR_AGREEMENT", $lng->getUserAgreement()); 
$tpl->setVariable("TXT_ACCEPT", $lng->txt("accept_usr_agreement"));
$tpl->setVariable("TXT_YES", $lng->txt("yes"));
$tpl->setVariable("TXT_NO", $lng->txt("no"));
$tpl->setVariable("TXT_SUBMIT", $lng->txt("save"));

$tpl->show();

?>