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

define('IL_LDAP_BIND_DEFAULT',0);
define('IL_LDAP_BIND_ADMIN',1);
define('IL_LDAP_BIND_TEST',2);

include_once('Services/LDAP/classes/class.ilLDAPAttributeMapping.php');
include_once('Services/LDAP/classes/class.ilLDAPResult.php');
include_once('Services/LDAP/classes/class.ilLDAPQueryException.php');

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesLDAP 
*/
class ilLDAPQuery
{
	private $ldap_server_url = null;
	private $settings = null;
	private $log = null;
	
	private $user_fields = array();
	
	/**
	 * Constructur
	 *
	 * @access private
	 * @param object ilLDAPServer or subclass
	 * @throws ilLDAPQueryException
	 * 
	 */
	public function __construct(ilLDAPServer $a_server,$a_url = '')
	{
		global $ilLog;
		
		$this->settings = $a_server;
		
		if(strlen($a_url))
		{
			$this->ldap_server_url = $a_url;
		}
		else
		{
			$this->ldap_server_url = $this->settings->getUrl();
		}
		
		$this->mapping = ilLDAPAttributeMapping::_getInstanceByServerId($this->settings->getServerId());
		$this->log = $ilLog;
		
		$this->fetchUserProfileFields();
		$this->connect();
	}
	
	/**
	 * Get one user by login name
	 *
	 * @access public
	 * @param string login name
	 * @return array of user data
	 */
	public function fetchUser($a_name)
	{
		$this->user_fields = array_merge(array($this->settings->getUserAttribute()),$this->mapping->getFields());
		
		if(!$this->readUserData($a_name))
		{
			return array();
		}
		else
		{
			return $this->users;
		}
	}
	
	
	/**
	 * Fetch all users 
	 *
	 * @access public
	 * @return array array of user data 
	 */
	public function fetchUsers()
	{
		// First of all check if a group restriction is enabled
		// YES: => fetch all group members
		// No:  => fetch all users
		if(strlen($this->settings->getGroupName()))
		{
			$this->log->write(__METHOD__.': Searching for group members.');

			$groups = $this->settings->getGroupNames();
			if(count($groups) <= 1)
			{
				$this->fetchGroupMembers();
			}			
			else
			{
				foreach($groups as $group)
				{
					$this->fetchGroupMembers($group);
				}
			}			
			
		}
		if(!strlen($this->settings->getGroupName()) or $this->settings->isMembershipOptional())
		{
			$this->log->write(__METHOD__.': Start reading all users...');
			$this->readAllUsers();
			#throw new ilLDAPQueryException('LDAP: Called import of users without specifying group restrictions. NOT IMPLEMENTED YET!');
		}
		return $this->users ? $this->users : array();
	}
	
	/**
	 * Perform a query
	 *
	 * @access public
	 * @param string search base
	 * @param string filter
	 * @param int scope 
	 * @param array attributes
	 * @return object ilLDAPResult
	 * @throws ilLDAPQueryException
	 */
	public function query($a_search_base,$a_filter,$a_scope,$a_attributes)
	{
		$res = $this->queryByScope($a_scope,$a_search_base,$a_filter,$a_attributes);
		if($res === false)
		{
			throw new ilLDAPQueryException(__METHOD__.' '.ldap_error($this->lh).' '.
				sprintf('DN: %s, Filter: %s, Scope: %s',
					$a_search_base,
					$a_filter,
					$a_scope));
		}
		return new ilLDAPResult($this->lh,$res);
	}
	
	/**
	 * Add value to an existing attribute
	 *
	 * @access public
	 * @throws ilLDAPQueryException 
	 */
	public function modAdd($a_dn,$a_attribute)
	{
	 	if(@ldap_mod_add($this->lh,$a_dn,$a_attribute))
	 	{
	 		return true;
	 	}
	 	throw new ilLDAPQueryException(__METHOD__.' '.ldap_error($this->lh));
	}
	
