<?php
/**
* mail
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";

$folder = $_POST["folder"];
if ($folder == "")
	$folder = $_GET["folder"];
if ($folder == "")
	$folder = "inbox";

$tpl->addBlockFile("CONTENT", "content", "tpl.mail.html");

//get mails from user
$myMails = new UserMail($ilias->account->Id);

$mails = $myMails->getMail($folder);

//mailactions
//possible actions are:
//read
//del
//mark_unread
//mark_read
//move_to_folder
if ($_POST["func"] != "")
{
	switch ($_POST["func"])
	{
	
		case "read":
			//check if a mail is selected
			if ($marker[0]!="")
			{
				header("location: mail_read.php?id=".$marker[0]);
			}
			break;
			
		case "del":
			for ($i=0; $i<count($marker); $i++)
			{
				if ($folder=="trash")
				{
					$myMails->setStatus($marker[$i], "rcp", "deleted");
				}
				else
					$myMails->rcpDelete($marker[$i]);
			}
			header("location: mail.php?folder=".$folder);
			break;
			
		case "mark_read":
			for ($i=0; $i<count($marker); $i++)
			{
				$myMails->setStatus($marker[$i], "rcp", "read");
			}
			header("location: mail.php?folder=".$folder);
			break;
			
		case "mark_unread":
			for ($i=0; $i<count($marker); $i++)
			{
				$myMails->setStatus($marker[$i], "rcp", "unread");
			}
			header("location: mail.php?folder=".$folder);
			break;
	}
}

include("./include/inc.mail_buttons.php");

$tpl->setVariable("ACTION", "mail.php?folder=".$folder);

//set actionsselectbox
$tpl->setVariable("TXT_ACTIONS", $lng->txt("actions"));
$tpl->setCurrentBlock("mailactions");
$tpl->setVariable("MAILACTION_VALUE", "del");
$tpl->setVariable("MAILACTION_OPTION", $lng->txt("delete_selected"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("mailactions");
$tpl->setVariable("MAILACTION_VALUE", "mark_read");
$tpl->setVariable("MAILACTION_OPTION", $lng->txt("mark_all_read"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("mailactions");
$tpl->setVariable("MAILACTION_VALUE", "mark_unread");
$tpl->setVariable("MAILACTION_OPTION", $lng->txt("mark_all_unread"));
$tpl->parseCurrentBlock();

//set movetoselectbox
$tpl->setVariable("TXT_MOVE_TO", $lng->txt("move_to"));
$tpl->setCurrentBlock("mailmove");
$tpl->setVariable("MAILMOVETO_VALUE", "inbox");
$tpl->setVariable("MAILMOVETO_OPTION", $lng->txt("inbox"));
$tpl->parseCurrentBlock();

//set folderselectbox
$tpl->setVariable("TXT_FOLDERS", $lng->txt("folders"));
$folders = $myMails->getMailFolders();

foreach ($folders as $row)
{
	$tpl->setCurrentBlock("folder");
	$tpl->setVariable("FOLDER_VALUE", $row["name"]);
	if ($folder == $row["name"])
		$tpl->setVariable("FOLDER_SELECTED", " selected");
		
	$tpl->setVariable("FOLDER_OPTION", $lng->txt($row["name"]));
	$tpl->parseCurrentBlock();
}
// output mails
foreach ($mails["msg"] as $row)
{
	$i++;
	$tpl->setCurrentBlock("row");
	$tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));

	//new mail or read mail?
	if ($row["new"] == true)
		$mailclass = "mailunread";
	else
		$mailclass = "mailread";
		
	$tpl->setVariable("MAILCLASS", $mailclass);
	$tpl->setVariable("MAIL_ID", $row["id"]);
	$tpl->setVariable("MAIL_FROM", $row["from"]);
	$tpl->setVariable("MAIL_SUBJ", $row["subject"]);
	$tpl->setVariable("MAIL_DATE", $row["datetime"]);
	$tpl->setVariable("MAIL_LINK_READ", "mail_read.php?id=".$row["id"]);
	$tpl->setVariable("MAIL_LINK_DEL", "");
	$tpl->setVariable("TXT_DELETE", $lng->txt("delete"));
	$tpl->setVariable("TXT_ARE_YOU_SURE", $lng->txt("are_you_sure"));
	$tpl->parseCurrentBlock();
}

//headline
//get parameter
$tpl->setCurrentBlock("content");
$tpl->setVariable("FOLDERNAME", $lng->txt($folder));
$tpl->setVariable("TXT_MAIL", $lng->txt("mail"));
$tpl->setVariable("TXT_MAIL_S", $lng->txt("mail_s"));
$tpl->setVariable("TXT_UNREAD", $lng->txt("unread"));
$tpl->setVariable("TXT_DELETE", $lng->txt("delete"));
$tpl->setVariable("TXT_READ", $lng->txt("read"));
$tpl->setVariable("TXT_SELECT_ALL", $lng->txt("select_all"));

$tpl->setVariable("MAIL_COUNT", $mails["count"]);
$tpl->setVariable("MAIL_COUNT_UNREAD", $mails["unread"]);
$tpl->setVariable("TXT_UNREAD_MAIL_S",$lng->txt("mail_s_unread"));
$tpl->setVariable("TXT_MAIL_S",$lng->txt("mail_s"));
//columns headlines
$tpl->setVariable("TXT_SENDER", $lng->txt("sender"));
$tpl->setVariable("TXT_SUBJECT", $lng->txt("subject"));
//	$tpl->setVariable("MAIL_SORT_SUBJ","link");
$tpl->setVariable("TXT_DATE",$lng->txt("date"));
$tpl->setVariable("DIRECTION", "up");
$tpl->parseCurrentBlock();

$tpl->show();

?>