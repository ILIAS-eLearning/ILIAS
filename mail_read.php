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

if ($_GET["id"]=="")
	header("location: mail.php");

//get the mail from user
$myMails = new UserMail($ilias->account->Id);

$mail = $myMails->getOneMail($id);


if ($_POST["cmd"] != "")
{
	switch ($_POST["cmd"])
	{
		case "reply":
			//reply
			break;
	}
}

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("mail"));

include("./include/inc.mail_buttons.php");

$tpl->setVariable("TXT_FROM", $lng->txt("from"));
$tpl->setVariable("TXT_TO", $lng->txt("to"));
$tpl->setVariable("TXT_SUBJECT",$lng->txt("subject"));
$tpl->setVariable("TXT_DATE", $lng->txt("date"));
$tpl->setVariable("TXT_MESSAGE", $lng->txt("message"));
$tpl->setVariable("TXT_URL",$lng->txt("url"));

//buttons
$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","./mail_new.php?cmd=reply&amp;id=".$mail["id"]);
$tplbtn->setVariable("BTN_TXT", $lng->txt("reply"));

$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail_new.php?cmd=forward&amp;id=".$mail["id"]);
$tplbtn->setVariable("BTN_TXT", $lng->txt("forward"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail_print.php?id=".$mail["id"]);
$tplbtn->setVariable("BTN_TXT", $lng->txt("print"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail.php?cmd=del&amp;id=".$mail["id"]);
$tplbtn->setVariable("BTN_TXT", $lng->txt("delete"));
$tplbtn->parseCurrentBlock();

$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl->setVariable("BUTTONS2",$tplbtn->get());

//maildata
$tpl->setVariable("FROM", $mail["from"]);
$tpl->setVariable("TO", $ilias->account->Id);
$tpl->setVariable("SUBJECT", $mail["body"]);
$tpl->setVariable("DATE", $mail["datetime"]);
$tpl->setVariable("MESSAGE", $mail["body"]);
$tpl->setVariable("URL", "".$mail["url"]);

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>
