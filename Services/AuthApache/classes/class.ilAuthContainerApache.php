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

include_once 'Auth/Container/MDB2.php';

/** 
* Authentication against ILIAS database
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesDatabase
*/
class ilAuthContainerApache extends Auth_Container
{

	public static $force_creation = false;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct($options);
	}
	/*
	public function loginObserver($a_username, $a_auth)
	{
		//var_dump($a_username);
	}
	*/
	public static function forceCreation($value)
	{
		self::$force_creation = $value;
	}
	
	function fetchData($a_username, $password, $isChallengeResponse=false)
	{	//var_dump(func_get_args());
		//var_dump($_SERVER);
		global $lng;
		$settings = new ilSetting('apache_auth');
		
		if (!$settings->get('apache_enable_auth'))
		{
			return false;
		}
		if (!$settings->get('apache_auth_indicator_name') || !$settings->get('apache_auth_indicator_value'))
		{
			return false;
		}
		if (!ilUtil::isLogin($a_username))
                {
                        return false;
                }

		if ($a_username == 'anonymous' && $password == 'anonymous') {
				global $ilDB;
				$query = 'SELECT * FROM usr_data WHERE login = %s';
				$qres = $ilDB->queryF($query, array('text'), array($a_username));
				
				$userRow = $ilDB->fetchAssoc($qres);
			
				if (is_array($userRow) && $userRow['usr_id'])
				{
					// user as a local account...
					// fetch logindata
					$this->activeUser = $userRow['login'];
					foreach ($userRow as $key => $value) {
		                if ($key == $this->options['passwordcol'] || $key == $this->options['usernamecol']) {
		                    continue;
		                }
		                // Use reference to the auth object if exists
		                // This is because the auth session variable can change so a static call to setAuthData does not make sense
		                $this->_auth_obj->setAuthData($key, $value);
		            }
		            //var_dump($userRow);
		            $this->_auth_obj->setAuth($userRow['login']);
		            return true;
				}
				return false;
		}
		
		if (!$_SESSION['login_invalid'] && $_SERVER[$settings->get('apache_auth_indicator_name')] == $settings->get('apache_auth_indicator_value'))
		{
			// we have a valid apache auth
			global $ilDB;

			if ($settings->get('apache_enable_local'))
			{
				$query = 'SELECT * FROM usr_data WHERE login = %s OR (auth_mode = %s AND ext_account = %s)';
				$qres = $ilDB->queryF($query, array('text', 'text', 'text'), array($a_username, 'apache', $a_username));
				
				$userRow = $ilDB->fetchAssoc($qres);
			
				if (is_array($userRow) && $userRow['usr_id'])
				{
					// user as a local account...
					// fetch logindata
					$this->activeUser = $userRow['login'];
					foreach ($userRow as $key => $value) {
		                if ($key == $this->options['passwordcol'] || $key == $this->options['usernamecol']) {
		                    continue;
		                }
		                // Use reference to the auth object if exists
		                // This is because the auth session variable can change so a static call to setAuthData does not make sense
		                $this->_auth_obj->setAuthData($key, $value);
		            }
		            //var_dump($userRow);
		            $this->_auth_obj->setAuth($userRow['login']);
		            return true;
				}
			}
				
			// if no local user has been found AND ldap lookup is enabled
			if($settings->get('apache_enable_ldap'))
			{
				include_once 'Services/LDAP/classes/class.ilLDAPServer.php';
				$this->server = new ilLDAPServer(ilLDAPServer::_getFirstActiveServer());
				$this->server->doConnectionCheck();
								
				$config = $this->server->toPearAuthArray();
								
				$query = new ilLDAPQuery($this->server);
				$ldapUser = $query->fetchUser($a_username);

				if ($ldapUser && $ldapUser[$a_username] && $ldapUser[$a_username][$config['userattr']] == $a_username)
				{
					$ldapUser[$a_username]['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount("apache",$a_username);
					$user_data = $ldapUser[$a_username];//array_change_key_case($a_auth->getAuthData(),CASE_LOWER);
					if($this->server->enabledSyncOnLogin())
					{
						if(!$user_data['ilInternalAccount'] && $this->server->isAccountMigrationEnabled() && !self::$force_creation)
						{
							$this->_auth_obj->logout();
							$_SESSION['tmp_auth_mode'] = 'apache';
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
							
							ilUtil::redirect('ilias.php?baseClass=ilStartUpGUI&cmdClass=ilstartupgui&cmd=showAccountMigration');
							exit;
						}

						if($this->updateRequired($a_username))
						{	
							$this->initLDAPAttributeToUser();
							$this->ldap_attr_to_user->setUserData($ldapUser);
							$this->ldap_attr_to_user->refresh();
							$user_data['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount("apache",$a_username);
						}
						else
						{
							// User exists and no update required
							$user_data['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount("apache",$a_username);
						}
					}
					if ($user_data['ilInternalAccount'])
					{
						$this->_auth_obj->setAuth($user_data['ilInternalAccount']);
						return true;
					}
				}
			}

			if ($settings->get('apache_enable_local') && $settings->get('apache_local_autocreate'))
			{
				// no local user, no ldap match or ldap not activated
//				if (!self::$force_creation)
//				{
//					$_SESSION['tmp_auth_mode'] = 'apache';
//					$_SESSION['tmp_external_account'] = $a_username;
//					$_SESSION['tmp_pass'] = $_POST['password'];
					//ilUtil::redirect('https://lernwelt.janposselt.de/ilias.php?baseClass=ilStartUpGUI&cmdClass=ilstartupgui&cmd=showAccountMigration');
//				}
//				else
//				{
					global $ilIliasIniFile;
					if ($_GET['r'])
						$_SESSION['profile_complete_redirect'] = $_GET['r'];

					$user = new ilObjUser();
					$user->setLogin($a_username);
					$user->setExternalAccount($a_username);
					$user->setProfileIncomplete(true);
					$user->create();
					$user->setAuthMode('apache');
					// set a timestamp for last_password_change
					// this ts is needed by the ACCOUNT_SECURITY_MODE_CUSTOMIZED
					// in ilSecuritySettings
					$user->setLastPasswordChangeTS( time() );
					$user->setTimeLimitUnlimited(1);
	
					$user->setActive(1);
					//insert user data in table user_data
					$user->saveAsNew();
					$user->writePrefs();
					global $rbacadmin;
					$rbacadmin->assignUser($settings->get('apache_default_role', 4),$user->getId(),true);
					return true;
//				}
			}
		}
		else if (defined('IL_CERT_SSO') && IL_CERT_SSO) {
			define('APACHE_ERRORCODE', AUTH_APACHE_FAILED);
		}
		
		return false;
	}
	
	/**
	 * Check if an update is required
	 * @return 
	 * @param string $a_username
	 */
	protected function updateRequired($a_username)
	{
		if(!ilObjUser::_checkExternalAuthAccount("apache",$a_username))
		{
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
	
}
?>