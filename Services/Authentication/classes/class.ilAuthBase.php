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

/**
 * @classDescription Base class for all PEAR and ILIAS auth classes.
 * Enables logging, observers.
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * 
 * @ingroup ServicesAuthentication
 */
abstract class ilAuthBase
{
	// Used for SOAP Auth
	// TODO: Find another solution
	protected $sub_status = null;
	
	
	/**
	 * Get sub status
	 * @return type
	 */
	public function getSubStatus()
	{
		return $this->sub_status;
	}
	
	/**
	 * Set sub status
	 * @param type $a_sub_status
	 */
	public function setSubStatus($a_sub_status)
	{
		$this->sub_status = $a_sub_status;
	}
	
	/**
	 * Returns true, if the current auth mode allows redirects to e.g 
	 * the login screen, public section ... 
	 * @return 
	 */
	public function supportsRedirects()
	{
		return true;
	}
	
	/**
	 * Get container object
	 * @return	object ilAuthContainerBase
	 */
	public final function getContainer()
	{
		return $this->storage;
	}
	
	/**
	 * Init auth object
	 * Enable logging, set callbacks...
	 * @return void
	 */
	protected final function initAuth()
	{
		ilSessionControl::initSession();

		$this->enableLogging = false;
		//$this->enableLogging = false;

		if ($this->enableLogging)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Init callbacks');
		}
		$this->setLoginCallback(array($this,'loginObserver'));
		$this->setFailedLoginCallback(array($this,'failedLoginObserver'));
		$this->setCheckAuthCallback(array($this,'checkAuthObserver'));
		$this->setLogoutCallback(array($this,'logoutObserver'));
		
		include_once('Services/Authentication/classes/class.ilAuthLogObserver.php');
		$this->attachLogObserver(new ilAuthLogObserver(AUTH_LOG_DEBUG));
		
	}
	
	/**
	 * Called after successful login
	 * @return 
	 * @param array $a_username
	 * @param object $a_auth
	 */
	protected function loginObserver($a_username,$a_auth)
	{		
		global $ilLog, $ilAppEventHandler, $ilSetting;
		
		if($this->getContainer()->loginObserver($a_username,$a_auth))
		{
			// validate user
			include_once "Services/User/classes/class.ilObjUser.php";
			$user_id = ilObjUser::_loginExists($a_auth->getUsername());
			if($user_id != ANONYMOUS_USER_ID)
			{
				$user = new ilObjUser($user_id);
					
			    // check if profile is complete						
				include_once "Services/User/classes/class.ilUserProfile.php";
				if(ilUserProfile::isProfileIncomplete($user) and ilAuthFactory::getContext() != ilAuthFactory::CONTEXT_ECS)
				{
					$user->setProfileIncomplete(true);
					$user->update();
				}

				// --- extended user validation
				// 
				// we only have a single status, so abort after each one
				// order from highest priority to lowest
				
				// active?
				if(!$user->getActive())
				{
					$this->status = AUTH_USER_INACTIVE;
					return;
				}
				
				// time limit
				if(!$user->checkTimeLimit())
				{
					$this->status = AUTH_USER_TIME_LIMIT_EXCEEDED;
					return;
				}
				
				// check client ip
				$clientip = $user->getClientIP();
				if (trim($clientip) != "")
				{
					$clientip = preg_replace("/[^0-9.?*,:]+/","",$clientip);
					$clientip = str_replace(".","\\.",$clientip);
					$clientip = str_replace(Array("?","*",","), Array("[0-9]","[0-9]*","|"), $clientip);
					if (!preg_match("/^".$clientip."$/", $_SERVER["REMOTE_ADDR"]))
					{
						$this->status = AUTH_USER_WRONG_IP;
						return;
					}
				}				
				
				// simultaneous login
				if($ilSetting->get('ps_prevent_simultaneous_logins') &&
					ilObjUser::hasActiveSession($user_id))
				{
					$this->status = AUTH_USER_SIMULTANEOUS_LOGIN;
					return;
				}

				include_once 'Services/Tracking/classes/class.ilOnlineTracking.php';
				ilOnlineTracking::addUser($user_id);

				include_once 'Modules/Forum/classes/class.ilObjForum.php';
				ilObjForum::_updateOldAccess($user_id);

				require_once 'Services/PrivacySecurity/classes/class.ilSecuritySettings.php';
				$security_settings = ilSecuritySettings::_getInstance();

				// determine first login of user for setting an indicator
				// which still is available in PersonalDesktop, Repository, ...
				// (last login date is set to current date in next step)		
				if($security_settings->isPasswordChangeOnFirstLoginEnabled() &&
					$user->getLastLogin() == null
				)
				{
					$user->resetLastPasswordChange();
				}

				$user->refreshLogin();
			
				// reset counter for failed logins
				ilObjUser::_resetLoginAttempts($user_id);				
			}

			// --- anonymous/registered user

			$ilLog->write(
				__METHOD__ . ': logged in as ' . $a_auth->getUsername() .
				', remote:' . $_SERVER['REMOTE_ADDR'] . ':' . $_SERVER['REMOTE_PORT'] .
				', server:' . $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT']
			);

			ilSessionControl::handleLoginEvent($a_auth->getUsername(), $a_auth);

			$ilAppEventHandler->raise(
				'Services/Authentication', 'afterLogin',
				array('username' => $a_auth->getUsername())
			);
		}
	}

	/**
	 * Called after failed login
	 * @return 
	 * @param array $a_username
	 * @param object $a_auth
	 */
	protected function failedLoginObserver($a_username, $a_auth)
	{
		global $ilLog;

		$ilLog->write(__METHOD__.': login failed for user '.$a_username.
			', remote:'.$_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'].
			', server:'.$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT']
		);
		
		if($a_username)
		{
			$usr_id = ilObjUser::_lookupId($a_username);
			if(!in_array($usr_id, array(ANONYMOUS_USER_ID, SYSTEM_USER_ID)))
			{
				ilObjUser::_incrementLoginAttempts($usr_id);
				$login_attempts = ilObjUser::_getLoginAttempts($usr_id);
				
				require_once 'Services/PrivacySecurity/classes/class.ilSecuritySettings.php';
				$security = ilSecuritySettings::_getInstance();			
				$max_attempts = $security->getLoginMaxAttempts();
				
				if((int)$max_attempts && $login_attempts >= $max_attempts)
				{
					ilObjUser::_setUserInactive($usr_id);
				}
			}
		}

		return $this->getContainer()->failedLoginObserver($a_username,$a_auth);
	}

	/**
	 * Called after each check auth request
	 * @return 
	 * @param array $a_username
	 * @param object $a_auth
	 */
	protected function checkAuthObserver($a_username,$a_auth)
	{
		#$GLOBALS['ilLog']->write(__METHOD__.': Check auth observer called');
		return $this->getContainer()->checkAuthObserver($a_username,$a_auth);
	}
	
	/**
	 * Called after logout
	 * @return 
	 * @param array $a_username
	 * @param object $a_auth
	 */
	protected function logoutObserver($a_username,$a_auth)
	{
		global $ilLog;
		
		$ilLog->write(__METHOD__.': Logout observer called');
		
		ilSessionControl::handleLogoutEvent();
				
		return $this->getContainer()->logoutObserver($a_username,$a_auth);
	}
	
}
?>