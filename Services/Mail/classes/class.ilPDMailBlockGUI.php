<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* BlockGUI class for Personal Desktop Mail block
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilPDMailBlockGUI: ilColumnGUI
*/
class ilPDMailBlockGUI extends ilBlockGUI
{
	static $block_type = "pdmail";
	
	/**
	* Constructor
	*/
	public function __construct()
	{
		global $lng;
		
		include_once './Services/User/classes/class.ilObjUser.php';
		include_once "Services/Mail/classes/class.ilMailbox.php";
		include_once "Services/Mail/classes/class.ilMail.php";
		
		parent::__construct();
		
		$this->setLimit(5);
		$this->setImage(ilUtil::getImagePath("icon_mail_s.gif"));
		$this->setTitle($lng->txt("mail"));
		$this->setAvailableDetailLevels(3);
	}
	
	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function getBlockType()
	{
		return self::$block_type;
	}
	
	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function isRepositoryObject()
	{
		return false;
	}

	/**
	* Get Screen Mode for current command.
	*/
	static function getScreenMode()
	{
		switch($_GET["cmd"])
		{
			case "showMail":
				return IL_SCREEN_CENTER;
				break;
				
			default:
				return IL_SCREEN_SIDE;
				break;
		}
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		return $this->$cmd();
	}

	function getHTML()
	{
		global $ilUser, $rbacsystem;
		
		$umail = new ilMail($ilUser->getId());		
		if(!$rbacsystem->checkAccess('mail_visible', $umail->getMailObjectReferenceId()))
		{
			return '';
		}
		
		if ($this->getCurrentDetailLevel() == 0)
		{
			return '';
		}
		else
		{
			$html = parent::getHTML();
			return $html;
		}
	}
	
	/**
	* Get Mails
	*/
	function getMails()
	{
		global $ilUser;
		
		// BEGIN MAILS
		$umail = new ilMail($ilUser->getId());
		$mbox = new ilMailBox($ilUser->getId());
		$inbox = $mbox->getInboxFolder();
		
		//SHOW MAILS FOR EVERY USER
		$this->mails = $umail->getMailsOfFolder($inbox, array('status' => 'unread', 'type' => 'normal'));
	}
	
	/**
	* Fill data section
	*/
	function fillDataSection()
	{
		$this->getMails();
		$this->setData($this->mails);

		if ($this->getCurrentDetailLevel() > 1 && count($this->mails) > 0)
		{
			$this->setRowTemplate("tpl.pd_mail_row.html", "Services/Mail");
			if ($this->getCurrentDetailLevel() > 2)
			{
				$this->setColSpan(2);
			}
			parent::fillDataSection();
		}
		else
		{
			$this->setEnableNumInfo(false);
			if (count($this->mails) == 0)
			{
				$this->setEnableDetailRow(false);
			}
			$this->setDataSection($this->getOverview());
		}
	}
	
	/**
	 *
	 * Caching method for user instances
	 *
	 * @access	private
	 * @param	integer		$a_usr_id	Id of a user instance
	 * @return	ilObjUser
	 *
	 */
	protected function getUserInstance($a_usr_id)
	{
		static $userObjectCache = array();

		if(isset($userObjectCache[$a_usr_id])) return $userObjectCache[$a_usr_id];

		$userObjectCache[$a_usr_id] = new ilObjUser($a_usr_id);

		return $userObjectCache[$a_usr_id];
	}
		
