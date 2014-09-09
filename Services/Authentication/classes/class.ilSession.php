<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('Services/Authentication/classes/class.ilSessionControl.php');
require_once('Services/Authentication/classes/class.ilSessionStatistics.php');
require_once('Services/Authentication/classes/class.ilSessionIStorage.php');

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
	const SESSION_HANDLING_LOAD_DEPENDENT = 1;
	
	/**
	 * Constant for reason of session destroy
	 * 
	 * @var integer
	 */
	const SESSION_CLOSE_USER   = 1;  // manual logout
	const SESSION_CLOSE_EXPIRE = 2;  // has expired
	const SESSION_CLOSE_FIRST  = 3;  // kicked by session control (first abidencer)
	const SESSION_CLOSE_IDLE   = 4;  // kickey by session control (ilde time)
	const SESSION_CLOSE_LIMIT  = 5;  // kicked by session control (limit reached)
	const SESSION_CLOSE_LOGIN  = 6;  // anonymous => login
	const SESSION_CLOSE_PUBLIC = 7;  // => anonymous
	const SESSION_CLOSE_TIME   = 8;  // account time limit reached
	const SESSION_CLOSE_IP     = 9;  // wrong ip
	const SESSION_CLOSE_SIMUL  = 10; // simultaneous login
	const SESSION_CLOSE_INACTIVE = 11; // inactive account
	const SESSION_CLOSE_CAPTCHA  = 12; // invalid captcha
	
	private static $closing_context = null;	

	/**
	* Get session data from table
	*
	* @param	string		session id
	* @return	string		session data
	*/
	static function _getData($a_session_id)
	{
		if(!$a_session_id) {
			return NULL;
		}
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
		global $ilDB, $ilClientIniFile;
		
		if ($GLOBALS['WEB_ACCESS_WITHOUT_SESSION'])
		{
			// Prevent session data written for web access checker
			// when no cookie was sent (e.g. for pdf files linking others).
			// This would result in new session records for each request.
			return false;
		}

		$now = time();

		// prepare session data
		$fields = array(
			"user_id" => array("integer", (int) $_SESSION["AccountId"]),
			"expires" => array("integer", self::getExpireValue()),
			"data" => array("clob", $a_data),
			"ctime" => array("integer", $now),
			"type" => array("integer", (int) $_SESSION["SessionType"])
			);
		if ($ilClientIniFile->readVariable("session","save_ip"))
		{
			$fields["remote_addr"] = array("text", $_SERVER["REMOTE_ADDR"]);
		}								

		if (ilSession::_exists($a_session_id))
		{
			$ilDB->update("usr_session", $fields, 
				array("session_id" => array("text", $a_session_id)));			
		}
		else
		{
			$fields["session_id"] = array("text", $a_session_id);
			$fields["createtime"] = array("integer", $now);
			
			$ilDB->insert("usr_session", $fields);
		
			// check type against session control
			$type = $fields["type"][1];
			if(in_array($type, ilSessionControl::$session_types_controlled))
			{		
				ilSessionStatistics::createRawEntry($fields["session_id"][1], 
					$type, $fields["createtime"][1], $fields["user_id"][1]);			
			}							
		}
		
		// finally delete deprecated sessions
		if(rand(0, 50) == 2)
		{
			// get time _before_ destroying expired sessions		
			self::_destroyExpiredSessions();	
		    ilSessionStatistics::aggretateRaw($now);
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
		if (! $a_session_id) {
			return false;
		}
		global $ilDB;

		$q = "SELECT 1 FROM usr_session WHERE session_id = " . $ilDB->quote($a_session_id, "text");
		$set = $ilDB->query($q);

		return $ilDB->numRows($set) > 0;
	}

	/**
	* Destroy session
	*
	* @param	string|array		session id|s
	* @param	int					closing context
	* @param	int|bool			expired at timestamp
	*/
	static function _destroy($a_session_id, $a_closing_context = null, $a_expired_at = null)
	{		
		global $ilDB;
		
		if(!$a_closing_context)
		{
			$a_closing_context = self::$closing_context;
		}
			
		ilSessionStatistics::closeRawEntry($a_session_id, $a_closing_context, $a_expired_at);	
		
		
		if(!is_array($a_session_id))
		{
			$q = "DELETE FROM usr_session WHERE session_id = ".
				$ilDB->quote($a_session_id, "text");			
		}
		else
		{
			// array: id => timestamp - so we get rid of timestamps
			if($a_expired_at)
			{
				$a_session_id = array_keys($a_session_id);
			}		
			$q = "DELETE FROM usr_session WHERE ".
				$ilDB->in("session_id", $a_session_id, "", "text");
		}

		ilSessionIStorage::destroySession($a_session_id);

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
				
		$q = "SELECT session_id,expires FROM usr_session WHERE expires < ".
			$ilDB->quote(time(), "integer");
		$res = $ilDB->query($q);
		$ids = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$ids[$row["session_id"]] = $row["expires"];
		}		
		if(sizeof($ids))
		{
			self::_destroy($ids, self::SESSION_CLOSE_EXPIRE, true);
		}	
		
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
		else if( $ilSetting->get('session_handling_type', self::SESSION_HANDLING_FIXED) == self::SESSION_HANDLING_LOAD_DEPENDENT )
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
		else if( $ilSetting->get('session_handling_type', self::SESSION_HANDLING_FIXED) ==  self::SESSION_HANDLING_LOAD_DEPENDENT )
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
	
	/**
	 * Set a value
	 *
	 * @param
	 * @return
	 */
	static function set($a_var, $a_val)
	{
		$_SESSION[$a_var] = $a_val;
	}
	
	/**
	 * Get a value
	 *
	 * @param
	 * @return
	 */
	static function get($a_var)
	{
		return $_SESSION[$a_var];
	}
	
	/**
	 * Unset a value
	 *
	 * @param
	 * @return
	 */
	static function clear($a_var)
	{
		unset($_SESSION[$a_var]);
	}
	
	/**
	 * set closing context (for statistics)
	 *
	 * @param int $a_context 
	 */
	public static function setClosingContext($a_context)
	{
		self::$closing_context = (int)$a_context;
	}
	
	/**
	 * get closing context (for statistics)
	 *
	 * @return int 
	 */
	public static function getClosingContext()
	{
		return self::$closing_context;
	}
}

?>