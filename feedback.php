<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


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
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

// set locator 
$tpl->setVariable("TXT_LOCATOR",$lng->txt("locator"));
$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("feedback"));
$tpl->setVariable("LINK_ITEM", "feedback.php");
$tpl->parseCurrentBlock();

// display infopanel if something happened
infoPanel();

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
		$subject = "ILIAS 3 Feedback";
		$from_host = "From: http://".$_SERVER["SERVER_NAME"].dirname($_SERVER["REQUEST_URI"])."\r\n";
		$message = $from_host.$_POST["msg_content"];
		$from_email = $ilias->account->getEmail();
		$from_sender = $ilias->account->getFullname();
		$headers = "From: ".$from_sender."<".$from_email.">\r\n";
		
		if (mail($recipient,$subject,$message,$headers))
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