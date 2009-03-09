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

include_once('Auth/Container/RADIUS.php');

/** 
* Overwritten Pear class AuthContainerRadius
* This class is overwritten to support to perform Radius authentication with
* specific ILIAS options.
*
* Usage note:
* If you use an ilAuthContainerRadius object as the container for an Auth object
* OTHER THAN ilAuthRadius, you MUST call setEnableObservers(true) on the
* ilAuthContainerRadius object.
* The observers are used to perform actions depending on the success or failure
* of a login attempt.
*
* FIXME - Class ilAuthRadius contains duplicates of the code of this class in the
*       functions loginObserver, and failedLoginObserver. If you do changes in
*       these functions, you MUST do corresponding changes in ilAuthRadius as well.
*       In a future revision of ILIAS, the class ilAuthRadius should be removed.
*       
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesRADIUS
*/
class ilAuthContainerRadius extends Auth_Container_RADIUS
{
	private $radius_settings = null;
	private $rad_to_user = null;
	private $log = null;
	private $force_creation = false;
	/**
	 * Constructor
	 *
	 * @access public
	 * @param array An associative array of pear parameters
	 * 
	 */
	public function __construct($options)
	{
		// Convert password to latin1
		if($this->radius_settings->getCharset() == ilRadiusSettings::RADIUS_CHARSET_LATIN1)
		{
			#$_POST['username'] = utf8_decode($_POST['username']);
			$_POST['password'] = utf8_decode($_POST['password']);
			$this->log->write(__METHOD__.': Decoded username and password to latin1.');
		}
		if(is_array($a_options))
		{
			$options = array_merge($this->radius_settings->toPearAuthArray(),$a_options);
		}
		else
		{
			$options = $this->radius_settings->toPearAuthArray();
		}

        // Get hold of the log
		global $ilLog;
	 	$this->log = $ilLog;


	 	parent::__construct($options);
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
	 * Force creation of user accounts
	 *
	 * @access public
	 * @param bool force_creation
	 *
	 */
	public function forceCreation($a_status)
	{
	 	$this->force_creation = $a_status;
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
		 	$this->log->write('RADIUS PEAR: '.$a_message);
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
		
		$ilBench->start('Auth','RADIUSLoginObserver');
		$user_data = array_change_key_case($this->_auth_obj->getAuthData(),CASE_LOWER);
		
		// user is authenticated
		// Now we trust the username received from ldap and use it as external account name,
		// to avoid problems with leading/trailing whitespace characters
		$a_username = isset($user_data[$this->server->getUserAttribute()]) ?
			$user_data[$this->server->getUserAttribute()] :
			trim($a_username);
		
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
				
				include_once('./Services/RADIUS/classes/class.ilRADIUSRoleAssignments.php');
				$role_ass = ilRADIUSRoleAssignments::_getInstanceByServer($this->server);
				$role_inf = $role_ass->assignedRoles($a_username,$user_data);
				$_SESSION['tmp_roles'] = array();
				foreach($role_inf as $info)
				{
					$_SESSION['tmp_roles'][] = $info['id'];
				}
				$ilBench->stop('Auth','RADIUSLoginObserver');
				ilUtil::redirect('ilias.php?baseClass=ilStartUpGUI&cmdClass=ilstartupgui&cmd=showAccountMigration');
			}

			// Refresh or create user data
			$ilBench->start('Auth','RADIUSUserSynchronization');
			$this->initRADIUSAttributeToUser();
			$this->ldap_attr_to_user->setUserData($users);
			$this->ldap_attr_to_user->refresh();
			$user_data['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount("ldap",$a_username);
			$ilBench->stop('Auth','RADIUSUserSynchronization');
		}

		if(!$user_data['ilInternalAccount'])
		{
			// No syncronisation allowed => create Error
			$this->_auth_obj->status = AUTH_RADIUS_NO_ILIAS_USER;
			$this->_auth_obj->logout();
			$ilBench->stop('Auth','RADIUSLoginObserver');
			return;
		}
		
		
		// Finally setAuth
		$this->_auth_obj->setAuth($user_data['ilInternalAccount']);
		$ilBench->stop('Auth','RADIUSLoginObserver');
		return;
		
	}
	/**
	 * Init RADIUS attribute mapping
	 *
	 * @access private
	 * 
	 */
	private function initRADIUSAttributeToUser()
	{
		include_once('Services/RADIUS/classes/class.ilRADIUSAttributeToUser.php');
		$this->ldap_attr_to_user = new ilRADIUSAttributeToUser($this->server);
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
	
}

?>