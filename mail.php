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
require_once "classes/class.ilObjUser.php";
require_once "classes/class.ilMailbox.php";
require_once "classes/class.ilMail.php";

$lng->loadLanguageModule("mail");
$umail = new ilMail($_SESSION["AccountId"]);
$mbox = new ilMailBox($_SESSION["AccountId"]);

$tpl->addBlockFile("CONTENT", "content", "tpl.mail.html");
// display infopanel if something happened
$tpl->setVariable("TXT_MAILS_OF",$lng->txt("mail_mails_of"));
infoPanel();

// IF THERE IS NO OBJ_ID GIVEN GET THE ID OF MAIL ROOT NODE
if(!$_GET["mobj_id"])
{
	$_GET["mobj_id"] = $mbox->getInboxFolder();
}

// IF REQUESTED FROM mail_read.php
if(isset($_GET["mail_id"]))
{
	$_POST["cmd"]["submit"] = true;
	$_POST["action"] = 'delete';
	$_POST["mail_id"] = array($_GET["mail_id"]);
}	
setLocator($_GET["mobj_id"],$_SESSION["AccountId"],"");

if (isset($_POST["cmd"]["submit"]))
{
	switch ($_POST["action"])
	{
		case 'mark_read':
			if(is_array($_POST["mail_id"]))
			{
				$umail->markRead($_POST["mail_id"]);
			}
			else
			{
				sendInfo($lng->txt("mail_select_one"));
			}
			break;
		case 'mark_unread':
			if(is_array($_POST["mail_id"]))
			{
				$umail->markUnread($_POST["mail_id"]);
			}
			else
			{
				sendInfo($lng->txt("mail_select_one"));
			}
			break;

		case 'delete':
			// IF MAILBOX IS TRASH ASK TO CONFIRM
			if($mbox->getTrashFolder() == $_GET["mobj_id"])
			{
				if(!is_array($_POST["mail_id"]))
				{
					sendInfo($lng->txt("mail_select_one"));
					$error_delete = true;
				}
				else
				{
					sendInfo($lng->txt("mail_sure_delete"));
				}
			} // END IF MAILBOX IS TRASH FOLDER
			else
			{
				// MOVE MAILS TO TRASH
				if(!is_array($_POST["mail_id"]))
				{
					sendInfo($lng->txt("mail_select_one"));
				}
				else if($umail->moveMailsToFolder($_POST["mail_id"],$mbox->getTrashFolder()))
				{
					sendInfo($lng->txt("mail_moved_to_trash"));
				}
				else
				{
					sendInfo($lng->txt("mail_move_error"));
				}
			}
			break;

		case 'add':
			header("location: mail_options.php?mobj_id=$_GET[mobj_id]&cmd=add");
			exit;

		default:
			if(!is_array($_POST["mail_id"]))
			{
				sendInfo($lng->txt("mail_select_one"));
			}
			else if($umail->moveMailsToFolder($_POST["mail_id"],$_POST["action"]))
			{
				sendInfo($lng->txt("mail_moved"));
			}
			else
			{
				sendInfo($lng->txt("mail_move_error"));
			}
			break;
	}
}
// ONLY IF FOLDER IS TRASH, IT WAS ASKED FOR CONFIRMATION
if($mbox->getTrashFolder() == $_GET["mobj_id"])
{
	if(isset($_POST["cmd"]["confirm"]))
	{
		if(!is_array($_POST["mail_id"]))
		{
			sendInfo($lng->txt("mail_select_one"));
		}
		else if($umail->deleteMails($_POST["mail_id"]))
		{
			sendInfo($lng->txt("mail_deleted"));
		}
		else
		{
			sendInfo($lng->txt("mail_delete_error"));
		}
	}
	if(isset($_POST["cmd"]["cancel"]))
	{
		header("location: mail.php?mobj_id=$_GET[mobj_id]");
		exit;
	}
}

include("./include/inc.mail_buttons.php");

