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

include_once('Auth/Container.php');

/** 
* Custom PEAR Auth Container for ECS auth checks
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ingroup ServicesWebServicesECS 
*/
class ilAuthContainerECS extends Auth_Container
{
	protected $mid = null;
	protected $abreviation = null;

	protected $currentServer = null;
	protected $servers = null;

	protected $log;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_params = array())
	{
	 	parent::__construct($a_params);

		$this->initECSServices();
		
		$this->log = $GLOBALS['ilLog'];
	}
	
	/**
	 * get abbreviation
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getAbreviation()
	{
	 	return $this->abreviation;
	}
	
	/**
	 * get mid
	 *
	 * @access public
	 */
	public function getMID()
	{
		return $this->mid;	 	
	}
	
	public function setMID($a_mid)
	{
		$this->mid = $a_mid;
	}

	/**
	 * Set current server
	 * @param ilECSSetting $server
	 */
	public function setCurrentServer(ilECSSetting $server = null)
	{
		$this->currentServer = $server;
	}

	/**
	 * Get current server
	 * @return ilECSSetting
	 */
	public function getCurrentServer()
	{
		return $this->currentServer;
	}

	/**
	 * Get server settings
	 * @return ilECSServerSettings
	 */
	public function getServerSettings()
	{
		return $this->servers;
	}

	/**
	 * Check for valid ecs_hash
	 * @param string $a_username
	 * @param string $a_pass
	 */
	public function fetchData($a_username,$a_pass)
	{
		global $ilLog;

		$ilLog->write(__METHOD__.': Starting ECS authentication.');

		if(!$this->getServerSettings()->activeServerExists())
		{
			$GLOBALS['ilLog']->write(__METHOD__.': no active ecs server found. Aborting');
			return false;
		}

		// Iterate through all active ecs instances
		include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';
		foreach($this->getServerSettings()->getServers() as $server)
		{
			$this->setCurrentServer($server);
			if($this->validateHash())
			{
				return true;
			}
		}
		$GLOBALS['ilLog']->write(__METHOD__.': Could not validate ecs hash for any server');
		return false;

	}
	
	
	/**
	 * Validate ECS hash
	 *
	 * @access public
	 * @param string username
	 * @param string pass
	 * 
	 */
	public function validateHash()
	{
	 	global $ilLog;
		
		// fetch hash
		if(isset($_GET['ecs_hash']) and strlen($_GET['ecs_hash']))
		{
			$hash = $_GET['ecs_hash'];
		}
		if(isset($_GET['ecs_hash_url']))
		{
			$hashurl = urldecode($_GET['ecs_hash_url']);
			$hash = basename(parse_url($hashurl,PHP_URL_PATH));
			//$hash = urldecode($_GET['ecs_hash_url']);
		}
		
		$GLOBALS['ilLog']->write(__METHOD__.': Using ecs hash '. $hash);

		// Check if hash is valid ...
	 	try
	 	{
		 	include_once('./Services/WebServices/ECS/classes/class.ilECSConnector.php');
	 		$connector = new ilECSConnector($this->getCurrentServer());
	 		$res = $connector->getAuth($hash);
			$auths = $res->getResult();
			
			$GLOBALS['ilLog']->write(__METHOD__.': Auths: '.print_r($auths,TRUE));
			
			if($auths->pid)
			{
				try
				{
					include_once './Services/WebServices/ECS/classes/class.ilECSCommunityReader.php';
					$reader = ilECSCommunityReader::getInstanceByServerId($this->getCurrentServer()->getServerId());
					$part = $reader->getParticipantByMID($auths->pid);
					$this->abreviation = $part->getOrganisation()->getAbbreviation();
				}
				catch(Exception $e)
				{
					$ilLog->write(__METHOD__.': Authentication failed with message: '.$e->getMessage());
					return false;
				}
			}
			else
			{
				$this->abreviation = $auths->abbr;
			}
			
			$ilLog->write(__METHOD__.': Got abr: '.$this->abreviation);
	 	}
	 	catch(ilECSConnectorException $e)
	 	{
	 		$ilLog->write(__METHOD__.': Authentication failed with message: '.$e->getMessage());
	 		return false;
	 	}
		
		// read current mid
		try
		{
		 	include_once('./Services/WebServices/ECS/classes/class.ilECSConnector.php');
	 		$connector = new ilECSConnector($this->getCurrentServer());
	 		$details = $connector->getAuth($hash,TRUE);
			
			$GLOBALS['ilLog']->write(__METHOD__.': '.print_r($details,TRUE));
			$GLOBALS['ilLog']->write(__METHOD__.': Token created for mid '. $details->getFirstSender());
			
			$this->setMID($details->getFirstSender());
		}
	 	catch(ilECSConnectorException $e)
	 	{
	 		$ilLog->write(__METHOD__.': Receiving mid failed with message: '.$e->getMessage());
	 		return false;
	 	}
		return TRUE;
	}
	
	/** 
	 * Called from base class after successful login
	 *
	 * @param string username
	 */
	public function loginObserver($a_username, $a_auth)
	{
		include_once('./Services/WebServices/ECS/classes/class.ilECSUser.php');
		
		$user = new ilECSUser($_GET);
		
		if(!$usr_id = ilObject::_lookupObjIdByImportId($user->getImportId()))
		{
			$username = $this->createUser($user);
		}
		else
		{
			$username = $this->updateUser($user,$usr_id);
		}
		
		// set user imported
		include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
		$import = new ilECSImport($this->getCurrentServer()->getServerId(), $usr_id);
		$import->save();
		
		// Store remote user data
		include_once './Services/WebServices/ECS/classes/class.ilECSRemoteUser.php';
		$remote = new ilECSRemoteUser();
		$remote->setServerId($this->getCurrentServer()->getServerId());
		$remote->setMid($this->getMID());
		$remote->setRemoteUserId($user->getImportId());
		$remote->setUserId(ilObjUser::_lookupId($username));
		
		$GLOBALS['ilLog']->write(__METHOD__.': Current username '.$username);
		
		if(!$remote->exists())
		{
			$remote->create();
		}
		
		$a_auth->setAuth($username);
		$this->log->write(__METHOD__.': Login succesesful');
		return true;
	}
	
	/** 
	 * Called from base class after failed login
	 *
	 * @param string username
	 */
	public function failedLoginObserver()
	{
		$this->log->write(__METHOD__.': Login failed');
		return false;
	}
	
	

	/**
	 * create new user
	 *
	 * @access protected
	 */
	protected function createUser(ilECSUser $user)
	{
		global $ilClientIniFile, $ilSetting, $rbacadmin, $ilLog;

		$userObj = new ilObjUser();

		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
		$local_user = ilAuthUtils::_generateLogin($this->getAbreviation() . '_' . $user->getLogin());

		$newUser["login"] = $local_user;
		$newUser["firstname"] = $user->getFirstname();
		$newUser["lastname"] = $user->getLastname();
		$newUser['email'] = $user->getEmail();
		$newUser['institution'] = $user->getInstitution();

		// set "plain md5" password (= no valid password)
		$newUser["passwd"] = "";
		$newUser["passwd_type"] = IL_PASSWD_CRYPTED;

		$newUser["auth_mode"] = "ecs";
		$newUser["profile_incomplete"] = 0;

		// system data
		$userObj->assignData($newUser);
		$userObj->setTitle($userObj->getFullname());
		$userObj->setDescription($userObj->getEmail());

		// set user language to system language
		$userObj->setLanguage($ilSetting->get("language"));

		// Time limit
		$userObj->setTimeLimitOwner(7);
		$userObj->setTimeLimitUnlimited(0);
		$userObj->setTimeLimitFrom(time() - 5);
		$userObj->setTimeLimitUntil(time() + $ilClientIniFile->readVariable("session", "expire"));

		#$now = new ilDateTime(time(), IL_CAL_UNIX);
		#$userObj->setAgreeDate($now->get(IL_CAL_DATETIME));

		// Create user in DB
		$userObj->setOwner(6);
		$userObj->create();
		$userObj->setActive(1);
		$userObj->updateOwner();
		$userObj->saveAsNew();
		$userObj->writePrefs();

		if($global_role = $this->getCurrentServer()->getGlobalRole())
		{
			$rbacadmin->assignUser($this->getCurrentServer()->getGlobalRole(), $userObj->getId(), true);
		}
		ilObject::_writeImportId($userObj->getId(), $user->getImportId());

		$ilLog->write(__METHOD__ . ': Created new remote user with usr_id: ' . $user->getImportId());

		// Send Mail
		#$this->sendNotification($userObj);
		$this->resetMailOptions($userObj->getId());
		
		return $userObj->getLogin();
	}
	
	/**
	 * update existing user
	 *
	 * @access protected
	 */
	protected function updateUser(ilECSUser $user,$a_local_user_id)
	{
		global $ilClientIniFile,$ilLog,$rbacadmin;
		
		$user_obj = new ilObjUser($a_local_user_id);
		$user_obj->setFirstname($user->getFirstname());
		$user_obj->setLastname($user->getLastname());
		$user_obj->setEmail($user->getEmail());
		$user_obj->setInstitution($user->getInstitution());
		$user_obj->setActive(true);
		
		$until = $user_obj->getTimeLimitUntil();

		if($until < (time() + $ilClientIniFile->readVariable('session','expire')))
		{		
			$user_obj->setTimeLimitFrom(time() - 60);
			$user_obj->setTimeLimitUntil(time() + $ilClientIniFile->readVariable("session","expire"));
		}
		$user_obj->update();
		$user_obj->refreshLogin();
		
		if($global_role = $this->getCurrentServer()->getGlobalRole())
		{
			$rbacadmin->assignUser(
				$this->getCurrentServer()->getGlobalRole(),
				$user_obj->getId(),
				true
			);
		}
		
		$this->resetMailOptions($a_local_user_id);

		$ilLog->write(__METHOD__.': Finished update of remote user with usr_id: '.$user->getImportId());	
		return $user_obj->getLogin();
	}
	
	/**
	 * Reset mail options to "local only"
	 * 
	 */
	protected function resetMailOptions($a_usr_id)
	{
		include_once './Services/Mail/classes/class.ilMailOptions.php';
		$options = new ilMailOptions($a_usr_id);
		$options->updateOptions(
				$options->getSignature(),
				$options->getLinebreak(),
				IL_MAIL_LOCAL,
				$options->getCronjobNotification()
		);
	}
	

	/**
	 * Init ECS Services
	 * @access private
	 * @param
	 * 
	 */
	private function initECSServices()
	{
	 	include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';
		$this->servers = ilECSServerSettings::getInstance();
	}
	
	/**
	 * Send notification
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function sendNotification($user_obj)
	{
		if(!count($this->getCurrentServer()->getUserRecipients()))
		{
			return true;
		}

		include_once('./Services/Language/classes/class.ilLanguageFactory.php');
		include_once './Services/Language/classes/class.ilLanguage.php';
		$lang = ilLanguageFactory::_getLanguage();
		$GLOBALS['lng'] = $lang;
		$GLOBALS['ilUser'] = $user_obj;
		$lang->loadLanguageModule('ecs');

		include_once('./Services/Mail/classes/class.ilMail.php');
		$mail = new ilMail(6);
		$mail->enableSoap(false);
		$subject = $lang->txt('ecs_new_user_subject');

				// build body
		$body = $lang->txt('ecs_new_user_body')."\n\n";
		$body .= $lang->txt('ecs_new_user_profile')."\n\n";
		$body .= $user_obj->getProfileAsString($lang)."\n\n";
		$body .= ilMail::_getAutoGeneratedMessageString($lang);
		
		$mail->sendMail(
			$this->getCurrentServer()->getUserRecipientsAsString(),
			"",
			"",
			$subject,
			$body,
			array(),
			array("normal")
		);
	}
}
?>
