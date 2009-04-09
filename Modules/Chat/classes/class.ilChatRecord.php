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
	public function __construct($a_id = 0)
	{
		global $ilias, $lng;

		define(MAX_TIME,60*60*24);

		$this->ilias =& $ilias;
		$this->lng =& $lng;

		if ($a_id > 0)
		{
			$this->setRefId($a_id);
		}
	}

	// SET/GET
	public function getErrorMessage()
	{
		return $this->error_msg;
	}

	public function setRoomId($a_id)
	{
		$this->room_id = $a_id;
	}
	
	public function getRoomId()
	{
		return $this->room_id;
	}

	public function setRefId($a_id)
	{
		$this->ref_id = $a_id;
	}
	
	public function getRefId()
	{
		return $this->ref_id;
	}

	public function setRecordId($a_id)
	{
		$this->record_id = $a_id;
	}
	
	public function getRecordId()
	{
		return $this->record_id;
	}

	public function setModeratorId($a_id)
	{
		$this->moderator_id = $a_id;
	}
	
	public function getModeratorId()
	{
		return $this->moderator_id;
	}

	public function setRecord($a_data)
	{
		$this->data = $a_data;
	}
	
	public function getRecord($a_id = 0)
	{
		global $ilDB;
		
		if ($a_id != 0)
		{
			$this->setRecordId($a_id);
		}

		$res = $ilDB->queryf('
			SELECT * FROM chat_records WHERE record_id = %s',
			array('integer'), array($this->getRecordId()));

		if (ilDB::isDbError($res))
			die("ilChatRecord::getRecord(): " . $res->getMessage() . "<br>SQL-Statement: ".$res);
		
		$row = array();

		//if ($ilDB->numRows($res) > 0)
		if ($res->numRows() > 0)
		{
			$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		}
		$this->setRecord($row);
	}

	public function setTitle($a_title)
	{
		$this->data["title"] = $a_title;
	}
	
	public function getTitle()
	{
		return $this->data["title"];
	}

	public function setDescription($a_description)
	{
		$this->data["description"] = $a_description;
	}
	
	public function getDescription()
	{
		return $this->data["description"];
	}

	/**
	 * starts a recording with a given title 
	 * @param	string	title used for storage
	 */
	public function startRecording($a_title = "")
	{
		global $ilDB;
	
		$next_id = $ilDB->nextId('chat_records');
		
		$res = $ilDB->manipulateF('
			INSERT INTO chat_records
			(record_id, moderator_id, chat_id, room_id, title, start_time ) 
			VALUES(%s, %s, %s, %s, %s,%s)',
			array('integer', 'integer', 'integer', 'integer', 'text', 'integer'),
			array($next_id, $this->getModeratorId(), $this->getRefId(), $this->getRoomId(), $a_title, time()));
		
		if (ilDB::isDbError($res))
			die("ilChatRecord::startRecording(): " . $res->getMessage() . "<br>SQL-Statement: ".$res);
		
		//$res = $ilDB->query("SELECT LAST_INSERT_ID()");
		//if (ilDB::isDbError($res)) die("ilChatRecord::startRecording(): " . $res->getMessage() . "<br>SQL-Statement: ".$res);
		
		if ($res->numRows() > 0)
		//if ($ilDB->numRows($res) > 0)
		{
			$lastId = $res->fetchRow(DB_FETCHMODE_ASSOC);
			$this->setRecordId($lastId[0]);
			$this->getRecord();
		}		
		
	}

	/**
	 * stops current recording 
	 *
	 */
	public function stopRecording()
	{
		global $ilDB;
		
		$res = $ilDB->manipulateF('
			UPDATE chat_records 
			SET end_time = %s
			WHERE chat_id = %s
			AND room_id = %s
			AND record_id = %s',
			array('integer', 'integer', 'integer', 'integer'),
			array(time(), $this->getRefId(), $this->getRoomId(), $this->getRecordId()));
		
		if (ilDB::isDbError($res))
			die("ilChatRecord::stopRecording(): " . $res->getMessage() . "<br>SQL-Statement: ".$res);	
		
		$this->setRecordId(0);
		$data = array();
		$this->setRecord($data);
	}

	public function isRecording()
	{
		global $ilDB;
		
		$res = $ilDB->queryf('
			SELECT record_id FROM chat_records 
			WHERE chat_id = %s 
			AND room_id = %s 
			AND start_time > %s 
			AND end_time = %s',
			array('integer', 'integer', 'integer', 'integer'),
			array($this->getRefId(), $this->getRoomId(), '0', '0'));
		
		if (ilDB::isDbError($res)) die("ilChatRecord::isRecording(): " . $res->getMessage() . "<br>SQL-Statement: ".$res);
		

		//if ($ilDB->numRows($res) > 0)
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
