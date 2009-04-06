<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* User Interface Class for Chat Invitation Navigation
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*/
class ilChatInvitationGUI
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
		
		// chat invitations
		include_once 'Modules/Chat/classes/class.ilChatInvitations.php';	
		$items = ilChatInvitations::_getNewInvitations($ilUser->getId());

		// do not show list, if no item is in list
		if(count($items) == 0)
		{
			return '';
		}

		$add = ' ('.count($items).')';
		
		$this->tpl->addJavascript('./Modules/Chat/js/ChatInvitationMainMenu.js');

		$tpl = new ilTemplate('tpl.chat_invitations_navigation.html', true, true,
			'Modules/Chat');				

		$cnt = 0;
		$sel_arr = array();
		$user_cache = array();
		$invitations = array();
		foreach($items as $item)
		{
			$chat_ref_id = 0;
			foreach((array)ilObject::_getAllReferences((int)$item['chat_id']) as $ref_id)
			{
				if($rbacsystem->checkAccess('read', $ref_id))
				{
					$chat_ref_id = $ref_id;
					break;
				}		
			}
			if(!(int)$chat_ref_id) continue;
			
			$beep = false;			
			
			$tpl->setCurrentBlock('item');
			$css_row = ($css_row != 'tblrow1_mo') ? 'tblrow1_mo' : 'tblrow2_mo';			
			$tpl->setVariable('CSS_ROW', $css_row);			
			$room_title = '';
			if((int)$item['room_id'])
			{
				include_once 'Modules/Chat/classes/class.ilChatRoom.php';
				$oTmpChatRoom = new ilChatRoom((int)$item['chat_id']);
				$oTmpChatRoom->setRoomId((int)$item['room_id']);
				$room_title = $oTmpChatRoom->getTitle();
				if($room_title != '')
				{
					$room_title = ', '.$room_title;
					
					if((int)$oTmpChatRoom->getOwnerId())
					{
						if(!isset($user_cache[$oTmpChatRoom->getOwnerId()]))
						{
							include_once 'Services/User/classes/class.ilObjUser.php';
							$user_cache[$oTmpChatRoom->getOwnerId()] = new ilObjUser($oTmpChatRoom->getOwnerId());							
						}						
						
						$room_title .= ' ('.$user_cache[$oTmpChatRoom->getOwnerId()]->getFullname().')';
					}					
				}				
			}
			$tpl->setVariable('HREF_ITEM', 'ilias.php?baseClass=ilChatPresentationGUI&room_id='.(int)$item['room_id'].'&ref_id='.(int)$chat_ref_id);
			$tpl->setVariable('TXT_ITEM', $ilObjDataCache->lookupTitle($item['chat_id']).$room_title);
			$sel_arr[(int)$chat_ref_id.'_'.(int)$item['room_id']] = $ilObjDataCache->lookupTitle($item['chat_id']).$room_title;					
			$tpl->parseCurrentBlock();
			
			$invitations[] = (int)$chat_ref_id.'_'.(int)$item['room_id'];
			if((int)$ilSetting->get('chat_sound_status') &&
			   (int)$ilSetting->get('chat_new_invitation_sound_status'))
			{
				$beep = true;
			}
			
			if($cnt == 0)
			{
				$sel_arr = array_reverse($sel_arr);
				$sel_arr[(int)$chat_ref_id.'__'.(int)$item['room_id']] = '-- '.$this->lng->txt('chat_invitation_subject').$add.' --';
				$sel_arr = array_reverse($sel_arr);
			}
			
			++$cnt;
		}
		
		if($cnt == 0) return '';		
		
		$select = ilUtil::formSelect('', 'invitation', $sel_arr, false, true, '0', 'ilEditSelect');
		$tpl->setVariable('NAVI_SELECT', $select);
		$tpl->setVariable('TXT_CHAT_INVITATIONS', $this->lng->txt('chat_chat_invitation').$add);
		$tpl->setVariable('IMG_DOWN', ilUtil::getImagePath('mm_down_arrow.gif'));		
		$tpl->setVariable('TXT_GO', $this->lng->txt('go'));
		$tpl->setVariable('ACTION', 'ilias.php?baseClass=ilChatPresentationGUI');		


		if((int)$ilSetting->get('chat_sound_status') && 
		   (int)$ilSetting->get('chat_new_invitation_sound_status'))
		{
			// beep	
			if($beep)
			{
				$tpl->setCurrentBlock('beep');
				$tpl->setVariable('BEEP_SRC', './Modules/Chat/sounds/receive.mp3');
				$tpl->parseCurrentBlock();
			}
			
			foreach((array)$invitations as $id)
			{
				$_SESSION['chat']['_already_beeped'][$id] = true;
			}
		}
		
		return $tpl->get();
	}
}
?>
