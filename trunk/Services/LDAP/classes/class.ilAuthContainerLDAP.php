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
	 * Overwritten from base class
	 * @param object $username
	 * @param object $password
	 * @return 
	 */
	public function fetchData($username, $password)
	{
		$res = parent::fetchData($username,$password);
		
		if (PEAR::isError($res))
		{ 
			$this->log('Container '.$key.': '.$res->getMessage(), AUTH_LOG_ERR);
			return $res;
		}
		elseif ($res == true)
		{
			$this->log('Container '.$key.': Authentication successful.', AUTH_LOG_DEBUG);
			return true;
		} 
		if(!$this->enabledOptionalGroupCheck() and $this->server->isMembershipOptional())
		{
			$this->enableOptionalGroupCheck();
			return parent::fetchData($username,$password);
		}
		return false;
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
	 	$this->options['userfilter'] = $this->server->getGroupUserFilter();
	}

	/** 
	 * Called from fetchData after successful login.
	 *
	 * @param string username
	 */
	public function loginObserver($a_username,$a_auth)
	{
		global $ilLog;
		
		$user_data = array_change_key_case($a_auth->getAuthData(),CASE_LOWER);
		
		$a_username = $this->extractUserName($user_data);

		include_once './Services/LDAP/classes/class.ilLDAPUserSynchronisation.php';
		$sync = new ilLDAPUserSynchronisation('ldap', $this->server->getServerId());
		$sync->setExternalAccount($a_username);
		$sync->setUserData($user_data);
		$sync->forceCreation(self::$force_creation);

		try {
			$internal_account = $sync->sync();
		}
		catch(UnexpectedValueException $e) {
			$GLOBALS['ilLog']->write(__METHOD__.': Login failed with message: '. $e->getMessage());
			$a_auth->status = AUTH_WRONG_LOGIN;
			$a_auth->logout();
			return false;
		}
		catch(ilLDAPSynchronisationForbiddenException $e) {
			// No syncronisation allowed => create Error
			$GLOBALS['ilLog']->write(__METHOD__.': Login failed with message: '. $e->getMessage());
			$a_auth->status = AUTH_LDAP_NO_ILIAS_USER;
			$a_auth->logout();
			return false;
		}
		catch(ilLDAPAccountMigrationRequiredException $e) {
			$GLOBALS['ilLog']->write(__METHOD__.': Starting account migration.');
			$a_auth->logout();
			ilUtil::redirect('ilias.php?baseClass=ilStartUpGUI&cmdClass=ilstartupgui&cmd=showAccountMigration');
		}

		$a_auth->setAuth($internal_account);
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
		return false;
	}
	
	/**
	 *  
	 * @param
	 * @return string	ldap username
	 */
	protected function extractUserName($a_user_data)
	{
		$a_username = isset($a_user_data[strtolower($this->server->getUserAttribute())]) ? 
			$a_user_data[strtolower($this->server->getUserAttribute())] :
			trim($a_user_data);

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

	/**
	 * @return bool
	 */
	public function supportsCaptchaVerification()
	{
		return true;
	}
}
?>