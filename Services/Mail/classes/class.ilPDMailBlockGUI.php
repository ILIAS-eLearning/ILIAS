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

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* BlockGUI class for Personal Desktop Mail block
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilPDMailBlockGUI extends ilBlockGUI
{
	
	/**
	* Constructor
	*/
	function ilPDMailBlockGUI($a_parent_class, $a_parent_cmd = "")
	{
		global $ilCtrl, $lng, $ilUser;
		
		include_once "./include/inc.mail.php";
		include_once "classes/class.ilObjUser.php";
		include_once "classes/class.ilMailbox.php";
		include_once "classes/class.ilMail.php";

		
		parent::ilBlockGUI($a_parent_class, $a_parent_cmd);
		
		$this->setLimit(5);
		$this->setImage(ilUtil::getImagePath("icon_mail_s.gif"));
		$this->setTitle($lng->txt("mail"));
		$this->setBlockIdentification("pdmail", $ilUser->getId());
		$this->setPrefix("pdmail");
		$this->setAvailableDetailLevels(3);
	}
	
	function getHTML()
	{
		if ($this->getCurrentDetailLevel() == 0)
		{
			return "";
		}
		else
		{
			return parent::getHTML();
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
		$mail_data = $umail->getMailsOfFolder($inbox);
		$mail_counter = $umail->getMailCounterData();
		$unreadmails = 0;
	
		$this->mails = array();
		foreach ($mail_data as $mail)
		{
			//ONLY NEW MAILS WOULD BE ON THE PERONAL DESKTOP
			if($mail["m_status"] == 'unread')
			{
				$this->mails[] = $mail;
			}
		}
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
			$this->setDataSection($this->getOverview());
		}
	}
		
	/**
	* get flat bookmark list for personal desktop
	*/
	function fillRow($mail)
	{
		global $ilUser, $ilCtrl, $lng;
		
		// GET SENDER NAME
		$user = new ilObjUser($mail["sender_id"]);
		
		if(!($fullname = $user->getFullname()))
		{
			$fullname = $lng->txt("unknown");
		}

		if ($this->getCurrentDetailLevel() > 2)
		{
			$this->tpl->setCurrentBlock("image");
			$this->tpl->setVariable("IMG_SENDER", $user->getPersonalPicturePath("xxsmall"));
			$this->tpl->setVariable("ALT_SENDER", $user->getLogin());
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("long");
			$this->tpl->setVariable("NEW_MAIL_FROM", $fullname);
			$this->tpl->setVariable("NEW_MAIL_FROM_LOGIN", $user->getLogin());
			$this->tpl->setVariable("NEW_MAIL_DATE", substr($mail["send_time"],0,10));
			$this->tpl->setVariable("TXT_FROM", $lng->txt("from"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("short");
			$this->tpl->setVariable("NEW_MAIL_FROM_LOGIN", $user->getLogin());
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable("NEW_MAIL_SUBJ", $mail["m_subject"]);
		$target_name = htmlentities(urlencode("mail_read.php?mobj_id=".$inbox."&mail_id=".$mail["mail_id"]));
		$this->tpl->setVariable("NEW_MAIL_LINK_READ", "mail_frameset.php?target=".$target_name);
		
	}

	/**
	* Get overview.
	*/
	function getOverview()
	{
		global $ilUser, $lng, $ilCtrl;
				
		return '<div class="small">'.((int) count($this->mails))." ".$lng->txt("mails_pl")."</div>";
	}

}

?>