	/**
	 * Delete value from an existing attribute
	 *
	 * @access public
	 * @throws ilLDAPQueryException 
	 */
	public function modDelete($a_dn,$a_attribute)
	{
	 	if(@ldap_mod_del($this->lh,$a_dn,$a_attribute))
	 	{
	 		return true;
	 	}
	 	throw new ilLDAPQueryException(__METHOD__.' '.ldap_error($this->lh));
	}
	
	/**
	 * Fetch all users
	 * This function splits the query to filters like e.g (uid=a*) (uid=b*)...
	 * This avoids AD page_size_limit
	 *
	 * @access public
	 * 
	 */
	private function readAllUsers()
	{
		// Build search base
		if(($dn = $this->settings->getSearchBase()) && substr($dn,-1) != ',')
		{
			$dn .= ',';
		}
		$dn .=	$this->settings->getBaseDN();
		
		// page results
		$filter = $this->settings->getFilter();
		$page_filter = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','-');
		$chars = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		
		foreach($page_filter as $letter)
		{
			$new_filter = '(&';
			$new_filter .= $filter;
			
			switch($letter)
			{
				case '-':
					$new_filter .= ('(!(|');
					foreach($chars as $char)
					{
						$new_filter .= ('('.$this->settings->getUserAttribute().'='.$char.'*)');
					}
					$new_filter .= ')))';
					break;

				default:
					$new_filter .= ('('.$this->settings->getUserAttribute().'='.$letter.'*))');
					break;
			}

			$this->log->write(__METHOD__.': Searching with ldap search and filter '.$new_filter.' in '.$dn);
		 	$res = $this->queryByScope($this->settings->getUserScope(),
		 		$dn,
	 			$new_filter,
				array($this->settings->getUserAttribute()));

			$tmp_result = new ilLDAPResult($this->lh,$res);
			if(!$tmp_result->numRows())
			{
				$this->log->write(__METHOD__.': No users found. Aborting.');
				continue;
			}
			$this->log->write(__METHOD__.': Found '.$tmp_result->numRows().' users.');
			foreach($tmp_result->getRows() as $data)
			{
				if(isset($data[$this->settings->getUserAttribute()]))
				{
					$this->readUserData($data[$this->settings->getUserAttribute()],false,false);
				}
				else
				{
					$this->log->write(__METHOD__.': Unknown error. No user attribute found.');
				}
			}
			unset($tmp_result);
		}
		return true;
	}
	

	/**
	 * Fetch group member ids
	 *
	 * @access public
	 * 
	 */
	private function fetchGroupMembers($a_name = '')
	{
		$group_name = strlen($a_name) ? $a_name : $this->settings->getGroupName();
		
		// Build filter
		$filter = sprintf('(&(%s=%s)%s)',
			$this->settings->getGroupAttribute(),
			$group_name,
			$this->settings->getGroupFilter());
		
		
		// Build search base
		if(($gdn = $this->settings->getGroupDN()) && substr($gdn,-1) != ',')
		{
			$gdn .= ',';
		}
		$gdn .=	$this->settings->getBaseDN();
		
		$this->log->write('LDAP: Using filter '.$filter);
		$this->log->write('LDAP: Using DN '.$gdn);
		$res = $this->queryByScope($this->settings->getGroupScope(),
			$gdn,
			$filter,
			array($this->settings->getGroupMember()));
			
		$tmp_result = new ilLDAPResult($this->lh,$res);
		$group_data = $tmp_result->getRows();
		
		
		if(!$tmp_result->numRows())
		{
			$this->log->write(__METHOD__.': No group found.');
			return false;
		}
				
		$attribute_name = strtolower($this->settings->getGroupMember());
		
		// All groups
		foreach($group_data as $data)
		{
			$this->log->write(__METHOD__.': found '.count($data[$attribute_name]).' group members for group '.$data['dn']);
			if(is_array($data[$attribute_name]))
			{
				foreach($data[$attribute_name] as $name)
				{
					$this->readUserData($name,true,true);
				}
			}
			else
			{
				$this->readUserData($data[$attribute_name],true,true);
			}
		}
		unset($tmp_result);
		return;
	}
	
