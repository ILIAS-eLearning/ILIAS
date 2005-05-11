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


define('AUTH_IDLED',       -1);
define('AUTH_EXPIRED',     -2);
define('AUTH_WRONG_LOGIN', -3);

/**
* Class Shibboleth
*
* This class provides basic functionality for Shibboleth authentication
* It basically implements the functions of the class PEAR::AUTH which are
* used in other Ilias authentication methods
*
*/

class ShibAuth
{
	/**
	 * Username
	 *
	 * @var string
	 */
	var $username;
	
	/**
	 * Name to be used for session
	 *
	 * @var string
	 */
	var $_sessionName = '_authsession';
	
	/**
	 * Authentication status
	 *
	 * @var string
	 */
	var $status = '';
	
	/**
	 * Auth lifetime in seconds
	 *
	 * If this variable is set to 0, auth never expires
	 *
	 * @var  integer
	 * @see  setExpire(), checkAuth()
	 */
	var $expire = 0;
	
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
	var $idle = 0;
	
	/**
	 * Is the maximum idletime over?
	 *
	 * @var boolean
	 * @see checkAuth(), drawLogin();
	 */
	var $idled = false;
	
	/**
	* Constructor
	* @access	public
	*/
	function ShibAuth($authParams, $updateUserData = false)
	{
		$this->updateUserData = $updateUserData;
		
		if (!empty($authParams['sessionName'])) {
			$this->_sessionName = $authParams['sessionName'];
			unset($authParams['sessionName']);
		}
		
	}
	
	/**
	* Checks if the current user is authenticated yet
	* @access	public
	* @return	boolean	true if user is authenticated
	*/
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
	
	/**
	* Deletes a role and deletes entries in object_data, rbac_pa, rbac_templates, rbac_ua, rbac_fa
	* @access	public
	* @param	integer		obj_id of role (role_id)
	* @param	integer		ref_id of role folder (ref_id)
	* @return	boolean     true on success
	*/
	function setIdle($time, $add = false)
	{
		if ($add) {
			$this->idle += $time;
		} else {
			$this->idle = $time;
		}
	}
	

	/**
	* Set the maximum expire time
	*
	* @access public
	* @param  integer time in seconds
	* @param  bool    add time to current expire time or not
	* @return void
	*/
	function setExpire($time, $add = false)
	{
		if ($add) {
			$this->expire += $time;
		} else {
			$this->expire = $time;
		}
	}
	
	/**
	* Checks if there is a session with valid auth information.
	*
	* @access private
	* @return boolean  Whether or not the user is authenticated.
	*/
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
	
	/**
	* Start new auth session
	*
	* @access public
	* @return void
	*/
	function start()
	{
		@session_start();
		
		if (!$this->checkAuth()) {
			//$this->login();
		}
	}
	
