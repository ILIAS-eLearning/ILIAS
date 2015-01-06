<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/classes/class.ilObjUser.php';
require_once 'Services/Mail/classes/class.ilMailbox.php';
require_once 'Services/Mail/classes/class.ilMail.php';
require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
include_once 'Services/Mail/classes/class.ilMailFolderTableGUI.php';

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
		$_GET['mobj_id'] = (int)$_GET['mobj_id'];
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
		$this->umail->deleteMailsOfFolder($_GET['mobj_id']); 
		ilUtil::sendInfo($this->lng->txt('mail_deleted'));
		$this->showFolder();
	}
	
	/**
	* confirmation message for empty trash action
	*/
	public function askForEmptyTrash()
	{
		if($this->umail->countMailsOfFolder((int)$_GET['mobj_id']))
		{	
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
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath("icon_usr.svg"));
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

		if('tree' != ilSession::get(ilMailGUI::VIEWMODE_SESSION_KEY))
		{
			$ilToolbar->addSeparator();
		}
		
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
		/**
		 * @var $ilUser ilObjUser
		 * @var $ilToolbar ilToolbarGUI
		 */
		global $ilUser, $ilToolbar;

		$this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail.html', 'Services/Mail');
		$this->tpl->setTitle($this->lng->txt('mail'));

		$sentFolderId = $this->mbox->getSentFolder();
		$draftsFolderId = $this->mbox->getDraftsFolder();

		$isTrashFolder = $_GET['mobj_id'] == $this->mbox->getTrashFolder();
		$isSentFolder = $_GET['mobj_id'] == $sentFolderId;	
		$isDraftFolder = $_GET['mobj_id'] == $draftsFolderId;		

		if($this->current_selected_cmd == 'deleteMails' &&
			!$this->errorDelete &&
			$this->current_selected_cmd != 'confirm' &&
			$isTrashFolder)
		{
			if(isset($_REQUEST['mail_id']) && !is_array($_REQUEST['mail_id'])) $_REQUEST['mail_id'] = array($_REQUEST['mail_id']);
			$confirmation = new ilConfirmationGUI();
			$confirmation->setHeaderText($this->lng->txt('mail_sure_delete'));
			$this->ctrl->setParameter($this, 'mail_id', implode(',', (array)$_REQUEST['mail_id']));
			$confirmation->setFormAction($this->ctrl->getFormAction($this, 'confirmDeleteMails'));
			$confirmation->setConfirm($this->lng->txt('confirm'), 'confirmDeleteMails');
			$confirmation->setCancel($this->lng->txt('cancel'), 'cancelDeleteMails');
			$this->tpl->setVariable('CONFIRMATION', $confirmation->getHTML());
		}

		$folders = $this->mbox->getSubFolders();
		$mtree = new ilTree($ilUser->getId());
		$mtree->setTableNames('mail_tree', 'mail_obj_data');

		$check_uf = false;
		$check_local = false;
		
		if('tree' == ilSession::get(ilMailGUI::VIEWMODE_SESSION_KEY))
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
			->initFilter();
		$mailtable->setSelectedItems($_POST['mail_id']);

		try
		{
			$mailtable->prepareHTML();
		}
		catch(Exception $e)
		{
			ilUtil::sendFailure(
				$this->lng->txt($e->getMessage()) != '-'.$e->getMessage().'-' ?
				$this->lng->txt($e->getMessage()) :
				$e->getMessage()
			);
		}

		$table_html = $mailtable->getHtml();

		$folder_options = array();
		if('tree' != ilSession::get(ilMailGUI::VIEWMODE_SESSION_KEY))
		{
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
					{
						$pre .= '&nbsp';
					}
					
					if ($folder_d['depth'] > 1)
					{
						$pre .= '+';
					}
					
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
			if('tree' != ilSession::get(ilMailGUI::VIEWMODE_SESSION_KEY))
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
		
		if($mailtable->isTrashFolder() && 
		   $mailtable->getNumerOfMails() > 0 &&
		   $this->askForConfirmation)
		{
			$confirmation = new ilConfirmationGUI();
			$confirmation->setHeaderText($this->lng->txt('mail_empty_trash_confirmation'));
			$confirmation->setFormAction($this->ctrl->getFormAction($this, 'performEmptyTrash'));
			$confirmation->setConfirm($this->lng->txt('confirm'), 'performEmptyTrash');
			$confirmation->setCancel($this->lng->txt('cancel'), 'cancelEmptyTrash');
			$this->tpl->setVariable('CONFIRMATION', $confirmation->getHTML());
		}

		$this->tpl->setVariable('MAIL_TABLE', $table_html);
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
			
			$this->ctrl->setParameterByClass("ilMailGUI", "mobj_id", $new_parent);
			$this->ctrl->redirectByClass("ilMailGUI");		
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("mail_error_delete"));
			return $this->showFolder();
		}
	}
	
	public function performAddSubFolder()
	{		
		if (isset($_POST["subfolder_title"]) && 'tree' == ilSession::get(ilMailGUI::VIEWMODE_SESSION_KEY)) $_SESSION["subfolder_title"] = ilUtil::stripSlashes($_POST['subfolder_title']);

		if (empty($_POST['subfolder_title']))
		{
			ilUtil::sendInfo($this->lng->txt("mail_insert_folder_name"));
			return $this->addSubFolder();
		}
		else if ($mobj_id = $this->mbox->addFolder($_GET["mobj_id"], ilUtil::stripSlashes($_POST["subfolder_title"])))
		{			
			unset($_SESSION["subfolder_title"]);
			ilUtil::sendInfo($this->lng->txt("mail_folder_created"), true);		
			
			$this->ctrl->setParameterByClass("ilMailGUI", 'mobj_id', $mobj_id);
			$this->ctrl->redirectByClass("ilMailGUI");
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("mail_folder_exists"));
			return $this->addSubFolder();
		}		
	}

	public function addSubFolder()
	{
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $tpl    ilTemplate
		 */
		global $ilCtrl, $tpl;

		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

		$tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail.html', 'Services/Mail');
		$tpl->setTitle($this->lng->txt('mail'));

		$oForm = new ilPropertyFormGUI();
		$oForm->setFormAction($ilCtrl->getFormAction($this, 'performAddSubFolder'));
		$oForm->setTitle($this->lng->txt('mail_add_folder'));

		//title
		$oTitle = new ilTextInputGUI();
		$oTitle->setTitle($this->lng->txt('title'));
		$oTitle->setPostVar('subfolder_title');
		$oForm->addItem($oTitle);

		$oForm->addCommandButton('performAddSubFolder', $this->lng->txt('save'));
		$oForm->addCommandButton('showFolder', $this->lng->txt('cancel'));

		$tpl->setVariable('FORM', $oForm->getHTML());
		$tpl->show();

		return true;
	}

	public function renameSubFolder()
	{
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $tpl    ilTemplate
		 */
		global $ilCtrl, $tpl;

		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

		$tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.mail.html', 'Services/Mail');
		$tpl->setTitle($this->lng->txt('mail'));

		$oForm = new ilPropertyFormGUI();
		$oForm->setFormAction($ilCtrl->getFormAction($this, 'performRenameSubFolder'));
		$oForm->setTitle($this->lng->txt('mail_rename_folder'));

		//title
		$oTitle = new ilTextInputGUI();
		$oTitle->setTitle($this->lng->txt('title'));
		$tmp_data = $this->mbox->getFolderData($_GET["mobj_id"]);
		$oTitle->setValue($tmp_data["title"]);
		$oTitle->setPostVar('subfolder_title');
		$oForm->addItem($oTitle);

		$oForm->addCommandButton('performRenameSubFolder', $this->lng->txt('save'));
		$oForm->addCommandButton('showFolder', $this->lng->txt('cancel'));
		$tpl->setVariable('FORM', $oForm->getHTML());
		$tpl->show();

		return true;
	}

	public function performRenameSubFolder()
	{
		if (isset($_POST["subfolder_title"]) && 'tree' == ilSession::get(ilMailGUI::VIEWMODE_SESSION_KEY)) $_SESSION["subfolder_title"] = $_POST['subfolder_title'];

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
					$this->ctrl->redirectByClass("ilMailGUI");
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

	/**
	 * 
	 */
	public function confirmDeleteMails()
	{
		if($this->mbox->getTrashFolder() == $_GET['mobj_id'])
		{
			$_POST['mail_id'] = $mail_ids = explode(',', $_GET['mail_id']);
			if(!is_array($mail_ids))
			{
				ilUtil::sendInfo($this->lng->txt('mail_select_one'));
			}
			else if($this->umail->deleteMails($mail_ids))
			{
				$_GET['offset'] = 0;
				ilUtil::sendInfo($this->lng->txt('mail_deleted'));
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt('mail_delete_error'));
			}
		}

		$this->showFolder();
	}

	public function cancelDeleteMails()
	{
		$this->ctrl->redirect($this);
	}

	/**
	 * Detail view of a mail
	 */
	public function showMail()
	{
		/**
		 * @var $ilUser ilObjUser
		 * @var $ilToolbar ilToolbarGUI
		 * @var $ilTabs ilTabsGUI
		 */
		global $ilUser, $ilToolbar, $ilTabs;

		if($_SESSION['mail_id'])
		{
			$_GET['mail_id']     = $_SESSION['mail_id'];
			$_SESSION['mail_id'] = '';
		}

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($this->lng->txt('back_to_folder'), $this->ctrl->getFormAction($this, 'showFolder'));

		$this->umail->markRead(array((int)$_GET['mail_id']));
		$mailData = $this->umail->getMail((int)$_GET['mail_id']);

		$this->tpl->setTitle($this->lng->txt('mail_mails_of'));

		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

		$form = new ilPropertyFormGUI();
		$form->setTableWidth('100%');
		$this->ctrl->setParameter($this, 'mail_id', (int)$_GET['mail_id']);
		$form->setFormAction($this->ctrl->getFormAction($this, 'showMail'));
		$this->ctrl->clearParameters($this);
		$form->setTitle($this->lng->txt('mail_mails_of'));

		if('tree' == ilSession::get(ilMailGUI::VIEWMODE_SESSION_KEY))
		{
			$this->tpl->setVariable('FORM_TARGET', ilFrameTargetInfo::_getFrame('MainContent'));
		}

		include_once 'Services/Accessibility/classes/class.ilAccessKeyGUI.php';

		/**
		 * @var $sender ilObjUser
		 */
		$sender = ilObjectFactory::getInstanceByObjId($mailData['sender_id'], false);

		if($sender && $sender->getId() && $sender->getId() != ANONYMOUS_USER_ID)
		{
			$this->ctrl->setParameterByClass('ilmailformgui', 'mail_id', (int)$_GET['mail_id']);
			$this->ctrl->setParameterByClass('ilmailformgui', 'type', 'reply');
			$this->ctrl->clearParametersByClass('iliasmailformgui');
			$ilToolbar->addButton($this->lng->txt('reply'), $this->ctrl->getLinkTargetByClass('ilmailformgui'), '', ilAccessKey::REPLY);
			$this->ctrl->clearParameters($this);
		}

		$this->ctrl->setParameterByClass('ilmailformgui', 'mail_id', (int)$_GET['mail_id']);
		$this->ctrl->setParameterByClass('ilmailformgui', 'type', 'forward');
		$this->ctrl->clearParametersByClass('iliasmailformgui');
		$ilToolbar->addButton($this->lng->txt('forward'), $this->ctrl->getLinkTargetByClass('ilmailformgui'), '', ilAccessKey::FORWARD_MAIL);
		$this->ctrl->clearParameters($this);

		$this->ctrl->setParameter($this, 'mail_id', (int)$_GET['mail_id']);
		$ilToolbar->addButton($this->lng->txt('print'), $this->ctrl->getLinkTarget($this, 'printMail'), '_blank');
		$this->ctrl->clearParameters($this);

		$this->ctrl->setParameter($this, 'mail_id', (int)$_GET['mail_id']);
		$this->ctrl->setParameter($this, 'selected_cmd', 'deleteMails');
		$ilToolbar->addButton($this->lng->txt('delete'), $this->ctrl->getLinkTarget($this), '', ilAccessKey::DELETE);
		$this->ctrl->clearParameters($this);

		if($sender && $sender->getId() && $sender->getId() != ANONYMOUS_USER_ID)
		{
			$linked_fullname    = $sender->getPublicName();
			$picture            = ilUtil::img($sender->getPersonalPicturePath('xsmall'), $sender->getPublicName());
			$add_to_addb_button = '';

			if(in_array(ilObjUser::_lookupPref($sender->getId(), 'public_profile'), array('y', 'g')))
			{
				$this->ctrl->setParameter($this, 'mail_id', (int)$_GET['mail_id']);
				$this->ctrl->setParameter($this, 'user', $sender->getId());
				$linked_fullname = '<br /><a href="' . $this->ctrl->getLinkTarget($this, 'showUser') . '" title="'.$linked_fullname.'">' . $linked_fullname . '</a>';
				$this->ctrl->clearParameters($this);
			}

			if($sender->getId() != $ilUser->getId())
			{
				require_once 'Services/Contact/classes/class.ilAddressbook.php';
				$abook = new ilAddressbook($ilUser->getId());
				if($abook->checkEntryByLogin($sender->getLogin()) == 0)
				{
					$tplbtn = new ilTemplate('tpl.buttons.html', true, true);

					$tplbtn->setCurrentBlock('btn_cell');
					$this->ctrl->setParameter($this, 'mail_id', (int)$_GET['mail_id']);
					$tplbtn->setVariable('BTN_LINK', $this->ctrl->getLinkTarget($this, 'add'));
					$this->ctrl->clearParameters($this);
					$tplbtn->setVariable('BTN_TXT', $this->lng->txt('mail_add_to_addressbook'));
					$tplbtn->parseCurrentBlock();

					$add_to_addb_button = '<br />' . $tplbtn->get();
				}
			}

			$from = new ilCustomInputGUI($this->lng->txt('from'));
			$from->setHtml($picture . ' ' . $linked_fullname . $add_to_addb_button);
			$form->addItem($from);
		}
		else if(!$sender || !$sender->getId())
		{
			$from = new ilCustomInputGUI($this->lng->txt('from'));
			$from->setHtml($mailData['import_name'] . ' (' . $this->lng->txt('user_deleted') . ')');
			$form->addItem($from);
		}
		else
		{
			$from = new ilCustomInputGUI($this->lng->txt('from'));
			$from->setHtml(ilUtil::img(ilUtil::getImagePath('HeaderIconAvatar.svg'), ilMail::_getIliasMailerName()) . '<br />' . ilMail::_getIliasMailerName());
			$form->addItem($from);
		}

		$to = new ilCustomInputGUI($this->lng->txt('mail_to'));
		$to->setHtml(ilUtil::htmlencodePlainString($this->umail->formatNamesForOutput($mailData['rcp_to']), false));
		$form->addItem($to);

		if($mailData['rcp_cc'])
		{
			$cc = new ilCustomInputGUI($this->lng->txt('cc'));
			$cc->setHtml(ilUtil::htmlencodePlainString($this->umail->formatNamesForOutput($mailData['rcp_cc']), false));
			$form->addItem($cc);
		}

		if($mailData['rcp_bcc'])
		{
			$bcc = new ilCustomInputGUI($this->lng->txt('bc'));
			$bcc->setHtml(ilUtil::htmlencodePlainString($this->umail->formatNamesForOutput($mailData['rcp_bcc']), false));
			$form->addItem($bcc);
		}

		$subject = new ilCustomInputGUI($this->lng->txt('subject'));
		$subject->setHtml(ilUtil::htmlencodePlainString($mailData['m_subject'], true));
		$form->addItem($subject);

		$date = new ilCustomInputGUI($this->lng->txt('date'));
		$date->setHtml(ilDatePresentation::formatDate(new ilDateTime($mailData['send_time'], IL_CAL_DATETIME)));
		$form->addItem($date);

		$message = new ilCustomInputGUI($this->lng->txt('message'));
		$message->setHtml(ilUtil::htmlencodePlainString($mailData['m_message'], true));
		$form->addItem($message);

		if($mailData['attachments'])
		{
			$att = new ilCustomInputGUI($this->lng->txt('attachments'));

			$radiog = new ilRadioGroupInputGUI('', 'filename');

			foreach($mailData['attachments'] as $file)
			{
				$radiog->addOption(new ilRadioOption($file, md5($file)));
			}

			$att->setHtml($radiog->render());
			$form->addCommandButton('deliverFile', $this->lng->txt('download'));
			$form->addItem($att);
		}

		$isTrashFolder = false;
		if($this->mbox->getTrashFolder() == $_GET['mobj_id'])
		{
			$isTrashFolder = true;
		}

		$current_folder_data = $this->mbox->getFolderData((int)$_GET['mobj_id']);

		$selectOptions = array();
		$actions       = $this->mbox->getActions((int)$_GET["mobj_id"]);
		foreach($actions as $key => $action)
		{
			if($key == 'moveMails')
			{
				$folders = $this->mbox->getSubFolders();
				foreach($folders as $folder)
				{
					if(
						($folder["type"] != 'trash' || !$isTrashFolder) &&
						$folder['obj_id'] != $current_folder_data['obj_id']
					)
					{
						$optionText = '';
						if($folder['type'] != 'user_folder')
						{
							$optionText = $action . ' ' . $this->lng->txt('mail_' . $folder['title']) . ($folder['type'] == 'trash' ? ' (' . $this->lng->txt('delete') . ')' : '');
						}
						else
						{
							$optionText = $action . ' ' . $folder['title'];
						}

						$selectOptions[$folder['obj_id']] = $optionText;
					}
				}
			}
		}

		if($current_folder_data['type'] == 'user_folder')
		{
			$txt_folder = $current_folder_data['title'];
		}
		else
		{
			$txt_folder = $this->lng->txt('mail_' . $current_folder_data['title']);
		}
		$ilToolbar->addSeparator();
		$ilToolbar->addText(sprintf($this->lng->txt('current_folder'), $txt_folder));

		if(is_array($selectOptions) && count($selectOptions))
		{
			include_once 'Services/Form/classes/class.ilSelectInputGUI.php';
			$actions = new ilSelectInputGUI('', 'selected_cmd');
			$actions->setOptions($selectOptions);
			$this->ctrl->setParameter($this, 'mail_id', (int)$_GET['mail_id']);
			$ilToolbar->setFormAction($this->ctrl->getFormAction($this, 'showMail'));
			$ilToolbar->addInputItem($actions);
			$ilToolbar->addFormButton($this->lng->txt('submit'), 'changeFolder');
		}

		// Navigation
		$prevMail = $this->umail->getPreviousMail((int)$_GET['mail_id']);
		$nextMail = $this->umail->getNextMail((int)$_GET['mail_id']);
		if(is_array($prevMail) || is_array($nextMail))
		{
			$ilToolbar->addSeparator();

			if($prevMail['mail_id'])
			{
				$this->ctrl->setParameter($this, 'mail_id', $prevMail['mail_id']);
				$ilToolbar->addButton($this->lng->txt('previous'), $this->ctrl->getLinkTarget($this, 'showMail'));
				$this->ctrl->clearParameters($this);
			}

			if($nextMail['mail_id'])
			{
				$this->ctrl->setParameter($this, 'mail_id', $nextMail['mail_id']);
				$ilToolbar->addButton($this->lng->txt('next'), $this->ctrl->getLinkTarget($this, 'showMail'));
				$this->ctrl->clearParameters($this);
			}
		}

		$this->tpl->setContent($form->getHTML());
		$this->tpl->show();
	}

	/**
	 * Print mail
	 */
	public function printMail()
	{
		/**
		 * @var $tpl ilTemplate
		 */
		global $tpl;

		$tplprint = new ilTemplate('tpl.mail_print.html', true, true, 'Services/Mail');
		$tplprint->setVariable('JSPATH', $tpl->tplPath);

		$mailData = $this->umail->getMail((int)$_GET['mail_id']);

		/**
		 * @var $sender ilObjUser
		 */
		$sender = ilObjectFactory::getInstanceByObjId($mailData['sender_id'], false);

		$tplprint->setVariable('TXT_FROM', $this->lng->txt('from'));
		if($sender && $sender->getId() && $sender->getId() != ANONYMOUS_USER_ID)
		{
			$tplprint->setVariable('FROM', $sender->getPublicName());
		}
		else if(!$sender || !$sender->getId())
		{
			$tplprint->setVariable('FROM',  $mailData['import_name'] . ' (' . $this->lng->txt('user_deleted') . ')');
		}
		else
		{
			$tplprint->setVariable('FROM', ilMail::_getIliasMailerName());
		}

		$tplprint->setVariable('TXT_TO', $this->lng->txt('mail_to'));
		$tplprint->setVariable('TO', $mailData['rcp_to']);

		if($mailData['rcp_cc'])
		{
			$tplprint->setCurrentBlock('cc');
			$tplprint->setVariable('TXT_CC', $this->lng->txt('cc'));
			$tplprint->setVariable('CC', $mailData['rcp_cc']);
			$tplprint->parseCurrentBlock();
		}

		if($mailData['rcp_bcc'])
		{
			$tplprint->setCurrentBlock('bcc');
			$tplprint->setVariable('TXT_BCC', $this->lng->txt('bc'));
			$tplprint->setVariable('BCC', $mailData['rcp_bcc']);
			$tplprint->parseCurrentBlock();
		}

		$tplprint->setVariable('TXT_SUBJECT', $this->lng->txt('subject'));
		$tplprint->setVariable('SUBJECT', htmlspecialchars($mailData['m_subject']));

		$tplprint->setVariable('TXT_DATE', $this->lng->txt('date'));
		$tplprint->setVariable('DATE', ilDatePresentation::formatDate(new ilDateTime($mailData['send_time'], IL_CAL_DATETIME)));

		$tplprint->setVariable('TXT_MESSAGE', $this->lng->txt('message'));
		$tplprint->setVariable('MAIL_MESSAGE', nl2br(htmlspecialchars($mailData['m_message'])));

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

	/**
	 * 
	 */
	public function applyFilter()
	{
		$sentFolderId   = $this->mbox->getSentFolder();
		$draftsFolderId = $this->mbox->getDraftsFolder();

		$isTrashFolder = $_GET['mobj_id'] == $this->mbox->getTrashFolder();
		$isSentFolder  = $_GET['mobj_id'] == $sentFolderId;
		$isDraftFolder = $_GET['mobj_id'] == $draftsFolderId;

		$table = new ilMailFolderTableGUI($this, (int)$_GET['mobj_id'], 'showFolder');
		$table->isSentFolder($isSentFolder)
			->isDraftFolder($isDraftFolder)
			->isTrashFolder($isTrashFolder)
			->initFilter();
		$table->resetOffset();
		$table->writeFilterToSession();

		$this->showFolder();
	}

	/**
	 *
	 */
	public function resetFilter()
	{
		$sentFolderId   = $this->mbox->getSentFolder();
		$draftsFolderId = $this->mbox->getDraftsFolder();

		$isTrashFolder = $_GET['mobj_id'] == $this->mbox->getTrashFolder();
		$isSentFolder  = $_GET['mobj_id'] == $sentFolderId;
		$isDraftFolder = $_GET['mobj_id'] == $draftsFolderId;

		$table = new ilMailFolderTableGUI($this, (int)$_GET['mobj_id'], 'showFolder');
		$table->isSentFolder($isSentFolder)
			->isDraftFolder($isDraftFolder)
			->isTrashFolder($isTrashFolder)
			->initFilter();
		$table->resetOffset();
		$table->resetFilter();

		$this->showFolder();
	}
}
?>