<?php
/**
 * mail
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @author Eva Wenzl <ewenzl@databay.de>
 * @package ilias
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include("./include/inc.main.php");

$tpl = new Template("tpl.mail_read.html", false, false);

//get mails from user
$myMails = new UserMail($ilias->db, $ilias->account->Id);

$mails = $myMails->getMail();

if ($_POST["func"] != "")
{
	switch ($_POST["func"])
	{
		case "reply":
			//reply
			break;
	}
}

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("mail"));

$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","./mail.php?folder=inbox");
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

$tpl->setVariable("TXT_FROM", $lng->txt("from"));
$tpl->setVariable("TXT_TO", $lng->txt("to"));
$tpl->setVariable("TXT_SUBJECT",$lng->txt("subject"));
$tpl->setVariable("TXT_DATE", $lng->txt("date"));
$tpl->setVariable("TXT_REPLY", $lng->txt("reply"));
$tpl->setVariable("TXT_FORWARD",$lng->txt("forward"));
$tpl->setVariable("TXT_PRINT", $lng->txt("print"));
$tpl->setVariable("TXT_DELETE",$lng->txt("delete"));
$tpl->setVariable("TXT_MESSAGE", $lng->txt("message"));
$tpl->setVariable("TXT_URL",$lng->txt("url"));


$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>