	/**
	* Login function
	*
	* @access private
	* @return void
	*/
	function login()
	{
	
		global $ilias, $rbacadmin;
		
		if (!empty($_SERVER[$ilias->getSetting('shib_login')]))
		{
			$username = $this->generateLogin();
			
			// Authorize this user
			$this->setAuth($username);
			
			$userObj = new ilObjUser();
			
			// Check wether this account exists already, if not create it
			if (!loginExists($username))
			{
				
				$newUser["firstname"] = $_SERVER[$ilias->getSetting('shib_firstname')];
				$newUser["lastname"] = $_SERVER[$ilias->getSetting('shib_lastname')];
				
				$newUser["login"] = $username;
				
				// Password must be random to prevent users from manually log in using the login data from Shibboleth users
				$newUser["passwd"] = rand(); 
				
				if ( 
					$ilias->getSetting('shib_update_gender')
					&& ($_SERVER[$ilias->getSetting('shib_gender')] == 'm'
					|| $_SERVER[$ilias->getSetting('shib_gender')] =='f')
					)
					$newUser["gender"] = $_SERVER[$ilias->getSetting('shib_gender')];
				
				// other data
				
					$newUser["title"] = $_SERVER[$ilias->getSetting('shib_title')];
					$newUser["institution"] = $_SERVER[$ilias->getSetting('shib_institution')];
					$newUser["department"] = $_SERVER[$ilias->getSetting('shib_department')];
					$newUser["street"] = $_SERVER[$ilias->getSetting('shib_street')];
					$newUser["city"] = $_SERVER[$ilias->getSetting('shib_city')];
					$newUser["zipcode"] = $_SERVER[$ilias->getSetting('shib_zipcode')];
					$newUser["country"] = $_SERVER[$ilias->getSetting('shib_country')];
					$newUser["phone_office"] = $_SERVER[$ilias->getSetting('shib_phone_office')];
					$newUser["phone_home"] = $_SERVER[$ilias->getSetting('shib_phone_home')];
					$newUser["phone_mobile"] = $_SERVER[$ilias->getSetting('shib_phone_mobile')];
					$newUser["fax"] = $_SERVER[$ilias->getSetting('shib_fax')];
					$newUser["matriculation"] = $_SERVER[$ilias->getSetting('shib_matriculation')];
					$newUser["email"] = $_SERVER[$ilias->getSetting('shib_email')];
					$newUser["hobby"] = $_SERVER[$ilias->getSetting('shib_hobby')];
				
				// system data
				$userObj->assignData($newUser);
				$userObj->setTitle($userObj->getFullname());
				$userObj->setDescription($userObj->getEmail());
				$userObj->setLanguage($_SERVER[$ilias->getSetting('shib_language')]);
				
				// Time limit
				$userObj->setTimeLimitOwner(7);
				$userObj->setTimeLimitUnlimited(1);
				$userObj->setTimeLimitFrom(time());
				$userObj->setTimeLimitUntil(time());
				
				// Modify user data before creating the user
				// Include custom code that can be used to further modify
				// certain Shibboleth user attributes
				if (	$ilias->getSetting('shib_data_conv') 
						&& $ilias->getSetting('shib_data_conv') != ''
						&& file_exists($ilias->getSetting('shib_data_conv'))
						)
				{
					include($ilias->getSetting('shib_data_conv'));
				}
				
				// Create use in DB
				$userObj->create();
				$userObj->setActive(1, 6);
				
				$userObj->updateOwner();
				
				//insert user data in table user_data
				$userObj->saveAsNew();
				
				// store acceptance of user agreement
				//$userObj->writeAccepted();
				
				// setup user preferences
				$userObj->writePrefs();
				
				//set role entries
				$rbacadmin->assignUser($ilias->getSetting('shib_user_default_role'), $userObj->getId(),true);
				
				// Save mapping
				// We save this mapping directly to prevent this value getting hashed
				// That way local users cannot login using the Shibboleth unique login ID as password
				$ilias->db->query("UPDATE usr_data SET passwd='".$_SERVER[$ilias->getSetting('shib_login')]."' WHERE login='".$username."'");
				
				unset($userObj);
				
			}
			else
			{
				// Update user account
				$userObj->checkUserId();
				$userObj->read();
				
				if ( 
					$ilias->getSetting('shib_update_gender')
					&& ($_SERVER[$ilias->getSetting('shib_gender')] == 'm'
					|| $_SERVER[$ilias->getSetting('shib_gender')] =='f')
					)
					$userObj->setGender($_SERVER[$ilias->getSetting('shib_gender')]);
				
				if ($ilias->getSetting('shib_update_title'))
				$userObj->setTitle($_SERVER[$ilias->getSetting('shib_title')]);
				
				$userObj->setFirstname($_SERVER[$ilias->getSetting('shib_firstname')]);
				$userObj->setLastname($_SERVER[$ilias->getSetting('shib_lastname')]);
				$userObj->setFullname();
				if ($ilias->getSetting('shib_update_institution'))
					$userObj->setInstitution($_SERVER[$ilias->getSetting('shib_institution')]);
				if ($ilias->getSetting('shib_update_department'))
					$userObj->setDepartment($_SERVER[$ilias->getSetting('shib_department')]);
				if ($ilias->getSetting('shib_update_street'))
					$userObj->setStreet($_SERVER[$ilias->getSetting('shib_street')]);
				if ($ilias->getSetting('shib_update_city'))
					$userObj->setCity($_SERVER[$ilias->getSetting('shib_city')]);
				if ($ilias->getSetting('shib_update_zipcode'))
					$userObj->setZipcode($_SERVER[$ilias->getSetting('shib_zipcode')]);
				if ($ilias->getSetting('shib_update_country'))
					$userObj->setCountry($_SERVER[$ilias->getSetting('shib_country')]);
				if ($ilias->getSetting('shib_update_phone_office'))
					$userObj->setPhoneOffice($_SERVER[$ilias->getSetting('shib_phone_office')]);
				if ($ilias->getSetting('shib_update_phone_home'))
					$userObj->setPhoneHome($_SERVER[$ilias->getSetting('shib_phone_home')]);
				if ($ilias->getSetting('shib_update_phone_mobile'))
					$userObj->setPhoneMobile($_SERVER[$ilias->getSetting('shib_phone_mobile')]);
				if ($ilias->getSetting('shib_update_fax'))
					$userObj->setFax($_SERVER[$ilias->getSetting('shib_fax')]);
				if ($ilias->getSetting('shib_update_matriculation'))
					$userObj->setMatriculation($_SERVER[$ilias->getSetting('shib_matriculation')]);
				if ($ilias->getSetting('shib_update_email'))
					$userObj->setEmail($_SERVER[$ilias->getSetting('shib_email')]);
				if ($ilias->getSetting('shib_update_hobby'))
					$userObj->setHobby($_SERVER[$ilias->getSetting('shib_hobby')]);
				
				if ($ilias->getSetting('shib_update_language'))
					$userObj->setLanguage($_SERVER[$ilias->getSetting('shib_language')]);
				
				// Include custom code that can be used to further modify
				// certain Shibboleth user attributes
				if (	$ilias->getSetting('shib_data_conv') 
						&& $ilias->getSetting('shib_data_conv') != ''
						&& file_exists($ilias->getSetting('shib_data_conv'))
						)
				{
					include($ilias->getSetting('shib_data_conv'));
				}

				
				$userObj->update();
			
			}
		}
		else
		{
			// This should never occur unless Shibboleth is not configured properly
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
		$session = &$this->_importGlobalVariable('session');
		
		
		$this->username = '';
		
		$session[$this->_sessionName] = array();
		if (isset($_SESSION)) {
			unset($session[$this->_sessionName]);
		} else {
			session_unregister($this->_sessionName);
		}
	}
	
	/**
	* Get the username
	*
	* @access public
	* @return string
	*/
	function getUsername()
	{
		$session = &$this->_importGlobalVariable('session');
		if (!isset($session[$this->_sessionName]['username'])) {
			return '';
		}
		return $session[$this->_sessionName]['username'];
	}
	
	/**
	* Get the current status
	*
	* @access public
	* @return string
	*/
	function getStatus()
	{
		
		return $status;
	}
	
	/**
	* Import variables from special namespaces.
	*
	* @access private
	* @param string Type of variable (server, session, post)
	* @return array
	*/
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
	
	/**
	* Automatically generates the username of a Shibboleth user or returns
	* the user's already existing username
	*
	* @access private
	* @param string Type of variable. This must be the unique ID of a Shibboleth user
	* @param string The user's lastname
	* @param string The user's firstname
	* @return array
	*/
	function generateLogin()
	{
		global $ilias;
		
		$shibID = $_SERVER[$ilias->getSetting('shib_login')];
		$lastname = $_SERVER[$ilias->getSetting('shib_lastname')];
		$firstname = $_SERVER[$ilias->getSetting('shib_firstname')];
		
		$r = $ilias->db->query("SELECT login FROM usr_data WHERE passwd='".$shibID."'");
		
		//query has got a result
		if ($r->numRows() > 0)
		{
			$data = $r->fetchRow();
			return $data[0];
		}
		
		
		// Generate new username
		// Somewhen it probably will be possible to generate the username
		// according to own rules.
		
		$prefix = ucfirst($lastname).ucfirst($firstname);
		$prefix = ereg_replace(" ",'', $prefix);
		
		if (!$this->checkMapping($prefix))
		{
			return $prefix;
		}
		
		// Add a number as prefix if the username already is taken
		$number = 2;
		while ($this->checkMapping($prefix.$number))
		{
			$number++;
		}
		
		return $prefix.$number;
	}
	
	/**
	* Checks whether a specific username is already used  by a user
	*
	* @access private
	* @param bool True if a username is already taken
	* @return array
	*/
	function checkMapping($login)
	{
		global $ilias;
		
		// Check if username already exists
		$r = $ilias->db->query("SELECT passwd FROM usr_data WHERE login='".$login."'");
		
		//query has got a result
		if ($r->numRows() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	
} // END class.ilShibAuth
?>
