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
require_once "./include/inc.mail.php";
require_once "classes/class.User.php";
require_once "classes/class.Mailbox.php";
require_once "classes/class.Mail.php";


$umail = new Mail($_SESSION["AccountId"]);
$mbox = new MailBox($_SESSION["AccountId"]);

$tpl->addBlockFile("CONTENT", "content", "tpl.mail.html");
// display infopanel if something happened
infoPanel();

// IF THERE IS NO OBJ_ID GIVEN GET THE ID OF MAIL ROOT NODE
if(!$_GET["mobj_id"])
{
	$mbox = new Mailbox($_SESSION["AccountId"]);
	$_GET["mobj_id"] = $mbox->getInboxFolder();
}
// IF REQUESTED FROM mail_read.php
if(isset($_GET["mail_id"]))
{
	$_POST["cmd"] = 'delete';
	$_POST["mail_id"] = array($_GET["mail_id"]);
}	

setLocator($_GET["mobj_id"],$_SESSION["AccountId"],$lng->txt("mail_mails_of"));

if ($_POST["cmd"] != "")
{
	switch ($_POST["cmd"])
	{
		case 'mark_read':
			if(is_array($_POST["mail_id"]))
			{
				$umail->markRead($_POST["mail_id"]);
			}
			else
			{
				$ilias->error_obj->sendInfo($lng->txt("mail_select_one"));
			}
			break;
		case 'mark_unread':
			if(is_array($_POST["mail_id"]))
			{
				$umail->markUnread($_POST["mail_id"]);
			}
			else
			{
				$ilias->error_obj->sendInfo($lng->txt("mail_select_one"));
			}
			break;

		case 'delete':
			// IF MAILBOX IS TRASH ASK TO CONFIRM
			if($mbox->getTrashFolder() == $_GET["mobj_id"])
			{
				if(isset($_POST["confirm"]))
				{
					if(!is_array($_POST["mail_id"]))
					{
						$ilias->error_obj->sendInfo($lng->txt("mail_select_one"));
					}
					else if($umail->deleteMails($_POST["mail_id"]))
					{
						$ilias->error_obj->sendInfo($lng->txt("mail_deleted"));
					}
					else
					{
						$ilias->error_obj->sendInfo($lng->txt("mail_delete_error"));
					}
					break;
				}
				else if(!isset($_POST["cancel"]))
				{ 
					if(!is_array($_POST["mail_id"]))
					{
						$ilias->error_obj->sendInfo($lng->txt("mail_select_one"));
						$error_delete = true;
					}
					else
					{
						$ilias->error_obj->sendInfo($lng->txt("mail_sure_delete"));
					}
				}
				else if(isset($_POST["cancel"]))
				{
					header("location: mail.php?mobj_id=$_GET[mobj_id]");
					exit;
				}
			} // END IF MAILBOX IS TRASH FOLDER
			else
			{
				// MOVE MAILS TO TRASH
				if(!is_array($_POST["mail_id"]))
				{
					$ilias->error_obj->sendInfo($lng->txt("mail_select_one"));
				}
				else if($umail->moveMailsToFolder($_POST["mail_id"],$mbox->getTrashFolder()))
				{
					$ilias->error_obj->sendInfo($lng->txt("mail_moved_to_trash"));
				}
				else
				{
					$ilias->error_obj->sendInfo($lng->txt("mail_move_error"));
				}
			}
			break;
		case 'move':
			if(!is_array($_POST["mail_id"]))
			{
				$ilias->error_obj->sendInfo($lng->txt("mail_select_one"));
			}
			else if($umail->moveMailsToFolder($_POST["mail_id"],$_POST["move_to"]))
			{
				$ilias->error_obj->sendInfo($lng->txt("mail_moved"));
			}
			else
			{
				$ilias->error_obj->sendInfo($lng->txt("mail_move_error"));
			}

			break;
	}
}

include("./include/inc.mail_buttons.php");

$tpl->setVariable("ACTION", "mail.php?mobj_id=$_GET[mobj_id]");

// BEGIN CONFIRM_DELETE
if($_POST["cmd"] == "delete" and !$error_delete and !isset($_POST["confirm"]) and $mbox->getTrashFolder() == $_GET["mobj_id"])
{
	$tpl->setCurrentBlock("CONFIRM_DELETE");
	$tpl->setVariable("BUTTON_CONFIRM",$lng->txt("confirm"));
	$tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));
	$tpl->parseCurrentBlock();
}

// BEGIN MAIL ACTIONS
$actions = $umail->getActions();

