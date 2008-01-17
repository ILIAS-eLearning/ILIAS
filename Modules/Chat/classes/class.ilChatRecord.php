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
* Class ilChatRecord
* 
* @author Jens Conze 
* @version $Id$
*
*/

class ilChatRecord
{
	var $ilias;
	var $lng;

	var $error_msg;

	var $ref_id = 0; // OF CHAT OBJECT
	var $moderator_id = 0;
	var $room_id = 0;
	var $record_id = 0;

	var $data = array();

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilChatRecord($a_id = 0)
	{
		global $ilias,$lng;

		define(MAX_TIME,60*60*24);

		$this->ilias =& $ilias;
		$this->lng =& $lng;

		if ($a_id > 0)
		{
			$this->setRefId($a_id);
		}
	}

	// SET/GET
	function getErrorMessage()
	{
		return $this->error_msg;
	}

	function setRoomId($a_id)
	{
		$this->room_id = $a_id;
	}
	function getRoomId()
	{
		return $this->room_id;
	}

	function setRefId($a_id)
	{
		$this->ref_id = $a_id;
	}
	function getRefId()
	{
		return $this->ref_id;
	}

	function setRecordId($a_id)
	{
		$this->record_id = $a_id;
	}
	function getRecordId()
	{
		return $this->record_id;
	}

	function setModeratorId($a_id)
	{
		$this->moderator_id = $a_id;
	}
	function getModeratorId()
	{
		return $this->moderator_id;
	}

	function setRecord($a_data)
	{
		$this->data = $a_data;
	}
	function getRecord($a_id = 0)
	{
		global $ilDB;
		
		if ($a_id != 0)
		{
			$this->setRecordId($a_id);
		}

		$query = "SELECT * FROM chat_records WHERE 
					record_id = ".$ilDB->quote($this->getRecordId())."";
		$res = $this->ilias->db->query($query);
		if (ilDBx::isDbError($res)) die("ilChatRecord::getRecord(): " . $res->getMessage() . "<br>SQL-Statement: ".$query);

		$data = array();
		if ($res->numRows() > 0)
		{
			$data = $res->fetchRow(DB_FETCHMODE_ASSOC);
		}
		$this->setRecord($data);
	}

	function setTitle($a_title)
	{
		$this->data["title"] = $a_title;
	}
	function getTitle()
	{
		return $this->data["title"];
	}

	function setDescription($a_description)
	{
		$this->data["description"] = $a_description;
	}
	function getDescription()
	{
		return $this->data["description"];
	}

	function startRecording($a_title = "")
	{
		global $ilDB;
		
		$query = "INSERT INTO chat_records SET 
					moderator_id = ".$ilDB->quote($this->getModeratorId()).", 
					chat_id = ".$ilDB->quote($this->getRefId()).", 
					room_id = ".$ilDB->quote($this->getRoomId()).", 
					title = ".$ilDB->quote($a_title).", 
					start_time = '" . time() . "'";
		$res = $this->ilias->db->query($query);
		if (ilDBx::isDbError($res)) die("ilChatRecord::startRecording(): " . $res->getMessage() . "<br>SQL-Statement: ".$query);

		$query = "SELECT LAST_INSERT_ID()";
		$res = $this->ilias->db->query($query);
		if (ilDBx::isDbError($res)) die("ilChatRecord::startRecording(): " . $res->getMessage() . "<br>SQL-Statement: ".$query);

		if ($res->numRows() > 0)
		{
			$lastId = $res->fetchRow();
			$this->setRecordId($lastId[0]);

			$this->getRecord();
		}
	}

	function stopRecording()
	{
		global $ilDB;
		
		$query = "UPDATE chat_records SET 
					end_time = '" . time() . "' WHERE 
					chat_id = ".$ilDB->quote($this->getRefId())." AND 
					room_id = ".$ilDB->quote($this->getRoomId())."";
		$res = $this->ilias->db->query($query);
		if (ilDBx::isDbError($res)) die("ilChatRecord::stopRecording(): " . $res->getMessage() . "<br>SQL-Statement: ".$query);

		$this->setRecordId(0);

		$data = array();
		$this->setRecord($data);
	}

	function isRecording()
	{
		global $ilDB;
		
		$query = "SELECT record_id FROM chat_records WHERE 
					chat_id = ".$ilDB->quote($this->getRefId())." AND 
					room_id = ".$ilDB->quote($this->getRoomId())." AND 
					start_time > 0 AND 
					end_time = 0";
		$res = $this->ilias->db->query($query);
		if (ilDBx::isDbError($res)) die("ilChatRecord::isRecording(): " . $res->getMessage() . "<br>SQL-Statement: ".$query);

		if ($res->numRows() > 0)
		{
			$id = $res->fetchRow(DB_FETCHMODE_ASSOC);
			$this->setRecordId($id["record_id"]);

			$this->getRecord();
			return true;
		}
		
		return false;
	}

} // END class.ilChatRecord
?>