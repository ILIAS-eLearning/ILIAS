<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

/**
* Class ilChatBlockGUI
* 
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
* @ilCtrl_IsCalledBy ilChatBlockGUI: ilColumnGUI
*/
include_once 'Services/Block/classes/class.ilBlockGUI.php';
class ilChatBlockGUI extends ilBlockGUI
{
	static $block_type = "chatviewer";
	
	/**
	* Constructor
	*/
	function ilChatBlockGUI()
	{
		global $ilCtrl, $lng, $ilUser;
		parent::ilBlockGUI();
		$lng->loadLanguageModule("chat");
		//$this->setLimit(5);
		$this->setImage(ilUtil::getImagePath("icon_chat.gif"));
		$this->setTitle($lng->txt("chat_chatviewer"));
		$this->setAvailableDetailLevels(1, 0);
		$this->allow_moving = true;
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
		global $tpl;
		include_once 'Modules/Chat/classes/class.ilChatServerConfig.php';
		include_once 'Services/YUI/classes/class.ilYuiUtil.php';
		
		ilYuiUtil::initJson();
		
		$serverconfig = new ilChatServerConfig();
		
		if ($this->getCurrentDetailLevel() == 0 || !$serverconfig->getActiveStatus() || !$serverconfig->isAlive())
			return "";
		else
		{	
			$tpl->addCss('Modules/Chat/templates/default/chat.css');
			$html = parent::getHTML();
			return $html;
		}

	}

	/**
	* Fill data section
	*/
	function fillDataSection()
	{
		global $tpl, $lng, $ilCtrl;
		
		include_once 'Modules/Chat/classes/class.ilChatBlock.php';
		
		$tpl->addJavascript("Modules/Chat/js/ChatChatviewerBlock.js");
		//$tpl->addJavascript("Modules/Chat/js/json.js");
		
		$chatblock = new ilChatBlock();
		$body_tpl = new ilTemplate('tpl.chat_block_message_body.html', true, true, 'Modules/Chat');

		$height = 120;
		if ($this->getCurrentDetailLevel() > 0 && $this->getCurrentDetailLevel() <= 3)
		{
			$height *= $this->getCurrentDetailLevel();
		}
		$body_tpl->setVariable('BLOCK_HEIGHT', $height);
		$body_tpl->setVariable('TXT_ENABLE_AUTOSCROLL', $lng->txt('chat_enable_autoscroll'));		
		$ilCtrl->setParameter($this, 'ref_id', '#__ref_id');
		$ilCtrl->setParameter($this, 'room_id', '#__room_id');
		$body_tpl->setVariable('CHATBLOCK_BASE_URL', 'ilias.php?ref_id=#__ref_id&room_id=#__room_id&cmdClass=ilobjchatgui&cmd=getChatViewerBlockContent&baseClass=ilChatPresentationGUI&cmdMode=asynch');
		$ilCtrl->clearParameters($this);
		
		$content = $body_tpl->get() . $chatblock->getRoomSelect();
		$this->setDataSection($content);

	}
		
	/**
	* Fill feedback row
	*/
	function fillRow($a_set)
	{
		global $ilUser, $ilCtrl, $lng;
//		$ilCtrl->setParameterByClass("ilfeedbackgui","barometer_id",$a_set["id"]);
//		$this->tpl->setVariable('LINK_FEEDBACK',
//			$ilCtrl->getLinkTargetByClass(array("ilpersonaldesktopgui", 'ilfeedbackgui'),'voteform'));
//		$this->tpl->setVariable('TXT_FEEDBACK', $a_set["title"]);
	}

	/**
	* Get overview.
	*/
	function getOverview()
	{
		global $ilUser, $lng, $ilCtrl;
				
//		return '<div class="small">'.((int) count($this->feedbacks))." ".$lng->txt("pdesk_feedbacks")."</div>";
	}
}
?>