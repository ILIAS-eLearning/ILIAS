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
* Class ilChatUser
* 
* @author Stefan Meyer 
* @version $Id$
*
*/

class ilChatRoom
{
	var $ilias;
	var $lng;

	var $error_msg;

	var $ref_id; // OF CHAT OBJECT
	var $owner_id;
	var $room_id;
	var $guests;
	var $title;
	
	var $user_id;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilChatRoom($a_id)
	{
		global $ilias,$lng,$ilUser;

		define(MAX_LINES,1000);

		$this->ilias =& $ilias;
		$this->lng =& $lng;

		$this->obj_id = $a_id;
		$this->owner_id = $ilUser->getId();
		$this->user_id = $_SESSION["AccountId"];
	}

	// SET/GET
	function getErrorMessage()
	{
		return $this->error_msg;
	}

	function setRoomId($a_id)
	{
		$this->room_id = $a_id;
		
		// READ DATA OF ROOM
		$this->__read();
	}
	function getRoomId()
	{
		return $this->room_id;
	}
	function getObjId()
	{
		return $this->obj_id;
	}
	function setOwnerId($a_id)
	{
		$this->owner_id = $a_id;
	}
	function getOwnerId()
	{
		return $this->owner_id;
	}
	
	function getName()
	{
		if(!$this->getRoomId())
		{
			return $this->getObjId();
		}
		else
		{
			// GET NAME OF PRIVATE CHATROOM
		}
	}
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	function getTitle()
	{
		return $this->title;
	}
	function getGuests()
	{
		return $this->guests ? $this->guests : array();
	}
	function setUserId($a_id)
	{
		$this->user_id = $a_id;
	}
	function getUserId()
	{
		return $this->user_id;
	}

	function invite($a_id)
	{
		global $ilDB;
		
		$res = $ilDB->queryf('
			SELECT * FROM chat_invitations
			WHERE chat_id = %s
			AND	room_id = %s
			AND	guest_id = %s',
			array('integer', 'integer', 'integer'),
			array($this->getObjId(), $this->getRoomId(), $a_id));

		if($res->numRows() > 0)
		{		
			$res = $ilDB->manipulateF('
				UPDATE chat_invitations
				SET invitation_time = %s,
				guest_informed = %s
				WHERE chat_id = %s
				AND	room_id = %s
				AND	guest_id = %s',
				array('integer', 'integer', 'integer', 'integer', 'integer'),
				array(time(), 0, $this->getObjId(), $this->getRoomId(), $a_id));	

		}
		else
		{
			$res = $ilDB->manipulateF(
				'INSERT INTO chat_invitations 
				(	chat_id, 
					room_id, 
					guest_id, 
					invitation_time
				)
				VALUES (%s, %s, %s, %s)',
				array('integer', 'integer', 'integer', 'integer'),
				array($this->getObjId(), $this->getRoomId(), $a_id, time()));

		}		
	}
	
	function drop($a_id)
	{
		global $ilDB;
		
			$res = $ilDB->manipulateF('
			DELETE FROM chat_invitations 
			WHERE chat_id = %s
			AND room_id = %s
			AND guest_id = %s',
			array('integer', 'integer', 'integer'),
			array($this->getObjId(), $this->getRoomId(), $a_id));
		
	}

	function visited($a_id)
	{
		global $ilDB;
	
			$res = $ilDB->manipulateF('
			UPDATE chat_invitations 
			SET guest_informed = %s
			WHERE chat_id = %s
			AND room_id = %s
			AND guest_id = %s',
			array('integer', 'integer', 'integer', 'integer'),
			array('1', $this->getObjId(), $this->getRoomId(), $a_id));
		
	}
	
	function checkAccess()
	{
		global $rbacsystem;
		
		if ($this->getObjId() ||
			$this->getRoomId())
		{
			if(!$this->isInvited($this->getUserId()) && 
			   !$this->isOwner() &&
			   !$rbacsystem->checkAccess('moderate', $_GET['ref_id']))
			{
				$this->setRoomId(0);
				return false;
			}
			
			$this->visited($this->getUserId());
		}
		return true;
	}

