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
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
*/

class ilChatBlockedUsers
{
	var $id;
	var $db;

	var $blocked = array();

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilChatBlockedUsers($a_id)
	{
		global $ilDB;

		$this->db =& $ilDB;
		$this->id = $a_id;

		$this->__read();
	}

	function getBlockedUsers()
	{
		return $this->blocked ? $this->blocked : array();
	}

	function isBlocked($a_usr_id)
	{
		return in_array($a_usr_id,$this->blocked) ? true : false;
	}
	function block($a_usr_id)
	{
		global $ilDB;
		
		if(in_array((int) $a_usr_id,$this->blocked) or !((int) $a_usr_id))
		{
			return false;
		}

		$statement = $this->db->manipulateF('
			INSERT INTO chat_blocked 
			SET chat_id = %s,
				usr_id = %s',
			array('integer', 'integer'),
			array($this->id, $a_usr_id));
		
		$this->__read();

		return true;
	}

	function unblock($a_usr_id)
	{
		global $ilDB;
		
		if(!in_array((int) $a_usr_id,$this->blocked))
		{
			return false;
		}

		$statement = $this->db->manipulateF('
			DELETE FROM chat_blocked 
			WHERE chat_id = %s
			AND	usr_id = %s',
			array('integer', 'integer'),
			array($this->id, $a_usr_id));
		
		$this->__read();

		return true;
	}
		

		
	// Static
	function _isBlocked($a_chat_id,$a_usr_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM chat_blocked 
			WHERE chat_id = %s
			AND	usr_id = %s',
			array('integer', 'integer'),
			array($this->id, $a_usr_id));
		
		return $res->numRows() ? true : false;
	}


	function _deleteUser($a_usr_id)
	{
		global $ilDB;

		$statement = $ilDB->manipulateF('
			DELETE FROM chat_blocked WHERE usr_id = %s',
			array('integer'),
			array((int) $a_usr_id));
		
		return true;
	}
	function _deleteChat($a_chat_id)
	{
		global $ilDB;

		$statement = $ilDB->manipulateF('
			DELETE FROM chat_blocked WHERE chat_id = %s',
			array('integer'),
			array((int) $a_chat_id));
		
		return true;
	}		


	// Private
	function __read()
	{
		global $ilDB;
		
		$this->blocked = array();

		$res = $this->db->queryf('
			SELECT * FROM chat_blocked WHERE chat_id = %s',
			array('integer'),
			array($this->id));
			
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->blocked[] = $row->usr_id;
		}
		return true;
	}

} // END class.ilBlockedUsers
?>