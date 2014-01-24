<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

define('IL_LDAP_BIND_ANONYMOUS',0);
define('IL_LDAP_BIND_USER',1);

define('IL_LDAP_SCOPE_SUB',0);
define('IL_LDAP_SCOPE_ONE',1);
define('IL_LDAP_SCOPE_BASE',2);

/** 
* @defgroup ServicesLDAP Services/LDAP
*/

/** 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesLDAP
*/
class ilLDAPServer
{
	private static $instances = array();
	
	const DEBUG = false;
	const DEFAULT_VERSION = 3;
	
	private $role_bind_dn = '';
	private $role_bind_pass = '';
	private $role_sync_active = 0;
	
	private $server_id = null;
	private $fallback_urls = array();

	private $enabled_authentication = true;
	private $authentication_mapping = 0;

	public function __construct($a_server_id = 0)
	{
		global $ilDB,$lng;

		$this->db = $ilDB;
		$this->lng = $lng;
		$this->server_id = $a_server_id;
		
		$this->read();
	}
	
	public static function getInstanceByServerId($a_server_id)
	{
		if(isset(self::$instances[$a_server_id]))
		{
			return self::$instances[$a_server_id];
		}
		return self::$instances[$a_server_id] = new ilLDAPServer($a_server_id);
	}
	
	/**
	 * Check if ldap module is installed
	 * @return 
	 */
	public static function checkLDAPLib()
	{
		return function_exists('ldap_bind');
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
			"WHERE active = 1 AND authentication = 1 ".
			"ORDER BY name ";
		$res = $ilDB->query($query);
		$server_ids = array();
		while($row = $ilDB->fetchObject($res))
		{
			$server_ids[] = $row->server_id;
		}
		return $server_ids;
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
		while($row = $ilDB->fetchObject($res))
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
		$server_ids = array();
		while($row = $ilDB->fetchObject($res))
		{
			$server_ids[] = $row->server_id;
		}
		return $server_ids;
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
		while($row = $ilDB->fetchObject($res))
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


	public static function getAvailableDataSources($a_auth_mode)
	{
		global $ilDB;

		$query = "SELECT server_id FROM ldap_server_settings ".
			"WHERE active = ".$ilDB->quote(1,'integer')." ".
			"AND authentication = ".$ilDB->quote(0,'integer')." ".
			"AND ( authentication_type = ".$ilDB->quote($a_auth_mode,'integer')." ".
			"OR authentication_type = ".$ilDB->quote(0,'integer').")";
		$res = $ilDB->query($query);

		$server_ids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$server_ids[] = $row->server_id;
		}
		return $server_ids;
	}

	/**
	 * Check if a data source is active for a specific auth mode
	 * @global ilDB $ilDB
	 * @param int $a_auth_mode
	 * @return bool
	 */
	public static function isDataSourceActive($a_auth_mode)
	{
		global $ilDB;

		$query = "SELECT server_id FROM ldap_server_settings ".
			"WHERE authentication_type = ".$ilDB->quote($a_auth_mode,'integer')." ".
			"AND authentication = ".$ilDB->quote(0,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return true;
		}
		return false;
	}

	public static function getDataSource($a_auth_mode)
	{
		global $ilDB;

		$query = "SELECT server_id FROM ldap_server_settings ".
			"WHERE authentication_type = ".$ilDB->quote($a_auth_mode,'integer')." ".
			"AND authentication = ".$ilDB->quote(0,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->server_id;
		}
		return 0;
	}

	/**
	 * Toggle Data Source
	 * @todo handle multiple ldap servers
	 * @param int $a_auth_mode
	 * @param int $a_status
	 */
	public static function toggleDataSource($a_auth_mode,$a_status)
	{
		global $ilDB;

		if($a_status)
		{
			$query = "UPDATE ldap_server_settings ".
				"SET authentication_type = ".$ilDB->quote($a_auth_mode,'integer')." ".
				"WHERE authentication = ".$ilDB->quote(0,'integer');
			$ilDB->query($query);
		}
		else
		{
			$query = "UPDATE ldap_server_settings ".
				"SET authentication_type = ".$ilDB->quote(0,'integer')." ".
				"WHERE authentication = ".$ilDB->quote(0,'integer');
			$ilDB->query($query);
		}
		return true;
	}
	
	// Set/Get
	public function getServerId()
	{
		return $this->server_id;
	}

