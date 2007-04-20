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


include_once("Auth/Auth.php");
include_once("./webservice/soap/lib/nusoap.php");

/**
* Class SOAPAuth
*
* SOAP Authentication class.
*
*/
class ilSOAPAuth extends Auth
{
	var		$valid 	= array();
	
	/**
	* Constructor
	* @access	public
	*/
	function ilSOAPAuth($a_params)
	{
		parent::Auth("");
		
		$this->server_hostname = $a_params["server_hostname"];
		$this->server_port = (int) $a_params["server_port"];
		$this->server_uri = $a_params["server_uri"];
		$this->namespace = $a_params["namespace"];
		$this->use_dotnet = $a_params["use_dotnet"];
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
//echo "<br>== Get SOAP client ==";
//echo "<br>SOAP client with URI: ".$this->uri."<br>";
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
	function validateSOAPUser($a_ext_uid, $a_soap_pw)
	{
		// check whether external user exists in ILIAS database
		$local_user = ilObjUser::_checkExternalAuthAccount("soap", $a_ext_uid);
		
		if ($local_user == "")
		{
			$new_user = true;
		}
		else
		{
			$new_user = false;
		}
		
		$soapAction = "";
		$nspref = "";
		if ($this->use_dotnet)
		{
			$soapAction = $this->namespace."/isValidSession";
			$nspref = "ns1:";
		}
		
		$valid = $this->soap_client->call('isValidSession',
			array($nspref.'ext_uid' => $a_ext_uid,
				$nspref.'soap_pw' => $a_soap_pw,
				$nspref.'new_user' => $new_user),
			$this->namespace,
			$soapAction);

//echo "<br>== Request ==";
//echo '<br><pre>' . htmlspecialchars($this->soap_client->request, ENT_QUOTES) . '</pre><br>';
//echo "<br>== Response ==";
//echo "<br>Valid: -".$valid["valid"]."-";
//echo '<br><pre>' . htmlspecialchars($this->soap_client->response, ENT_QUOTES) . '</pre>';

		// to do check SOAP error!?
		$valid["local_user"] = $local_user;
		
		$this->valid = $valid;
		
		return $valid;
	}
	
	/**
	* Get validation data.
	*/
	function getValidationData()
	{
		return $this->valid;
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

		if (empty($_GET["ext_uid"]) || empty($_GET["soap_pw"]))
		{
			$this->status = AUTH_WRONG_LOGIN;
			return;
		}

		$validation_data = $this->validateSoapUser($_GET["ext_uid"], $_GET["soap_pw"]);
		
		if (!$validation_data["valid"])
		{
			$this->status = AUTH_WRONG_LOGIN;
			return;
		}
		
		$local_user = $validation_data["local_user"];
		
		if ($local_user != "")
		{
			// to do: handle update of user
			$this->setAuth($local_user);
		}
		else
		{
			if (!$ilSetting->get("soap_auth_create_users"))
			{
				$this->status = AUTH_SOAP_NO_ILIAS_USER;
				$this->logout();
				return;
			}
//echo "1";
			// try to map external user via e-mail to ILIAS user
			if ($validation_data["email"] != "")
			{
//echo "2";
//var_dump ($_POST);
				$email_user = ilObjUser::_getLocalAccountsForEmail($validation_data["email"]);

				// check, if password has been provided in user mapping screen
				// (see ilStartUpGUI::showUserMappingSelection)
				if ($_POST["LoginMappedUser"] != "")
				{ 
					if (count($email_user) > 0)
					{
						if (ilObjUser::_checkPassword($_POST["usr_id"], $_POST["password"]))
						{
							// password is correct -> map user
							//$this->setAuth($local_user); (use login not id)
							ilObjUser::_writeExternalAccount($_POST["usr_id"], $_GET["ext_uid"]);
							ilObjUser::_writeAuthMode($_POST["usr_id"], "soap");
							$_GET["cmd"] = $_POST["cmd"] = $_GET["auth_stat"]= "";
							$local_user = ilObjUser::_lookupLogin($_POST["usr_id"]);
							$this->status = "";
							$this->setAuth($local_user);
							return;
						}
						else
						{
//echo "6"; exit;
							$this->status = AUTH_SOAP_NO_ILIAS_USER_BUT_EMAIL;
							$this->sub_status = AUTH_WRONG_LOGIN;
							$this->logout();
							return;
						}
					}
				}
				
				if (count($email_user) > 0 && $_POST["CreateUser"] == "")
				{					
					$_GET["email"] = $validation_data["email"]; 
					$this->status = AUTH_SOAP_NO_ILIAS_USER_BUT_EMAIL;
					$this->logout();
					return;
				}
			}

			$userObj = new ilObjUser();
			
			$local_user = ilAuthUtils::_generateLogin($_GET["ext_uid"]);
			
			$newUser["firstname"] = $validation_data["firstname"];
			$newUser["lastname"] = $validation_data["lastname"];
			$newUser["email"] = $validation_data["email"];
			
			$newUser["login"] = $local_user;
			
			// to do: set valid password and send mail
			$newUser["passwd"] = ""; 
			$newUser["passwd_type"] = IL_PASSWD_MD5;
			
			// generate password, if local authentication is allowed
			// and account mail is activated
			$pw = "";

			if ($ilSetting->get("soap_auth_allow_local") &&
				$ilSetting->get("soap_auth_account_mail"))
			{
				$pw = ilUtil::generatePasswords(1);
				$pw = $pw[0];
				$newUser["passwd"] = md5($pw); 
				$newUser["passwd_type"] = IL_PASSWD_MD5;
			}

			//$newUser["gender"] = "m";
			$newUser["auth_mode"] = "soap";
			$newUser["ext_account"] = $_GET["ext_uid"];
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
			$rbacadmin->assignUser($ilSetting->get('soap_auth_user_default_role'), $userObj->getId(),true);

			// send account mail
			if ($ilSetting->get("soap_auth_account_mail"))
			{
				include_once("classes/class.ilObjUserFolder.php");
				$amail = ilObjUserFolder::_lookupNewAccountMail($ilSetting->get("language"));
				if (trim($amail["body"]) != "" && trim($amail["subject"]) != "")
				{
					include_once("Services/Mail/classes/class.ilAccountMail.php");
					$acc_mail = new ilAccountMail();

					if ($pw != "")
					{
						$acc_mail->setUserPassword($pw);
					}
					$acc_mail->setUser($userObj);
					$acc_mail->send();
				}
			}

			unset($userObj);

			$this->setAuth($local_user);

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
