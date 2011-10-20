<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('Services/Authentication/classes/class.ilSessionControl.php');

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
	 * 
	 * Constant for fixed dession handling
	 * 
	 * @var integer
	 * 
	 */
	const SESSION_HANDLING_FIXED = 0;
	
	/**
	 * 
	 * Constant for load dependend session handling
	 * 
	 * @var integer
	 * 
	 */
	const SESSION_HANDLING_LOAD_DEPENDED = 1;

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
		global $ilDB, $ilSetting, $ilClientIniFile;
		
		if ($GLOBALS['WEB_ACCESS_WITHOUT_SESSION'])
		{
			// Prevent session data written for web access checker
			// when no cookie was sent (e.g. for pdf files linking others).
			// This would result in new session records for each request.
			return false;
		}

		$expires = self::getExpireValue();

		if (ilSession::_exists($a_session_id))
		{
			/*$q = "UPDATE usr_session SET ".
				"expires = ".$ilDB->quote($expires, "integer").", ".
				"data = ".$ilDB->quote($a_data, "clob").
				", ctime = ".$ilDB->quote(time(), "integer").
				", user_id = ".$ilDB->quote((int) $_SESSION["AccountId"], "integer").
				" WHERE session_id = ".$ilDB->quote($a_session_id, "text");
				array("integer", "clob", "integer", "integer", "text");
			$ilDB->manipulate($q);*/

			if ($ilClientIniFile->readVariable("session","save_ip"))
			{
				$ilDB->update("usr_session", array(
					"user_id" => array("integer", (int) $_SESSION["AccountId"]),
					"expires" => array("integer", $expires),
					"data" => array("clob", $a_data),
					"ctime" => array("integer", time()),
					"type" => array("integer", (int) $_SESSION["SessionType"]),
					"remote_addr" => array("text", $_SERVER["REMOTE_ADDR"])
					), array(
					"session_id" => array("text", $a_session_id)
					));
			}
			else
			{		
				$ilDB->update("usr_session", array(
					"user_id" => array("integer", (int) $_SESSION["AccountId"]),
					"expires" => array("integer", $expires),
					"data" => array("clob", $a_data),
					"ctime" => array("integer", time()),
					"type" => array("integer", (int) $_SESSION["SessionType"])
					), array(
					"session_id" => array("text", $a_session_id)
					));
			}

		}
		else
		{
			/*$q = "INSERT INTO usr_session (session_id, expires, data, ctime,user_id) ".
					"VALUES(".$ilDB->quote($a_session_id, "text").",".
					$ilDB->quote($expires, "integer").",".
					$ilDB->quote($a_data, "clob").",".
					$ilDB->quote(time(), "integer").",".
					$ilDB->quote((int) $_SESSION["AccountId"], "integer").")";
			$ilDB->manipulate($q);*/

			if ($ilClientIniFile->readVariable("session","save_ip"))
			{
				$ilDB->insert("usr_session", array(
					"session_id" => array("text", $a_session_id),
					"expires" => array("integer", $expires),
					"data" => array("clob", $a_data),
					"ctime" => array("integer", time()),
					"user_id" => array("integer", (int) $_SESSION["AccountId"]),
					"type" => array("integer", (int) $_SESSION["SessionType"]),
					"createtime" => array("integer", time()),
					"remote_addr" => array("text", $_SERVER["REMOTE_ADDR"])
					));
			}
			else
			{
				$ilDB->insert("usr_session", array(
					"session_id" => array("text", $a_session_id),
					"expires" => array("integer", $expires),
					"data" => array("clob", $a_data),
					"ctime" => array("integer", time()),
					"user_id" => array("integer", (int) $_SESSION["AccountId"]),
					"type" => array("integer", (int) $_SESSION["SessionType"]),
					"createtime" => array("integer", time())
					));
			}

		}
		
		// finally delete deprecated sessions
		if(rand(0, 50) == 2)
		{
			ilSession::_destroyExpiredSessions();
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

		$q = "SELECT session_id FROM usr_session WHERE session_id = ".
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
	
	/**
	 * 
	 * Returns the expiration timestamp in seconds
	 * 
	 * @param	boolean	If passed, the value for fixed session is returned
	 * @return	integer	The expiration timestamp in seconds 
	 * @access	public
	 * @static
	 * 
	 */
	public static function getExpireValue($fixedMode = false)
	{
		global $ilSetting;
		
		if( $fixedMode || $ilSetting->get('session_handling_type', self::SESSION_HANDLING_FIXED) == self::SESSION_HANDLING_FIXED )
		{
			// fixed session
			return time() + ini_get('session.gc_maxlifetime');
		}
		else if( $ilSetting->get('session_handling_type', self::SESSION_HANDLING_FIXED) == self::SESSION_HANDLING_LOAD_DEPENDED )
		{
			// load dependent session settings
			return time() + (int) ($ilSetting->get('session_max_idle', ilSessionControl::DEFAULT_MAX_IDLE) * 60);
		}
	}

	/**
	 * 
	 * Returns the idle time in seconds
	 * 
	 * @param	boolean	If passed, the value for fixed session is returned
	 * @return	integer	The idle time in seconds 
	 * @access	public
	 * @static
	 * 
	 */
	public static function getIdleValue($fixedMode = false)
	{
		global $ilSetting, $ilClientIniFile;
		
		if( $fixedMode || $ilSetting->get('session_handling_type', self::SESSION_HANDLING_FIXED) ==  self::SESSION_HANDLING_FIXED )
		{
			// fixed session
			return $ilClientIniFile->readVariable('session','expire');
		}
		else if( $ilSetting->get('session_handling_type', self::SESSION_HANDLING_FIXED) ==  self::SESSION_HANDLING_LOAD_DEPENDED )
		{
			// load dependent session settings
			return (int) ($ilSetting->get('session_max_idle', ilSessionControl::DEFAULT_MAX_IDLE) * 60);
		}
	}
	
	/**
	 * 
	 * Returns the session expiration value
	 * 
	 * @return integer	The expiration value in seconds
	 * @access	public
	 * @static
	 * 
	 */
	public static function getSessionExpireValue()
	{
		return self::getIdleValue(true);
	}
	
	/**
	 * Get the active users with a specific remote ip address
	 * 
	 * @param	string	ip address
	 * @return 	array	list of active user id
	 */
	static function _getUsersWithIp($a_ip)
	{
		global $ilDB;
		
		$query = "SELECT DISTINCT user_id FROM usr_session"
				. " WHERE remote_addr = " . $ilDB->quote($a_ip, "text")
				. " AND user_id > 0";		
		$result = $ilDB->query($query);
		
		$users = array();
		while ($row = $ilDB->fetchObject($result))
		{
			$users[] = $row->user_id;
		}
		return $users;
	}
}
?>