<?php
/**
* feedback
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.feedback.html");
$tpl->addBlockFile("MESSAGE","message","tpl.message.html");

$recipient = $ilias->getSetting("feedback_recipient");

if (empty($recipient))
{
	$recipient = $ilias->getSetting("admin_email");
}

if ($_POST["msg_send"])
{
	if (empty($recipient))
	{
		$tpl->setVariable("MSG", $lng->txt("no_recipient")."<br/>".$lng->txt("mail_not_sent"));
	}
	else
	{
		if (mail($recipient, "ILIAS 3 Feedback", $_POST["msg_content"]))
		{
			$tpl->setVariable("MSG", $lng->txt("mail_sent"));
		}
		else
		{
			$tpl->setVariable("MSG", $lng->txt("mail_not_sent"));
		}
	}
}

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("feedback"));
$tpl->setVariable("TXT_MSG_DEFAULT",  $lng->txt("type_your_message_here"));
$tpl->setVariable("TXT_MSG_TO", $lng->txt("message_to"));
$tpl->setVariable("FEEDBACK_RECIPIENT", $recipient);
$tpl->setVariable("TXT_YOUR_MSG",  $lng->txt("your_message"));
$tpl->setVariable("TXT_SEND",  $lng->txt("send"));
$tpl->parseCurrentBlock();

// parse message block
$tpl->setCurrentBlock("message");
$tpl->parseCurrentBlock();

$tpl->show();
?>