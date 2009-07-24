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

include_once 'Auth/Container/LDAP.php';

/** 
* Overwritten Pear class AuthContainerLDAP
* This class is overwritten to support nested groups.
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* 
* @ingroup ServicesLDAP
*/
class ilAuthContainerLDAP extends Auth_Container_LDAP
{
	private static $force_creation = false;
	
	private $optional_check = false;
	
	private $log = null;
	private $server = null;
	private $ldap_attr_to_user = null;
        
        
	/**
	 * Constructor
	 *
	 * @access public
	 * @param array array of pear parameters
	 * 
	 */
	public function __construct()
	{
		global $ilLog;
		
		include_once 'Services/LDAP/classes/class.ilLDAPServer.php';
		$this->server = new ilLDAPServer(ilLDAPServer::_getFirstActiveServer());
		$this->server->doConnectionCheck();
	 	$this->log = $ilLog;
		
		parent::__construct($this->server->toPearAuthArray());
	}
	
	public function forceCreation($a_status)
	{
		self::$force_creation = $a_status;
	}

	/**
	 * enable optional group check
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function enableOptionalGroupCheck()
	{
	 	$this->optional_check = true;
	 	$this->updateUserFilter();
	}
	
	/**
	 * Check if optional group check is enabled
	 *
	 * @access public
	 * 
	 */
	public function enabledOptionalGroupCheck()
	{
	 	return (bool) $this->optional_check;
	}

	
	/**
	 * check group 
	 * overwritten base class
	 *
	 * @access public
	 * @param string user name (DN or external account name)
	 * 
	 */
	public function checkGroup($a_name)
	{
		$this->log->write(__METHOD__.': checking group restrictions...');

		// if there are multiple groups define check all of them for membership
		$groups = $this->server->getGroupNames();
		
		if(!count($groups))
		{
			$this->log->write(__METHOD__.': No group restrictions found.');
			return true;
		}
		elseif($this->server->isMembershipOptional() and !$this->optional_check)
		{
			$this->log->write(__METHOD__.': Group membership is optional.');
			return true;
		}
	
		foreach($groups as $group)
		{
			$this->options['group'] = $group;
			
			if(parent::checkGroup($a_name))
			{
				return true;
			}
		}
	 	return false;	
	}
	
	/**
	 * Update user filter
	 *
	 * @access private
	 * 
	 */
	private function updateUserFilter()
	{
	 	$this->getContainer()->options['userfilter'] = $this->server->getGroupUserFilter();
	}

	/** 
	 * Called from fetchData after successful login.
	 *
	 * @param string username
	 */
	public function loginObserver($a_username,$a_auth)
	{
		global $ilBench;
		global $ilLog;
		
		$ilBench->start('Auth','LDAPLoginObserver');
		$user_data = array_change_key_case($a_auth->getAuthData(),CASE_LOWER);
		
		$a_username = $this->extractUserName($user_data);

		$user_data['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount("ldap",$a_username);
		$users[$a_username] = $user_data;
		
		if($this->server->enabledSyncOnLogin())
		{
			if(!$user_data['ilInternalAccount'] and 
				$this->server->isAccountMigrationEnabled() and 
				!self::$force_creation)
			{
				$a_auth->logout();
				$_SESSION['tmp_auth_mode'] = 'ldap';
				$_SESSION['tmp_external_account'] = $a_username;
				$_SESSION['tmp_pass'] = $_POST['password'];
				
				include_once('./Services/LDAP/classes/class.ilLDAPRoleAssignmentRules.php');
				$roles = ilLDAPRoleAssignmentRules::getAssignmentsForCreation($a_username, $user_data);
				$_SESSION['tmp_roles'] = array();
				foreach($roles as $info)
				{
					if($info['action'] == ilLDAPRoleAssignmentRules::ROLE_ACTION_ASSIGN)
					{
						$_SESSION['tmp_roles'][] = $info['id'];	
					}
				}
				$ilBench->stop('Auth','LDAPLoginObserver');
				ilUtil::redirect('ilias.php?baseClass=ilStartUpGUI&cmdClass=ilstartupgui&cmd=showAccountMigration');
			}
			
			// Refresh or create user data
			$ilBench->start('Auth','LDAPUserSynchronization');
			if($this->updateRequired($a_username))
			{
				#$GLOBALS['ilLog']->write(__METHOD__.': Starting update');
				$this->initLDAPAttributeToUser();
				$this->ldap_attr_to_user->setUserData($users);
				$this->ldap_attr_to_user->refresh();
				$user_data['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount("ldap",$a_username);
			}
			else
			{
				// User exists and no update required
				$user_data['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount("ldap",$a_username);
			}
			$ilBench->stop('Auth','LDAPUserSynchronization');
		}

		if(!$user_data['ilInternalAccount'])
		{
			// No syncronisation allowed => create Error
			$a_auth->status = AUTH_LDAP_NO_ILIAS_USER;
			$a_auth->logout();
			$ilBench->stop('Auth','LDAPLoginObserver');
			return false;
		}
		
		
		// Finally setAuth
		$a_auth->setAuth($user_data['ilInternalAccount']);
		$ilBench->stop('Auth','LDAPLoginObserver');
		return true;
		
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
		$this->ldap_attr_to_user = new ilLDAPAttributeToUser($this->server);
	}
	
	/** 
	 * Called from fetchData after failed login
	 * @param string username
	 * @param object PEAR auth object
	 */
	public function failedLoginObserver($a_username,$a_auth)
	{
		if(!$this->enabledOptionalGroupCheck() and $this->server->isMembershipOptional())
		{
			$a_auth->logout();
			$this->enableOptionalGroupCheck();
			$a_auth->start();
			return false;
		}
		return true;
	}
	
	/**
	 *  
	 * @param
	 * @return string	ldap username
	 */
	protected function extractUserName($a_user_data)
	{
		$a_username = isset($a_user_data[$this->server->getUserAttribute()]) ? 
			$a_user_data[$this->server->getUserAttribute()] :
			trim($a_username);
		
		// Support for multiple user attributes
		if(!is_array($a_username))
		{
			return $a_username;
		}
		foreach($a_username as $name)
		{
			// User found with authentication method 'ldap'
			if(ilObjUser::_checkExternalAuthAccount("ldap",$name))
			{
				return trim($name);
			}
		}
		// No existing user found  => return first name
		return $a_username[0];				
	}

	/**
	 * Check if an update is required
	 * @return 
	 * @param string $a_username
	 */
	protected function updateRequired($a_username)
	{
		if(!ilObjUser::_checkExternalAuthAccount("ldap",$a_username))
		{
			#$GLOBALS['ilLog']->write(__METHOD__.': Required 1');
			return true;
		}
		// Check attribute mapping on login
		include_once './Services/LDAP/classes/class.ilLDAPAttributeMapping.php';
		if(ilLDAPAttributeMapping::hasRulesForUpdate($this->server->getServerId()))
		{
			#$GLOBALS['ilLog']->write(__METHOD__.': Required 2');
			return true;
		}
		include_once './Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php';
		if(ilLDAPRoleAssignmentRule::hasRulesForUpdate())
		{
			#$GLOBALS['ilLog']->write(__METHOD__.': Required 3');
			return true;
		}
		return false;
	}
}
?>