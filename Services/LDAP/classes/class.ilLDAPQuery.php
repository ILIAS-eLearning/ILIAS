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
* @defgroup 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup 
*/
class ilLDAPQuery
{
	private $ldap_server_url = null;
	private $settings = null;
	private $log = null;
	
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
		
		$this->connect();
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
			$this->log->write('LDAP: Searching for group members.');

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
		else
		{
			throw new ilLDAPQueryException('LDAP: Called import of users without specifying group restrictions. NOT IMPLEMENTED YET!');
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
		$this->user_fields = array_merge(array($this->settings->getUserAttribute()),$this->mapping->getFields());
		
		// All groups
		foreach($group_data as $data)
		{
			$this->log->write(__METHOD__.': found '.count($data[$attribute_name]).' group members for group '.$data['dn']);
			if(is_array($data[$attribute_name]))
			{
				foreach($data[$attribute_name] as $name)
				{
					$this->readUserData($name);
				}
			}
			else
			{
				$this->readUserData($data[$attribute_name]);
			}
		}
		unset($tmp_result);
		return;
	}
	
	/**
	 * Read user data 
	 * @access private
	 */
	private function readUserData($a_name)
	{
		// Build filter
		if($this->settings->enabledGroupMemberIsDN())
		{
			$filter = $this->settings->getFilter();
			$dn = $a_name;
			$res = $this->queryByScope(IL_LDAP_SCOPE_BASE,$dn,$filter,$this->user_fields);
		}
		else
		{
			$filter = sprintf('(&(%s=%s)%s)',
				$this->settings->getUserAttribute(),
				$a_name,
				$this->settings->getFilter());

			// Build search base
			if(($dn = $this->settings->getSearchBase()) && substr($dn,-1) != ',')
			{
				$dn .= ',';
			}
			$dn .=	$this->settings->getBaseDN();
			$res = $this->queryByScope($this->settings->getUserScope(),strtolower($dn),$filter,$this->user_fields);
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
			$user_ext = $user_data[strtolower($this->settings->getUserAttribute())];
			$user_data['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount('ldap',$user_ext);
			$this->users[$user_ext] = $user_data;
		}
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
	 	switch($a_scope)
	 	{
	 		case IL_LDAP_SCOPE_SUB:
	 			$this->log->write('LDAP: Scope is: sub, using ldap_search');
	 			$res = ldap_search($this->lh,$a_base_dn,$a_filter,$a_attributes);
	 			break;
	 			
 			case IL_LDAP_SCOPE_ONE:
	 			#$this->log->write('LDAP: Scope is: one, using ldap_list');
	 			$res = @ldap_list($this->lh,$a_base_dn,$a_filter,$a_attributes);
				break;
			
			case IL_LDAP_SCOPE_BASE:
				$res = @ldap_read($this->lh,$a_base_dn,$a_filter,$a_attributes);
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
			@ldap_set_rebind_proc($this->lh,'referralRebind');
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
				if(strlen($this->settings->getBindUser()))
				{
					$user = $this->settings->getBindUser();
					$pass = $this->settings->getBindPassword();

					define('IL_LDAP_REBIND_USER',$user);
					define('IL_LDAP_REBIND_PASS',$pass);
				}
				else
				{
					$user = $pass = '';
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
	
	$ilLog->write('LDAP: Called referralRebind. If someone will see this line please report it to smeyer@databay.de');
	
	ldap_set_option($a_ds, LDAP_OPT_PROTOCOL_VERSION, 3);
	
	if (!ldap_bind($a_ds,IL_LDAP_REBIND_USER,IL_LDAP_REBIND_PASS))
	{
		$ilLog->write('LDAP: Rebind failed');
  	}
}

?>