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

$tpl->setVariable("TXT_DELETE_SELECTED", $lng->txt("delete_selected"));
$tpl->setVariable("TXT_DELETE_ALL", $lng->txt("delete_all"));
$tpl->setVariable("TXT_EXECUTE", $lng->txt("execute"));

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