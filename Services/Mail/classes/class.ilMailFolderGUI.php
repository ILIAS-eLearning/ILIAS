<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	private $current_select_cmd;
	private $current_selected_cmd;

	private $tpl = null;
	private $ctrl = null;
	private $lng = null;
	
	public $umail = null;
	public $mbox = null;

	private $errorDelete = false;

	public function __construct()
	{
		global $tpl, $ilCtrl, $lng, $ilUser;

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		
		$this->umail = new ilMail($ilUser->getId());
		$this->mbox = new ilMailBox($ilUser->getId());

		if(isset($_POST['mobj_id']) && (int)$_POST['mobj_id'])
		{
			$_GET['mobj_id'] = $_POST['mobj_id'];
		}
		// IF THERE IS NO OBJ_ID GIVEN GET THE ID OF MAIL ROOT NODE
		if(!(int)$_GET['mobj_id'])
		{
			$_GET['mobj_id'] = $this->mbox->getInboxFolder();
		}
		$ilCtrl->saveParameter($this, 'mobj_id');
		$ilCtrl->setParameter($this, 'mobj_id', $_GET['mobj_id']);
		
	}

	public function executeCommand()
	{
		if ($_POST["select_cmd"])
		{
			$_GET["cmd"] = 'editFolder';

			// lower menubar execute-button
			$this->current_select_cmd = $_POST['select_cmd'];
			$this->current_selected_cmd = $_POST['selected_cmd'];
		}
		else if ($_POST["select_cmd2"])
		{
			// upper menubar execute-button
			$_GET["cmd"] = 'editFolder';
			$this->current_select_cmd = $_POST['select_cmd2'];
			$this->current_selected_cmd = $_POST['selected_cmd2'];
		}

		/* Fix: User views mail and wants to delete it... 
		   mjansen: The mail system needs a revision :-)
		*/
		if ($_GET['selected_cmd'] == "deleteMails" && $_GET["mail_id"])
		{
			$_GET["cmd"] = "editFolder";
			$this->current_selected_cmd = "deleteMails";
			$_POST["mail_id"] = array($_GET["mail_id"]);
		}
		
		/* Fix: User views mail and wants to move it...
		   mjansen: The mail system needs a revision :-)
		*/
		$cmd = $this->ctrl->getCmd();
		if($cmd == 'changeFolder' && 
		   is_numeric($_POST['selected_cmd']) && 
		   $_GET["mail_id"])
		{
			$this->current_selected_cmd = (int)$_POST['selected_cmd'];
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
				$this->tpl->setTitle($this->lng->txt("mail"));
				$this->ctrl->saveParameter($this, "mail_id");
				$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
				$profile_gui->setBackUrl($this->ctrl->getLinkTarget($this, "showMail"));
				$ret = $this->ctrl->forwardCommand($profile_gui);
				if ($ret != "")
				{
					$this->tpl->setContent($ret);
					$this->tpl->show();
				}
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
		if($this->umail->countMailsOfFolder((int)$_GET['mobj_id']))
		{	
			ilUtil::sendQuestion($this->lng->txt('mail_empty_trash_confirmation'));
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
		//$ilToolbar->addButton($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, "showMail"));
		
		$this->tpl->setVariable("TBL_TITLE", $this->lng->txt("profile_of")." ".
			ilObjUser::_lookupLogin($_GET["user"]));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath("icon_usr.gif"));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT", $this->lng->txt("public_profile"));
		
		include_once './Services/User/classes/class.ilPublicUserProfileGUI.php';		
		$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
		$profile_gui->setBackUrl($this->ctrl->getLinkTarget($this, "showMail"));
		$this->tpl->setContent($ilCtrl->getHTML($profile_gui));
		$this->tpl->show();
		
		return true;
	}	

	public function addSubfolderCommands($check_uf = false)
	{
		global $ilToolbar;

		if($_SESSION['viewmode'] != 'tree')
			$ilToolbar->addSeparator();
		
		$ilToolbar->addButton($this->lng->txt('mail_add_subfolder'), $this->ctrl->getLinkTarget($this, 'addSubFolder'));

		if($check_uf == true)
		{
			$ilToolbar->addButton($this->lng->txt('rename'), $this->ctrl->getLinkTarget($this, 'renameSubFolder'));
			$ilToolbar->addButton($this->lng->txt('delete'), $this->ctrl->getLinkTarget($this, 'deleteSubFolder'));
		}
		return true;
	}
	/**
	* Shows current folder. Current Folder is determined by $_GET["mobj_id"]
	*/
	public function showFolder($a_show_confirmation = false)
	{
		global $ilUser, $ilToolbar;

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail.html', 'Services/Mail');
		$this->tpl->setTitle($this->lng->txt('mail'));
		
		include_once 'Services/Mail/classes/class.ilMailFolderTableGUI.php';
		
		$sentFolderId = $this->mbox->getSentFolder();
		$draftsFolderId = $this->mbox->getDraftsFolder();

		$isTrashFolder = $_GET['mobj_id'] == $this->mbox->getTrashFolder();
		$isSentFolder = $_GET['mobj_id'] == $sentFolderId;	
		$isDraftFolder = $_GET['mobj_id'] == $draftsFolderId;		

		// BEGIN CONFIRM_DELETE
		if($this->current_selected_cmd == 'deleteMails' &&
			!$this->errorDelete &&
			$this->current_selected_cmd != 'confirm' &&
			$isTrashFolder)
		{
			if(isset($_REQUEST['mail_id']) && !is_array($_REQUEST['mail_id'])) $_REQUEST['mail_id'] = array($_REQUEST['mail_id']);
			foreach((array)$_REQUEST['mail_id'] as $id)
			{
				$this->tpl->setCurrentBlock('mail_ids');
				$this->tpl->setVariable('MAIL_ID_VALUE', $id);
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock('confirm_delete');
			$this->tpl->setVariable('ACTION', $this->ctrl->getFormAction($this, 'confirmDeleteMails'));
			$this->tpl->setVariable('BUTTON_CONFIRM',$this->lng->txt('confirm'));
			$this->tpl->setVariable('BUTTON_CANCEL',$this->lng->txt('cancel'));			
			$this->tpl->parseCurrentBlock();
		}

		// SHOW_FOLDER ONLY IF viewmode is flatview
		$folders = $this->mbox->getSubFolders();
		$mtree = new ilTree($ilUser->getId());
		$mtree->setTableNames('mail_tree', 'mail_obj_data');

		$check_uf = false;
		$check_local = false;

		if($_SESSION['viewmode'] == 'tree')
		{
			$folder_d = $mtree->getNodeData($_GET['mobj_id']);
			if($folder_d['m_type'] == 'user_folder')
			{
				$check_uf = true;
			}
			else if($folder_d['m_type'] == 'local')
			{
				$check_local = true;
			}
		}

		$mailtable = new ilMailFolderTableGUI($this, (int)$_GET['mobj_id'], 'showFolder');
		$mailtable->isSentFolder($isSentFolder)
				  ->isDraftFolder($isDraftFolder)
				  ->isTrashFolder($isTrashFolder)
				  ->setSelectedItems($_POST['mail_id'])
				  ->prepareHTML();

		#if(!isset($_SESSION['viewmode']) || $_SESSION['viewmode'] == 'flat')
		if($_SESSION['viewmode'] != 'tree')
		{
			$folder_options = array();
			foreach($folders as $folder)
			{
				$folder_d = $mtree->getNodeData($folder['obj_id']);
				if($folder['obj_id'] == $_GET['mobj_id'])
				{
					if($folder['type'] == 'user_folder')
					{
						$check_uf = true;
					}
					else if($folder['type'] == 'local')
					{
						$check_local = true;
						$check_uf = false;
					}
				}
				if($folder['type'] == 'user_folder')
				{
					$pre = '';
					for ($i = 2; $i < $folder_d['depth'] - 1; $i++)
						$pre .= '&nbsp';
					if ($folder_d['depth'] > 1)
						$pre .= '+';					
					$folder_options[$folder['obj_id']] = $pre.' '.$folder['title'];
				}
				else
				{
					$folder_options[$folder['obj_id']] = $this->lng->txt('mail_'.$folder['title']);
				}
			}
		}
		if($a_show_confirmation == false)
		{
			if($_SESSION['viewmode'] != 'tree')
			{
				$ilToolbar->addText($this->lng->txt('mail_change_to_folder'));
				include_once './Services/Form/classes/class.ilSelectInputGUI.php';
				$si = new ilSelectInputGUI("", "mobj_id");
				$si->setOptions($folder_options);
				$si->setValue($_GET['mobj_id']);
				$ilToolbar->addInputItem($si);

				$ilToolbar->addFormButton($this->lng->txt('change'),'showFolder');
				$ilToolbar->setFormAction($this->ctrl->getFormAction($this, 'showFolder'));
			}
			if($check_local == true || $check_uf == true)
			{
				$this->addSubfolderCommands($check_uf);
			}
		}
		// END SHOW_FOLDER
		
		// BEGIN Trash delete confirmation	
		if($mailtable->isTrashFolder() && 
		   $mailtable->getNumerOfMails() > 0)
		{
			if($this->askForConfirmation == true)
			{
				$this->tpl->setCurrentBlock('CONFIRM_EMPTY_TRASH');
                $this->tpl->setVariable('ACTION_EMPTY_TRASH_CONFIRMATION', $this->ctrl->getFormAction($this, 'performEmptyTrash'));
                $this->tpl->setVariable('BUTTON_CONFIRM_EMPTY_TRASH', $this->lng->txt('confirm'));
                $this->tpl->setVariable('BUTTON_CANCEL_EMPTY_TRASH', $this->lng->txt('cancel'));
                $this->tpl->parseCurrentBlock();
			}		
		}
		// END Trash delete confirmation		
		
		$this->tpl->setVariable('MAIL_TABLE', $mailtable->getHtml());
		$this->tpl->show();
	}
	
	public function deleteSubfolder($a_show_confirm = true)
	{
		if($a_show_confirm)
		{
			include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
			$oConfirmationGUI = new ilConfirmationGUI();

			// set confirm/cancel commands
			$oConfirmationGUI->setFormAction($this->ctrl->getFormAction($this,"showFolder"));
			$oConfirmationGUI->setHeaderText($this->lng->txt("mail_sure_delete_folder"));
			$oConfirmationGUI->setCancel($this->lng->txt("cancel"), "showFolder");
			$oConfirmationGUI->setConfirm($this->lng->txt("confirm"), "performDeleteSubfolder");
			$this->tpl->setVariable('CONFIRMATION',$oConfirmationGUI->getHTML());

			return $this->showFolder(true);
		}
		else
			return $this->showFolder(false);
	}

	public function performDeleteSubFolder()
	{
		$new_parent = $this->mbox->getParentFolderId($_GET["mobj_id"]);

		if ($this->mbox->deleteFolder($_GET["mobj_id"]))
		{			
			ilUtil::sendInfo($this->lng->txt("mail_folder_deleted"),true);
			ilUtil::redirect("ilias.php?baseClass=ilMailGUI");			
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("mail_error_delete"));
			return $this->showFolder();
		}
	}
	
	public function performAddSubFolder()
	{
		global $ilCtrl;

		if (isset($_POST["subfolder_title"]) && $_SESSION["viewmode"] == "tree") $_SESSION["subfolder_title"] = ilUtil::stripSlashes($_POST['subfolder_title']);

		if (empty($_POST['subfolder_title']))
		{
			ilUtil::sendInfo($this->lng->txt("mail_insert_folder_name"));
			return $this->addSubFolder();
		}
		else if ($mobj_id = $this->mbox->addFolder($_GET["mobj_id"], ilUtil::stripSlashes($_POST["subfolder_title"])))
		{
			$ilCtrl->saveParameter($this, 'mobj_id');
			$ilCtrl->setParameter($this, 'mobj_id', $mobj_id);

			unset($_SESSION["subfolder_title"]);
			ilUtil::sendInfo($this->lng->txt("mail_folder_created"));						
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("mail_folder_exists"));
			$this->addSubFolder();
		}
		return $this->showFolder();
	}

	public function addSubFolder()
	{
		global $ilCtrl, $tpl;
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		
		$tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail.html', 'Services/Mail');
		$tpl->setTitle($this->lng->txt('mail'));

		$oForm = new ilPropertyFormGUI();
		$oForm->setFormAction($ilCtrl->getFormAction($this, 'performAddSubFolder'));
		$oForm->setTitle('add_folder');

		//title
		$oTitle = new ilTextInputGUI();
		$oTitle->setTitle($this->lng->txt('title'));
		$oTitle->setPostVar('subfolder_title');
		$oForm->addItem($oTitle);

		$oForm->addCommandButton('performAddSubFolder',$this->lng->txt('save'));
		$oForm->addCommandButton('showFolder',$this->lng->txt('cancel'));

		$tpl->setVariable('FORM',$oForm->getHTML());
		$tpl->show();
		
		return true;
	}

	public function renameSubFolder()
	{
		global $ilCtrl, $tpl;
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

		$tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail.html', 'Services/Mail');
		$tpl->setTitle($this->lng->txt('mail'));

		$oForm = new ilPropertyFormGUI();
		$oForm->setFormAction($ilCtrl->getFormAction($this, 'performRenameSubFolder'));
		$oForm->setTitle('rename_folder');

		//title
		$oTitle = new ilTextInputGUI();
		$oTitle->setTitle($this->lng->txt('title'));
		$tmp_data = $this->mbox->getFolderData($_GET["mobj_id"]);
		$oTitle->setValue($tmp_data["title"]);
		$oTitle->setPostVar('subfolder_title');
		$oForm->addItem($oTitle);

		$oForm->addCommandButton('performRenameSubFolder',$this->lng->txt('save'));
		$oForm->addCommandButton('showFolder',$this->lng->txt('cancel'));
		$tpl->setVariable('FORM',$oForm->getHTML());
		$tpl->show();

		return true;
	}

	public function performRenameSubFolder()
	{
		if (isset($_POST["subfolder_title"]) && $_SESSION["viewmode"] == "tree") $_SESSION["subfolder_title"] = $_POST['subfolder_title'];

		$tmp_data = $this->mbox->getFolderData($_GET["mobj_id"]);
		if ($tmp_data["title"] != $_POST["subfolder_title"])
		{
			if ($_POST["subfolder_title"] == "")
			{
				ilUtil::sendInfo($this->lng->txt("mail_insert_folder_name"));
				return $this->renameSubFolder();
			}
			else
			{
				if ($this->mbox->renameFolder($_GET["mobj_id"], ilUtil::stripSlashes($_POST["subfolder_title"])))
				{
					ilUtil::sendInfo($this->lng->txt("mail_folder_name_changed"), true);
					unset($_SESSION["subfolder_title"]);
				}
				else
				{
					ilUtil::sendFailure($this->lng->txt("mail_folder_exists"));
					return $this->renameSubFolder();
				}
			}
		}
		return $this->showFolder();
	}

	public function changeFolder()
	{
		switch ($this->current_selected_cmd)
		{
			default:
				if(!(int)$_GET["mail_id"] || !(int)$this->current_selected_cmd)
				{
					ilUtil::sendInfo($this->lng->txt("mail_move_error"));
					return $this->showMail();
				}

				if ($this->umail->moveMailsToFolder(array($_GET["mail_id"]), $this->current_selected_cmd))
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
		switch ($this->current_selected_cmd)
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
						ilUtil::sendQuestion($this->lng->txt("mail_sure_delete"));
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
				else if($this->umail->moveMailsToFolder($_POST["mail_id"],$this->current_selected_cmd))
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
		$this->tpl->setTitle($this->lng->txt("mail_mails_of"));
		
		if ($_SESSION["viewmode"] == "tree") $this->tpl->setVariable("FORM_TARGET", ilFrameTargetInfo::_getFrame("MainContent"));
		
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
			if (in_array(ilObjUser::_lookupPref($mailData['sender_id'], 'public_profile'), array("y", "g")))
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
			$this->tpl->setVariable('MAIL_LOGIN', ilMail::_getIliasMailerName());
			$this->tpl->setCurrentBlock('pers_image');
			$this->tpl->setVariable('IMG_SENDER', ilUtil::getImagePath('HeaderIcon.png'));
			$this->tpl->setVariable('ALT_SENDER', ilMail::_getIliasMailerName());
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable('TXT_FROM', $this->lng->txt('from'));
		
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

		// BCC
		if($mailData['rcp_bcc'])
		{
			$this->tpl->setCurrentBlock('bcc');
			$this->tpl->setVariable('TXT_BCC',$this->lng->txt('bc'));
			// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
			$this->tpl->setVariable('BCC', ilUtil::htmlencodePlainString($this->umail->formatNamesForOutput($mailData['rcp_bcc']), false));
			$this->tpl->setVariable('CSSROW_BCC', (++$counter) % 2 ? 'tblrow1' : 'tblrow2');
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
		if($mailData['attachments'])
		{
			$this->tpl->setCurrentBlock('attachment');
			$this->tpl->setCurrentBlock('a_row');
			$counter = 1;
			foreach($mailData['attachments'] as $file)
			{
				$this->tpl->setVariable('A_CSSROW', (++$counter) % 2 ? 'tblrow1' : 'tblrow2');
				$this->tpl->setVariable('FILE', md5($file));
				$this->tpl->setVariable('FILE_NAME', $file);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setVariable('TXT_ATTACHMENT', $this->lng->txt('attachments'));
			$this->tpl->setVariable('TXT_DOWNLOAD', $this->lng->txt('download'));
			$this->tpl->parseCurrentBlock();
		}
		
		// MESSAGE
		$this->tpl->setVariable('TXT_MESSAGE', $this->lng->txt('message'));
		
		// Note: For security reasons, ILIAS only allows Plain text strings in E-Mails.
		$this->tpl->setVariable('MAIL_MESSAGE', ilUtil::htmlencodePlainString($mailData['m_message'], true));

		$isTrashFolder = false;
		if ($this->mbox->getTrashFolder() == $_GET['mobj_id'])
		{
			$isTrashFolder = true;
		}
		
		// Bottom toolbar		
		$oBottomToolbar = new ilToolbarGUI();
		
		$selectOptions = array();		
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
						$optionText = '';
						if($folder['type'] != 'user_folder')
						{							
							$optionText = $action.' '.$this->lng->txt('mail_'.$folder['title']).($folder['type'] == 'trash' ? ' ('.$this->lng->txt('delete').')' : '');
						}
						else
						{
							$optionText = $action.' '.$folder['title'];
						}
						
						$selectOptions[$folder['obj_id']] = $optionText;
					}
				}
			}
		}		
		if(is_array($selectOptions) && count($selectOptions))
		{
			include_once 'Services/Form/classes/class.ilSelectInputGUI.php';
			$oActionSelectBox = new ilSelectInputGUI('', 'selected_cmd');
			$oActionSelectBox->setOptions($selectOptions);
			$oBottomToolbar->addInputItem($oActionSelectBox);
			$oBottomToolbar->addFormButton($this->lng->txt('submit'), 'changeFolder');
		}
		
		// Navigation
		$prevMail = $this->umail->getPreviousMail($_GET['mail_id']);
		$nextMail = $this->umail->getNextMail($_GET['mail_id']);		
		if(is_array($prevMail) || is_array($nextMail))
		{
			$oBottomToolbar->addSeparator();
			
			if($prevMail['mail_id'])
			{
				$this->ctrl->setParameter($this, 'mail_id', $prevMail['mail_id']);
				$this->ctrl->setParameter($this, 'cmd', 'showMail');				
				$oBottomToolbar->addButton($this->lng->txt('previous'), $this->ctrl->getLinkTarget($this));
				$this->ctrl->clearParameters($this);
			}				
			
			if($nextMail['mail_id'])
			{
				$this->ctrl->setParameter($this, 'mail_id', $nextMail['mail_id']);
				$this->ctrl->setParameter($this, 'cmd', 'showMail');
				$oBottomToolbar->addButton($this->lng->txt('next'), $this->ctrl->getLinkTarget($this));
				$this->ctrl->clearParameters($this);
			}
		}
		
		$this->tpl->setVariable('MAIL_NAVIGATION', $oBottomToolbar->getHTML());
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
			$tplprint->setVariable('FROM', ilMail::_getIliasMailerName());
		}
		
		$tplprint->setVariable('TXT_FROM', $this->lng->txt('from'));
		
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
			require_once "./Services/Mail/classes/class.ilFileDataMail.php";
			
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