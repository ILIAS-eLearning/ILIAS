<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

require_once 'Services/User/classes/class.ilObjUser.php';
require_once 'Services/Mail/classes/class.ilMailbox.php';
require_once 'Services/Mail/classes/class.ilMail.php';

/**
* @author Jens Conze
* @version $Id$
*
* @ingroup ServicesMail
* @ilCtrl_Calls ilMailFolderGUI: ilMailOptionsGUI, ilMailAttachmentGUI, ilMailSearchGUI
* @ilCtrl_Calls ilMailFolderGUI: ilPublicUserProfileGUI
*/

// removed ilCtrl_Calls
// ilMailAddressbookGUI

class ilMailFolderGUI
{
	private $tpl = null;
	private $ctrl = null;
	private $lng = null;
	
	private $umail = null;
	private $mbox = null;

	private $errorDelete = false;

	public function __construct()
	{
		global $tpl, $ilCtrl, $lng, $ilUser;

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		
		$this->umail = new ilMail($ilUser->getId());
		$this->mbox = new ilMailBox($ilUser->getId());

		if ($_POST["mobj_id"] != "")
		{
			$_GET["mobj_id"] = $_POST["mobj_id"];
		}
		// IF THERE IS NO OBJ_ID GIVEN GET THE ID OF MAIL ROOT NODE
		if(!$_GET["mobj_id"])
		{
			$_GET["mobj_id"] = $this->mbox->getInboxFolder();
		}
		$ilCtrl->saveParameter($this, "mobj_id");
		$ilCtrl->setParameter($this, "mobj_id", $_GET["mobj_id"]);
		
	}

	public function executeCommand()
	{
		if ($_POST["select_cmd"])
		{
			$_GET["cmd"] = 'editFolder';
		}

		/* User views mail and wants to delete it */
		if ($_GET["selected_cmd"] == "deleteMails" && $_GET["mail_id"])
		{
			$_GET["cmd"] = "editFolder";
			$_POST["selected_cmd"] = "deleteMails";
			$_POST["mail_id"] = array($_GET["mail_id"]);
		}		

		$forward_class = $this->ctrl->getNextClass($this);		
		switch($forward_class)
		{
			case 'ilmailaddressbookgui':
				include_once 'Services/Contact/classes/class.ilMailAddressbookGUI.php';

				$this->ctrl->forwardCommand(new ilMailAddressbookGUI());
				break;

			case 'ilmailoptionsgui':
				include_once 'Services/Mail/classes/class.ilMailOptionsGUI.php';

				$this->ctrl->forwardCommand(new ilMailOptionsGUI());
				break;

			case 'ilpublicuserprofilegui':
				include_once("Services/User/classes/class.ilPublicUserProfileGUI.php");
				$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
				$ret = $this->ctrl->forwardCommand($profile_gui);
				break;

			default:
				if (!($cmd = $this->ctrl->getCmd()))
				{
					$cmd = "showFolder";
				}
				$this->$cmd();
				break;
		}
		return true;
	}

	public function add()
	{
		global $lng, $ilUser;

		if($_GET["mail_id"] != "")
		{
			if (is_array($mail_data = $this->umail->getMail($_GET["mail_id"])))
			{
				require_once "Services/Contact/classes/class.ilAddressbook.php";
				$abook = new ilAddressbook($ilUser->getId());

				$tmp_user = new ilObjUser($mail_data["sender_id"]);
				if ($abook->checkEntryByLogin($tmp_user->getLogin()) > 0)
				{
					ilUtil::sendInfo($lng->txt("mail_entry_exists"));
				}
				else
				{
					$abook->addEntry($tmp_user->getLogin(),
								$tmp_user->getFirstname(),
								$tmp_user->getLastname(),
								$tmp_user->getEmail());
					ilUtil::sendInfo($lng->txt("mail_entry_added"));
				}
			}
		}
		
		$this->showMail();
		
	}
	
	/**
	* cancel Empty Trash Action and return to folder
	*/
	public function cancelEmptyTrash()
	{
		$this->showFolder();
	}
	
	/**
	* empty Trash and return to folder
	*/
	public function performEmptyTrash()
	{
		$this->umail->deleteMailsOfFolder($_GET["mobj_id"]); 

		ilUtil::sendInfo($this->lng->txt("mail_deleted"));		
		$this->showFolder();
		
		return true;
	}
	
	/**
	* confirmation message for empty trash action
	*/
	public function askForEmptyTrash()
	{
		if ($this->umail->countMailsOfFolder($_GET["mobj_id"]))
		{		
			ilUtil::sendInfo($this->lng->txt("mail_empty_trash_confirmation"));		
			$this->askForConfirmation = true;
		}
		
		$this->showFolder();
		
		return true;
	}
	
	public function showUser()
	{
		global $ilCtrl, $ilToolbar;
		
		$this->ctrl->setParameter($this, "mail_id", $_GET["mail_id"]);
		
		$this->tpl->setTitle($this->lng->txt("mail"));
		$ilToolbar->addButton($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "showMail"));
		
