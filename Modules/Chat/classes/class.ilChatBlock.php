<?php

include_once "Modules/Chat/classes/class.ilChatRoom.php";
include_once "Modules/Chat/classes/class.ilObjChat.php";
include_once "Modules/Chat/classes/class.ilChatBlockedUsers.php";

class ilChatBlock {
	private function getReadableAreas() {
		global $ilUser, $rbacsystem;
	
		$rooms = ilChatRoom::getAllRooms();
		
		$titel = array();
		foreach($rooms as $k => $v) {
			$titel[$k] = strtolower($v['title']);
		}
		array_multisort($titel, SORT_STRING, $rooms);
		
		$readable_rooms = array();
		
		for ($i = 0; $i < count($rooms); $i++)
		{
			if (ilChatBlockedUsers::_isBlocked($rooms[$i]['obj_id'], $ilUser->getId()))
			{
				continue;
			}
			$room_obj = new ilChatRoom($rooms[$i]["obj_id"]);
			$priv_rooms = $room_obj->getAllRoomsOfObject();
			$rooms[$i]["sub"] = array();
			foreach($priv_rooms as $room_id => $pr)
			{
				$room_obj->setRoomId($room_id);
				if (
					$room_obj->isInvited($ilUser->getId()) ||
					$room_obj->isOwner($ilUser->getId()) ||
					$rbacsystem->checkAccess('moderate', $room_obj->getRoomId())
				)
				{
					$rooms[$i]["sub"][] = array(
						'room_id' => $room_obj->getRoomId(),
						'title' => $room_obj->getTitle()
					);
				} 
			}
			$titel = array();
			
			foreach($rooms[$i]["sub"] as $k => $v) {
				$titel[$k] = strtolower($v['title']);
			}
			
			array_multisort($titel, SORT_STRING, $rooms[$i]["sub"]);
			$readable_rooms[] = $rooms[$i];
		}
		return $readable_rooms;
	}
	
	public function getRoomSelect()
	{
		global $lng, $ilUser;
		
		$sel_ref_id = false;
		$sel_room_id = false;
		
		if ($ilUser->getPref('chatviewer_last_selected_room'))
		{
			$parts = split(",", $ilUser->getPref('chatviewer_last_selected_room'));
			$sel_ref_id = $parts[0];
			if ($parts[1])
				$sel_room_id = $parts[1];
		}

		$readable = $this->getReadableAreas();
		$tpl = new ilTemplate("tpl.chat_block_room_select.html", true, true, "Modules/Chat");
		$tpl->setVariable('TXT_SELECT_ROOM', $lng->txt('chat_select_room'));
		foreach($readable as $room)
		{
			$tpl->setCurrentBlock("room_row");
			$tpl->setVariable("ROW_VALUE", $room["ref_id"]);
			$tpl->setVariable("ROW_CAPTION", $room["title"]);
			
			if ($sel_ref_id == $room["obj_id"] && !$sel_room_id)
				$tpl->setVariable('ROW_SELECTED', 'selected="selected"');
			
			$tpl->parseCurrentBlock("select_room_row");
			foreach($room["sub"] as $priv_room)
			{
				$tpl->setCurrentBlock("room_row");
				$tpl->setVariable("ROW_VALUE_PRIV", $room["ref_id"].','.$priv_room['room_id']);
				$tpl->setVariable("ROW_CAPTION_PRIV", $priv_room["title"]);
				if ($sel_ref_id == $room["obj_id"] && $sel_room_id == $priv_room['room_id'])
					$tpl->setVariable('ROW_SELECTED_PRIV', 'selected="selected"');
				$tpl->parseCurrentBlock("select_privroom_row");				
			}
			
		}
		return $tpl->get();
	}
	
	public function getMessages($obj_id, $room_id, $last_known_id, &$new_last_known_id = -1)
	{
		$room = new ilChatRoom($obj_id);
		$room->setRoomId($room_id);
		$messages = $room->getNewMessages($last_known_id, $new_last_known_id, time() - 60*60*12);
		
		return $messages;
	}
}