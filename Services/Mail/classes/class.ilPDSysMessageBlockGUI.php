<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Mail/classes/class.ilPDMailBlockGUI.php");

/**
* BlockGUI class for System Messages block on personal desktop
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilPDSysMessageBlockGUI: ilColumnGUI
*/
class ilPDSysMessageBlockGUI extends ilPDMailBlockGUI
{
	static $block_type = "pdsysmess";

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->setTitle($this->lng->txt("show_system_messages"));
		$this->setAvailableDetailLevels(3);
		$this->mail_mode = "system";
		$this->allow_moving = false;
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

	function getHTML()
	{
		if ($this->getCurrentDetailLevel() < 1)
		{
			$this->setCurrentDetailLevel(1);
		}

		$html = parent::getHTML();
		
		if (count($this->mails) == 0)
		{
			return "";
		}
		else
		{
			return $html;
		}
	}
	
	/**
	* Get Mails
	*/
	function getMails()
	{
		$umail = new ilMail($this->user->getId());
		$mbox  = new ilMailBox($this->user->getId());
		$inbox = $mbox->getInboxFolder();

		$this->mails = $umail->getMailsOfFolder($inbox, array('status' => 'unread', 'type' => 'system'));
	}

	/**
	* Get overview.
	*/
	function getOverview()
	{
		return '<div class="small">'.((int) count($this->mails))." ".$this->lng->txt("system_message")."</div>";
	}
}
