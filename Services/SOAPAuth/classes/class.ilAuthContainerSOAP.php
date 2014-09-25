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

include_once 'Auth/Container/LDAP.php';
include_once("./webservice/soap/lib/nusoap.php");

/**
 * @classDescription Authentication against external SOAP server
 * @todo This class should inherit either from Auth_Container_SOAP or Auth_Container_SOAP5 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $id$
 * 
 * @ingroup ServicesSOAPAuth
 */
class ilAuthContainerSOAP extends Auth_Container
{
	protected $server_host	= null;
	protected $server_port	= null;
	protected $server_uri	= null;
	protected $server_https	= null;
	protected $server_nms	= null;
	protected $use_dot_net	= null;
	
	protected $uri = null;
	
	protected $client = null;
	protected $response = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$_POST['username'] = $_GET['ext_uid'];
		$_POST['password'] = $_GET['soap_pw'];
		
		parent::__construct();
		$this->initClient();
	}
	
	/**
	 * Init soap client
	 * @return 
	 */
	public function initClient()
	{
		global $ilSetting;
		
		$this->server_host	= $ilSetting->get('soap_auth_server');
		$this->server_port	= $ilSetting->get('soap_auth_port');
		$this->server_uri	= $ilSetting->get('soap_auth_uri');
		$this->server_https	= $ilSetting->get('soap_auth_use_https');
		$this->server_nms	= $ilSetting->get('soap_auth_namespace');
		$this->use_dot_net	= $ilSetting->get('use_dotnet');
		
		$this->uri  = $this->server_https ? 'https://' : 'http://';
		$this->uri .= $this->server_host;
		
		if($this->server_port > 0)
		{
			$this->uri .= (':'.$this->server_port);
		}
		if($this->server_uri)
		{
			$this->uri .= ('/'.$this->server_uri);
		}
		$this->client = new nusoap_client($this->uri);
	}
	
	/**
	 *  Call is isValidSession of soap server
	 * @return bool 
	 * @param string $a_username
	 * @param string $a_password
	 * @param bool $isChallengeResponse[optional]
	 */
	public function fetchData($a_username,$a_password,$isChallengeResponse = false)
	{
		$GLOBALS['ilLog']->write(__METHOD__.': Soap auth fetch data');

		// check whether external user exists in ILIAS database
		$local_user = ilObjUser::_checkExternalAuthAccount("soap", $a_username);
		
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
			$soapAction = $this->server_nms."/isValidSession";
			$nspref = "ns1:";
		}
		$valid = $this->client->call('isValidSession',
			array($nspref.'ext_uid' => $a_username,
				$nspref.'soap_pw' => $a_password,
				$nspref.'new_user' => $new_user),
			$this->server_nms,
			$soapAction);
