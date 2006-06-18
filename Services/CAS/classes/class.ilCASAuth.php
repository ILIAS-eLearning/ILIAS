<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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


//define('AUTH_IDLED',       -1);
//define('AUTH_EXPIRED',     -2);
//define('AUTH_WRONG_LOGIN', -3);

include_once("Auth.php");

/**
* Class CASAuth
*
* CAS Authentication class.
*
*/
class ilCASAuth extends Auth
{
	/**
	 * Username
	 *
	 * @var string
	 */
//	var $username;
	
	/**
	 * Name to be used for session
	 *
	 * @var string
	 */
//	var $_sessionName = '_authsession';
	
	/**
	 * Authentication status
	 *
	 * @var string
	 */
//	var $status = '';
	
	/**
	 * Auth lifetime in seconds
	 *
	 * If this variable is set to 0, auth never expires
	 *
	 * @var  integer
	 * @see  setExpire(), checkAuth()
	 */
//	var $expire = 0;
	
	/**
	 * Maximum time of idleness in seconds
	 *
	 * The difference to $expire is, that the idletime gets
	 * refreshed each time, checkAuth() is called. If this
	 * variable is set to 0, idle time is never checked.
	 *
	 * @var integer
	 * @see setIdle(), checkAuth()
	 */
//	var $idle = 0;
	
	/**
	 * Is the maximum idletime over?
	 *
	 * @var boolean
	 * @see checkAuth(), drawLogin();
	 */
//	var $idled = false;
	
	/**
	* Constructor
	* @access	public
	*/
	function ilCASAuth($a_params)
	{
		include_once("Services/CAS/phpcas/source/CAS/CAS.php");
		$this->server_version = CAS_VERSION_2_0;
		$this->server_hostname = $a_params["server_hostname"];
		$this->server_port = (int) $a_params["server_port"];
		$this->server_uri = $a_params["server_uri"];

		phpCAS::setDebug();
		phpCAS::client($this->server_version, $this->server_hostname,
			$this->server_port, $this->server_uri);
	}
	
	/**
	* check cas autehntication
	*
	* can be called before forceAuthentication,
	* but forceAuthentication must be called afterwards
	*/
	function checkCASAuth()
	{
		global $PHPCAS_CLIENT;

		return $PHPCAS_CLIENT->isAuthenticated();
	}
	
	function forceCASAuth()
	{
		phpCAS::forceAuthentication();
	}
	
	/**
	* Checks if the current user is authenticated yet
	* @access	public
	* @return	boolean	true if user is authenticated
	*/
/*
	function getAuth()
	{
		$session = &$this->_importGlobalVariable('session');

		if (!empty($session) &&
		(isset($session[$this->_sessionName]['registered']) &&
		$session[$this->_sessionName]['registered'] === true))
		{
			return true;
		} else {
			return false;
		}
	}
*/

    /**
     * Set the maximum idle time
     *
     * @param  integer time in seconds
     * @param  bool    add time to current maximum idle time or not
     * @return void
     * @access public
     */
/*
    function setIdle($time, $add = false)
    {
        $add ? $this->idle += $time : $this->idle = $time;
    }
*/

    /**
     * Set the maximum expire time
     *
     * @param  integer time in seconds
     * @param  bool    add time to current expire time or not
     * @return void
     * @access public
     */
/*
    function setExpire($time, $add = false)
    {
        $add ? $this->expire += $time : $this->expire = $time;
    }
*/

	/**
	* Checks if there is a session with valid auth information.
	*
	* @access private
	* @return boolean  Whether or not the user is authenticated.
	*/
/*
	function checkAuth()
	{
		$session = &$this->_importGlobalVariable('session');

        if (isset($session[$this->_sessionName])) {
            // Check if authentication session is expired
            if ($this->expire > 0 &&
                isset($session[$this->_sessionName]['timestamp']) &&
                ($session[$this->_sessionName]['timestamp'] + $this->expire) < time()) {

                $this->logout();
                $this->expired = true;
                $this->status = AUTH_EXPIRED;

                return false;
            }

            // Check if maximum idle time is reached
            if ($this->idle > 0 &&
                isset($session[$this->_sessionName]['idle']) &&
                ($session[$this->_sessionName]['idle'] + $this->idle) < time()) {

                $this->logout();
                $this->idled = true;
                $this->status = AUTH_IDLED;

                return false;
            }

            if (isset($session[$this->_sessionName]['registered']) &&
                isset($session[$this->_sessionName]['username']) &&
                $session[$this->_sessionName]['registered'] == true &&
                $session[$this->_sessionName]['username'] != '') {

                Auth::updateIdle();

                return true;
            }
        }

        return false;
	}
*/
	
	/**
	* Start new auth session
	*
	* @access public
	* @return void
	*/
/*
	function start()
	{
		@session_start();
		
		if (!$this->checkAuth()) {
			$this->login();
		}
	}
*/
	