	/**
	 * Enable authentication for this ldap server
	 * @param bool $a_status
	 */
	public function enableAuthentication($a_status)
	{
		$this->enabled_authentication = (bool) $a_status;
	}

	/**
	 * Check if authentication is enabled
	 * @return bool
	 */
	public function isAuthenticationEnabled()
	{
		return (bool) $this->enabled_authentication;
	}

	/**
	 * Set mapped authentication mapping
	 * @param int $a_map
	 */
	public function setAuthenticationMapping($a_map)
	{
		$this->authentication_mapping = $a_map;
	}

	/**
	 * Get authentication mode that is mapped
	 * @return int
	 */
	public function getAuthenticationMapping()
	{
		return $this->authentication_mapping;
	}

	/**
	 * Get authentication mapping key
	 * Default is ldap
	 * @return string
	 */
	public function getAuthenticationMappingKey()
	{
		if($this->isAuthenticationEnabled() or !$this->getAuthenticationMapping())
		{
			return 'ldap';
		}
		return ilAuthUtils::_getAuthModeName($this->getAuthenticationMapping());
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
        $this->url_string = $a_url;
        
        // Maybe there are more than one url's (comma seperated). 
		$urls = explode(',',$a_url);
		
		$counter = 0;
		foreach($urls as $url)
		{
			$url = trim($url);
			if(!$counter++)
			{
				$this->url = $url;
			}
			else
			{
				$this->fallback_urls[] = $url;
			} 
		}
    }
    public function getUrlString()
    {
    	return $this->url_string;
    }
    
