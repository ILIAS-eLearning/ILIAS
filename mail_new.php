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

$tpl = new Template("tpl.mail_new.html", true, true);

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("mail"));

include "./include/inc.mail_buttons.php";

$myMails = new UserMail($ilias->account->Id);

if ($_POST["msg_send"] != "")
{
	$myMails->sendMail($_POST["msg_to"], $_POST["msg_subject"], $_POST["msg_content"]);
}

if ($_GET["func"] != "")
{
	if ($_GET["id"] != "")
	{
		$mail = $myMails->getOneMail($_GET["id"]);
		$subject = "RE: ".$mail["subject"];
		$bodylines = explode("\n", $mail["body"]);
		for ($i=0; $i<count($bodylines); $i++)
		{
			$bodylines[$i] = "> ".$bodylines[$i];
		}
		$body = implode("\n", $bodylines);
		$rcp = $mail["from"];
	}
}

$tpl->setVariable("ACTION", "mail_new.php");
$tpl->setVariable("TXT_RECIPIENT", $lng->txt("to"));
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

//mail data
$tpl->setVariable("RECIPIENT", $rcp);
$tpl->setVariable("CONTENT", $body);
$tpl->setVariable("SUBJECT", $subject);

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();
?>