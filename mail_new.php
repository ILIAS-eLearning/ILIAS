<?php
/**
 * mail
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include("./include/inc.main.php");

$tpl = new Template("tpl.mail_new.html", false, false);

$lng = new Language($ilias->account->data["language"]);

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("mail"));

$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","");
$tplbtn->setVariable("BTN_TXT", $lng->txt("inbox"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail_new.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("compose"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","");
$tplbtn->setVariable("BTN_TXT", $lng->txt("options"));
$tplbtn->parseCurrentBlock();

$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","");
$tplbtn->setVariable("BTN_TXT", $lng->txt("old"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","");
$tplbtn->setVariable("BTN_TXT", $lng->txt("sent"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","");
$tplbtn->setVariable("BTN_TXT", $lng->txt("saved"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","");
$tplbtn->setVariable("BTN_TXT", $lng->txt("deleted"));
$tplbtn->parseCurrentBlock();

$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl->setVariable("BUTTONS",$tplbtn->get());

$tpl->setVariable("TXT_RECIPIENT", $lng->txt("recipient"));
$tpl->setVariable("TXT_SEARCH_RECIPIENT", $lng->txt("search_recipient"));
$tpl->setVariable("TXT_CC", $lng->txt("cc"));
$tpl->setVariable("TXT_SEARCH_CC_RECIPIENT", $lng->txt("search_cc_recipient"));
$tpl->setVariable("TXT_BC", $lng->txt("bc"));
$tpl->setVariable("TXT_SEARCH_BC_RECIPIENT", $lng->txt("search_bc_recipient"));
$tpl->setVariable("TXT_SUBJECT", $lng->txt("subject"));
$tpl->setVariable("TXT_TYPE", $lng->txt("type"));
$tpl->setVariable("TXT_NORMAL", $lng->txt("normal"));
$tpl->setVariable("TXT_SYSTEM_MSG", $lng->txt("system_message"));
$tpl->setVariable("TXT_ALSO_AS_EMAIL", $lng->txt("also_as_email"));
$tpl->setVariable("TXT_URL", $lng->txt("url"));
$tpl->setVariable("TXT_URL_DESC", $lng->txt("url_description"));
$tpl->setVariable("TXT_MSG_CONTENT", $lng->txt("message_content"));
$tpl->setVariable("TXT_SEND", $lng->txt("send"));
$tpl->setVariable("TXT_MSG_SAVE", $lng->txt("save_message"));

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>