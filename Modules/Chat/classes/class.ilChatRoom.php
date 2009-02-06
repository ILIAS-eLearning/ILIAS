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
		
		$statement = $ilDB->prepare('
			SELECT * FROM chat_invitations
			WHERE chat_id = ?
			AND	room_id = ?
			AND	guest_id = ?',
			array('integer', 'integer', 'integer')
		);
		
		$data = array($this->getObjId(), $this->getRoomId(), $a_id);
		$res = $ilDB->execute($statement, $data);

		if($res->numRows() > 0)
		{		
			$statement = $ilDB->prepareManip('
				UPDATE chat_invitations
				SET invitation_time = ?,
				guest_informed = ?
				WHERE chat_id = ?
				AND	room_id = ?
				AND	guest_id = ?',
				array('integer', 'integer', 'integer', 'integer', 'integer')
			);
			
			$data = array(time(), 0, $this->getObjId(), $this->getRoomId(), $a_id);	
			$res = $ilDB->execute($statement, $data);
		}
		else
		{
			$statement = $ilDB->prepareManip(
				'INSERT INTO chat_invitations (chat_id, room_id, guest_id, invitation_time) '.
				'VALUES(?, ?, ?, ?)',
				array('integer', 'integer', 'integer', 'integer')
			);
			
			$data = array($this->getObjId(), $this->getRoomId(), $a_id, time());
			$res = $ilDB->execute($statement, $data);
		}		
	}
	
	function drop($a_id)
	{
		global $ilDB;
		
		$statement = $this->ilias->db->prepareManip('
			DELETE FROM chat_invitations 
			WHERE chat_id = ?
			AND room_id = ?
			AND guest_id = ?',
			array('integer', 'integer', 'integer')
		);
		
		$data = array($this->getObjId(), $this->getRoomId(), $a_id);
		$res = $this->ilias->db->execute($statement, $data);
	}

	function visited($a_id)
	{
		global $ilDB;
	
		$statement = $this->ilias->db->prepareManip('
			UPDATE chat_invitations 
			SET guest_informed = ?
			WHERE chat_id = ?
			AND room_id = ?
			AND guest_id = ?',
			array('integer', 'integer', 'integer', 'integer')
		);
		
		$data = array('1', $this->getObjId(), $this->getRoomId(), $a_id);
		
		$res = $this->ilias->db->execute($statement, $data);
		
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

		$statement = $this->ilias->db->prepare('
			SELECT * FROM chat_invitations ci JOIN chat_rooms ca 
			WHERE ci.room_id = ca.room_id 
			AND ci.chat_id = ?
			AND ci.room_id = ?
			AND owner = ?
			AND ci.guest_id = ?',
			array('integer', 'integer', 'integer','integer')
		);
		
		$data = array($this->getObjId(), $this->getRoomId(), $this->getOwnerId(), $a_id);
		$res = $this->ilias->db->execute($statement, $data);
				
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

		$statement = $this->ilias->db->prepare('
			SELECT message FROM chat_room_messages 
			WHERE chat_id = ?
			AND room_id = ?
			ORDER BY commit_timestamp',
			array('integer', 'integer')
		);
		
		$sql_data = array($this->getObjId(), $this->getRoomId());
		$res = $this->ilias->db->execute($statement, $sql_data);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$data[] = $row->message;
		}
		return is_array($data) ? implode("<br />",$data) : "";		
	}
	
	function deleteAllMessages()
	{
		global $ilDB;

		$statement = $this->ilias->db->prepareManip('
			DELETE FROM chat_room_messages
			WHERE chat_id = ?
			AND room_id = ?',
			array('integer', 'integer')
		);
		$data = array($this->getObjId(), $this->getRoomId());
		
		$res = $this->ilias->db->execute($statement, $data);
		
		return true;
	}

	function updateLastVisit()
	{
		// CHECK IF OLD DATA EXISTS
		global $ilDB;
		
		$statement = $this->ilias->db->prepareManip('
			DELETE FROM chat_user WHERE usr_id = ?',
			array('integer')
		);
		
		$data = array($this->getUserId());
		$res = $this->ilias->db->execute($statement, $data);
		
		$statement = $this->ilias->db->prepareManip('
			INSERT INTO chat_user
			SET usr_id = ?,
				room_id = ?,
				chat_id = ?,
				last_conn_timestamp = ?',
			array('integer', 'integer', 'integer', 'integer')
		);
		
		$data = array($this->getUserId(), $this->getRoomId(), $this->getObjId(), time());
		$res = $this->ilias->db->execute($statement, $data);

		return true;
	}

	function setKicked($a_usr_id)
	{
		global $ilDB;
		
		$statement = $this->ilias->db->prepareManip('
			UPDATE chat_user 
			SET kicked = ?
			WHERE usr_id = ?
			AND chat_id = ?
			AND room_id = ?',
			array('integer', 'integer', 'integer', 'integer')
		);
		
		$data = array('1', $a_usr_id, $this->getObjId(), '0');
		
		$res = $this->ilias->db->execute($statement, $data);
		
		return true;
	}

	function setUnkicked($a_usr_id)
	{
		global $ilDB;
		
		$statement = $this->ilias->db->prepareManip('
			UPDATE chat_user SET kicked = ?
			WHERE usr_id = ?
			AND chat_id = ?
			AND room_id = ?',
			array('integer', 'integer', 'integer', 'integer')
		);
		
		$data = array('0', $a_usr_id, $this->getObjId(), '0');

		$res = $this->ilias->db->execute($statement, $data);
				
		return true;
	}

	function  isKicked($a_usr_id)
	{
		global $ilDB;
		
		$statement = $this->ilias->db->prepare('
			SELECT * FROM chat_user 
			WHERE kicked = ?
			AND usr_id = ?
			AND chat_id = ?',
			array('integer', 'integer', 'integer')
		);	
		
		$data = array('1', $a_usr_id, $this->getObjId());
		 
		$res = $this->ilias->db->execute($statement, $data);		
		
		return $res->numRows() ? true : false;
	}		

	function getCountActiveUser($chat_id,$room_id)
	{
		global $ilDB;

		$statement = $this->ilias->db->prepare('
			SELECT * FROM chat_user 
			WHERE chat_id = ?
			AND room_id = ?
			AND last_conn_timestamp > ?',
			array('integer', 'integer', 'integer')
		);
		
		$data = array($chat_id, $room_id, time() - 40);
		
		$res = $this->ilias->db->execute($statement, $data);		
				
		return $res->numRows();
	}

	function _getCountActiveUsers($chat_id,$room_id = 0)
	{
		global $ilDB;

		$statement = $ilDB->prepare('
			SELECT * FROM chat_user 
			WHERE chat_id = ?
			AND room_id = ?
			AND last_conn_timestamp > ?',
			array('integer', 'integer', 'integer')
		);
		
		$data = array($chat_id, $room_id, time() - 40);
		
		$res = $ilDB->execute($statement, $data);		
				
		return $res->numRows();
	}
		

	function getActiveUsers()
	{
		global $ilDB;
		
		$statement = $ilDB->prepare('
			SELECT * FROM chat_user 
			WHERE chat_id = ?
			AND room_id = ?
			AND last_conn_timestamp > ?',
			array('integer', 'integer', 'integer')
		);
		
		$data = array($this->getObjId(), $this->room_id, time() - 40);
		
		$res = $ilDB->execute($statement, $data);		
				
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

		$statement = $ilDB->prepare('
			SELECT * FROM chat_user 
			WHERE room_id = ?
			AND usr_id = ?
			AND last_conn_timestamp > ?',
			array('integer', 'integer', 'integer')
		);
		
		$data = array('0', $usr_id, time() - 40);
		
		$res = $ilDB->execute($statement, $data);		
				
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
		
		$query = 'DELETE FROM chat_rooms WHERE room_id = ?';
		array_push($data_types, 'integer');
		array_push($data_values, $a_id);
		
		if ($a_owner > 0)
		{
			$query .=' AND owner = ';
			array_push($data_types, 'integer');
			array_push($data_values,$a_owner);
		}
		$statement = $this->ilias->db->prepareManip($query, $data_types);
		$res = $this->ilias->db->execute($statement, $data_values);	

		// DELETE INVITATIONS
		$statement =$this->ilias->db->prepareManip('
			DELETE FROM chat_invitations WHERE room_id = ?',
			array('integer')
		);	
		$data = array($a_id);
		$res = $this->ilias->db->execute($statement, $data);	

		// DELETE MESSAGES
		$statement =$this->ilias->db->prepareManip('
			DELETE FROM chat_room_messages WHERE room_id = ?',
			array('integer')
		);	
		$data = array($a_id);
		$res = $this->ilias->db->execute($statement, $data);	
		
		// DELETE USER_DATA
		$data_types = array();
		$data_values = array();
		
		$query = 'DELETE FROM chat_user WHERE room_id = ?';
		array_push($data_types, 'integer');
		array_push($data_values,$a_id);

		if ($a_owner > 0)
		{
			$query .= ' AND owner = ?';
			array_push($data_types, 'integer');
			array_push($data_values,$a_owner);
		}
		$statement = $this->ilias->db->prepareManip($query, $data_types);
		$res = $this->ilias->db->execute($statement, $data_values);	
			
		// AND ALL RECORDINGS
		$statement = $this->ilias->db->prepare('
			SELECT record_id FROM chat_records WHERE room_id = ?',
			array('integer')
		);

		$data = array($a_id);
		$res = $this->ilias->db->execute($statement, $data);			
		
		if (ilDB::isDbError($res)) die("ilObjChat::delete(): " . $res->getMessage() . "<br>SQL-Statement: ".$statement);
		if (($num = $res->numRows()) > 0)
		{
			for ($i = 0; $i < $num; $i++)
			{
				$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
				$statement_2 = $this->ilias->db->prepareManip('
					DELETE FROM chat_record_data WHERE record_id = ?',
					array('integer')
				);
				$data_2 = array($row['record_id']);
				$this->ilias->db->execute($statement_2, $data_2);
			}
			
		}
		$statement = $this->ilias->db->prepareManip('
			DELETE FROM chat_records WHERE room_id = ?',
			array('integer')
		);
		
		$data = array($a_id);
		$res = $this->ilias->db->execute($statement, $data);			
		
		return true;
	}

	function rename()
	{
		global $ilDB;
		
		$statement = $this->ilias->db->prepareManip('
			UPDATE chat_rooms 
			SET title = ?
			WHERE room_id = ?',
			array('text', 'integer')
		);
		
		$data = array($this->getTitle(), $this->getRoomId());
		
		$res = $this->ilias->db->execute($statement, $data);			
		return true;
	}

	function lookupRoomId()
	{
		global $ilDB;
		
/*		$query = "SELECT * FROM chat_rooms ".
			"WHERE title = ".$ilDB->quote($this->getTitle())." ".
			"AND chat_id = ".$ilDB->quote($this->getObjId())." ".
			"AND owner = ".$ilDB->quote($this->getOwnerId())."";

		$res = $this->ilias->db->query($query);
*/		
		$statement = $this->ilias->db->prepare('
			SELECT * FROM chat_rooms 
			WHERE title = ?
			AND chat_id = ?
			AND owner = ?',
			array('text', 'integer', 'integer')
		);
		
		$data = array($this->getTitle(), $this->getObjId(), $this->getOwnerId());
		
		$res = $this->ilias->db->execute($statement, $data);			
				
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->room_id;
		}
		return false;
	}

	function add()
	{
		global $ilDB;

		$statement = $this->ilias->db->prepareManip('
			INSERT INTO chat_rooms 
			SET title = ?,
			chat_id = ?,
			owner = ?',
			array('text', 'integer', 'integer')
		);
		
		$data = array($this->getTitle(), $this->getObjId(), $this->getOwnerId());
		
		$res = $this->ilias->db->execute($statement, $data);			
		
		return ($id = $this->ilias->db->getLastInsertId()) ? $id : false;
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
				WHERE (owner = ?)OR (guest_id = ?)';
		
		array_push($data_types, 'integer', 'integer');
		array_push($data_values, $this->getUserId(), $this->getUserId());
		
		if($rbacsystem->checkAccess('moderate', $_GET['ref_id']))
		{
			$query .= ' OR ?';
			array_push($data_types, 'integer');
			array_push($data_values, '1');
		}
		$statement = $this->ilias->db->prepare($query, $data_types);
		$res = $this->ilias->db->execute($statement, $data_values);

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

		$statement = $this->ilias->db->prepare('
			SELECT * FROM chat_rooms 
			WHERE chat_id = ?
			AND owner = ?',
			array('integer', 'integer')
		);
		
		$data_values = array($this->getObjId(), $this->getUserId());
		$res = $this->ilias->db->execute($statement, $data_values);
		
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

		$statement = $this->ilias->db->prepare('
			SELECT * FROM chat_rooms 
			WHERE chat_id = ?',
			array('integer')
		);		
		
		$data_values = array($this->getObjId());
		$res = $this->ilias->db->execute($statement, $data_values);
					
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

		$statement =$ilDB->prepare('
			SELECT COUNT(entry_id) number_lines FROM chat_room_messages 
			WHERE chat_id = ?
			AND room_id = ?',
			array('integer', 'integer')
		);
		
		$data = array($this->getObjId(), $this->getRoomId());
		$res = $ilDB->execute($statement, $data);
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->number_lines;
		}
		return 0;
	}
	
	function __deleteFirstLine()
	{
		global $ilDB;
	
/*		$query = "SELECT entry_id, MIN(commit_timestamp) as last_comm FROM chat_room_messages ".
			"WHERE chat_id = ".$ilDB->quote($this->getObjId()). " ".
			"AND room_id = ".$ilDB->quote($this->getRoomId()). " ".
			"GROUP BY null";

		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$entry_id = $row->entry_id;
		}
		if($entry_id)
		{
			$query = "DELETE FROM chat_room_messages ".
				"WHERE entry_id = ".$ilDB->quote($entry_id)."";
			
			$res = $this->ilias->db->query($query);
		}
*/
		$statement = $ilDB->prepare('
			SELECT entry_id, MIN(commit_timestamp) last_comm FROM chat_room_messages
			WHERE chat_id = ?
			AND room_id = ?
			GROUP BY null',
			array('integer', 'integer')
		);
		
		$data = array($this->getObjId(), $this->getRoomId());
		$res = $ilDB->execute($statement, $data);		
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$entry_id = $row->entry_id;
		}
		if($entry_id)
		{
			$statement = $ilDB->prepareManip('
				DELETE FROM chat_room_messages WHERE entry_id = ?',
				array('integer')
			);
			$data = array($entry_id);
			
			$res = $ilDB->execute($statement, $data);		
		}		
		return true;
	}
	
	function __addLine($message)
	{
		global $ilDB;

		$statement = $ilDB->prepareManip('
			INSERT INTO chat_room_messages
			SET chat_id = ?,
				room_id = ?,
				message = ?,
				commit_timestamp = ?',
			array('integer', 'integer', 'text', 'timestamp')
		);

		$data = array($this->getObjId(), $this->getRoomId(), $message, date('Y-m-d H:i:s', time()));
		$res = $ilDB->execute($statement, $data);		
			
		$this->chat_record = new ilChatRecording($this->getObjId());
		$this->chat_record->setRoomId($this->getRoomId());
		if ($this->chat_record->isRecording())
		{

			$statement = $ilDB->prepareManip('
				INSERT INTO chat_record_data
				SET record_id = ?,
					message = ?,
					msg_time  =?',
				array('integer', 'text', 'integer')
			);
			
			$data = array($this->chat_record->getRecordId(), $message, time());
			$res = $ilDB->execute($statement, $data);					
		}

		return true;
	}


	function __read()
	{
		global $ilDB;
		
		$this->guests = array();

		$statement = $ilDB->prepare('
			SELECT * FROM chat_rooms WHERE room_id = ?',
			array('integer')
		);

		$data = array($this->getRoomId());
		
		$res = $ilDB->execute($statement, $data);					
					
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setTitle($row->title);
			$this->setOwnerId($row->owner);
		}

		$statement = $ilDB->prepare('
			SELECT * FROM chat_invitations
			WHERE chat_id = ?
			AND room_id = ?',
			array('integer', 'integer')
		);
		
		$data = array($this->getObjId(), $this->getRoomId());
		$res = $ilDB->execute($statement, $data);					
				
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->guests[] = $row->guest_id;
		}
		return true;
	}

	function _unkick($a_usr_id)
	{
		global $ilDB;

		$statement = $ilDB->prepareManip('
			UPDATE chat_user SET kicked = ?
			WHERE usr_id = ?',
			array('integer', 'integer')
		);
		
		$data = array('0', $a_usr_id);
		$ilDB->execute($statement, $data);						

		return true;
	}


} // END class.ilChatRoom
?>