	/**
	* Login function
	*
	* @access private
	* @return void
	*/
	function login()
	{
		global $ilias, $rbacadmin, $lng, $ilSetting;

		if (phpCAS::getUser() != "")
		{

			$username = phpCAS::getUser();

			// Authorize this user
			$local_user = ilObjUser::_checkExternalAuthAccount("cas", $username);

			if ($local_user != "")
			{
				$this->setAuth($local_user);
			}
			else
			{
				$userObj = new ilObjUser();
				
				$local_user = ilAuthUtils::_generateLogin($username);
				
				$newUser["firstname"] = $local_user;
				$newUser["lastname"] = "";
				
				$newUser["login"] = $local_user;
				
				// set "plain md5" password (= no valid password)
				$newUser["passwd"] = ""; 
				$newUser["passwd_type"] = IL_PASSWD_MD5; 
				
				//$newUser["gender"] = "m";
				$newUser["auth_mode"] = "cas";
				$newUser["ext_account"] = $username;
				$newUser["profile_incomplete"] = 1;
				
				// system data
				$userObj->assignData($newUser);
				$userObj->setTitle($userObj->getFullname());
				$userObj->setDescription($userObj->getEmail());
			
				// set user language to system language
				$userObj->setLanguage($lng->lang_default);
				
				// Time limit
				$userObj->setTimeLimitOwner(7);
				$userObj->setTimeLimitUnlimited(1);
				$userObj->setTimeLimitFrom(time());
				$userObj->setTimeLimitUntil(time());
								
				// Create user in DB
				$userObj->setOwner(6);
				$userObj->create();
				$userObj->setActive(1, 6);
				
				$userObj->updateOwner();
				
				//insert user data in table user_data
				$userObj->saveAsNew();
				
				// setup user preferences
				$userObj->writePrefs();
				
				// to do: test this
				$rbacadmin->assignUser($ilSetting->get('cas_user_default_role'), $userObj->getId(),true);
				
				unset($userObj);
				
				$this->setAuth($local_user);

			}
		}
		else
		{
			// This should never occur unless CAS is not configured properly
			$this->status = AUTH_WRONG_LOGIN;
		}
	}
	
	/**
	* Register variable in a session telling that the user
	* has logged in successfully
	*
	* @access public
	* @param  string Username
	* @return void
	*/
/*
	function setAuth($username)
	{
		$session = &Auth::_importGlobalVariable('session');
		
		if (!isset($session[$this->_sessionName]) && !isset($_SESSION)) {
			session_register($this->_sessionName);
		}
		
		if (!isset($session[$this->_sessionName]) || !is_array($session[$this->_sessionName])) {
			$session[$this->_sessionName] = array();
		}
		
		if(!isset($session[$this->_sessionName]['data'])){
			$session[$this->_sessionName]['data']       = array();
		}
			$session[$this->_sessionName]['registered'] = true;
			$session[$this->_sessionName]['username']   = $username;
			$session[$this->_sessionName]['timestamp']  = time();
			$session[$this->_sessionName]['idle']       = time();
	}
*/
	
	/**
	* Logout function
	*
	* This function clears any auth tokens in the currently
	* active session and executes the logout callback function,
	* if any
	*
	* @access public
	* @return void
	*/

	function logout()
	{
		parent::logout();
		//PHPCAS::logout();
	}
	
	/**
	* Get the username
	*
	* @access public
	* @return string
	*/
/*
	function getUsername()
	{
		$session = &$this->_importGlobalVariable('session');
		if (!isset($session[$this->_sessionName]['username'])) {
			return '';
		}
		return $session[$this->_sessionName]['username'];
	}
*/
	
	/**
	* Get the current status
	*
	* @access public
	* @return string
	*/
/*
	function getStatus()
	{
		
		return $status;
	}
*/
	
	/**
	* Import variables from special namespaces.
	*
	* @access private
	* @param string Type of variable (server, session, post)
	* @return array
	*/
/*
	function &_importGlobalVariable($variable)
	{
		$var = null;
		
		switch (strtolower($variable)) {
		
			case 'server' :
				if (isset($_SERVER)) {
					$var = &$_SERVER;
				} else {
					$var = &$GLOBALS['HTTP_SERVER_VARS'];
				}
				break;
			
			case 'session' :
				if (isset($_SESSION)) {
					$var = &$_SESSION;
				} else {
					$var = &$GLOBALS['HTTP_SESSION_VARS'];
				}
				break;
			
			case 'post' :
				if (isset($_POST)) {
					$var = &$_POST;
				} else {
					$var = &$GLOBALS['HTTP_POST_VARS'];
				}
				break;
			
			case 'cookie' :
				if (isset($_COOKIE)) {
					$var = &$_COOKIE;
				} else {
					$var = &$GLOBALS['HTTP_COOKIE_VARS'];
				}
				break;
			
			case 'get' :
				if (isset($_GET)) {
					$var = &$_GET;
				} else {
					$var = &$GLOBALS['HTTP_GET_VARS'];
				}
				break;
			
			default:
				break;
		
		}

		return $var;
	}
*/	
} // END class.ilCASAuth
?>
