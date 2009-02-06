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
		
		$st = $ilDB->prepare("SELECT data FROM usr_session WHERE session_id = ?",
			array("text"));
		$set = $ilDB->execute($st, array($a_session_id));
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
		if (ilSession::_exists($a_session_id))
		{
			$st = $ilDB->prepareManip("UPDATE usr_session SET expires = ?, ".
				"data = ?, ctime = ?, user_id = ? WHERE session_id = ?",
				 array("integer", "clob", "integer", "integer", "text"));
			$ilDB->execute($st, array($expires, $a_data, time(), (int) $_SESSION["AccountId"], $a_session_id));
		}
		else
		{
			$st = $ilDB->prepareManip("INSERT INTO usr_session (session_id, expires, data, ctime,user_id) ".
				 "VALUES (?,?,?,?,?)", array("text", "integer", "clob", "integer", "integer"));
			$ilDB->execute($st, array($a_session_id, $expires, $a_data, time(), (int) $_SESSION["AccountId"]));
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

		$st = $ilDB->prepare("SELECT data FROM usr_session WHERE session_id = ?",
			array("text"));
		$set = $ilDB->execute($st, array($a_session_id));
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

		$st = $ilDB->prepareManip("DELETE FROM usr_session WHERE session_id = ?",
			 array("text"));
		$ilDB->execute($st, array($a_session_id));
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

		$st = $ilDB->prepareManip("DELETE FROM usr_session WHERE user_id = ?",
			 array("integer"));
		$ilDB->execute($st, array($a_user_id));
		return true;
	}

	/**
	* Destroy expired sessions
	*/
	static function _destroyExpiredSessions()
	{
		global $ilDB;

		$st = $ilDB->prepareManip("DELETE FROM usr_session WHERE expires < ?",
			 array("integer"));
		$ilDB->execute($st, array(time()));
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
			$st = $ilDB->prepare("SELECT * FROM usr_session WHERE ".
				"session_id = ?", array("text"));
			$res = $ilDB->execute($st, array($new_session));		
		} while($ilDB->fetchAssoc($res));
		
		$st = $ilDB->prepare("SELECT * FROM usr_session WHERE ".
			"session_id = ?", array("text"));
		$res = $ilDB->execute($st, array($a_session_id));		
		while ($row = $ilDB->fetchObject($res))
		{
			ilSession::_writeData($new_session,$row->data);
			return $new_session;
		}
		return false;
	}

}
?>