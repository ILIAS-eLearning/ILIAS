<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Chatroom/classes/class.ilChatroom.php';

/**
 * ilChatroomBlock
 * @author			Michael Jansen <mjansen@databay.de>
 * @version		   $Id$
 */
class ilChatroomBlock
{
	/**
	 * @return array
	 */
	private function getReadableAreas()
	{
		/**
		 * @var $ilUser     ilObjUser
		 * @var $rbacsystem ilRbacSystem
		 */
		global $ilUser, $rbacsystem;

		$readable_rooms = array();

		$chatroom_objects = ilChatroom::getUntrashedChatReferences(array(
			'last_activity' => strtotime('-5 days', time())
		));
		foreach($chatroom_objects as $object)
		{
			if(isset($readable_rooms[$object['obj_id']]))
			{
				continue;
			}

			if($rbacsystem->checkAccess('read', '', $object['ref_id']))
			{
				$room = ilChatroom::byObjectId($object['obj_id']);
				if($room && !$room->isUserBanned($ilUser->getId()))
				{
					$readable_rooms[$object['obj_id']] = array(
						'ref_id'       => $object['ref_id'],
						'obj_id'       => $object['obj_id'],
						'room_id'      => $room->getRoomId(),
						'title'        => $object['title'],
						'parent_title' => $object['parent_title']
					);
				}
			}
		}

		$title = array();
		foreach($readable_rooms as $k => $v)
		{
			$title[$k] = strtolower($v['title']);
		}
		array_multisort($title, SORT_STRING, $readable_rooms);

		return $readable_rooms;
	}

	/**
	 * @return string
	 */
	public function getRoomSelect()
	{
		/**
		 * @var $lng    ilLanguage
		 * @var $ilUser ilObjUser
		 */
		global $lng, $ilUser;

		$readable = $this->getReadableAreas();
		$tpl      = new ilTemplate('tpl.chatroom_block_room_select.html', true, true, 'Modules/Chatroom');
		$tpl->setVariable('TXT_SELECT_ROOM', $lng->txt('chat_select_room'));
		foreach($readable as $room)
		{
			$tpl->setCurrentBlock('select_room_row');
			$tpl->setVariable('ROW_VALUE', $room['ref_id']);
			$tpl->setVariable('ROW_CAPTION', sprintf($lng->txt('room_in_container'), $room['title'], $room['parent_title']));

			if($ilUser->getPref('chatviewer_last_selected_room') == $room['ref_id'])
				$tpl->setVariable('ROW_SELECTED', 'selected="selected"');

			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}

	/**
	 * @param ilChatroom $room
	 * @return array
	 */
	public function getMessages(ilChatroom $room)
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		include 'Modules/Chatroom/classes/class.ilChatroomUser.php';
		$messages = $room->getLastMessagesForChatViewer($room->getSetting('display_past_msgs'), new ilChatroomUser($ilUser, $room));

		$output_messages = array();

		foreach($messages as $msg)
		{
			$output_messages[] = $msg;
		}

		return $output_messages;
	}
}
