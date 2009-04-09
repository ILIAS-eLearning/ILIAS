<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* User Interface Class for Chat Message Notification in Main Menu
*
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
*/
class ilChatMessageNotifyGUI
{
	private $tpl;
	private $lng;
	
	public function __construct()
	{
		global $tpl, $lng;
		
		$this->tpl = $tpl;
		$this->lng = $lng;
		
		$this->lng->loadLanguageModule('chat');
	}
	
	public function getHTML()	
	{
		global $ilUser, $ilObjDataCache, $ilAccess, $ilSetting, $rbacsystem;
		
		include_once 'Modules/Chat/classes/class.ilChatServerCommunicator.php';
		
		$chatinfo = ilChatServerCommunicator::_lookupUser($ilUser->getId());
		$last_message_info = false;
	
		if ($chatinfo !== false)
		{
			$last_msg_info = ilChatServerCommunicator::_getTailMessages($chatinfo->chatId, $chatinfo->roomId, time() - 60 * 60);
			if ($last_msg_info == false)
			{
				return "";
			}
			else if ($_SESSION["il_notify_last_msg_checksum"] == $last_msg_info->entryId )
			{
				return "";
			}
		}
		else
		{
			return "";
		}
		$last_msg = $last_msg_info->message;

		$_SESSION["il_notify_last_msg_checksum"] = $last_msg_info->entryId;
		
		$this->tpl->addJavascript('./Modules/Chat/js/ChatMessagesMainMenu.js');
		$tpl = new ilTemplate('tpl.chat_messages_navigation.html', true, true,'Modules/Chat');				
		if((int)$ilSetting->get('chat_sound_status') && (int)$ilUser->getPref('chat_new_message_sound_status'))
		{
			$tpl->setCurrentBlock('beep');
			$tpl->setVariable('BEEP_SRC', './Modules/Chat/sounds/receive.mp3');
			$tpl->parseCurrentBlock();
		}

		include_once 'Modules/Chat/classes/class.ilChatServerCommunicator.php';

		$tpl->setVariable("CHAT_RECENT_POSTINGS", $this->lng->txt("chat_recent_postings"));
		$tpl->setCurrentBlock('msg_item');
		$tpl->setVariable("TXT_MSG", $last_msg);
		$tpl->setVariable("TXT_ROOM", $chatinfo->chatTitle);
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}
}
?>