//echo "<br>== Request ==";
//echo '<br><pre>' . htmlspecialchars($this->soap_client->request, ENT_QUOTES) . '</pre><br>';
//echo "<br>== Response ==";
//echo "<br>Valid: -".$valid["valid"]."-";
//echo '<br><pre>' . htmlspecialchars($this->soap_client->response, ENT_QUOTES) . '</pre>';
		
		if (trim($valid["valid"]) == "false")
		{
			$valid["valid"] = false;
		}

		// to do check SOAP error!?
		$valid["local_user"] = $local_user;
		$this->response = $valid;
		return $valid['valid'] == true;
	}
	
	/**
	 * Called after login and successful call of fetch data
	 * @return 
	 * @param object $a_username
	 * @param object $a_auth
	 */
	public function loginObserver($a_username,$a_auth)
	{
		global $ilias, $rbacadmin, $lng, $ilSetting;
		
		$GLOBALS['ilLog']->write(__METHOD__.': SOAP login observer called');
		

		// TODO: handle passed credentials via GET
		/*
		if (empty($_GET["ext_uid"]) || empty($_GET["soap_pw"]))
		{
			$this->status = AUTH_WRONG_LOGIN;
			return;
		}
		*/
		
		// Not required anymore
		/*
		$validation_data = $this->validateSoapUser($_GET["ext_uid"], $_GET["soap_pw"]);
		
		if (!$validation_data["valid"])
		{
			$this->status = AUTH_WRONG_LOGIN;
			return;
		}
		*/
		
		$local_user = $this->response["local_user"];
		if ($local_user != "")
		{
			// to do: handle update of user
			$a_auth->setAuth($local_user);
			return true;
		}
		if(!$ilSetting->get("soap_auth_create_users"))
		{
			$a_auth->status = AUTH_SOAP_NO_ILIAS_USER;
			$a_auth->logout();
			return false;
		}
//echo "1";
		// try to map external user via e-mail to ILIAS user
		if ($this->response["email"] != "")
		{
//echo "2";
//var_dump ($_POST);
			$email_user = ilObjUser::_getLocalAccountsForEmail($this->response["email"]);

			// check, if password has been provided in user mapping screen
			// (see ilStartUpGUI::showUserMappingSelection)
			// FIXME
			if ($_POST["LoginMappedUser"] != "")
			{ 
				if (count($email_user) > 0)
				{
					$user = ilObjectFactory::getInstanceByObjId($_POST["usr_id"]);
					require_once 'Services/User/classes/class.ilUserPasswordManager.php';
					if(ilUserPasswordManager::getInstance()->verifyPassword($user, ilUtil::stripSlashes($_POST["password"])))
					{
						// password is correct -> map user
						//$this->setAuth($local_user); (use login not id)
						ilObjUser::_writeExternalAccount($_POST["usr_id"], $_GET["ext_uid"]);
						ilObjUser::_writeAuthMode($_POST["usr_id"], "soap");
						$_GET["cmd"] = $_POST["cmd"] = $_GET["auth_stat"]= "";
						$local_user = ilObjUser::_lookupLogin($_POST["usr_id"]);
						$a_auth->status = '';
						$a_auth->setAuth($local_user);
						return true;
					}
					else
					{
//echo "6"; exit;
						
						$a_auth->status = AUTH_SOAP_NO_ILIAS_USER_BUT_EMAIL;
						$a_auth->setSubStatus(AUTH_WRONG_LOGIN);
						$a_auth->logout();
						return false;
					}
				}
			}
			
			if (count($email_user) > 0 && $_POST["CreateUser"] == "")
			{					
				$_GET["email"] = $this->response["email"]; 
				$a_auth->status = AUTH_SOAP_NO_ILIAS_USER_BUT_EMAIL;
				$a_auth->logout();
				return false;
			}
		}

		$userObj = new ilObjUser();
		$local_user = ilAuthUtils::_generateLogin($a_username);
		
		$newUser["firstname"] = $this->response["firstname"];
		$newUser["lastname"] = $this->response["lastname"];
		$newUser["email"] = $this->response["email"];
		
		$newUser["login"] = $local_user;
		
		// to do: set valid password and send mail
		$newUser["passwd"] = ""; 
		$newUser["passwd_type"] = IL_PASSWD_CRYPTED;
		
		// generate password, if local authentication is allowed
		// and account mail is activated
		$pw = "";

		if ($ilSetting->get("soap_auth_allow_local") &&
			$ilSetting->get("soap_auth_account_mail"))
		{
			$pw = ilUtil::generatePasswords(1);
			$pw = $pw[0];
			$newUser["passwd"] = $pw;
			$newUser["passwd_type"] = IL_PASSWD_PLAIN;
		}

		//$newUser["gender"] = "m";
		$newUser["auth_mode"] = "soap";
		$newUser["ext_account"] = $a_username;
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
		$userObj->setOwner(0);
		$userObj->create();
		$userObj->setActive(1);
		
		$userObj->updateOwner();
		
		//insert user data in table user_data
		$userObj->saveAsNew(false);
		
		// setup user preferences
		$userObj->writePrefs();
		
		// to do: test this
		$rbacadmin->assignUser($ilSetting->get('soap_auth_user_default_role'), $userObj->getId(),true);

		// send account mail
		if ($ilSetting->get("soap_auth_account_mail"))
		{
			include_once('./Services/User/classes/class.ilObjUserFolder.php');
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
		$a_auth->setAuth($local_user);
		return true;
	}
}