	/**
	* get flat bookmark list for personal desktop
	*/
	function fillRow($mail)
	{
		global $ilUser, $ilCtrl, $lng;

		// GET SENDER NAME
		$user = $this->getUserInstance($mail['sender_id']);		
		if ($this->getCurrentDetailLevel() > 2)
		{
			$this->tpl->setCurrentBlock('image');
			$this->tpl->setVariable('IMG_SENDER', $user->getPersonalPicturePath('xxsmall'));
			$this->tpl->setVariable('ALT_SENDER', $user->getLogin());
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock('long');
			if($mail['sender_id'] != ANONYMOUS_USER_ID)
			{
				if(in_array($user->getPref('public_profile'), array('y', 'g')))
				{
					if(!($fullname = $user->getFullname()))
					{
						$fullname = $lng->txt('unknown');
					}
					$this->tpl->setVariable('NEW_MAIL_FROM', $fullname);
				}
				$this->tpl->setVariable('NEW_MAIL_FROM_LOGIN', $user->getLogin());
			}
			else
			{
				$this->tpl->setVariable('NEW_MAIL_FROM_LOGIN', ilMail::_getIliasMailerName());				
			}
			$this->tpl->setVariable('NEW_MAIL_DATE', ilDatePresentation::formatDate(new ilDate($mail['send_time'],IL_CAL_DATE)));
			$this->tpl->setVariable('TXT_FROM', $lng->txt('from'));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			if($mail['sender_id'] != ANONYMOUS_USER_ID)
			{
				$this->tpl->setCurrentBlock('short');
				$this->tpl->setVariable('NEW_MAIL_FROM_LOGIN', $user->getLogin());
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock('short');
				$this->tpl->setVariable('NEW_MAIL_FROM_LOGIN', ilMail::_getIliasMailerName());
				$this->tpl->parseCurrentBlock();
			}
		}
		
		$this->tpl->setVariable('NEW_MAIL_SUBJ', htmlentities($mail['m_subject'],ENT_NOQUOTES,'UTF-8'));
		$ilCtrl->setParameter($this, 'mobj_id', $inbox);
		$ilCtrl->setParameter($this, 'mail_id', $mail['mail_id']);
		$ilCtrl->setParameter($this, 'mail_mode', $this->mail_mode);
		$this->tpl->setVariable('NEW_MAIL_LINK_READ',
			$ilCtrl->getLinkTarget($this, 'showMail'));
		$ilCtrl->clearParameters($this);
	}

	/**
	* Get overview.
	*/
	function getOverview()
	{
		global $ilUser, $lng, $ilCtrl;
				
		return '<div class="small">'.((int) count($this->mails))." ".$lng->txt("mails_pl")."</div>";
	}

	/**
	* show mail
	*/
	function showMail()
	{
		global $lng, $ilCtrl;
		
		include_once("./Services/Mail/classes/class.ilPDMailGUI.php");
		$mail_gui = new ilPDMailGUI();

		include_once("./Services/PersonalDesktop/classes/class.ilPDContentBlockGUI.php");
		$content_block = new ilPDContentBlockGUI();
		$content_block->setContent($mail_gui->getPDMailHTML($_GET["mail_id"],
			$_GET["mobj_id"]));
		$content_block->setTitle($lng->txt("message"));
		$content_block->setColSpan(2);
		$content_block->setImage(ilUtil::getImagePath("icon_mail.gif"));
		$content_block->addHeaderCommand($ilCtrl->getLinkTargetByClass("ilpersonaldesktopgui", "show"),
			$lng->txt("selected_items_back"));
			
		if ($_GET["mail_mode"] != "system")
		{
			$content_block->addBlockCommand("ilias.php?baseClass=ilMailGUI&mail_id=".
				$_GET["mail_id"]."&mobj_id".$_GET["mobj_id"]."&type=reply",
				$lng->txt("reply"));
			$content_block->addBlockCommand("ilias.php?baseClass=ilMailGUI&mail_id=".
				$_GET["mail_id"]."&mobj_id".$_GET["mobj_id"]."&type=read",
				$lng->txt("inbox"));
			
			$ilCtrl->setParameter($this, 'mail_id', (int) $_GET['mail_id']);		
			$content_block->addBlockCommand($ilCtrl->getLinkTarget($this, 'deleteMail'), $lng->txt('delete'));
				
				
		}
		else
		{
			$ilCtrl->setParameter($this, "mail_id", $_GET["mail_id"]);
			$ilCtrl->setParameter($this, "mobj_id", $_GET["mobj_id"]);
			$content_block->addBlockCommand(
				$ilCtrl->getLinkTarget($this, "deleteMail"),
				$lng->txt("delete"));
			$ilCtrl->clearParameters($this);
		}
		
		return $content_block->getHTML();
	}

	/**
	* delete mail
	*/
	function deleteMail()
	{
		global $lng, $ilCtrl;
		
		$lng->loadLanguageModule('mail');
		
		$umail = new ilMail($_SESSION['AccountId']);
		$mbox = new ilMailBox($_SESSION['AccountId']);

		// IF THERE IS NO OBJ_ID GIVEN GET THE ID OF MAIL ROOT NODE
		if(!$_GET['mobj_id'])
		{
			$_GET['mobj_id'] = $mbox->getInboxFolder();
		}

		if ($umail->moveMailsToFolder(array($_GET['mail_id']),
			$mbox->getTrashFolder()))
		{
			ilUtil::sendInfo($lng->txt('mail_moved_to_trash'), true);
		}
		else
		{
			ilUtil::sendInfo($lng->txt('mail_move_error'), true);
		}
		$ilCtrl->redirectByClass('ilpersonaldesktopgui', 'show');
	}
}
?>