	/**
	 * Read user data 
	 * @param bool check dn
	 * @param bool use group filter
	 * @access private
	 */
	private function readUserData($a_name,$a_check_dn = true,$a_try_group_user_filter = false)
	{
		$filter = $this->settings->getFilter();
		if($a_try_group_user_filter)
		{
			if($this->settings->isMembershipOptional())
			{
				$filter = $this->settings->getGroupUserFilter();
			}
		}
		
		// Build filter
		if($this->settings->enabledGroupMemberIsDN() and $a_check_dn)
		{
			$dn = $a_name;
			#$res = $this->queryByScope(IL_LDAP_SCOPE_BASE,$dn,$filter,$this->user_fields);

			$fields = array_merge($this->user_fields,array('useraccountcontrol'));
			$res = $this->queryByScope(IL_LDAP_SCOPE_BASE,strtolower($dn),$filter,$fields);
		}
		else
		{
			$filter = sprintf('(&(%s=%s)%s)',
				$this->settings->getUserAttribute(),
				$a_name,
				$filter);

			// Build search base
			if(($dn = $this->settings->getSearchBase()) && substr($dn,-1) != ',')
			{
				$dn .= ',';
			}
			$dn .=	$this->settings->getBaseDN();
			$fields = array_merge($this->user_fields,array('useraccountcontrol'));
			$res = $this->queryByScope($this->settings->getUserScope(),strtolower($dn),$filter,$fields);
		}
		
		
		$tmp_result = new ilLDAPResult($this->lh,$res);
		if(!$tmp_result->numRows())
		{
			$this->log->write('LDAP: No user data found for: '.$a_name);
			unset($tmp_result);
			return false;
		}
		
		if($user_data = $tmp_result->get())
		{
			if(isset($user_data['useraccountcontrol']))
			{
				if(($user_data['useraccountcontrol'] & 0x02))
				{
					$this->log->write(__METHOD__.': '.$a_name.' account disabled.');
					return;
				}
			}
			
			$user_ext = $user_data[strtolower($this->settings->getUserAttribute())];

			// auth mode depends on ldap server settings
			$auth_mode = $this->parseAuthMode();

			$user_data['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount($auth_mode,$user_ext);
			$this->users[$user_ext] = $user_data;
		}
		return true;
	}

	/**
	 * Parse authentication mode
	 * @return string auth mode
	 */
	private function parseAuthMode()
	{
		if($this->settings->isAuthenticationEnabled() or !$this->settings->getAuthenticationMapping())
		{
			return 'ldap';
		}
		return ilAuthUtils::_getAuthModeName($this->settings->getAuthenticationMapping());
	}
	
	/**
	 * Query by scope
	 * IL_SCOPE_SUB => ldap_search
	 * IL_SCOPE_ONE => ldap_list
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function queryByScope($a_scope,$a_base_dn,$a_filter,$a_attributes)
	{
		$a_filter = $a_filter ? $a_filter : "(objectclass=*)";

	 	switch($a_scope)
	 	{
	 		case IL_LDAP_SCOPE_SUB:
	 			$res = @ldap_search($this->lh,$a_base_dn,$a_filter,$a_attributes);
	 			break;
	 			
 			case IL_LDAP_SCOPE_ONE:
	 			$res = @ldap_list($this->lh,$a_base_dn,$a_filter,$a_attributes);
				break;
			
			case IL_LDAP_SCOPE_BASE:

				$res = ldap_read($this->lh,$a_base_dn,$a_filter,$a_attributes);
				break;

			default:
				$this->log->write("LDAP: LDAPQuery: Unknown search scope");
	 	}
		
	 	return $res;
	
	}
	
	/**
	 * Connect to LDAP server
	 *
	 * @access private
	 * @throws ilLDAPQueryException on connection failure.
	 * 
	 */
	private function connect()
	{
		$this->lh = @ldap_connect($this->ldap_server_url);
		
		// LDAP Connect
		if(!$this->lh)
		{
			throw new ilLDAPQueryException("LDAP: Cannot connect to LDAP Server: ".$this->settings->getUrl());
		}
		// LDAP Version
		if(!ldap_set_option($this->lh,LDAP_OPT_PROTOCOL_VERSION,$this->settings->getVersion()))
		{
			throw new ilLDAPQueryException("LDAP: Cannot set version to: ".$this->settings->getVersion());
		}
		// Switch on referrals
		if($this->settings->isActiveReferrer())
		{
			if(!ldap_set_option($this->lh,LDAP_OPT_REFERRALS,true))
			{
				throw new ilLDAPQueryException("LDAP: Cannot switch on LDAP referrals");
			}
			#@ldap_set_rebind_proc($this->lh,'referralRebind');
		}
		else
		{
			ldap_set_option($this->lh,LDAP_OPT_REFERRALS,false);
			$this->log->write(__METHOD__.': Switching referrals to false.');
		}
		// Start TLS
		if($this->settings->isActiveTLS())
		{
			if(!ldap_start_tls($this->lh))
			{
				throw new ilLDAPQueryException("LDAP: Cannot start LDAP TLS");
			}
		}
	}
	
	/**
	 * Bind to LDAP server
	 *
	 * @access public
	 * @param int binding_type IL_LDAP_BIND_DEFAULT || IL_LDAP_BIND_ADMIN
	 * @throws ilLDAPQueryException on connection failure.
	 * 
	 */
	public function bind($a_binding_type = IL_LDAP_BIND_DEFAULT,$a_user_dn = '',$a_password = '')
	{
		switch($a_binding_type)
		{
			case IL_LDAP_BIND_DEFAULT:
				// Now bind anonymously or as user
				if(
					IL_LDAP_BIND_USER == $this->settings->getBindingType() &&
					strlen($this->settings->getBindUser())
				)
				{
					$user = $this->settings->getBindUser();
					$pass = $this->settings->getBindPassword();

					define('IL_LDAP_REBIND_USER',$user);
					define('IL_LDAP_REBIND_PASS',$pass);
					$this->log->write(__METHOD__.': Bind as '.$user);
				}
				else
				{
					$user = $pass = '';
					$this->log->write(__METHOD__.': Bind anonymous');
				}
				break;
			
			case IL_LDAP_BIND_ADMIN:
				$user = $this->settings->getRoleBindDN();
				$pass = $this->settings->getRoleBindPassword();
				
				if(!strlen($user) or !strlen($pass))
				{
					$user = $this->settings->getBindUser();
					$pass = $this->settings->getBindPassword();
				}

				define('IL_LDAP_REBIND_USER',$user);
				define('IL_LDAP_REBIND_PASS',$pass);
				break;
				
			case IL_LDAP_BIND_TEST:
				if(!@ldap_bind($this->lh,$a_user_dn,$a_password))
				{
					return false;
				}
				return true;
				
			default:
				throw new ilLDAPQueryException('LDAP: unknown binding type in: '.__METHOD__);
		}
		
		if(!@ldap_bind($this->lh,$user,$pass))
		{
			throw new ilLDAPQueryException('LDAP: Cannot bind as '.$user);
		}
		else
		{
			$this->log->write(__METHOD__.': Bind successful.');
		}
	}
	
	/**
	 * fetch required fields of user profile data
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function fetchUserProfileFields()
	{
		include_once('Services/LDAP/classes/class.ilLDAPRoleAssignmentRules.php');
		
		$this->user_fields = array_merge(
			array($this->settings->getUserAttribute()),
			array('dn'),
			$this->mapping->getFields(),
			ilLDAPRoleAssignmentRules::getAttributeNames()
		);
	}
	
	
	/**
	 * Unbind
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function unbind()
	{
	 	if($this->lh)
	 	{
	 		@ldap_unbind($this->lh);
	 	}
	}
	
	
	/**
	 * Destructor unbind from ldap server
	 *
	 * @access private
	 * @param
	 * 
	 */
	public function __destruct()
	{
	 	if($this->lh)
	 	{
	 		@ldap_unbind($this->lh);
	 	}
	}
}

function referralRebind($a_ds,$a_url)
{
	global $ilLog;
	
	$ilLog->write('LDAP: Called referralRebind.');
	
	ldap_set_option($a_ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	
	if (!ldap_bind($a_ds,IL_LDAP_REBIND_USER,IL_LDAP_REBIND_PASS))
	{
		$ilLog->write('LDAP: Rebind failed');
  	}
}

?>