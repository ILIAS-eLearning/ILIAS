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

/** 
* Auth LDAP overwrites PEAR Auth to perform LDAP authentication with specific ILIAS options
* 
* Important note to maintainers of this class: 
*	All changes that are done here need to be done in class.ilAuthHTTPLDAP.php
*   as well, and vice versa.
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesLDAP 
*/

include_once('Auth/Auth.php');

class ilAuthLDAP extends Auth
{
	private $ldap_server = null;
	private $ldap_container = null;
	private $ldap_attr_to_user = null;
	private $log = null;
	private $logCache = '';
	
	private $force_creation = false;
	
// BEGIN WebDAV Constructor with parameters
    public function ilAuthLDAP($options = '')
// END WebDAV Constructor with parameters
	{
		global $ilLog;
		
		$this->log = $ilLog;
		
		// Read setting of LDAP server
		$this->initServer();
		$this->initContainer();
		// BEGIN WebDAV: Constructor with parameters
		if (is_array($options))
		{
			$options = array_merge($this->ldap_server->toPearAuthArray(), $options);
		}
		else
		{
			$options = $this->ldap_server->toPearAuthArray();
		}
		parent::Auth($this->ldap_container,$options,'',false);
		// END WebDAV
		
		$this->initLogObserver();		
		
		// Set callbacks
		$this->setCallbacks();
	}
	
	/**
	 * Force creation of user accounts
	 *
	 * @access public
	 * @param bool force_creation
	 * 
	 */
	public function forceCreation($a_status)
	{
	 	$this->force_creation = true;
	}
	
	/** 
	 * Called from base class after successful login
	 *
	 * @param string username
	 */
	protected function loginObserver($a_username)
	{
		global $ilBench;
		
		
		$ilBench->start('Auth','LDAPLoginObserver');
		$user_data = array_change_key_case($this->getAuthData(),CASE_LOWER);
		
		$user_data['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount("ldap",$a_username);
		$users[$a_username] = $user_data;
		
		if($this->ldap_server->enabledSyncOnLogin())
		{
			if(!$user_data['ilInternalAccount'] and $this->ldap_server->isAccountMigrationEnabled() and !$this->force_creation)
			{
				$this->logout();
				$_SESSION['tmp_auth_mode'] = 'ldap';
				$_SESSION['tmp_external_account'] = $a_username;
				$_SESSION['tmp_pass'] = $_POST['password'];
				
				$ilBench->stop('Auth','LDAPLoginObserver');
				ilUtil::redirect('ilias.php?baseClass=ilStartUpGUI&cmdClass=ilstartupgui&cmd=showAccountMigration');
			}

			// Refresh or create user data
			$ilBench->start('Auth','LDAPUserSynchronization');
			$this->initLDAPAttributeToUser();
			$this->ldap_attr_to_user->setUserData($users);
			$this->ldap_attr_to_user->refresh();
			$user_data['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount("ldap",$a_username);
			$ilBench->stop('Auth','LDAPUserSynchronization');
		}

		if(!$user_data['ilInternalAccount'])
		{
			// No syncronisation allowed => create Error
			$this->status = AUTH_LDAP_NO_ILIAS_USER;
			$this->logout();
			$ilBench->stop('Auth','LDAPLoginObserver');
			return;
		}
		// Finally setAuth
		$this->setAuth($user_data['ilInternalAccount']);
		$ilBench->stop('Auth','LDAPLoginObserver');
		return;
		
	}
	
	/** 
	 * Called from base class after failed login
	 *
	 * @param string username
	 */
	protected function failedLoginObserver()
	{
		if(!$this->ldap_container->enabledOptionalGroupCheck() and $this->ldap_server->isMembershipOptional())
		{
			$this->logout();
			$this->ldap_container->enableOptionalGroupCheck();
			$this->start();
		}
	}
	
	/**
	 * Init LDAP attribute mapping
	 *
	 * @access private
	 * 
	 */
	private function initLDAPAttributeToUser()
	{
		include_once('Services/LDAP/classes/class.ilLDAPAttributeToUser.php');
		$this->ldap_attr_to_user = new ilLDAPAttributeToUser($this->ldap_server);
	}

	private function initServer()
	{
		include_once 'Services/LDAP/classes/class.ilLDAPServer.php';
		$this->ldap_server = new ilLDAPServer(ilLDAPServer::_getFirstActiveServer());
		$this->ldap_server->doConnectionCheck();
	}
	
	/**
	 * Init overwritten 
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function initContainer()
	{
	 	include_once('Services/LDAP/classes/class.ilAuthContainerLDAP.php');
	 	$this->ldap_container = new ilAuthContainerLDAP($this->ldap_server,$this->ldap_server->toPearAuthArray());
	}
	
	/** 
	 * Set callback function for PEAR Auth 
	 *
	 */
	private function setCallbacks() 
	{
		$this->setLoginCallback(array($this,'loginObserver'));
		$this->setFailedLoginCallback(array($this,'failedLoginObserver'));
	}
	
	/**
	 * Init Log observer
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function initLogObserver()
	{
	 	global $ilLog;
	 	
	 	if(!method_exists($this,'attachLogObserver'))
	 	{
			$ilLog->write(__METHOD__.': PEAR Auth < 1.5 => disabling logging.');
	 		return false;
	 	}
	 	
	 	if(@include_once('Log.php'))
	 	{
		 	if(@include_once('Log/observer.php'))
		 	{
				$ilLog->write(__METHOD__.': Attached Logging observer.');
				include_once('Services/LDAP/classes/class.ilAuthLDAPLogObserver.php');
				$this->attachLogObserver(new ilAuthLDAPLogObserver(AUTH_LOG_DEBUG));
				return true;
		 	}
	 	}
		$ilLog->write(__METHOD__.': PEAR Log not installed. Logging disabled');
	 	
	}
	
}
?>