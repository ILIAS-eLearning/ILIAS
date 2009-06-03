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
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id:$
* 
* @externalTableAccess ilObjUser on usr_session
* @ingroup ServicesAuthentication
*/
class ilSession
{
	/**
	* Get session data from table
	*
	* @param	string		session id
	* @return	string		session data
	*/
	static function _getData($a_session_id)
	{
		global $ilDB;
		
		$q = "SELECT data FROM usr_session WHERE session_id = ".
			$ilDB->quote($a_session_id, "text");
		$set = $ilDB->query($q);
		$rec = $ilDB->fetchAssoc($set);

		return $rec["data"];
	}
	
	/**
	* Write session data
	*
	* @param	string		session id
	* @param	string		session data
	*/
	static function _writeData($a_session_id, $a_data)
	{
		global $ilDB;

		$expires = time() + ini_get("session.gc_maxlifetime");

		// Note: We always try to update our entry in usr_session and, in
		// case no rows were changed, we insert a new row.
		// We have to do it this way, because rows in usr_session may expire
		// at any time, and can then be discared by other processes
		// concurrently.

		// First, try to update our row in table usr_session
		$r = $ilDB->update("usr_session", array(
		"user_id" => array("integer", (int) $_SESSION["AccountId"]),
		"expires" => array("integer", $expires),
		"data" => array("clob", $a_data),
		"ctime" => array("integer", time())
		), array(
		"session_id" => array("text", $a_session_id)
		));

		if ($r == 0)
		{
			// We got here, because our row in table usr_session either
			// did not exist yet, or because it was deleted in the meantime by
			// another process.

			// Insert a row in table usr_session.
			$ilDB->insert("usr_session", array(
				"session_id" => array("text", $a_session_id),
				"expires" => array("integer", $expires),
				"data" => array("clob", $a_data),
				"ctime" => array("integer", time()),
				"user_id" => array("integer", (int) $_SESSION["AccountId"])
				));

		}
		
		return true;
	}

	/**
	* Check whether session exists
	*
	* @param	string		session id
	* @return	boolean		true, if session id exists
	*/
	static function _exists($a_session_id)
	{
		global $ilDB;

		$q = "SELECT data FROM usr_session WHERE session_id = ".
			$ilDB->quote($a_session_id, "text");
		$set = $ilDB->query($q);
		if ($ilDB->fetchAssoc($set))
		{
			return true;
		}
		return false;
	}

	/**
	* Destroy session
	*
	* @param	string		session id
	*/
	static function _destroy($a_session_id)
	{
		global $ilDB;

		$q = "DELETE FROM usr_session WHERE session_id = ".
			$ilDB->quote($a_session_id, "text");
		$ilDB->manipulate($q);

		return true;
	}

	/**
	* Destroy session
	*
	* @param	string		session id
	*/
	static function _destroyByUserId($a_user_id)
	{
		global $ilDB;

		$q = "DELETE FROM usr_session WHERE user_id = ".
			$ilDB->quote($a_user_id, "integer");
		$ilDB->manipulate($q);
		
		return true;
	}

	/**
	* Destroy expired sessions
	*/
	static function _destroyExpiredSessions()
	{
		global $ilDB;

		$q = "DELETE FROM usr_session WHERE expires < ".
			$ilDB->quote(time(), "integer");
		$ilDB->manipulate($q);

		return true;
	}
	
	/**
	* Duplicate session
	*
	* @param	string		session id
	* @return	string		new session id
	*/
	static function _duplicate($a_session_id)
	{
		global $ilDB;
	
		// Create new session id
		$new_session = $a_session_id;
		do
		{
			$new_session = md5($new_session);
			$q ="SELECT * FROM usr_session WHERE ".
				"session_id = ".$ilDB->quote($new_session, "text");
			$res = $ilDB->query($q);
		} while($ilDB->fetchAssoc($res));
		
		$query = "SELECT * FROM usr_session ".
			"WHERE session_id = ".$ilDB->quote($a_session_id, "text");
		$res = $ilDB->query($query);

		while ($row = $ilDB->fetchObject($res))
		{
			ilSession::_writeData($new_session,$row->data);
			return $new_session;
		}
		return false;
	}

}
?>