    /**
	 * Check ldap connection and do a fallback to the next server 
	 * if no connection is possible.
	 *
	 * @access public
	 * 
	 */
	public function doConnectionCheck()
	{
	 	global $ilLog;
	 	
	 	include_once('Services/LDAP/classes/class.ilLDAPQuery.php');
	 	
	 	foreach(array_merge(array(0 => $this->url),$this->fallback_urls) as $url)
	 	{
			try
			{
				// Need to do a full bind, since openldap return valid connection links for invalid hosts 
				$query = new ilLDAPQuery($this,$url);
				$query->bind();
				$this->url = $url;
		 		$ilLog->write(__METHOD__.': Using url: '.$url.'.');
				return true;
			}
			catch(ilLDAPQueryException $exc)
			{
		 		$ilLog->write(__METHOD__.': Cannot connect to LDAP server: '.$url.'. Trying fallback...');
			}
	 	}
 		$ilLog->write(__METHOD__.': No valid LDAP server found.');
		return false;
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
	 * Enable account migration
	 *
	 * @access public
	 * @param bool status
	 * 
	 */
	public function enableAccountMigration($a_status)
	{
	 	$this->account_migration = $a_status;
	}
	
	/**
	 * enabled account migration
	 *
	 * @access public
	 * 
	 */
	public function isAccountMigrationEnabled()
	{
	 	return $this->account_migration ? true : false;
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
		global $ilDB;
		
		$next_id = $ilDB->nextId('ldap_server_settings');
		
		$query = 'INSERT INTO ldap_server_settings (server_id,active,name,url,version,base_dn,referrals,tls,bind_type,bind_user,bind_pass,'.
			'search_base,user_scope,user_attribute,filter,group_dn,group_scope,group_filter,group_member,group_memberisdn,group_name,'.
			'group_attribute,group_optional,group_user_filter,sync_on_login,sync_per_cron,role_sync_active,role_bind_dn,role_bind_pass,migration, '.
			'authentication,authentication_type) '.
			'VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)';
		$res = $ilDB->queryF($query,
			array(
				'integer','integer','text','text','integer','text','integer','integer','integer','text','text','text','integer',
				'text','text','text','integer','text','text','integer','text','text','integer','text','integer','integer','integer',
				'text','text', 'integer','integer','integer'),
			array(
				$next_id,
				$this->isActive(),
				$this->getName(),
				$this->getUrlString(),
				$this->getVersion(),
				$this->getBaseDN(),
				$this->isActiveReferrer(),
				$this->isActiveTLS(),
				$this->getBindingType(),
				$this->getBindUser(),
				$this->getBindPassword(),
				$this->getSearchBase(),
				$this->getUserScope(),
				$this->getUserAttribute(),
				$this->getFilter(),
				$this->getGroupDN(),
				$this->getGroupScope(),
				$this->getGroupFilter(),
				$this->getGroupMember(),
				$this->enabledGroupMemberIsDN(),
				$this->getGroupName(),
				$this->getGroupAttribute(),
				$this->isMembershipOptional(),
				$this->getGroupUserFilter(),
				$this->enabledSyncOnLogin(),
				$this->enabledSyncPerCron(),
				$this->enabledRoleSynchronization(),
				$this->getRoleBindDN(),
				$this->getRoleBindPassword(),
				$this->isAccountMigrationEnabled(),
				$this->isAuthenticationEnabled(),
				$this->getAuthenticationMapping()
			));
			
		return $next_id;
	}
	
	public function update()
	{
		global $ilDB;

		$query = "UPDATE ldap_server_settings SET ".
			"active = ".$this->db->quote($this->isActive(),'integer').", ".
			"name = ".$this->db->quote($this->getName(),'text').", ".
			"url = ".$this->db->quote($this->getUrlString(),'text').", ".
			"version = ".$this->db->quote($this->getVersion(),'integer').", ".
			"base_dn = ".$this->db->quote($this->getBaseDN(),'text').", ".
			"referrals = ".$this->db->quote($this->isActiveReferrer(),'integer').", ".
			"tls = ".$this->db->quote($this->isActiveTLS(),'integer').", ".
			"bind_type = ".$this->db->quote($this->getBindingType(),'integer').", ".
			"bind_user = ".$this->db->quote($this->getBindUser(),'text').", ".
			"bind_pass = ".$this->db->quote($this->getBindPassword(),'text').", ".
			"search_base = ".$this->db->quote($this->getSearchBase(),'text').", ".
			"user_scope = ".$this->db->quote($this->getUserScope(),'integer').", ".
			"user_attribute = ".$this->db->quote($this->getUserAttribute(),'text').", ".
			"filter = ".$this->db->quote($this->getFilter(),'text').", ".
			"group_dn = ".$this->db->quote($this->getGroupDN(),'text').", ".
			"group_scope = ".$this->db->quote($this->getGroupScope(),'integer').", ".
			"group_filter = ".$this->db->quote($this->getGroupFilter(),'text').", ".
			"group_member = ".$this->db->quote($this->getGroupMember(),'text').", ".
			"group_memberisdn =".$this->db->quote((int) $this->enabledGroupMemberIsDN(),'integer').", ".
			"group_name = ".$this->db->quote($this->getGroupName(),'text').", ".
			"group_attribute = ".$this->db->quote($this->getGroupAttribute(),'text').", ".
			"group_optional = ".$this->db->quote((int) $this->isMembershipOptional(),'integer').", ".
			"group_user_filter = ".$this->db->quote($this->getGroupUserFilter(),'text').", ".
			"sync_on_login = ".$this->db->quote(($this->enabledSyncOnLogin() ? 1 : 0),'integer').", ".
			"sync_per_cron = ".$this->db->quote(($this->enabledSyncPerCron() ? 1 : 0),'integer').", ".
			"role_sync_active = ".$this->db->quote($this->enabledRoleSynchronization(),'integer').", ".
			"role_bind_dn = ".$this->db->quote($this->getRoleBindDN(),'text').", ".
			"role_bind_pass = ".$this->db->quote($this->getRoleBindPassword(),'text').", ".
			"migration = ".$this->db->quote((int)$this->isAccountMigrationEnabled(),'integer').", ".
			'authentication = '.$this->db->quote((int) $this->isAuthenticationEnabled(),'integer').', '.
			'authentication_type = '.$this->db->quote((int) $this->getAuthenticationMapping(),'integer').' '.
			"WHERE server_id = ".$this->db->quote($this->getServerId(),'integer');
			
		$res = $ilDB->manipulate($query);
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
		
		if(@include_once('Log.php'))
		{
			if(@include_once('Log/observer.php'))
			{
				$options['enableLogging'] = true;
			}	
		}
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
			include_once('Services/LDAP/classes/class.ilLDAPRoleAssignmentRules.php');
			$mapping = ilLDAPAttributeMapping::_getInstanceByServerId($this->getServerId());
	 		return array_merge(
				array($this->getUserAttribute()),
	 			$mapping->getFields(),
	 			array('dn'),
				ilLDAPRoleAssignmentRules::getAttributeNames()
			);
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
			$this->enableAccountMigration($row->migration);
			$this->enableAuthentication($row->authentication);
			$this->setAuthenticationMapping($row->authentication_type);
		}
	}
}
?>