<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Block/classes/class.ilBlockGUI.php';
include_once 'Services/Mail/classes/class.ilMailUserCache.php';

/**
 * BlockGUI class for Personal Desktop Mail block
 * @author			Alex Killing <alex.killing@gmx.de>
 * @version		   $Id$
 * @ilCtrl_IsCalledBy ilPDMailBlockGUI: ilColumnGUI
 */
class ilPDMailBlockGUI extends ilBlockGUI
{
	static $block_type = 'pdmail';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $lng;

		include_once 'Services/User/classes/class.ilObjUser.php';
		include_once 'Services/Mail/classes/class.ilMailbox.php';
		include_once 'Services/Mail/classes/class.ilMail.php';

		parent::__construct();

		$this->setLimit(5);
		$this->setImage(ilUtil::getImagePath('icon_mail.svg'));
		$this->setTitle($lng->txt('mail'));
		$this->setAvailableDetailLevels(3);
	}

	/**
	 * Get block type
	 * @return	string	Block type.
	 */
	static function getBlockType()
	{
		return self::$block_type;
	}

	/**
	 * Get block type
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
		switch($_GET['cmd'])
		{
			case 'showMail':
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
	public function executeCommand()
	{
		global $ilCtrl;

		$cmd = $ilCtrl->getCmd('getHTML');

		return $this->$cmd();
	}

	public function getHTML()
	{
		global $ilUser, $rbacsystem;

		$umail = new ilMail($ilUser->getId());
		if(!$rbacsystem->checkAccess('internal_mail', $umail->getMailObjectReferenceId()))
		{
			return '';
		}

		if($this->getCurrentDetailLevel() == 0)
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
	protected function getMails()
	{
		global $ilUser;

		$umail       = new ilMail($ilUser->getId());
		$mbox        = new ilMailBox($ilUser->getId());
		$this->inbox = $mbox->getInboxFolder();

		$this->mails = $umail->getMailsOfFolder(
			$this->inbox,
			array(
				 'status'  => 'unread',
				 'type'	=> 'normal'
			)
		);
	}

	/**
	 * Fill data section
	 */
	public function fillDataSection()
	{
		$this->getMails();
		$this->setData($this->mails);

		if($this->getCurrentDetailLevel() > 1 && count($this->mails) > 0)
		{
			$this->setRowTemplate("tpl.pd_mail_row.html", "Services/Mail");
			if($this->getCurrentDetailLevel() > 2)
			{
				$this->setColSpan(2);
			}
			parent::fillDataSection();
		}
		else
		{
			$this->setEnableNumInfo(false);
			if(count($this->mails) == 0)
			{
				$this->setEnableDetailRow(false);
			}
			$this->setDataSection($this->getOverview());
		}
	}

	/**
	 * get flat bookmark list for personal desktop
	 */
	public function fillRow($mail)
	{
		global $ilCtrl, $lng;

		$user = ilMailUserCache::getUserObjectById($mail['sender_id']);
		
		if($this->getCurrentDetailLevel() > 2)
		{
			$this->tpl->touchBlock('usr_image_space');
			if($user && $user->getId() != ANONYMOUS_USER_ID)
			{
				$this->tpl->setVariable('PUBLIC_NAME_LONG', $user->getPublicName());
				$this->tpl->setVariable('IMG_SENDER', $user->getPersonalPicturePath('xxsmall'));
				$this->tpl->setVariable('ALT_SENDER', $user->getPublicName());
			}
			else if(!$user)
			{
				$this->tpl->setVariable('PUBLIC_NAME_LONG', $mail['import_name'] . ' (' . $lng->txt('user_deleted') . ')');
				
				$this->tpl->setCurrentBlock('image_container');
				$this->tpl->touchBlock('image_container');
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setVariable('PUBLIC_NAME_LONG', ilMail::_getIliasMailerName());
				$this->tpl->setVariable('IMG_SENDER', ilUtil::getImagePath('HeaderIconAvatar.svg'));
				$this->tpl->setVariable('ALT_SENDER', ilMail::_getIliasMailerName());
			}

			$this->tpl->setVariable('NEW_MAIL_DATE', ilDatePresentation::formatDate(new ilDate($mail['send_time'], IL_CAL_DATE)));
		}
		else
		{
			if($user && $user->getId() != ANONYMOUS_USER_ID)
			{
				$this->tpl->setVariable('PUBLIC_NAME_SHORT', $user->getPublicName());
			}
			else if(!$user)
			{
				$this->tpl->setVariable('PUBLIC_NAME_SHORT', $mail['import_name'] . ' (' . $lng->txt('user_deleted') . ')');
			}
			else
			{
				$this->tpl->setVariable('PUBLIC_NAME_SHORT', ilMail::_getIliasMailerName());
			}
		}

		$this->tpl->setVariable('NEW_MAIL_SUBJ', htmlentities($mail['m_subject'], ENT_NOQUOTES, 'UTF-8'));
		$ilCtrl->setParameter($this, 'mobj_id', $this->inbox);
		$ilCtrl->setParameter($this, 'mail_id', $mail['mail_id']);
		$ilCtrl->setParameter($this, 'mail_mode', $this->mail_mode);
		$this->tpl->setVariable('NEW_MAIL_LINK_READ', $ilCtrl->getLinkTarget($this, 'showMail'));
		$ilCtrl->clearParameters($this);
	}

	/**
	 * Get overview.
	 */
	protected function getOverview()
	{
		global $lng;

		return '<div class="small">' . ((int)count($this->mails)) . " " . $lng->txt("mails_pl") . "</div>";
	}

	/**
	 * show mail
	 */
	protected function showMail()
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
		$content_block->setImage(ilUtil::getImagePath("icon_mail.svg"));
		$content_block->addHeaderCommand($ilCtrl->getLinkTargetByClass("ilpersonaldesktopgui", "show"),
			$lng->txt("selected_items_back"));

		if($_GET["mail_mode"] != "system")
		{
			$content_block->addBlockCommand("ilias.php?baseClass=ilMailGUI&mail_id=" .
					$_GET["mail_id"] . "&mobj_id=" . $_GET["mobj_id"] . "&type=reply",
				$lng->txt("reply"));
			$content_block->addBlockCommand("ilias.php?baseClass=ilMailGUI&mail_id=" .
					$_GET["mail_id"] . "&mobj_id=" . $_GET["mobj_id"] . "&type=read",
				$lng->txt("inbox"));

			$ilCtrl->setParameter($this, 'mail_id', (int)$_GET['mail_id']);
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
	public function deleteMail()
	{
		global $lng, $ilCtrl;

		$lng->loadLanguageModule('mail');

		$umail = new ilMail($_SESSION['AccountId']);
		$mbox  = new ilMailBox($_SESSION['AccountId']);

		if(!$_GET['mobj_id'])
		{
			$_GET['mobj_id'] = $mbox->getInboxFolder();
		}

		if($umail->moveMailsToFolder(array($_GET['mail_id']),
			$mbox->getTrashFolder())
		)
		{
			ilUtil::sendInfo($lng->txt('mail_moved_to_trash'), true);
		}
		else
		{
			ilUtil::sendInfo($lng->txt('mail_move_error'), true);
		}
		$ilCtrl->redirectByClass('ilpersonaldesktopgui', 'show');
	}

	/**
	 * @param array $data
	 */
	protected function preloadData(array $data)
	{
		$usr_ids = array();

		foreach($data as $mail)
		{
			if($mail['sender_id'] && $mail['sender_id'] != ANONYMOUS_USER_ID)
			{
				$usr_ids[$mail['sender_id']] = $mail['sender_id'];
			}
		}

		ilMailUserCache::preloadUserObjects($usr_ids);
	}
}