		$this->tpl->setVariable("TBL_TITLE", $this->lng->txt("profile_of")." ".
			ilObjUser::_lookupLogin($_GET["user"]));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath("icon_usr.gif"));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT", $this->lng->txt("public_profile"));
		
		include_once './Services/User/classes/class.ilPublicUserProfileGUI.php';		
		$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
		$this->tpl->setContent($ilCtrl->getHTML($profile_gui));
		$this->tpl->show();
		
		return true;
	}

	/**
	* Shows current folder. Current Folder is determined by $_GET["mobj_id"]
	*/
	public function showFolder()
	{
		global $ilUser;	

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail.html", "Services/Mail");
		$this->tpl->setVariable("HEADER", $this->lng->txt("mail"));

		$isTrashFolder = ($this->mbox->getTrashFolder() == $_GET["mobj_id"]) ? true : false;

		include_once 'Services/Mail/classes/class.ilMailFolderTableGUI.php';
		$mailtable = new ilMailFolderTableGUI($this->mbox, $this, $_GET["mobj_id"]);

		// BEGIN CONFIRM_DELETE
		if($_POST["selected_cmd"] == "deleteMails" &&
			!$this->errorDelete &&
			$_POST["selected_cmd"] != "confirm" &&
			$isTrashFolder)
		{
			if(isset($_REQUEST["mail_id"]) && !is_array($_REQUEST["mail_id"])) $_REQUEST["mail_id"] = array($_REQUEST["mail_id"]);
			foreach((array)$_REQUEST["mail_id"] as $id)
			{
				$this->tpl->setCurrentBlock("mail_ids");
				$this->tpl->setVariable("MAIL_ID_VALUE", $id);
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("confirm_delete");
			$this->tpl->setVariable("BUTTON_CONFIRM",$this->lng->txt("confirm"));
			$this->tpl->setVariable("BUTTON_CANCEL",$this->lng->txt("cancel"));			
			$this->tpl->parseCurrentBlock();
		}	
		
		// BEGIN MAIL ACTIONS
		$actions = $this->mbox->getActions($_GET["mobj_id"]);
		$mailtable->setMailActions($actions, $isTrashFolder);
		$this->tpl->setCurrentBlock("mailactions");
		
		foreach($actions as $key => $action)
		{
			if($key == 'moveMails')
			{
				$folders = $this->mbox->getSubFolders();
				foreach($folders as $folder)
				{
					if ($folder["type"] != 'trash' ||
						!$isTrashFolder)
					{
						$this->tpl->setVariable("MAILACTION_VALUE", $folder["obj_id"]);
						if($folder["type"] != 'user_folder')
						{
							$this->tpl->setVariable("MAILACTION_NAME",$action." ".$this->lng->txt("mail_".$folder["title"]).($folder["type"] == 'trash' ? " (".$this->lng->txt("delete").")" : ""));
						}
						else
						{
							$this->tpl->setVariable("MAILACTION_NAME",$action." ".$folder["title"]);
						}
						$this->tpl->parseCurrentBlock();
					}
				}
			}
			else
			{
				if ($key != 'deleteMails' ||
					$isTrashFolder)
				{
					$this->tpl->setVariable("MAILACTION_NAME", $action);
					$this->tpl->setVariable("MAILACTION_VALUE", $key);
					$this->tpl->setVariable("MAILACTION_SELECTED",$_POST["selected_cmd"] == 'delete' ? 'selected' : '');
					$this->tpl->parseCurrentBlock();
				}	
			}
		}
		// END MAIL ACTIONS
		
		$mtree = new ilTree($ilUser->getId());
		$mtree->setTableNames('mail_tree','mail_obj_data');
		
		// SHOW_FOLDER ONLY IF viewmode is flatview
		if(!isset($_SESSION["viewmode"]) || $_SESSION["viewmode"] == 'flat')
		{
			$this->tpl->setCurrentBlock("show_folder");
			$this->tpl->setCurrentBLock("flat_select");
		   
			foreach($folders as $folder)
			{
				$folder = $mtree->getNodeData($folder['obj_id']);
				if($folder["obj_id"] == $_GET["mobj_id"])
				{
					$this->tpl->setVariable("FLAT_SELECTED","selected");
				}
				$this->tpl->setVariable("FLAT_VALUE",$folder["obj_id"]);
				if($folder["type"] == 'user_folder')
				{
					$pre = "";
					for ($i = 2; $i < $folder["depth"] - 1; $i++)
						$pre .= "&nbsp";
					if ($folder["depth"] > 1)
						$pre .= "+";					
					$this->tpl->setVariable("FLAT_NAME", $pre." ".$folder["title"]);
				}
				else
				{
					$this->tpl->setVariable("FLAT_NAME", $this->lng->txt("mail_".$folder["title"]));
				}
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setVariable("TXT_FOLDERS", $this->lng->txt("mail_change_to_folder"));
			$this->tpl->setVariable("FOLDER_VALUE",$this->lng->txt("submit"));
			$this->tpl->parseCurrentBlock();
			
			#$this->ctrl->setParameter($this, "offset", $_GET["offset"]);
			$this->tpl->setVariable("ACTION_FLAT", $this->ctrl->getFormAction($this, 'showFolder'));
			#$this->ctrl->clearParameters($this);
		}
		// END SHOW_FOLDER		
		
		// BEGIN MAILS
		$mailData = $this->umail->getMailsOfFolder($_GET["mobj_id"]);
		$mail_count = count($mailData);
		
		if ($isTrashFolder == true && $mail_count > 0)
		{
			if ($this->askForConfirmation == true)
			{
				$this->tpl->setCurrentBlock("CONFIRM_EMPTY_TRASH");
				$this->tpl->setVariable("ACTION_EMPTY_TRASH_CONFIRMATION", $this->ctrl->getFormAction($this, 'performEmptyTrash'));
				$this->tpl->setVariable("BUTTON_CONFIRM_EMPTY_TRASH", $this->lng->txt("confirm"));
				$this->tpl->setVariable("BUTTON_CANCEL_EMPTY_TRASH", $this->lng->txt("cancel"));
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("EMPTY_TRASH");
			$this->tpl->setVariable("LINK_EMPTY_TRASH", $this->ctrl->getLinkTarget($this, "askForEmptyTrash"));
			$this->tpl->setVariable("TXT_EMPTY_TRASH", $this->lng->txt("mail_empty_trash"));
			$this->tpl->parseCurrentBlock();			
		}
		
		// TODO: READ FROM MAIL_OPTIONS
		$mail_max_hits = $ilUser->getPref('hits_per_page');
		$counter = 0;

		$folder_node = $mtree->getNodeData($_GET[mobj_id]);

		$tableData = array();
		
		foreach ($mailData as $mail)
		{
			$rowData = array();
			if($mail["sender_id"] &&
				!ilObjectFactory::ObjectIdExists($mail["sender_id"]))
			{
				--$mail_count;
				continue;
			}
			++$counter;
			
			$rowData["MAIL_ID"] = $mail["mail_id"];

			if(is_array($_POST["mail_id"]))
			{
				//$this->tpl->setVariable("CHECKBOX_CHECKED",in_array($mail["mail_id"],$_POST["mail_id"]) ? 'checked' : "");
				if (in_array($mail["mail_id"],$_POST["mail_id"]))
				{
					$rowData["CHECKED"] = " checked='checked' ";	
				}
			}

			// GET FULLNAME OF SENDER
			if($_GET['mobj_id'] == $this->mbox->getSentFolder() ||
				$_GET['mobj_id'] == $this->mbox->getDraftsFolder())
			{
				$rowData["MAIL_LOGIN"] = $this->umail->formatNamesForOutput($mail['rcp_to']);
			}
			else
			{
				if($mail["sender_id"] != ANONYMOUS_USER_ID)
				{
					$tmp_user = new ilObjUser($mail["sender_id"]); 
					if(ilObjUser::_lookupPref($mail['sender_id'], 'public_profile') == 'y')
					{
						$rowData["MAIL_FROM"] = $tmp_user->getFullname();
					}					
					if(!($login = $tmp_user->getLogin()))
					{
						$login = $mail["import_name"]." (".$this->lng->txt("user_deleted").")";
						$rowData["MAIL_LOGIN"] = $mail["import_name"]." (".$this->lng->txt("user_deleted").")"; 				
					}
					$pic_path = $tmp_user->getPersonalPicturePath("xxsmall");
					
					$rowData["IMG_SENDER"] = $pic_path;
					$rowData["ALT_SENDER"] = $login;
					$rowData["MAIL_LOGIN"] = $login;
				}
				else
				{
					$tmp_user = new ilObjUser(ANONYMOUS_USER_ID);					
					$pic_path = $tmp_user->getPersonalPicturePath('xxsmall');
					
					$rowData["IMG_SENDER"] = $pic_path;
					$rowData["ALT_SENDER"] = ilMail::_getAnonymousName();
					$rowData["MAIL_LOGIN"] = ilMail::_getAnonymousName();
				}
			}
			//$this->tpl->setVariable("MAILCLASS", $mail["m_status"] == 'read' ? 'mailread' : 'mailunread');
			$rowData["MAILCLASS"] = $mail["m_status"] == 'read' ? 'mailread' : 'mailunread';
			// IF ACTUAL FOLDER IS DRAFT BOX, DIRECT TO COMPOSE MESSAGE
			if($_GET["mobj_id"] == $this->mbox->getDraftsFolder())
			{
				$this->ctrl->setParameterByClass("ilmailformgui", "mail_id", $mail["mail_id"]);
				$this->ctrl->setParameterByClass("ilmailformgui", "type", "draft");
				$rowData["MAIL_LINK_READ"] = $this->ctrl->getLinkTargetByClass("ilmailformgui");
				$this->ctrl->clearParametersByClass("ilmailformgui");
			}
			else
			{
				$this->ctrl->setParameter($this, "mail_id", $mail["mail_id"]);
				$this->ctrl->setParameter($this, "cmd", "showMail");
				$rowData["MAIL_LINK_READ"] = $this->ctrl->getLinkTarget($this);
				$this->ctrl->clearParameters($this);
			}
			$rowData["MAIL_SUBJECT"] = htmlspecialchars($mail["m_subject"]);
			$rowData["MAIL_DATE"] = ilDatePresentation::formatDate(new ilDateTime($mail['send_time'],IL_CAL_DATETIME)); 
			$tableData[] = $rowData;
		}
		// END MAILS
		$mailtable->setData($tableData);
		
		// MAIL SUMMARY
		$mail_counter = $this->umail->getMailCounterData();

		$txt_folder = "";
		$img_folder = "";
		if($folder_node["type"] == 'user_folder')
		{
			$txt_folder = $folder_node["title"];
			$img_folder = "icon_user_folder.gif";		
		}
		else
		{
			$txt_folder = $this->lng->txt("mail_".$folder_node["title"]);
			$img_folder = "icon".substr($folder_node["title"], 1).".gif";
		}
		
		$mailtable->setTitleData($txt_folder,$mail_counter["total"],$mail_counter["unread"], $img_folder);
		$this->tpl->setVariable('MAIL_TABLE', $mailtable->getHtml());
		$this->tpl->show();
	}
	
	public function deleteFolder()
	{		
		if ($_SESSION["viewmode"] == "tree")
		{					
			ilUtil::redirect("ilias.php?baseClass=ilMailGUI&mobj_id=".$_GET["mobj_id"]."&type=confirmdelete_folderdata");
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("mail_sure_delete_folder"));
			$this->enterFolderData("saveFolderSettings", true);
			
			return true;
		}
	}
	
	public function confirmDeleteFolder()
	{
		ilUtil::sendInfo($this->lng->txt("mail_sure_delete_folder"));
		$this->enterFolderData("saveFolderSettings", true);
		
		return true;
	}

	public function performDeleteFolder()
	{
		$new_parent = $this->mbox->getParentFolderId($_GET["mobj_id"]);

		if ($this->mbox->deleteFolder($_GET["mobj_id"]))
		{			
			ilUtil::sendInfo($this->lng->txt("mail_folder_deleted"),true);
			ilUtil::redirect("ilias.php?baseClass=ilMailGUI");			
		}
		else
		{			
			if ($_SESSION["viewmode"] == "tree")
			{
				ilUtil::sendInfo($this->lng->txt("mail_error_delete"), true);
				ilUtil::redirect("ilias.php?baseClass=ilMailGUI&mobj_id=".$_GET["mobj_id"]."&type=confirmdelete_folderdata");					
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("mail_error_delete"));
				$this->enterFolderData();
				
				return true;
			}
		}	
	}
	
	public function cancelDeleteFolder()
	{
		if ($_SESSION["viewmode"] == "tree")
		{
			ilUtil::redirect("ilias.php?baseClass=ilMailGUI&mobj_id=".$_GET["mobj_id"]."&type=enter_folderdata");					
		}
		else
		{
			$this->enterFolderData();
			return true;
		}
	}

	public function cancelEnterFolderData()
	{		
		if ($_SESSION["viewmode"] == "tree")
		{
			ilUtil::redirect("ilias.php?baseClass=ilMailGUI&mobj_id=".$_GET["mobj_id"]."&target=ilmailfoldergui");					
		}
		else
		{
			$this->showFolder();
			return true;
		}
	}
	
	public function saveFolderSettings()
	{
		if (isset($_POST["folder_name_add"]) && $_SESSION["viewmode"] == "tree") $_SESSION["folder_name_add"] = $_POST['folder_name_add'];

		$tmp_data = $this->mbox->getFolderData($_GET["mobj_id"]);
		if ($tmp_data["title"] != $_POST["folder_name_add"])
		{
			if ($_POST["folder_name_add"] == "")
			{				
				if ($_SESSION["viewmode"] == "tree")
				{
					ilUtil::sendInfo($this->lng->txt("mail_insert_folder_name"), true);
					ilUtil::redirect("ilias.php?baseClass=ilMailGUI&mobj_id=".$_GET["mobj_id"]."&type=enter_folderdata");					
				}
				else
				{
					ilUtil::sendInfo($this->lng->txt("mail_insert_folder_name"));
					$this->enterFolderData();
					return true;
				}
			}
			else
			{
				if ($this->mbox->renameFolder($_GET["mobj_id"], ilUtil::stripSlashes($_POST["folder_name_add"])))
				{
					ilUtil::sendInfo($this->lng->txt("mail_folder_name_changed"), true);
					unset($_SESSION["folder_name_add"]);
				}
				else
				{					
					if ($_SESSION["viewmode"] == "tree")
					{
						ilUtil::sendInfo($this->lng->txt("mail_folder_exists"), true);
						ilUtil::redirect("ilias.php?baseClass=ilMailGUI&mobj_id=".$_GET["mobj_id"]."&type=enter_folderdata");					
					}
					else
					{
						ilUtil::sendInfo($this->lng->txt("mail_folder_exists"));
						$this->enterFolderData();
						return true;
					}
				}
			}
		}		
		
		if ($_SESSION["viewmode"] == "tree")
		{
			ilUtil::redirect("ilias.php?baseClass=ilMailGUI&mobj_id=".$_GET["mobj_id"]."&type=enter_folderdata");					
		}
		else
		{
			$this->enterFolderData();
			return true;
		}
	}
	
	public function saveSubFolderSettings()
	{
		if (isset($_POST["folder_name_add"]) && $_SESSION["viewmode"] == "tree") $_SESSION["folder_name_add"] = ilUtil::stripSlashes($_POST['folder_name_add']);
		
		if (empty($_POST['folder_name_add']))
		{	
			if ($_SESSION["viewmode"] == "tree")
			{
				ilUtil::sendInfo($this->lng->txt("mail_insert_folder_name"), true);
				ilUtil::redirect("ilias.php?baseClass=ilMailGUI&mobj_id=".$_GET["mobj_id"]."&type=add_subfolder");					
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("mail_insert_folder_name"));
				$this->addSubFolder();
				return true;
			}			
		}
		else if ($_GET["mobj_id"] = $this->mbox->addFolder($_GET["mobj_id"], ilUtil::stripSlashes($_POST["folder_name_add"])))
		{
			unset($_SESSION["folder_name_add"]);		
						
			if ($_SESSION["viewmode"] == "tree")
			{
				ilUtil::sendInfo($this->lng->txt("mail_folder_created"), true);
				ilUtil::redirect("ilias.php?baseClass=ilMailGUI&mobj_id=".$_GET["mobj_id"]);					
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("mail_folder_created"));
				$this->enterFolderData();
				return true;
			}			
		}
		else
		{
			if ($_SESSION["viewmode"] == "tree")
			{
				ilUtil::sendInfo($this->lng->txt("mail_folder_exists"), true);
				ilUtil::redirect("ilias.php?baseClass=ilMailGUI&mobj_id=".$_GET["mobj_id"]."&type=add_subfolder");					
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("mail_folder_exists"));
				$this->addSubFolder();
				return true;
			}			
		}
	}
	
	function addSubFolder()
	{
		$this->enterFolderData("saveSubFolderSettings");
		
		return true;
	}
	
	public function enterFolderData($cmd = "saveFolderSettings", $confirmDelete = false)
	{
		global $ilUser;
		
		$mtree = new ilTree($ilUser->getId());
		$mtree->setTableNames('mail_tree','mail_obj_data');
		$folder_node = $mtree->getNodeData($_GET[mobj_id]);
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_edit_user_folder.html",'Services/Mail');
		
		if ($confirmDelete)
		{
			$this->tpl->setCurrentBlock("confirm_delete");
			$this->ctrl->setParameter($this, "cmd", "post");
			$this->tpl->setVariable("ACTION_DELETE", $this->ctrl->getFormAction($this));
			$this->tpl->setVariable("FRAME_DELETE", ilFrameTargetInfo::_getFrame("MainContent"));
			$this->ctrl->clearParameters($this);
			$this->tpl->setVariable("TXT_DELETE_CONFIRM",$this->lng->txt("confirm"));
			$this->tpl->setVariable("TXT_DELETE_CANCEL",$this->lng->txt("cancel"));			
			$this->tpl->parseCurrentBlock();
		}
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");		
						
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("FRAME_ADD", ilFrameTargetInfo::_getFrame("MainContent"));
				
		if ($cmd == "saveFolderSettings")
		{
			$this->tpl->setVariable("TXT_HEADLINE", $this->lng->txt('mail_folder_edit'));
			$this->tpl->setVariable("TXT_TITLE", $this->lng->txt('name'));
			$this->tpl->setVariable("TXT_DELETE", $this->lng->txt('delete'));
			
			if (isset($_SESSION["folder_name_add"])) $title_value = ilUtil::prepareFormOutput($_SESSION["folder_name_add"], true);
			else if (isset($_POST["folder_name_add"])) $title_value = ilUtil::prepareFormOutput($_POST["folder_name_add"], true);
			else $title_value = ilUtil::stripSlashes($folder_node["title"]);
		}
		else
		{
			$this->tpl->setVariable("TXT_HEADLINE", $this->lng->txt('mail_add_subfolder'));
			$this->tpl->setVariable("TXT_TITLE", $this->lng->txt('name'));
			
			if (isset($_SESSION["folder_name_add"])) $title_value = ilUtil::prepareFormOutput($_SESSION["folder_name_add"], true);
			else if (isset($_POST["folder_name_add"])) $title_value = ilUtil::prepareFormOutput($_POST["folder_name_add"], true);
		}
		
		unset($_SESSION["folder_name_add"]);
		
		$this->tpl->setVariable("CMD_SUBMIT", $cmd);
		$this->tpl->setVariable("TXT_SUBMIT", ($cmd == "saveSubFolderSettings" ? $this->lng->txt('save') : $this->lng->txt('rename')));		
		$this->tpl->setVariable("TITLE_VALUE", $title_value);
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt('cancel'));
		
		$this->tpl->show();
		
		return true;
	}
	
	public function changeFolder()
	{
		switch ($_POST["selected_cmd"])
		{
			default:
				if ($this->umail->moveMailsToFolder(array($_GET["mail_id"]), $_POST["selected_cmd"]))
				{
					ilUtil::sendInfo($this->lng->txt("mail_moved"), true);
					$this->ctrl->redirectByClass("ilMailGUI");
				}
				else
				{
					ilUtil::sendInfo($this->lng->txt("mail_move_error"));
				}
				break;
		}
		
		$this->showMail();
		
		return true;
	}

	public function editFolder()
	{
		switch ($_POST["selected_cmd"])
		{
			case 'markMailsRead':
				if(is_array($_POST["mail_id"]))
				{
					$this->umail->markRead($_POST["mail_id"]);
				}
				else
				{
					ilUtil::sendInfo($this->lng->txt("mail_select_one"));
				}
				break;
			case 'markMailsUnread':
				if(is_array($_POST["mail_id"]))
				{
					$this->umail->markUnread($_POST["mail_id"]);
				}
				else
				{
					ilUtil::sendInfo($this->lng->txt("mail_select_one"));
				}
				break;
	
			case 'deleteMails':
				// IF MAILBOX IS TRASH ASK TO CONFIRM
				if($this->mbox->getTrashFolder() == $_GET["mobj_id"])
				{
					if(!is_array($_POST["mail_id"]))
					{
						ilUtil::sendInfo($this->lng->txt("mail_select_one"));
						$this->errorDelete = true;
					}
					else
					{
						ilUtil::sendInfo($this->lng->txt("mail_sure_delete"));
					}
				} // END IF MAILBOX IS TRASH FOLDER
				else
				{
					// MOVE MAILS TO TRASH
					if(!is_array($_POST["mail_id"]))
					{
						ilUtil::sendInfo($this->lng->txt("mail_select_one"));
					}
					else if($this->umail->moveMailsToFolder($_POST["mail_id"], $this->mbox->getTrashFolder()))
					{
						$_GET["offset"] = 0;
						ilUtil::sendInfo($this->lng->txt("mail_moved_to_trash"));
					}
					else
					{
						ilUtil::sendInfo($this->lng->txt("mail_move_error"));
					}
				}
				break;
	
			case 'add':
				$this->ctrl->setParameterByClass("ilmailoptionsgui", "cmd", "add");
				$this->ctrl->redirectByClass("ilmailoptionsgui");
	
			case 'moveMails':
			default:
				if(!is_array($_POST["mail_id"]))
				{
					ilUtil::sendInfo($this->lng->txt("mail_select_one"));
				}
				else if($this->umail->moveMailsToFolder($_POST["mail_id"],$_POST["selected_cmd"]))
				{
					ilUtil::sendInfo($this->lng->txt("mail_moved"));
				}
				else
				{
					ilUtil::sendInfo($this->lng->txt("mail_move_error"));
				}
				break;
		}
		
		$this->showFolder();
	}
	
	public function confirmDeleteMails()
	{
		// ONLY IF FOLDER IS TRASH, IT WAS ASKED FOR CONFIRMATION
		if($this->mbox->getTrashFolder() == $_GET["mobj_id"])
		{
			if(!is_array($_POST["mail_id"]))
			{
				ilUtil::sendInfo($this->lng->txt("mail_select_one"));
			}
			else if($this->umail->deleteMails($_POST["mail_id"]))
			{
				$_GET["offset"] = 0;
				ilUtil::sendInfo($this->lng->txt("mail_deleted"));
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("mail_delete_error"));
			}
		}
		
		$this->showFolder();
	}

	public function cancelDeleteMails()
	{
		//$this->ctrl->setParameter($this, "offset", $_GET["offset"]);
		$this->ctrl->redirect($this);
	}

	public function showMail()
	{
		global $ilUser;

		if ($_SESSION["mail_id"])
		{
			$_GET["mail_id"] = $_SESSION["mail_id"];
			$_SESSION["mail_id"] = "";
		}
			
		$this->umail->markRead(array($_GET["mail_id"]));

		$mailData = $this->umail->getMail($_GET["mail_id"]);

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_read.html", "Services/Mail");
		$this->tpl->setVariable("HEADER",$this->lng->txt("mail_mails_of"));
		
		include_once("./Services/Accessibility/classes/class.ilAccessKeyGUI.php");
		
		// buttons...
		// reply
		include_once("./Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php");
		$toolbar = new ilToolbarGUI();
		if($mailData["sender_id"] &&
		   $mailData["sender_id"] != ANONYMOUS_USER_ID)
		{
			$this->ctrl->setParameterByClass("ilmailformgui", "mail_id", $_GET["mail_id"]);
			$this->ctrl->setParameterByClass("ilmailformgui", "type", "reply");
			$this->ctrl->clearParametersByClass("iliasmailformgui");
			
			$toolbar->addButton($this->lng->txt("reply"), $this->ctrl->getLinkTargetByClass("ilmailformgui"),
				"", ilAccessKey::REPLY);
		}
		
		// forward
		$this->ctrl->setParameterByClass("ilmailformgui", "mail_id", $_GET["mail_id"]);
		$this->ctrl->setParameterByClass("ilmailformgui", "type", "forward");
		$this->ctrl->clearParametersByClass("iliasmailformgui");
		$toolbar->addButton($this->lng->txt("forward"), $this->ctrl->getLinkTargetByClass("ilmailformgui"),
				"", ilAccessKey::FORWARD_MAIL);
		
		// print
		$this->ctrl->setParameter($this, "mail_id", $_GET["mail_id"]);
		$this->ctrl->setParameter($this, "cmd", "printMail");
		$toolbar->addButton($this->lng->txt("print"), $this->ctrl->getLinkTarget($this),
				"_blank");
		$this->ctrl->clearParameters($this);
		
		// delete
		$this->ctrl->setParameter($this, "mail_id", $_GET["mail_id"]);
		$this->ctrl->setParameter($this, "selected_cmd", "deleteMails");
		$toolbar->addButton($this->lng->txt("delete"), $this->ctrl->getLinkTarget($this),
				"", ilAccessKey::DELETE);
		$this->ctrl->clearParameters($this);
		
		$this->tpl->setVariable("BUTTONS2",$toolbar->getHTML());

		$this->ctrl->setParameter($this, "mail_id", $_GET["mail_id"]);
		$this->tpl->setVariable("ACTION", $this->ctrl->getFormAction($this));
		$this->ctrl->clearParameters($this);

		if ($mailData["sender_id"] && 
		    $mailData["sender_id"] != $ilUser->getId() && 
			$mailData["sender_id"] != ANONYMOUS_USER_ID)
		{
			require_once "Services/Contact/classes/class.ilAddressbook.php";
			$abook = new ilAddressbook($ilUser->getId());
			$tmp_user = new ilObjUser($mailData["sender_id"]);
			if ($abook->checkEntryByLogin($tmp_user->getLogin()) == 0)
			{
				$tplbtn = new ilTemplate("tpl.buttons.html", true, true);
			
				$tplbtn->setCurrentBlock("btn_cell");
				$this->ctrl->setParameter($this, "mail_id", $_GET["mail_id"]);
				$this->ctrl->setParameter($this, "cmd", "add");
				$tplbtn->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this));
				$this->ctrl->clearParameters($this);
				$tplbtn->setVariable("BTN_TXT", $this->lng->txt("mail_add_to_addressbook"));
				$tplbtn->parseCurrentBlock();
				
				$this->tpl->setVariable("ADD_USER_BTN",$tplbtn->get());
			}			
		}
		
		// SET MAIL DATA
		$counter = 1;
		
		// FROM
		if($mailData["sender_id"] != ANONYMOUS_USER_ID)
		{
			$tmp_user = new ilObjUser($mailData['sender_id']);		
			$this->ctrl->setParameter($this, 'mail_id', $_GET['mail_id']);
			$this->ctrl->setParameter($this, 'user', $tmp_user->getId());				
			if(ilObjUser::_lookupPref($mailData['sender_id'], 'public_profile') == 'y')
			{
				$this->tpl->setVariable('PROFILE_LINK_FROM', $this->ctrl->getLinkTarget($this, 'showUser'));
				$this->tpl->setVariable('FROM', $tmp_user->getFullname());
			}			
			$this->tpl->setCurrentBlock("pers_image");
			$this->tpl->setVariable("IMG_SENDER", $tmp_user->getPersonalPicturePath("xsmall"));
			$this->tpl->setVariable("ALT_SENDER", $tmp_user->getFullname());
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("adm_content");		
			if(!($login = $tmp_user->getLogin()))
			{
				$login = $mailData["import_name"]." (".$this->lng->txt("user_deleted").")";
			}
			$this->tpl->setVariable("MAIL_LOGIN",$login);
			$this->tpl->setVariable("CSSROW_FROM", (++$counter) % 2 ? 'tblrow1' : 'tblrow2');
		}
		else
		{
			$tmp_user = new ilObjUser(ANONYMOUS_USER_ID);		
			$this->tpl->setVariable('MAIL_LOGIN', ilMail::_getAnonymousName());
			$this->tpl->setCurrentBlock('pers_image');
			$this->tpl->setVariable('IMG_SENDER', $tmp_user->getPersonalPicturePath('xsmall'));
			$this->tpl->setVariable('ALT_SENDER', ilMail::_getAnonymousName());
			$this->tpl->parseCurrentBlock();
		}
		
		// TO
		$this->tpl->setVariable('TXT_TO', $this->lng->txt('mail_to'));		
		// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
		$this->tpl->setVariable('TO', ilUtil::htmlencodePlainString($this->umail->formatNamesForOutput($mailData['rcp_to']), false));	
		$this->tpl->setVariable('CSSROW_TO', (++$counter) % 2 ? 'tblrow1' : 'tblrow2');
		
		// CC
		if($mailData['rcp_cc'])
		{
			$this->tpl->setCurrentBlock('cc');
			$this->tpl->setVariable('TXT_CC',$this->lng->txt('cc'));
			// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
			$this->tpl->setVariable('CC', ilUtil::htmlencodePlainString($this->umail->formatNamesForOutput($mailData['rcp_cc']), false));
			$this->tpl->setVariable('CSSROW_CC', (++$counter) % 2 ? 'tblrow1' : 'tblrow2');
			$this->tpl->parseCurrentBlock();
		}
		
		// SUBJECT
		$this->tpl->setVariable('TXT_SUBJECT', $this->lng->txt('subject'));
		// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
		$this->tpl->setVariable('SUBJECT', ilUtil::htmlencodePlainString($mailData['m_subject'], true));
		$this->tpl->setVariable('CSSROW_SUBJ', (++$counter) % 2 ? 'tblrow1' : 'tblrow2');
		
		// DATE
		$this->tpl->setVariable('TXT_DATE', $this->lng->txt('date'));
		$this->tpl->setVariable('DATE',ilDatePresentation::formatDate(new ilDateTime($mailData['send_time'],IL_CAL_DATETIME)));
		$this->tpl->setVariable('CSSROW_DATE', (++$counter) % 2 ? 'tblrow1' : 'tblrow2');
		
		// ATTACHMENTS
		if($mailData["attachments"])
		{
			$this->tpl->setCurrentBlock("attachment");
			$this->tpl->setCurrentBlock("a_row");
			$counter = 1;
			foreach($mailData["attachments"] as $file)
			{
				$this->tpl->setVariable("A_CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
				$this->tpl->setVariable("FILE",md5($file));
				$this->tpl->setVariable("FILE_NAME",$file);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setVariable("TXT_ATTACHMENT",$this->lng->txt("attachments"));
			$this->tpl->setVariable("TXT_DOWNLOAD",$this->lng->txt("download"));
			$this->tpl->parseCurrentBlock();
		}
		
		// MESSAGE
		$this->tpl->setVariable("TXT_MESSAGE", $this->lng->txt("message"));
		
		// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
		$this->tpl->setVariable("MAIL_MESSAGE", ilUtil::htmlencodePlainString($mailData["m_message"], true));
		
		$isTrashFolder = false;
		if ($this->mbox->getTrashFolder() == $_GET["mobj_id"])
		{
			$isTrashFolder = true;
		}
		$actions = $this->mbox->getActions($_GET["mobj_id"]);				
		foreach($actions as $key => $action)
		{
			if($key == 'moveMails')
			{
				$folders = $this->mbox->getSubFolders();
				foreach($folders as $folder)
				{
					if ($folder["type"] != 'trash' ||
						!$isTrashFolder)
					{
						$this->tpl->setCurrentBlock("movemail");
						$this->tpl->setVariable("MOVEMAIL_VALUE", $folder["obj_id"]);
						if($folder["type"] != 'user_folder')
						{
							$this->tpl->setVariable("MOVEMAIL_NAME",$action." ".$this->lng->txt("mail_".$folder["title"]).($folder["type"] == 'trash' ? " (".$this->lng->txt("delete").")" : ""));
						}
						else
						{
							$this->tpl->setVariable("MOVEMAIL_NAME",$action." ".$folder["title"]);
						}
						$this->tpl->parseCurrentBlock();
					}
				}
			}
		}	
		if ($_SESSION["viewmode"] == "tree") $this->tpl->setVariable("FORM_TARGET", ilFrameTargetInfo::_getFrame("MainContent"));
		$this->tpl->setVariable("TXT_MOVEMAIL_SEND", $this->lng->txt('submit'));
		
		// PREV- & NEXT-BUTTON
		
		$prevMail = $this->umail->getPreviousMail($_GET["mail_id"]);
		$nextMail = $this->umail->getNextMail($_GET["mail_id"]);
		
		if (is_array($prevMail) || is_array($nextMail))
		{			
			$show = false;
			
			$tplbtn = new ilTemplate("tpl.buttons.html", true, true);			
					
			if ($prevMail["mail_id"])
			{
				$show = true;
				
				$tplbtn->setCurrentBlock("btn_cell");
				$this->ctrl->setParameter($this, "mail_id", $prevMail["mail_id"]);
				$this->ctrl->setParameter($this, "cmd", "showMail");
				$tplbtn->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this));
				$this->ctrl->clearParameters($this);
				$tplbtn->setVariable("BTN_TXT", $this->lng->txt("previous"));
				$tplbtn->parseCurrentBlock();
			}				
			
			if ($nextMail["mail_id"])
			{
				$show = true;
				
				$tplbtn->setCurrentBlock("btn_cell");
				$this->ctrl->setParameter($this, "mail_id", $nextMail["mail_id"]);
				$this->ctrl->setParameter($this, "cmd", "showMail");
				$tplbtn->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this));
				$this->ctrl->clearParameters($this);
				$tplbtn->setVariable("BTN_TXT", $this->lng->txt("next"));
				$tplbtn->parseCurrentBlock();
			}
			
			if ($show == true)$this->tpl->setVariable("NAV_BUTTONS", $tplbtn->get());
		}			

		$this->tpl->show();
	}	

	public function printMail()
	{
		$tplprint = new ilTemplate("Services/Mail/templates/default/tpl.mail_print.html",true,true,true);
		$tplprint->setVariable("JSPATH",$tpl->tplPath);
		
		//get the mail from user
		$mailData = $this->umail->getMail($_GET["mail_id"]);
		
		// SET MAIL DATA
		// FROM		 
		if($mailData["sender_id"] != ANONYMOUS_USER_ID)
		{
			$tmp_user = new ilObjUser($mailData["sender_id"]);
			if(!($login = $tmp_user->getFullname()))
			{
				$login = $mailData["import_name"]." (".$this->lng->txt("user_deleted").")";
			}
			$tplprint->setVariable("FROM", $login);
		}
		else
		{
			$tplprint->setVariable('FROM', ilMail::_getAnonymousName());
		}
		
		// TO
		$tplprint->setVariable("TXT_TO", $this->lng->txt("mail_to"));
		$tplprint->setVariable("TO", $mailData["rcp_to"]);
		
		// CC
		if($mailData["rcp_cc"])
		{
			$tplprint->setCurrentBlock("cc");
			$tplprint->setVariable("TXT_CC",$this->lng->txt("cc"));
			$tplprint->setVariable("CC",$mailData["rcp_cc"]);
			$tplprint->parseCurrentBlock();
		}
		// SUBJECT
		$tplprint->setVariable("TXT_SUBJECT",$this->lng->txt("subject"));
		$tplprint->setVariable("SUBJECT",htmlspecialchars($mailData["m_subject"]));
		
		// DATE
		$tplprint->setVariable("TXT_DATE", $this->lng->txt("date"));
		$tplprint->setVariable("DATE", ilDatePresentation::formatDate(new ilDateTime($mailData["send_time"],IL_CAL_DATETIME)));
		
		
		// MESSAGE
		$tplprint->setVariable("TXT_MESSAGE", $this->lng->txt("message"));
		$tplprint->setVariable("MAIL_MESSAGE", nl2br(htmlspecialchars($mailData["m_message"])));
		
		
		$tplprint->show();
	}

	function deliverFile()
	{
		if ($_SESSION["mail_id"])
		{
			$_GET["mail_id"] = $_SESSION["mail_id"];
		}
		$_SESSION["mail_id"] = "";

		$filename = ($_SESSION["filename"]
						? $_SESSION["filename"]
						: ($_POST["filename"]
							? $_POST["filename"]
							: $_GET["filename"]));
		$_SESSION["filename"] = "";

		if ($filename != "")
		{
			require_once "classes/class.ilFileDataMail.php";
			
			// secure filename
			$filename = str_replace("..", "", $filename);
			
			$mfile = new ilFileDataMail($_SESSION["AccountId"]);
			if(!is_array($file = $mfile->getAttachmentPathByMD5Filename($filename, $_GET['mail_id'])))
			{
				ilUtil::sendInfo($this->lng->txt('mail_error_reading_attachment'));
				$this->showMail();
			}
			else
			{
				ilUtil::deliverFile($file['path'], $file['filename']);
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('mail_select_attachment'));
			$this->showMail();
		}
	}

}
?>