	function isInvited($a_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM chat_invitations ci JOIN chat_rooms ca 
			WHERE ci.room_id = ca.room_id 
			AND ci.chat_id = %s
			AND ci.room_id = %s
			AND owner = %s
			AND ci.guest_id = %s',
			array('integer', 'integer', 'integer','integer'),
			array($this->getObjId(), $this->getRoomId(), $this->getOwnerId(), $a_id));
				
		return $res->numRows() ? true : false;
	}
	function isOwner()
	{
		return $this->getOwnerId() == $this->getUserId();
	}

	// METHODS FOR EXPORTTING CHAT
	function appendMessageToDb($message)
	{
		if($this->__getCountLines() >= MAX_LINES)
		{
			$this->__deleteFirstLine();
		}
		$this->__addLine($message);

		return true;
	}
	function getAllMessages()
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT message FROM chat_room_messages 
			WHERE chat_id = %s
			AND room_id = %s
			ORDER BY commit_timestamp',
			array('integer', 'integer'),
			array($this->getObjId(), $this->getRoomId()));

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$data[] = $row->message;
		}
		return is_array($data) ? implode("<br />",$data) : "";		
	}
	
	function deleteAllMessages()
	{
		global $ilDB;

		$res = $ilDB->manipulateF('
			DELETE FROM chat_room_messages
			WHERE chat_id = %s
			AND room_id = %s',
			array('integer', 'integer'),
			array($this->getObjId(), $this->getRoomId()));
		
		return true;
	}

	function updateLastVisit()
	{
		// CHECK IF OLD DATA EXISTS
		global $ilDB;
		
		$res = $ilDB->manipulateF('
			DELETE FROM chat_user WHERE usr_id = %s',
			array('integer'),
			array($this->getUserId()));
		
		$res = $ilDB->manipulateF('
			INSERT INTO chat_user
			(	usr_id,
				room_id,
				chat_id,
				last_conn_timestamp
			)
			VALUES(%s, %s, %s, %s)',
			array('integer', 'integer', 'integer', 'integer'),
			array($this->getUserId(), $this->getRoomId(), $this->getObjId(), time()));

		return true;
	}

	function setKicked($a_usr_id)
	{
		global $ilDB;
		
		$res = $ilDB->manipulateF('
			UPDATE chat_user 
			SET kicked = %s
			WHERE usr_id = %s
			AND chat_id = %s
			AND room_id = %s',
			array('integer', 'integer', 'integer', 'integer'),
			array('1', $a_usr_id, $this->getObjId(), '0'));
		
		return true;
	}

	function setUnkicked($a_usr_id)
	{
		global $ilDB;
		
		$res = $ilDB->manipulateF('
			UPDATE chat_user SET kicked = %s
			WHERE usr_id = %s
			AND chat_id = %s
			AND room_id = %s',
			array('integer', 'integer', 'integer', 'integer'),
			array('0', $a_usr_id, $this->getObjId(), '0'));
				
		return true;
	}

	function  isKicked($a_usr_id)
	{
		global $ilDB;
		
		$res = $ilDB->queryf('
			SELECT * FROM chat_user 
			WHERE kicked = %s
			AND usr_id = %s
			AND chat_id = %s',
			array('integer', 'integer', 'integer'),
			array('1', $a_usr_id, $this->getObjId()));
		 
		return $res->numRows() ? true : false;
	}		

	function getCountActiveUser($chat_id,$room_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM chat_user 
			WHERE chat_id = %s
			AND room_id = %s
			AND last_conn_timestamp > %s',
			array('integer', 'integer', 'integer'),
			array($chat_id, $room_id, time() - 40));
				
		return $res->numRows();
	}

	function _getCountActiveUsers($chat_id,$room_id = 0)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM chat_user 
			WHERE chat_id = %s
			AND room_id = %s
			AND last_conn_timestamp > %s',
			array('integer', 'integer', 'integer'),
			 array($chat_id, $room_id, time() - 40));
				
		return $res->numRows();
	}
		

	function getActiveUsers()
	{
		global $ilDB;
		
		$res = $ilDB->queryf('
			SELECT * FROM chat_user 
			WHERE chat_id = %s
			AND room_id = %s
			AND last_conn_timestamp > %s',
			array('integer', 'integer', 'integer'),
			array($this->getObjId(), $this->room_id, time() - 40));
		

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$usr_ids[] = $row->usr_id;
		}
		return $usr_ids ? $usr_ids : array();
	}

	// Static
	function _isActive($usr_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM chat_user 
			WHERE room_id = %s
			AND usr_id = %s
			AND last_conn_timestamp > %s',
			array('integer', 'integer', 'integer'),
			array('0', $usr_id, time() - 40));
					
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->chat_id;
		}
		return false;
	}

	function getOnlineUsers()
	{
		// TODO: CHECK INVITABLE AND ALLOW MESSAGES 
		return ilUtil::getUsersOnline();
	}

	function validate()
	{
		$this->error_msg = "";

		if(!$this->getTitle())
		{
			$this->error_msg .= $this->lng->txt("chat_title_missing");
		}
		if(!$this->getOwnerId())
		{
			$this->ilias->raiseError("MISSING OWNER ID",$this->ilias->error_obj->FATAL);
		}
		return $this->error_msg ? false : true;
	}
	function deleteRooms($a_ids)
	{
		if(!is_array($a_ids))
		{
			$this->ilias->raiseError("ARRAY REQUIRED",$this->ilias->error_obj->FATAL);
		}
		foreach($a_ids as $id)
		{
			$this->delete($id);
		}
		return true;
	}

	function delete($a_id, $a_owner = 0)
	{
		// DELETE ROOM
		global $ilDB;
		
		$data_values = array();
		$data_types = array();
		
		$query = 'DELETE FROM chat_rooms WHERE room_id = %s';
		array_push($data_types, 'integer');
		array_push($data_values, $a_id);
		
		if ($a_owner > 0)
		{
			$query .=' AND owner = %s';
			array_push($data_types, 'integer');
			array_push($data_values,$a_owner);
		}
		$res = $ilDB->manipulateF($query, $data_types, $data_values);

		// DELETE INVITATIONS
		$res = $ilDB->manipulateF('
			DELETE FROM chat_invitations WHERE room_id = %s',
			array('integer'), array($a_id));


		// DELETE MESSAGES
		$res = $ilDB->manipulateF('
			DELETE FROM chat_room_messages WHERE room_id = %s',
			array('integer'),array($a_id));
		
		// DELETE USER_DATA
		$data_types = array();
		$data_values = array();
		
		$query = 'DELETE FROM chat_user WHERE room_id = %s';
		array_push($data_types, 'integer');
		array_push($data_values,$a_id);

		if ($a_owner > 0)
		{
			$query .= ' AND owner = %s';
			array_push($data_types, 'integer');
			array_push($data_values,$a_owner);
		}
		$res = $ilDB->manipulateF($query, $data_types, $data_values);

			
		// AND ALL RECORDINGS
		$res = $ilDB->queryf('
			SELECT record_id FROM chat_records WHERE room_id = %s',
			array('integer'), array($a_id));
		
		if (ilDB::isDbError($res)) die("ilObjChat::delete(): " . $res->getMessage() . "<br>SQL-Statement: ".$res);
		if (($num = $res->numRows()) > 0)
		{
			for ($i = 0; $i < $num; $i++)
			{
				$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
				$statement_2 = $ilDB->manipulateF('
					DELETE FROM chat_record_data WHERE record_id = %s',
					array('integer'), array($row['record_id']));
				
			}
			
		}
		$statement = $ilDB->manipulateF('
			DELETE FROM chat_records WHERE room_id = %s',
			array('integer'), array($a_id));
		
		return true;
	}

	function rename()
	{
		global $ilDB;
		
		$res = $ilDB->manipulateF('
			UPDATE chat_rooms 
			SET title = %s
			WHERE room_id = %s',
			array('text', 'integer'),
			array($this->getTitle(), $this->getRoomId()));

		return true;
	}

	function lookupRoomId()
	{
		global $ilDB;
		
		$res = $ilDB->queryf('
			SELECT * FROM chat_rooms 
			WHERE title = %s
			AND chat_id = %s
			AND owner = %s',
			array('text', 'integer', 'integer'),
			array($this->getTitle(), $this->getObjId(), $this->getOwnerId()));
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->room_id;
		}
		return false;
	}

	function add()
	{
		global $ilDB;

		$res = $ilDB->manipulateF('
			INSERT INTO chat_rooms 
			(	title,
				chat_id,
				owner)
			VALUES(%s,%s,%s)',
			array('text', 'integer', 'integer'),
			array($this->getTitle(), $this->getObjId(), $this->getOwnerId()));
		
		return ($id = $ilDB->getLastInsertId()) ? $id : false;
	}

	function getInternalName()
	{
		if(!$this->getRoomId())
		{
			return $this->getObjId();
		}
		else
		{
			return $this->getObjId()."_".$this->getRoomId();
		}
	}
	
	function getRooms()
	{
		
		global $tree, $ilDB, $rbacsystem;

		$data_types = array();
		$data_values = array();
		$query = 'SELECT DISTINCT(cr.room_id) room_id, owner, title, cr.chat_id chat_id 
				FROM chat_rooms cr NATURAL LEFT JOIN chat_invitations 
				WHERE (owner = %s) OR (guest_id = %s)';
		
		array_push($data_types, 'integer', 'integer');
		array_push($data_values, $this->getUserId(), $this->getUserId());
		
		if($rbacsystem->checkAccess('moderate', $_GET['ref_id']))
		{
			$query .= ' OR %s';
			array_push($data_types, 'integer');
			array_push($data_values, '1');
		}
		$res = $ilDB->queryf($query, $data_types, $data_values);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$data[$row->room_id]["room_id"] = $row->room_id;
			$data[$row->room_id]["chat_id"] = $row->chat_id;
			$data[$row->room_id]["owner"] = $row->owner;
			$data[$row->room_id]["title"] = $row->title;
		}
		return $data ? $data : array();
	}

	function getRoomsOfObject()
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM chat_rooms 
			WHERE chat_id = %s
			AND owner = %s',
			array('integer', 'integer'),
			array($this->getObjId(), $this->getUserId()));
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$data[$row->room_id]["room_id"] = $row->room_id;
			$data[$row->room_id]["owner"] = $row->owner;
			$data[$row->room_id]["title"] = $row->title;
			$data[$row->room_id]["owner"] = $row->owner;
		}
		return $data ? $data : array();
	}
	
	function getAllRoomsOfObject()
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM chat_rooms 
			WHERE chat_id = %s',
			array('integer'),
			array($this->getObjId()));
					
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$data[$row->room_id]["room_id"] = $row->room_id;
			$data[$row->room_id]["owner"] = $row->owner;
			$data[$row->room_id]["title"] = $row->title;
			$data[$row->room_id]["owner"] = $row->owner;
		}
		return $data ? $data : array();
	}		

	function getAllRooms()
	{
		global $ilObjDataCache,$ilUser,$rbacsystem;

		$obj_ids = array();
		$unique_chats = array();

		$pub_chat_id = ilObjChat::_getPublicChatRefId();
		if($rbacsystem->checkAccess('read',$pub_chat_id))
		{
			$obj_id = $ilObjDataCache->lookupObjId($pub_chat_id);
			if(!in_array($obj_id,$obj_ids))
			{
				$unique_data['child'] = $pub_chat_id;
				$unique_data['title'] = $ilObjDataCache->lookupTitle($obj_id);
				$unique_data['obj_id'] = $obj_id;
				$unique_data['ref_id'] = $pub_chat_id;
				
				$unique_chats[] = $unique_data;
				$obj_ids[] = $obj_id;
			}
		}

		foreach(ilUtil::_getObjectsByOperations("chat","read",$ilUser->getId(),-1) as $chat_id)
		{
			$obj_id = $ilObjDataCache->lookupObjId($chat_id);
			if(!in_array($obj_id,$obj_ids))
			{
				$unique_data['child'] = $chat_id;
				$unique_data['title'] = $ilObjDataCache->lookupTitle($obj_id);
				$unique_data['obj_id'] = $obj_id;
				$unique_data['ref_id'] = $chat_id;
				
				$unique_chats[] = $unique_data;
				$obj_ids[] = $obj_id;
			}
		}
		return $unique_chats ? $unique_chats : array();
	}

	function checkWriteAccess()
	{
		global $rbacsystem;
		
		if($rbacsystem->checkAccess('moderate', $_GET['ref_id']))
		{
			return true;
		}
		
		if($this->isKicked($this->getUserId()))
		{
			return false;
		}
		if(!$this->getRoomId())
		{
			return true;
		}
		if($this->getUserId() == $this->getOwnerId())
		{
			return true;
		}
		if($this->isInvited($this->getUserId()))
		{
			return true;
		}
		return false;
	}

	// PRIVATE
	function __getCountLines()
	{
		global $ilDB;

		$res =$ilDB->queryf('
			SELECT COUNT(entry_id) number_lines FROM chat_room_messages 
			WHERE chat_id = %s
			AND room_id = %s',
			array('integer', 'integer'),
			array($this->getObjId(), $this->getRoomId()));
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->number_lines;
		}
		return 0;
	}
	
	function __deleteFirstLine()
	{
		global $ilDB;
	
		$res = $ilDB->queryf('
			SELECT entry_id, MIN(commit_timestamp) last_comm FROM chat_room_messages
			WHERE chat_id = %s
			AND room_id = %s
			GROUP BY null',
			array('integer', 'integer'),
			array($this->getObjId(), $this->getRoomId()));
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$entry_id = $row->entry_id;
		}
		if($entry_id)
		{
			$res = $ilDB->manipulateF('
				DELETE FROM chat_room_messages WHERE entry_id = %s',
				array('integer'), array($entry_id));
			
		}		
		return true;
	}
	
	function __addLine($message)
	{
		global $ilDB;

			$res = $ilDB->manipulateF('
			INSERT INTO chat_room_messages
			(	chat_id,
				room_id,
				message,
				commit_timestamp)
			VALUES(%s, %s, %s, %s)',
			array('integer', 'integer', 'text', 'timestamp'),
			 array($this->getObjId(), $this->getRoomId(), $message, date('Y-m-d H:i:s', time())));
			
		$this->chat_record = new ilChatRecording($this->getObjId());
		$this->chat_record->setRoomId($this->getRoomId());
		if ($this->chat_record->isRecording())
		{

			$res = $ilDB->manipulateF('
				INSERT INTO chat_record_data
				(	record_id,
					message,
					msg_time)
				VALUES(%s, %s, %s)',
				array('integer', 'text', 'integer'),
				array($this->chat_record->getRecordId(), $message, time()));
		}

		return true;
	}


	function __read()
	{
		global $ilDB;
		
		$this->guests = array();

		$res = $ilDB->queryf('
			SELECT * FROM chat_rooms WHERE room_id = %s',
			array('integer'),
			array($this->getRoomId()));
				
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setTitle($row->title);
			$this->setOwnerId($row->owner);
		}

		$res = $ilDB->queryf('
			SELECT * FROM chat_invitations
			WHERE chat_id = %s
			AND room_id = %s',
			array('integer', 'integer'),
			array($this->getObjId(), $this->getRoomId()));
				
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->guests[] = $row->guest_id;
		}
		return true;
	}

	function _unkick($a_usr_id)
	{
		global $ilDB;

		$statement = $ilDB->manipulateF('
			UPDATE chat_user SET kicked = %s
			WHERE usr_id = %s',
			array('integer', 'integer'),
			array('0', $a_usr_id));
								
		return true;
	}


} // END class.ilChatRoom
?>