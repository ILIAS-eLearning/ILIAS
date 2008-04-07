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
* BlockGUI class for Personal Desktop Feedback block
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
** @ilCtrl_IsCalledBy ilPDFeedbackBlockGUI: ilColumnGUI
*/
class ilPDFeedbackBlockGUI extends ilBlockGUI
{
	static $block_type = "pdfeedb";
	/**
	* Constructor
	*/
	function ilPDFeedbackBlockGUI()
	{
		global $ilCtrl, $lng, $ilUser;
		
		parent::ilBlockGUI();
		
		$this->setLimit(5);
		$this->setImage(ilUtil::getImagePath("icon_feedb_s.gif"));
		$this->setTitle($lng->txt("pdesk_feedback_request"));
		$this->setAvailableDetailLevels(2, 1);
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
	* Is block used in repository object?
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
		$html = parent::getHTML();
		if (count($this->feedbacks) == 0)
		{
			return "";
		}
		else
		{
			return $html;
		}
	}

	/**
	* Fill data section
	*/
	function fillDataSection()
	{
		global $ilAccess, $ilUser,$tree;
		
		include_once('Services/Feedback/classes/class.ilFeedback.php');
		$feedback = new ilFeedback();
		$feedbacks = $feedback->getAllBarometer(0);
		$this->feedbacks = array();
		foreach($feedbacks as $feedback)
		{
			if($tree->isDeleted($feedback->getRefId()))
			{
				continue;
			}
			
			// do not show feedback for tutors/admins
			if (!$ilAccess->checkAccess("write", "", $feedback->getRefId())
				&& $feedback->canVote($ilUser->getId(), $feedback->getId()) == 1
				&& !$feedback->getAnonymous())
			{
				$this->feedbacks[] = array (
					"id" => $feedback->getId(),
					"title" => $feedback->getTitle()
					);
			}
		}
		
		$this->setData($this->feedbacks);
		
		if ($this->getCurrentDetailLevel() > 1 && count($this->feedbacks) > 0)
		{
			$this->setRowTemplate("tpl.feedback_pdbox.html", "Services/Feedback");
			parent::fillDataSection();
		}
		else
		{
			$this->setEnableNumInfo(false);
			$this->setDataSection($this->getOverview());
		}
	}
		
	/**
	* Fill feedback row
	*/
	function fillRow($a_set)
	{
		global $ilUser, $ilCtrl, $lng;

		$ilCtrl->setParameterByClass("ilfeedbackgui","barometer_id",$a_set["id"]);
		$this->tpl->setVariable('LINK_FEEDBACK',
			$ilCtrl->getLinkTargetByClass(array("ilpersonaldesktopgui", 'ilfeedbackgui'),'voteform'));
		$this->tpl->setVariable('TXT_FEEDBACK', $a_set["title"]);
	}

	/**
	* Get overview.
	*/
	function getOverview()
	{
		global $ilUser, $lng, $ilCtrl;
				
		return '<div class="small">'.((int) count($this->feedbacks))." ".$lng->txt("pdesk_feedbacks")."</div>";
	}

}

?>
