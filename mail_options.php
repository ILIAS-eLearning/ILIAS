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

$tpl = new Template("tpl.mail_options.html", false, false);

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
$tplbtn->setVariable("BTN_LINK", "mail_options.php");
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

$tpl->setVariable("TXT_LINEBREAK", $lng->txt("linebreak"));
$tpl->setVariable("TXT_SIGNATURE", $lng->txt("signature"));
$tpl->setVariable("TXT_SAVE", $lng->txt("save"));

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>