$tpl->setCurrentBlock("mailactions");
foreach($actions as $key => $action)
{
	$tpl->setVariable("MAILACTION_NAME", $key);
	$tpl->setVariable("MAILACTION_VALUE", $action);
	$tpl->setVariable("MAILACTION_SELECTED",$_POST["cmd"] == 'delete' ? 'selected' : '');
	$tpl->parseCurrentBlock();
}
// END MAIL ACTIONS

// BEGIN MAILMOVE

$folders = $mbox->getSubFolders();
$tpl->setCurrentBlock("mailmove");
foreach($folders as $folder)
{
	if($folder["obj_id"] == $_GET["mobj_id"])
	{
		continue;
	}
	$tpl->setVariable("MAILMOVE_VALUE", $folder["obj_id"]);
	$tpl->setVariable("MAILMOVE_NAME", $folder["title"]);
	$tpl->parseCurrentBlock();
}

// END MAILMOVE

// SHOW_FOLDER ONLY IF viewmode is flatview
if(!isset($_SESSION["viewmode"]) or $_SESSION["viewmode"] == 'flat')
{
	$tpl->setCurrentBlock("show_folder");
	$tpl->setCurrentBLock("flat_select");
   
	foreach($folders as $folder)
	{
		if($folder["obj_id"] == $_GET["mobj_id"])
		{
			$tpl->setVariable("FLAT_SELECTED","selected");
		}
		$tpl->setVariable("FLAT_VALUE",$folder["obj_id"]);
		$tpl->setVariable("FLAT_NAME",$folder["title"]);
		$tpl->parseCurrentBlock();
	}
	$tpl->setVariable("TXT_FOLDERS", $lng->txt("folders"));
	$tpl->parseCurrentBlock();
}
// END SHOW_FOLDER

// BEGIN MAILS
$mail_data = $umail->getMailsOfFolder($_GET["mobj_id"]);
$counter = 0;
foreach ($mail_data as $mail)
{
	++$counter;
	$tpl->setCurrentBlock("mails");
	$tpl->setVariable("ROWCOL","tblrow".(($counter % 2)+1));
	$tpl->setVariable("MAIL_ID", $mail["mail_id"]);

	if(is_array($_POST["mail_id"]))
	{
		$tpl->setVariable("CHECKBOX_CHECKED",in_array($mail["mail_id"],$_POST["mail_id"]) ? 'checked' : "");
	}

	// GET FULLNAME OF SENDER
	$tmp_user = new User($mail["sender_id"]); 
	$tpl->setVariable("MAIL_FROM", $tmp_user->getFullname());

	$tpl->setVariable("MAILCLASS", $mail["m_status"] == 'read' ? 'mailread' : 'mailunread');
	// IF ACTUAL FOLDER IS DRAFT BOX, DIRECT TO COMPOSE MESSAGE
	if($_GET["mobj_id"] == $mbox->getDraftsFolder())
	{
		$tpl->setVariable("MAIL_LINK_READ", "mail_new.php?mail_id=".
						  $mail["mail_id"]."&mobj_id=$_GET[mobj_id]&type=draft");
	}
	else
	{
		$tpl->setVariable("MAIL_LINK_READ", "mail_read.php?mail_id=".
						  $mail["mail_id"]."&mobj_id=$_GET[mobj_id]");
	}
	$tpl->setVariable("MAIL_SUBJECT", $mail["m_subject"]);
	$tpl->setVariable("MAIL_DATE", Format::formatDate($mail["send_time"]));
	$tpl->parseCurrentBlock();
}
// END MAILS

//headline
$tpl->setVariable("TXT_MAIL", $lng->txt("mail"));
$tpl->setVariable("TXT_MAIL_S", $lng->txt("mail_s"));
$tpl->setVariable("TXT_UNREAD", $lng->txt("unread"));
$tpl->setVariable("TXT_SUBMIT",$lng->txt("submit"));
$tpl->setVariable("TXT_SELECT_ALL", $lng->txt("select_all"));
$tpl->setVariable("IMGPATH",$tpl->tplPath);

// MAIL SUMMARY
$mail_counter = $umail->getMailCounterData();
$tpl->setVariable("MAIL_COUNT", $mail_counter["total"]);
$tpl->setVariable("MAIL_COUNT_UNREAD", $mail_counter["unread"]);
$tpl->setVariable("TXT_UNREAD_MAIL_S",$lng->txt("mail_s_unread"));
$tpl->setVariable("TXT_MAIL_S",$lng->txt("mail_s"));

//columns headlines
$tpl->setVariable("TXT_SENDER", $lng->txt("sender"));
$tpl->setVariable("TXT_SUBJECT", $lng->txt("subject"));
//	$tpl->setVariable("MAIL_SORT_SUBJ","link");
$tpl->setVariable("TXT_DATE",$lng->txt("date"));
$tpl->setVariable("DIRECTION", "up");

$tpl->show();

?>