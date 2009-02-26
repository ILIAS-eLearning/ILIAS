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

include_once('Auth/Container/LDAP.php');

/** 
* Overwritten Pear class AuthContainerLDAP
* This class is overwritten to support nested groups.
*
* Usage note:
* If you use an ilAuthContainerLDAP object as the container for an Auth object
* OTHER THAN ilAuthLDAP, you MUST call setEnableObservers(true) on the
* ilAuthContainerLDAP object. 
* The observers are used to perform actions depending on the success or failure
* of a login attempt.
*
* FIXME - Class ilAuthLDAP contains duplicates of the code of this class in the 
*       functions loginObserver, and failedLoginObserver. If you do changes in
*       these functions, you MUST do corresponding changes in ilAuthLDAP as well. 
*       In a future revision of ILIAS, the class ilAuthLDAP should be removed.
*       
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesLDAP
*/
class ilAuthContainerLDAP extends Auth_Container_LDAP
{
	private $optional_check = false;
	
	private $log = null;
	private $server = null;
	private $ldap_attr_to_user = null;
        
        /**
         * If this variable is set to true, function fetchData calls
         * function loginObserver on a successful login and function 
         * failedLoginObserver on a failed login.
         * 
         * @var boolean
         */
        private $isObserversEnabled;
        
	/**
	 * Constructor
	 *
	 * @access public
	 * @param array array of pear parameters
	 * 
	 */
	public function __construct(ilLDAPServer $server,$a_params)
	{
		global $ilLog;
		
		$this->server = $server;
	 	parent::__construct($a_params);
	 	$this->log = $ilLog;
	}

	/**
	 * Fetch data from storage container
	 *
	 * @access public
	 */
	function fetchData($username, $password, $isChallengeResponse=false)
	{
		$isSuccessful = parent::fetchData($username, $password, $isChallengeResponse);
		if ($this->isObserversEnabled)
		{
			if ($isSuccessful)
			{
				$this->loginObserver($username);        
			}
			else
			{
				$this->failedLoginObserver();        
			}
		}
		return $isSuccessful;
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
	 * Overwritten debug method
	 * Writes infos to log file
	 *
	 * @access public
	 * @param string message
	 * @param int line
	 * 
	 */
	public function _debug($a_message = '',$a_line = 0)
	{
		if(is_object($this->log))
		{
		 	$this->log->write('LDAP PEAR: '.$a_message);
		}
	 	parent::_debug($a_message,$a_line);
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
	 * Enables/disables the observers of this container.
	 */
        public function setObserversEnabled($boolean) 
	{
	        $this->isObserversEnabled = $boolean;
	}
	
	/** 
	 * Returns true, if the observers of this container are enabled.
	 */
	public function isObserversEnabled() 
	{
		$this->isObserversEnabled;
	}
	
	/** 
	 * Called from fetchData after successful login.
	 *
	 * @param string username
	 */
	public function loginObserver($a_username)
	{
		global $ilBench;
		global $ilLog;
		
		$ilLog->write(__METHOD__.': logged in as '.$a_username.
			', remote:'.$_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'].
			', server:'.$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT']
		);
		
		$ilBench->start('Auth','LDAPLoginObserver');
		$user_data = array_change_key_case($this->_auth_obj->getAuthData(),CASE_LOWER);
		
		$a_username = $this->extractUserName($user_data);

		$user_data['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount("ldap",$a_username);
		$users[$a_username] = $user_data;
		
		
		if($this->server->enabledSyncOnLogin())
		{
			if(!$user_data['ilInternalAccount'] and $this->server->isAccountMigrationEnabled() and !$this->_auth_obj->force_creation)
			{
				$this->_auth_obj->logout();
				$_SESSION['tmp_auth_mode'] = 'ldap';
				$_SESSION['tmp_external_account'] = $a_username;
				$_SESSION['tmp_pass'] = $_POST['password'];
				
				include_once('./Services/LDAP/classes/class.ilLDAPRoleAssignments.php');
				$role_ass = ilLDAPRoleAssignments::_getInstanceByServer($this->server);
				$role_inf = $role_ass->assignedRoles($a_username,$user_data);
				$_SESSION['tmp_roles'] = array();
				foreach($role_inf as $info)
				{
					$_SESSION['tmp_roles'][] = $info['id'];
				}
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
			$this->_auth_obj->status = AUTH_LDAP_NO_ILIAS_USER;
			$this->_auth_obj->logout();
			$ilBench->stop('Auth','LDAPLoginObserver');
			return;
		}
		
		
		// Finally setAuth
		$this->_auth_obj->setAuth($user_data['ilInternalAccount']);
		$ilBench->stop('Auth','LDAPLoginObserver');
		return;
		
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
	 *
	 * @param string username
	 */
	public function failedLoginObserver()
	{
                global $ilLog;
		$ilLog->write(__METHOD__.': login failed'.
			', remote:'.$_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'].
			', server:'.$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT']
		);
                
		if(!$this->enabledOptionalGroupCheck() and $this->server->isMembershipOptional())
		{
			$this->_auth_obj->logout();
			$this->enableOptionalGroupCheck();
			$this->_auth_obj->start();
		}
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
			if(ilObjUser::_checkExternalAuthAccount("ldap",$a_username))
			{
				return trim($name);
			}
		}
		// No existing user found  => return first name
		return $a_username[0];				
	}
	
	
}

?>