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

$tpl = new Template("tpl.mail.html", true, true);

$lng = new Language($ilias->account->data["language"]);

$tpl->setVariable("TXT_PAGEHEADLINE","_mail");

$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","");
$tplbtn->setVariable("BTN_TXT","_Inbox");
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","");
$tplbtn->setVariable("BTN_TXT","_Compose");
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","");
$tplbtn->setVariable("BTN_TXT","_Change Options");
$tplbtn->parseCurrentBlock();

$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","");
$tplbtn->setVariable("BTN_TXT","_Old");
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","");
$tplbtn->setVariable("BTN_TXT","_Sent");
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","");
$tplbtn->setVariable("BTN_TXT","_Saved");
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","");
$tplbtn->setVariable("BTN_TXT","_Deleted");
$tplbtn->parseCurrentBlock();

$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl->setVariable("BUTTONS",$tplbtn->get());

$tpl->setVariable("TXT_DELETE_SELECTED", "_delete selected");
$tpl->setVariable("TXT_DELETE_ALL", "_delete all");
$tpl->setVariable("TXT_MARK_SELECTED", "_mark selected");
$tpl->setVariable("TXT_MARK_ALL", "_mark all");
$tpl->setVariable("TXT_EXECUTE", "_execute");

$mails = $ilias->account->getUnreadMail();

foreach ($mails as $row)
{
	$i++;
	$tpl->setCurrentBlock("row");
	$tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
	$tpl->setVariable("MAIL_SND_LNAME", $row["from"]);
	$tpl->setVariable("MAIL_SND_FNAME", "");
	$tpl->setVariable("MAIL_SND_NICK", "");
	$tpl->setVariable("MAIL_SND", "");
	$tpl->setVariable("MAIL_SUBJ", $row["subject"]);
	$tpl->setVariable("MAIL_DATE", $row["datetime"]);
	$tpl->setVariable("MAIL_LINK_READ", "mail.php?id=".$row["id"]);
	$tpl->setVariable("MAIL_LINK_DEL","");
	$tpl->setVariable("TXT_DELETE",$lng->txt("delete"));
	$tpl->setVariable("TXT_ARE_YOU_SURE",$lng->txt("are_you_sure"));
	$tpl->parseCurrentBlock();
}

//headline
$tpl->setVariable("MAIL_COUNT",count($mails));
$tpl->setVariable("TXT_MAIL_S",$lng->txt("mail_s_unread"));
//columns headlines
$tpl->setVariable("TXT_SENDER", $lng->txt("sender"));
$tpl->setVariable("TXT_SUBJECT", $lng->txt("subject"));
//	$tpl->setVariable("MAIL_SORT_SUBJ","link");
$tpl->setVariable("TXT_DATETIME",$lng->txt("date")."/".$lng->txt("time"));
//	$tpl->setVariable("MAIL_SORT_DATE","link");

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>