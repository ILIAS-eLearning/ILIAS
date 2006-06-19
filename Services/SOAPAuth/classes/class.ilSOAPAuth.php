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


define('AUTH_SOAP_NO_ILIAS_USER', -100);

include_once("Auth.php");
include_once("./webservice/soap/lib/nusoap.php");

/**
* Class SOAPAuth
*
* SOAP Authentication class.
*
*/
class ilSOAPAuth extends Auth
{
	
	/**
	* Constructor
	* @access	public
	*/
	function ilSOAPAuth($a_params)
	{
		$this->server_hostname = $a_params["server_hostname"];
		$this->server_port = (int) $a_params["server_port"];
		$this->server_uri = $a_params["server_uri"];
		if ($a_params["https"])
		{
			$this->https = true;
			$uri = "https://";
		}
		else
		{
			$this->https = false;
			$uri = "http://";
		}
		
		$uri.= $this->server_hostname;
		
		if ($this->server_port > 0)
		{
			$uri.= ":".$this->server_port;
		}

		if ($this->server_uri != "")
		{
			$uri.= "/".$this->server_uri;
		}
		
		$this->uri = $uri;

		$this->soap_client = new soap_client($this->uri);
		
		if ($err = $this->soap_client->getError()) 
		{
			die("SOAP Authentication Initialisation Error: ".$err);
		}
	}
	
	/**
	* soap validation lookup: call isValidSession service
	* of soap server
	*
	*/
	function validateSOAPUser($a_ext_account, $a_soap_pw)
	{
		// todo: check if user exists
		$valid = $this->soap_client->call('isValidSession', array('ext_uid' => $a_ext_account,
			'soap_pw' => $a_soap_pw,
			'new_user' => false));

	}
	
	/**
	* Login function
	*
	* @access private
	* @return void
	*/
	function login()
	{
		global $ilias, $rbacadmin, $lng, $ilSetting;

echo "ilSOAPAuth::login"; exit;
		
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
				if (!$ilSetting->get("cas_create_users"))
				{
					$this->status = AUTH_CAS_NO_ILIAS_USER;
					$this->logout();
					return;
				}
				
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
