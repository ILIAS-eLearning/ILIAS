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
	function ilPDSysMessageBlockGUI()
	{
		global $ilCtrl, $lng, $ilUser;
		parent::ilPDMailBlockGUI();
		$this->setImage(ilUtil::getImagePath("icon_sysmess_s.gif"));
		$this->setTitle($lng->txt("show_system_messages"));
		$this->setAvailableDetailLevels(3, 1);
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
		global $ilUser;
		
		// BEGIN MAILS
		$umail = new ilMail($_SESSION["AccountId"]);
		$mbox = new ilMailBox($_SESSION["AccountId"]);
		$inbox = $mbox->getInboxFolder();
		
		//SHOW MAILS FOR EVERY USER
		$this->mails = $umail->getMailsOfFolder($inbox, array('status' => 'unread', 'type' => 'system'));
	}

	/**
	* Get overview.
	*/
	function getOverview()
	{
		global $ilUser, $lng, $ilCtrl;
				
		return '<div class="small">'.((int) count($this->mails))." ".$lng->txt("system_message")."</div>";
	}

}

?>
