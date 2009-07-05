<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/class.ilAuthContainerDecorator.php';
include_once 'Auth/Container.php';


/**
 * @classDescription CAS authentication
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 * @ingroup ServicesCAS
 */
class ilAuthContainerCAS extends Auth_Container
{


	protected $server_version = null;
	protected $server_hostname = null;
	protected $server_port = null;
	protected $server_uri = null;


    /**
     * @see ilAuthContainerDecorator::__construct()
     */
    public function __construct()
	{
		parent::__construct();
		$this->initCAS();
    }	

	/**
	 * Force CAS authentication
	 * @return 
	 * @param object $username
	 * @param object $status
	 * @param object $auth
	 */
	public function forceAuthentication($username,$status,$auth)
	{
		global $PHPCAS_CLIENT,$ilLog;
		
		if(!$PHPCAS_CLIENT->isAuthenticated())
		{
			$PHPCAS_CLIENT->forceAuthentication();
		}
	}
	
    /**
     * @see ilAuthContainerBase::loginObserver()
     */
    public function loginObserver($a_username, $a_auth)
    {
		global $ilias, $rbacadmin, $ilSetting,$ilLog,$PHPCAS_CLIENT;
		
		$ilLog->write(__METHOD__.': Successful CAS login.');
		
		include_once("./Services/CAS/phpcas/source/CAS/CAS.php");
		
		if ($PHPCAS_CLIENT->getUser() != "")
		{
			$username = $PHPCAS_CLIENT->getUser();
			$ilLog->write(__METHOD__.': Username: '.$username);

			// Authorize this user
			include_once('./Services/User/classes/class.ilObjUser.php');
			$local_user = ilObjUser::_checkExternalAuthAccount("cas", $username);

			if ($local_user != "")
			{
				$a_auth->setAuth($local_user);
			}
			else
			{
				if (!$ilSetting->get("cas_create_users"))
				{
					$a_auth->status = AUTH_CAS_NO_ILIAS_USER;
					$a_auth->logout();
					return false;
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
				$userObj->setLanguage($ilSetting->get("language"));
				
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
				
				$a_auth->setAuth($local_user);
				return true;
			}
		}
		else
		{
			$ilLog->write(__METHOD__.': Login failed.');

			// This should never occur unless CAS is not configured properly
			$a_auth->status = AUTH_WRONG_LOGIN;
			return false;
		}
		return false;
    }
	
	
	/**
	 * 
	 * @return bool 
	 * @param string $a_username
	 * @param string $a_password
	 * @param bool $isChallengeResponse[optional]
	 */
	public function fetchData($a_username,$a_password,$isChallengeResponse = false)
	{
		global $PHPCAS_CLIENT,$ilLog;
		
		$ilLog->write(__METHOD__.': Fetch Data called');
		return $PHPCAS_CLIENT->isAuthenticated();
	}
	
	protected function initCAS()
	{
		global $ilSetting;
		
		include_once("./Services/CAS/phpcas/source/CAS/CAS.php");

		$this->server_version = CAS_VERSION_2_0;
		$this->server_hostname = $ilSetting->get('cas_server');
		$this->server_port = (int) $ilSetting->get('cas_port');
		$this->server_uri = $ilSetting->get('cas_uri');
		
		phpCAS::setDebug();
		phpCAS::client(
			$this->server_version,
			$this->server_hostname,
			$this->server_port,
			$this->server_uri
		);
	}
	
}
?>