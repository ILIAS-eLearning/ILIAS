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

define('IL_LDAP_BIND_ANONYMOUS',0);
define('IL_LDAP_BIND_USER',1);

define('IL_LDAP_SCOPE_SUB',0);
define('IL_LDAP_SCOPE_ONE',1);
define('IL_LDAP_SCOPE_BASE',2);

/** 
* @defgroup ServicesLDAP Services/LDAP
*/

/** 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesLDAP
*/
class ilLDAPServer
{
	const DEBUG = false;
	const DEFAULT_VERSION = 3;
	
	private $server_id = null;

	public function __construct($a_server_id = 0)
	{
		global $ilDB,$lng;

		$this->db = $ilDB;
		$this->lng = $lng;
		$this->server_id = $a_server_id;
		
		$this->read();
	}
	
	/** 
	 * Get active server list
	 *
	 * @return array server ids of active ldap server
	 */
	public static function _getActiveServerList() 
	{
		global $ilDB;
		
		$query = "SELECT server_id FROM ldap_server_settings ".
			"WHERE active = 1 ".
			"ORDER BY name ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$server_ids[] = $row->server_id;
		}
		return $server_ids ? $server_ids : array();
	}
	
	/**
	 * Get list of acticve servers with option 'SyncCron'
	 *
	 * @return array server ids of active ldap server
	 */
	public static function _getCronServerIds()
	{
		global $ilDB;
		
		$query = "SELECT server_id FROM ldap_server_settings ".
			"WHERE active = 1 ".
			"AND sync_per_cron = 1 ".
			"ORDER BY name";
			
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$server_ids[] = $row->server_id;
		}
		return $server_ids ? $server_ids : array();
	}
	
	/**
	 * Check whether there if there is an active server with option role_sync_active
	 *
	 * @access public
	 * @param
	 * 
	 */
	public static function _getRoleSyncServerIds()
	{
		global $ilDB;
		
		$query = "SELECT server_id FROM ldap_server_settings ".
			"WHERE active = 1 ".
			"AND role_sync_active = 1 ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$server_ids[] = $row->server_id;
		}
		return $server_ids ? $server_ids : array();
	}
	
	/**
	 * Checks whether password synchronistation is enabled for an user
	 *
	 * @access public
	 * @param int user_id
	 * 
	 */
	public static function _getPasswordServers()
	{
 		return ilLDAPServer::_getActiveServerList();
	}
	
	
	/** 
	 * Get first active server
	 *
	 * @return int first active server
	 */
	public static function _getFirstActiveServer() 
	{
		$servers = ilLDAPServer::_getActiveServerList();
		if(count($servers))
		{
			return $servers[0];
		}
		return 0;
	}

	/**
	 * Get list of all configured servers
	 * 
	 * @return array list of server ids
	 */
	public static function _getServerList()
	{
		global $ilDB;
		
		$query = "SELECT server_id FROM ldap_server_settings ORDER BY name";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$server_ids[] = $row->server_id;
		}
		return $server_ids ? $server_ids : array();
	}
	
	/* 
	 * Get first server id
	 *
	 * @return integer server_id
	 */
	public static function _getFirstServer() 
	{
		$servers = ilLDAPServer::_getServerList();
		
		if(count($servers))
		{
			return $servers[0];
		}
		return 0;
	}	
	
	// Set/Get
	public function getServerId()
	{
		return $this->server_id;
	}
	
	
    public function toggleActive($a_status) 
    {
        $this->active = $a_status;
    }
	public function isActive()
	{
		return $this->active;
	}    
    public function getUrl() 
    {
        return $this->url;
    }
    public function setUrl($a_url) 
    {
        $this->url = $a_url;
    }
    public function getName() 
    {
        return $this->name;
    }
    public function setName($a_name) 
    {
        $this->name = $a_name;
    }
    public function getVersion() 
    {
        return $this->version ? $this->version : self::DEFAULT_VERSION;
    }
    public function setVersion($a_version) 
    {
        $this->version = $a_version;
    }
    public function getBaseDN() 
    {
        return $this->base_dn;
    }
    public function setBaseDN($a_base_dn) 
    {
        $this->base_dn = $a_base_dn;
    }
	public function isActiveReferrer() 
	{
		return $this->referrals ? true : false;
	}
	public function toggleReferrer($a_status)
	{
		$this->referrals = $a_status; 
	}
	public function isActiveTLS()
	{
		return $this->tls ? true : false;
	}
	public function toggleTLS($a_status)
	{
		$this->tls = $a_status;
	}
	public function getBindingType()
	{
		return $this->binding_type;
	}
	public function setBindingType($a_type)
	{
		if($a_type == IL_LDAP_BIND_USER)
		{
			$this->binding_type = IL_LDAP_BIND_USER;
		}
		else
		{
			$this->binding_type = IL_LDAP_BIND_ANONYMOUS;
		}
	}
	public function getBindUser()
	{
		return $this->bind_user;
	}
	public function setBindUser($a_user)
	{
		$this->bind_user = $a_user;
	}
	public function getBindPassword()
	{
		return $this->bind_password;
	}
	public function setBindPassword($a_password)
	{
		$this->bind_password = $a_password;
	}
	public function getSearchBase()
	{
		return $this->search_base;
	}
	public function setSearchBase($a_search_base)
	{
		$this->search_base = $a_search_base;
	}
	public function getUserAttribute()
	{
		return $this->user_attribute;
	}
	public function setUserAttribute($a_user_attr)
	{
		$this->user_attribute = $a_user_attr;
	}
	public function getFilter()
	{
		return $this->prepareFilter($this->filter);
	}
	public function setFilter($a_filter)
	{
		$this->filter = $a_filter;
	}
	public function getGroupDN()
	{
		return $this->group_dn;
	}
	public function setGroupDN($a_value)
	{
		$this->group_dn = $a_value;
	}
	public function getGroupFilter()
	{
		return $this->prepareFilter($this->group_filter);
	}
	public function setGroupFilter($a_value)
	{
		$this->group_filter = $a_value;
	}
	public function getGroupMember()
	{
		return $this->group_member;
	}
	public function setGroupMember($a_value)
	{
		$this->group_member = $a_value;
	}
	public function getGroupName()
	{
		return $this->group_name;
	}
	public function setGroupName($a_value)
	{
		$this->group_name = $a_value;
	}
	/**
	 * Get group names as array
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getGroupNames()
	{
	 	$names = explode(',',$this->getGroupName());
		 	
		if(!is_array($names))
		{
			return array();
		}
		foreach($names as $name)
		{
			$new_names[] = trim($name);
		}
		return $new_names;
	}
	
	
	public function getGroupAttribute()
	{
		return $this->group_attribute;
	}
	public function setGroupAttribute($a_value)
	{
		$this->group_attribute = $a_value;
	}
	
	public function toggleMembershipOptional($a_status)
	{
		$this->group_optional = (bool) $a_status;	 	
	}
	public function isMembershipOptional()
	{
		return (bool) $this->group_optional;
	}
	public function setGroupUserFilter($a_filter)
	{
		$this->group_user_filter = $a_filter;
	}
	public function getGroupUserFilter()
	{
		return $this->group_user_filter;
	}

	public function enabledGroupMemberIsDN()
	{
		return (bool) $this->memberisdn;
	}
	public function enableGroupMemberIsDN($a_value)
	{
		$this->memberisdn = (bool) $a_value;
	} 
	public function setGroupScope($a_value)
	{
		$this->group_scope = $a_value;
	}
	public function getGroupScope()
	{
		return $this->group_scope;
	}
	public function setUserScope($a_value)
	{
		$this->user_scope = $a_value;
	}
	public function getUserScope()
	{
		return $this->user_scope;
	}
	public function enabledSyncOnLogin()
	{
		return $this->sync_on_login;
	}
	public function enableSyncOnLogin($a_value)
	{
		$this->sync_on_login = (int) $a_value;
	}
	public function enabledSyncPerCron()
	{
		return $this->sync_per_cron;
	}
	public function enableSyncPerCron($a_value)
	{
		$this->sync_per_cron = (int) $a_value;
	}
	public function setGlobalRole($a_role)
	{
		$this->global_role = $a_role;
	}
	public function getRoleBindDN()
	{
		return $this->role_bind_dn;
	}
	public function setRoleBindDN($a_value)
	{
		$this->role_bind_dn = $a_value;
	}
	public function getRoleBindPassword()
	{
		return $this->role_bind_pass;
	}
	public function setRoleBindPassword($a_value)
	{
		$this->role_bind_pass = $a_value;
	}
	public function enabledRoleSynchronization()
	{
		return $this->role_sync_active;
	}
	public function enableRoleSynchronization($a_value)
	{
		$this->role_sync_active = $a_value;
	}
	
	/** 
	 * Validate user input
	 * @param 
	 * @return boolean
	 */
	public function validate() 
	{
		global $ilErr;
		
		$ilErr->setMessage('');
		if(!strlen($this->getName()) ||
			!strlen($this->getUrl()) ||
			!strlen($this->getBaseDN()) ||
			!strlen($this->getUserAttribute()))
		{
			$ilErr->setMessage($this->lng->txt('fill_out_all_required_fields'));
		}
		
		if($this->getBindingType() == IL_LDAP_BIND_USER
			&& (!strlen($this->getBindUser()) || !strlen($this->getBindPassword())))
		{
			$ilErr->appendMessage($this->lng->txt('ldap_missing_bind_user'));
		}
		
		if(($this->enabledSyncPerCron() or $this->enabledSyncOnLogin()) and !$this->global_role)
		{
			$ilErr->appendMessage($this->lng->txt('ldap_missing_role_assignment'));
		}
		if($this->getVersion() == 2 and $this->isActiveTLS())
		{
			$ilErr->appendMessage($this->lng->txt('ldap_tls_conflict'));
		}
		
		return strlen($ilErr->getMessage()) ? false : true;
	}
	
	public function create() 
	{
		$query = "INSERT INTO  ldap_server_settings SET ".
			"active = ".$this->db->quote($this->isActive()).", ".
			"name = ".$this->db->quote($this->getName()).", ".
			"url = ".$this->db->quote($this->getUrl()).", ".
			"version = ".$this->db->quote($this->getVersion()).", ".
			"base_dn = ".$this->db->quote($this->getBaseDN()).", ".
			"referrals = ".$this->db->quote($this->isActiveReferrer()).", ".
			"tls = ".$this->db->quote($this->isActiveTLS()).", ".
			"bind_type = ".$this->db->quote($this->getBindingType()).", ".
			"bind_user = ".$this->db->quote($this->getBindUser()).", ".
			"bind_pass = ".$this->db->quote($this->getBindPassword()).", ".
			"search_base = ".$this->db->quote($this->getSearchBase()).", ".
			"user_scope = ".$this->db->quote($this->getUserScope()).", ".
			"user_attribute = ".$this->db->quote($this->getUserAttribute()).", ".
			"filter = ".$this->db->quote($this->getFilter())." ";
			"group_dn = ".$this->db->quote($this->getGroupDN()).", ".
			"group_scope = ".$this->db->quote($this->getGroupScope()).", ".
			"group_filter = ".$this->db->quote($this->getGroupFilter()).", ".
			"group_member = ".$this->db->quote($this->getGroupMember()).", ".
			"group_memberisdn =".$this->db->quote((int) $this->enabledGroupMemberIsDN()).", ".
			"group_name = ".$this->db->quote($this->getGroupName()).", ".
			"group_attribute = ".$this->db->quote($this->getGroupAttribute()).", ".
			"group_optional = ".$this->db->quote((int) $this->isMembershipOptional()).", ".
			"group_user_filter = ".$this->db->quote($this->getGroupUserFilter()).", ".
			"sync_on_login = ".$this->db->quote($this->enabledSyncOnLogin() ? 1 : 0).", ".
			"sync_per_cron = ".$this->db->quote($this->enabledSyncPerCron() ? 1 : 0).", ".
			"role_sync_active = ".$this->db->quote($this->enabledRoleSynchronization()).", ".
			"role_bind_dn = ".$this->db->quote($this->getRoleBindDN()).", ".
			"role_bind_pass = ".$this->db->quote($this->getRoleBindPassword())." ";
			
			
			
			
		$this->db->query($query);
		return $this->db->getLastInsertId();
	}
	
	public function update()
	{
		$query = "UPDATE ldap_server_settings SET ".
			"active = ".$this->db->quote($this->isActive()).", ".
			"name = ".$this->db->quote($this->getName()).", ".
			"url = ".$this->db->quote($this->getUrl()).", ".
			"version = ".$this->db->quote($this->getVersion()).", ".
			"base_dn = ".$this->db->quote($this->getBaseDN()).", ".
			"referrals = ".$this->db->quote($this->isActiveReferrer()).", ".
			"tls = ".$this->db->quote($this->isActiveTLS()).", ".
			"bind_type = ".$this->db->quote($this->getBindingType()).", ".
			"bind_user = ".$this->db->quote($this->getBindUser()).", ".
			"bind_pass = ".$this->db->quote($this->getBindPassword()).", ".
			"search_base = ".$this->db->quote($this->getSearchBase()).", ".
			"user_scope = ".$this->db->quote($this->getUserScope()).", ".
			"user_attribute = ".$this->db->quote($this->getUserAttribute()).", ".
			"filter = ".$this->db->quote($this->getFilter()).", ".
			"group_dn = ".$this->db->quote($this->getGroupDN()).", ".
			"group_scope = ".$this->db->quote($this->getGroupScope()).", ".
			"group_filter = ".$this->db->quote($this->getGroupFilter()).", ".
			"group_member = ".$this->db->quote($this->getGroupMember()).", ".
			"group_memberisdn =".$this->db->quote((int) $this->enabledGroupMemberIsDN()).", ".
			"group_name = ".$this->db->quote($this->getGroupName()).", ".
			"group_attribute = ".$this->db->quote($this->getGroupAttribute()).", ".
			"group_optional = ".$this->db->quote((int) $this->isMembershipOptional()).", ".
			"group_user_filter = ".$this->db->quote($this->getGroupUserFilter()).", ".
			"sync_on_login = ".$this->db->quote($this->enabledSyncOnLogin() ? 1 : 0).", ".
			"sync_per_cron = ".$this->db->quote($this->enabledSyncPerCron() ? 1 : 0).", ".
			"role_sync_active = ".$this->db->quote($this->enabledRoleSynchronization()).", ".
			"role_bind_dn = ".$this->db->quote($this->getRoleBindDN()).", ".
			"role_bind_pass = ".$this->db->quote($this->getRoleBindPassword())." ".
			"WHERE server_id = ".$this->db->quote($this->getServerId());

		$this->db->query($query);
		return true;		
	}
	
	/** 
	 * Creates an array of options compatible to PEAR Auth
	 *
	 * @return array auth settings
	 */
	public function toPearAuthArray() 
	{
		$options = array(
			'url'		=> $this->getUrl(),
			'version'	=> (int) $this->getVersion(),
			'referrals'	=> (bool) $this->isActiveReferrer());
		
		if($this->getBindingType() == IL_LDAP_BIND_USER)
		{
			$options['binddn'] = $this->getBindUser();
			$options['bindpw'] = $this->getBindPassword();
		}			
		$options['basedn'] = $this->getBaseDN();
		$options['start_tls'] = (bool) $this->isActiveTLS();
		$options['userdn'] = $this->getSearchBase();
		switch($this->getUserScope())
		{
			case IL_LDAP_SCOPE_ONE:
				$options['userscope'] = 'one';
				break;
			default:
				$options['userscope'] = 'sub';
				break;
		}
		
		$options['userattr'] = $this->getUserAttribute();
		$options['userfilter'] = $this->getFilter();
		$options['attributes'] = $this->getPearAtributeArray();
		$options['debug'] = self::DEBUG;


		switch($this->getGroupScope())
		{
			case IL_LDAP_SCOPE_BASE:
				$options['groupscope'] = 'base';
				break;
			case IL_LDAP_SCOPE_ONE:
				$options['groupscope'] = 'one';
				break;
			default:
				$options['groupscope'] = 'sub';
				break;
		}
		$options['groupdn'] = $this->getGroupDN();
		$options['groupattr'] = $this->getGroupAttribute();
		$options['groupfilter'] = $this->getGroupFilter();
		$options['memberattr'] = $this->getGroupMember();
		$options['memberisdn'] = $this->enabledGroupMemberIsDN();
		$options['group'] = $this->getGroupName();
		
		
		return $options;
	}
	
	/**
	 * Create brackets for filters if they do not exist
	 *
	 * @access private
	 * @param string filter
	 * 
	 */
	private function prepareFilter($a_filter)
	{
		$filter = trim($a_filter);
		
		if(!strlen($filter))
		{
			return $filter;
		}
		
		if(strpos($filter,'(') !== 0)
		{
			$filter = ('('.$filter);
		}
	 	if(substr($filter,-1) != ')')
	 	{
	 		$filter = ($filter.')');
	 	}
	 	return $filter;
	}
	
	/**
	 * Get attribute array for pear auth data
	 *
	 * @access private
	 * @param 
	 * 
	 */
	private function getPearAtributeArray()
	{
		if($this->enabledSyncOnLogin())
		{
			include_once('Services/LDAP/classes/class.ilLDAPAttributeMapping.php');
			$mapping = ilLDAPAttributeMapping::_getInstanceByServerId($this->getServerId());
	 		return array_merge(array($this->getUserAttribute()),$mapping->getFields());
		}
		else
		{
			return array($this->getUserAttribute());
		}	
	}
	
	
	
	/**
	 * Read server settings
	 *
	 */
	private function read()
	{
		if(!$this->server_id)
		{
			return true;
		}
		$query = "SELECT * FROM ldap_server_settings WHERE server_id = ".$this->db->quote($this->server_id)."";
#		var_dump("<pre>",$query,"</pre>");
		
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->toggleActive($row->active);
			$this->setName($row->name);
			$this->setUrl($row->url);
			$this->setVersion($row->version);
			$this->setBaseDN($row->base_dn);
			$this->toggleReferrer($row->referrals);
			$this->toggleTLS($row->tls);
			$this->setBindingType($row->bind_type);
			$this->setBindUser($row->bind_user);
			$this->setBindPassword($row->bind_pass);
			$this->setSearchBase($row->search_base);
			$this->setUserScope($row->user_scope);
			$this->setUserAttribute($row->user_attribute);
			$this->setFilter($row->filter);
			$this->setGroupDN($row->group_dn);
			$this->setGroupScope($row->group_scope);
			$this->setGroupFilter($row->group_filter);
			$this->setGroupMember($row->group_member);
			$this->setGroupAttribute($row->group_attribute);
			$this->toggleMembershipOptional($row->group_optional);
			$this->setGroupUserFilter($row->group_user_filter);
			$this->enableGroupMemberIsDN($row->group_memberisdn);
			$this->setGroupName($row->group_name);
			$this->enableSyncOnLogin($row->sync_on_login);
			$this->enableSyncPerCron($row->sync_per_cron);
			$this->enableRoleSynchronization($row->role_sync_active);
			$this->setRoleBindDN($row->role_bind_dn);
			$this->setRoleBindPassword($row->role_bind_pass);
		}
	}
}
?>