$tpl->setVariable("ACTION", "mail.php?mobj_id=$_GET[mobj_id]");

// BEGIN CONFIRM_DELETE
if($_POST["action"] == "delete" and !$error_delete and !isset($_POST["cmd"]["confirm"]) and $mbox->getTrashFolder() == $_GET["mobj_id"])
{
	$tpl->setCurrentBlock("CONFIRM_DELETE");
	$tpl->setVariable("BUTTON_CONFIRM",$lng->txt("confirm"));
	$tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));
	$tpl->parseCurrentBlock();
}

// BEGIN MAIL ACTIONS
$actions = $mbox->getActions($_GET["mobj_id"]);

$tpl->setCurrentBlock("mailactions");
foreach($actions as $key => $action)
{
	if($key == 'move')
	{
		$folders = $mbox->getSubFolders();
		foreach($folders as $folder)
		{
			$tpl->setVariable("MAILACTION_VALUE", $folder["obj_id"]);
			if($folder["type"] != 'user_folder')
			{
				$tpl->setVariable("MAILACTION_NAME",$action." ".$lng->txt("mail_".$folder["title"]));
			}
			else
			{
				$tpl->setVariable("MAILACTION_NAME",$action." ".$folder["title"]);
			}
			$tpl->parseCurrentBlock();
		}
	}
	else
	{
		$tpl->setVariable("MAILACTION_NAME", $action);
		$tpl->setVariable("MAILACTION_VALUE", $key);
		$tpl->setVariable("MAILACTION_SELECTED",$_POST["action"] == 'delete' ? 'selected' : '');
		$tpl->parseCurrentBlock();
	}
}
// END MAIL ACTIONS


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
		if($folder["type"] == 'user_folder')
		{
			$tpl->setVariable("FLAT_NAME", $folder["title"]);
		}
		else
		{
			$tpl->setVariable("FLAT_NAME", $lng->txt("mail_".$folder["title"]));
		}
		$tpl->parseCurrentBlock();
	}
	$tpl->setVariable("TXT_FOLDERS", $lng->txt("mail_change_to_folder"));
	$tpl->setVariable("FOLDER_VALUE",$lng->txt("submit"));
	$tpl->parseCurrentBlock();
}
// END SHOW_FOLDER
$tpl->setVariable("ACTION_FLAT","mail.php");

// BEGIN MAILS
$mail_data = $umail->getMailsOfFolder($_GET["mobj_id"]);
$mail_count = count($mail_data);

// TODO: READ FROM MAIL_OPTIONS
$mail_max_hits = 20;
$counter = 0;
foreach ($mail_data as $mail)
{
	// LINKBAR
	if($mail_count > $mail_max_hits)
	{
		$params = array(
			"mobj_id"		=> $_GET["mobj_id"]);
	}
	$start = $_GET["offset"];
	$linkbar = ilUtil::Linkbar(basename($_SERVER["PHP_SELF"]),$mail_count,$mail_max_hits,$start,$params);
	if ($linkbar)
	{
		$tpl->setVariable("LINKBAR", $linkbar);
	}
	if($counter >= ($start+$mail_max_hits))
	{
		break;
	}
	if($counter < $start)
	{
		++$counter;
		continue;
	}

	// END LINKBAR
	++$counter;
	$tpl->setCurrentBlock("mails");
	$tpl->setVariable("ROWCOL","tblrow".(($counter % 2)+1));
	$tpl->setVariable("MAIL_ID", $mail["mail_id"]);

	if(is_array($_POST["mail_id"]))
	{
		$tpl->setVariable("CHECKBOX_CHECKED",in_array($mail["mail_id"],$_POST["mail_id"]) ? 'checked' : "");
	}

	// GET FULLNAME OF SENDER
	$tmp_user = new ilObjUser($mail["sender_id"]);
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
	$tpl->setVariable("MAIL_DATE", ilFormat::formatDate($mail["send_time"]));
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
