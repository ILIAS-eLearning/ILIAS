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

include_once('Services/LDAP/classes/class.ilLDAPServer.php');

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesLDAP 
*/
class ilLDAPRoleGroupMapping
{
	private $log = null;
	private static $instance = null;
	private $servers = null;
	private $mappings = array();
	private $mapping_members = array();
	private $query = array();
	private $active_servers = false;
	
	/**
	 * Singleton contructor
	 *
	 * @access private
	 * 
	 */
	private function __construct()
	{
		global $ilLog;
		
		$this->log = $ilLog;
		$this->initServers();
	}
	
	/**
	 * Get singleton instance of this class
	 *
	 * @access public
	 * 
	 */
	public static function _getInstance()
	{
	 	if(is_object(self::$instance))
	 	{
	 		return self::$instance;	
	 	}
	 	return self::$instance = new ilLDAPRoleGroupMapping();
	}
	
	/**
	 * Get info string for object
	 * If check info type is enabled this function will check if the info string is visible in the repository.
	 *
	 * @access public
	 * @param int object id
	 * @param bool check info type
	 * 
	 */
	public function getInfoStrings($a_obj_id,$a_check_type = false)
	{
	 	if(!$this->active_servers)
	 	{
	 		return false;
	 	}
		if($a_check_type)
		{
		 	if(isset($this->mapping_info_strict[$a_obj_id]) and is_array($this->mapping_info_strict[$a_obj_id]))
	 		{
		 		return $this->mapping_info_strict[$a_obj_id];
			}
		}
		else
		{
		 	if(isset($this->mapping_info[$a_obj_id]) and is_array($this->mapping_info[$a_obj_id]))
		 	{
		 		return $this->mapping_info[$a_obj_id];
		 	}
			
		}
	 	return false;
	}
	
	
	/**
	 * This method is typically called from class RbacAdmin::assignUser()
	 * It checks if there is a role mapping and if the user has auth mode LDAP
	 * After these checks the user is assigned to the LDAP group
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function assign($a_role_id,$a_usr_id)
	{
		// return if there nothing to do
		if(!$this->active_servers)
		{
			return false;
		}
		
	 	if(!$this->isHandledRole($a_role_id))
	 	{
	 		return false;
	 	}
	 	if(!$this->isHandledUser($a_usr_id))
	 	{
		 	$this->log->write('LDAP assign: User ID: '.$a_usr_id.' has no LDAP account');
	 		return false;
	 	}
	 	$this->log->write('LDAP assign: User ID: '.$a_usr_id.' Role Id: '.$a_role_id);
 		$this->assignToGroup($a_role_id,$a_usr_id);

		return true;
	}
	
	/**
	 * Delete role.
	 * This function triggered from ilRbacAdmin::deleteRole
	 * It deassigns all user from the mapped ldap group.
	 *
	 * @access public
	 * @param int role id
	 * 
	 */
	public function deleteRole($a_role_id)
	{
		global $rbacreview;
		
		// return if there nothing to do
		if(!$this->active_servers)
		{
			return false;
		}
		
	 	if(!$this->isHandledRole($a_role_id))
	 	{
	 		return false;
	 	}
	 	
	 	foreach($rbacreview->assignedUsers($a_role_id) as $usr_id)
	 	{
	 		$this->deassign($a_role_id,$usr_id);
	 	}
	 	return true;
	}
	
	
	/**
	 * This method is typically called from class RbacAdmin::deassignUser()
	 * It checks if there is a role mapping and if the user has auth mode LDAP
	 * After these checks the user is deassigned from the LDAP group
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function deassign($a_role_id,$a_usr_id)
	{
		// return if there notzing to do
		if(!$this->active_servers)
		{
			return false;
		}
	 	if(!$this->isHandledRole($a_role_id))
	 	{
	 		return false;
	 	}
	 	if(!$this->isHandledUser($a_usr_id))
	 	{
	 		return false;
	 	}
	 	$this->log->write('LDAP deassign: User ID: '.$a_usr_id.' Role Id: '.$a_role_id);
 		$this->deassignFromGroup($a_role_id,$a_usr_id);
	 	
	 	return true;
	}
	
	/**
	 * Delete user => deassign from all ldap groups
	 *
	 * @access public
	 * @param int user id  
	 */
	public function deleteUser($a_usr_id)
	{
		foreach($this->mappings as $role_id => $data)
		{
			$this->deassign($role_id,$a_usr_id);
		}
		return true;	 	
	}
	
	
	/**
	 * Check if there is any active server with 
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function initServers()
	{
		$server_ids = ilLDAPServer::_getRoleSyncServerIds();
		
		if(!count($server_ids))
		{
			return false;
		}
		
		// Init servers
		include_once('Services/LDAP/classes/class.ilLDAPRoleGroupMappingSettings.php');
		
		$this->active_servers = true;
		$this->mappings = array();
		foreach($server_ids as $server_id)
		{
			$this->servers[$server_id]  = new ilLDAPServer($server_id);
			$this->mappings = ilLDAPRoleGroupMappingSettings::_getAllActiveMappings();
		}
		$this->mapping_info = array();
		$this->mapping_info_strict = array();
		foreach($this->mappings as $mapping)
		{
			foreach($mapping as $key => $data)
			{
				if(strlen($data['info']) and $data['object_id'])
				{
					$this->mapping_info[$data['object_id']][] = $data['info'];
				}
				if(strlen($data['info']) && ($data['info_type'] == ilLDAPRoleGroupMappingSettings::MAPPING_INFO_ALL))
				{
					$this->mapping_info_strict[$data['object_id']][] = $data['info'];
				}
			}
		}
		$this->users = ilObjUser::_getExternalAccountsByAuthMode('ldap',true);
		
		return true;
	}
	
	/**
	 * Check if a role is handled or not
	 *
	 * @access private
	 * @param int role_id
	 * @return int server id or 0 if mapping exists
	 * 
	 */
	private function isHandledRole($a_role_id)
	{
		return array_key_exists($a_role_id,$this->mappings);
	}
	
	/**
	 * Check if user is ldap user
	 *
	 * @access private
	 */
	private function isHandledUser($a_usr_id)
	{
		return array_key_exists($a_usr_id,$this->users);
	}
	
	
	/**
	 * Assign user to group
	 *
	 * @access private
	 * @param int role_id
	 * @param int user_id
	 */
	private function assignToGroup($a_role_id,$a_usr_id)
	{
	 	foreach($this->mappings[$a_role_id] as $data)
	 	{
	 		try
	 		{
	 			if($data['isdn'])
	 			{
					$external_account = $this->readDN($a_usr_id,$data['server_id']);
	 			}
	 			else
	 			{
		 			$external_account = $this->users[$a_usr_id];
	 			}
	 			
	 			// Forcing modAdd since Active directory is too slow and i cannot check if a user is member or not.
	 			#if($this->isMember($external_account,$data))
	 			#{
				#	$this->log->write("LDAP assign: User already assigned to group '".$data['dn']."'");
	 			#}
				#else
				{
					// Add user
			 		$query_obj = $this->getLDAPQueryInstance($data['server_id'],$data['url']);
					$query_obj->modAdd($data['dn'],array($data['member'] => $external_account));
					$this->log->write('LDAP assign: Assigned '.$external_account.' to group '.$data['dn']);		
				}	 			
	 		}
			catch(ilLDAPQueryException $exc)
			{
				$this->log->write($exc->getMessage());
				// try next mapping
				continue;
			}	 		
	 	}
	}
	
	/**
	 * Deassign user from group
	 *
	 * @access private
	 * @param int role_id
	 * @param int user_id
	 * 
	 */
	private function deassignFromGroup($a_role_id,$a_usr_id)
	{
		foreach($this->mappings[$a_role_id] as $data)
	 	{
	 		try
	 		{
	 			if($data['isdn'])
	 			{
					$external_account = $this->readDN($a_usr_id,$data['server_id']);
	 			}
	 			else
	 			{
		 			$external_account = $this->users[$a_usr_id];
	 			}
		 		
				// Check for other role membership
				if($role_id = $this->checkOtherMembership($a_usr_id,$a_role_id,$data))
				{
					$this->log->write('LDAP deassign: User is still assigned to role "'.$role_id.'".');
					continue;
				}
				/*
				if(!$this->isMember($external_account,$data))
		 		{
					$this->log->write("LDAP deassign: User not assigned to group '".$data['dn']."'");
					continue;
		 		}
				*/
				// Deassign user
		 		$query_obj = $this->getLDAPQueryInstance($data['server_id'],$data['url']);
				$query_obj->modDelete($data['dn'],array($data['member'] => $external_account));
				$this->log->write('LDAP deassign: Deassigned '.$external_account.' from group '.$data['dn']);
				
				// Delete from cache
				if(is_array($this->mapping_members[$data['mapping_id']]))
				{
					$key = array_search($external_account,$this->mapping_members[$data['mapping_id']]);
					if($key or $key === 0)
					{
						unset($this->mapping_members[$data['mapping_id']]);
					}
				}
				
	 		}
	 		catch(ilLDAPQueryException $exc)
			{
				$this->log->write($exc->getMessage());
				// try next mapping
				continue;
			}	 		
	 	}	
	}
	
	/**
	 * Check if user is member
	 * 
	 * @access private
	 * @throws ilLDAPQueryException
	 */
	private function isMember($a_uid,$data)
	{
		if(!isset($this->mapping_members["$data[mapping_id]"]))
		{
			// Read members
			try
			{
				$server = $this->servers["$data[server_id]"];
		 		$query_obj = $this->getLDAPQueryInstance($data['server_id'],$server->getUrl());

				// query for members
		 		$res = $query_obj->query($data['dn'],
					'(objectClass=*)',
		 			IL_LDAP_SCOPE_BASE,
		 			array($data['member']));
				
				$this->storeMembers($data['mapping_id'],$res->get());
				unset($res);
			}
			catch(ilLDAPQueryException $exc)
			{
				throw $exc;
			}
		}
		#var_dump("<pre>",$a_uid,$this->mapping_members,"</pre>");
		
		// Now check for membership in stored result
		if(in_array($a_uid,$this->mapping_members["$data[mapping_id]"]))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Check other membership
	 *
	 * @access private
	 * @return string role name
	 * 
	 */
	private function checkOtherMembership($a_usr_id,$a_role_id,$a_data)
	{
		global $rbacreview,$ilObjDataCache;
		
		foreach($this->mappings as $role_id => $tmp_data)
		{
			foreach($tmp_data as $data)
			{
				if($role_id == $a_role_id)
				{
					continue;
				}
				if($data['server_id'] != $a_data['server_id'])
				{
					continue;
				}
				if($data['dn'] != $a_data['dn'])
				{
					continue;
				}
				if($rbacreview->isAssigned($a_usr_id,$role_id))
				{
					return $ilObjDataCache->lookupTitle($role_id);
				}
			}
		}
		return false;
	 	
	}
	
	/**
	 * Store Members
	 *
	 * @access private
	 * 
	 */
	private function storeMembers($a_mapping_id,$a_data)
	{
		$this->mapping_members[$a_mapping_id] = array();
		foreach($a_data as $field => $value)
		{
			if(strtolower($field) == 'dn')
			{
				continue;
			}
			
			if(!is_array($value))
			{
				$this->mapping_members[$a_mapping_id][] = $value;
				continue;
			}
			foreach($value as $external_account)
			{
				$this->mapping_members[$a_mapping_id][] = $external_account;
			}
		}
		return true;
	}
	
	/**
	 * Read DN of user 
	 *
	 * @access private
	 * @param int user id
	 * @param int server id
	 * @throws ilLDAPQueryException
	 */
	private function readDN($a_usr_id,$a_server_id)
	{
		if(isset($this->user_dns[$a_usr_id]))
		{
			return $this->user_dns[$a_usr_id];
		}
		
	 	$external_account = $this->users[$a_usr_id];
	 	
	 	try
	 	{
		 	$server = $this->servers[$a_server_id];
		 	$query_obj = $this->getLDAPQueryInstance($a_server_id,$server->getUrl());
				 		
			if($search_base = $server->getSearchBase())
			{
				$search_base .= ',';
			}
			$search_base .= $server->getBaseDN();
			
			// try optional group user filter first
			if($server->isMembershipOptional() and $server->getGroupUserFilter())
			{
				$userFilter = $server->getGroupUserFilter();
			}
			else
			{
				$userFilter = $server->getFilter();
			}

			$filter = sprintf('(&(%s=%s)%s)',
				$server->getUserAttribute(),
				$external_account,
				$userFilter);

			$res = $query_obj->query($search_base,$filter,$server->getUserScope(),array('dn'));
			
			if(!$res->numRows())
			{
				include_once('Services/LDAP/classes/class.ilLDAPQueryException.php');
				throw new ilLDAPQueryException(__METHOD__.' cannot find dn for user '.$external_account);
			}
			if($res->numRows() > 1)
			{
				include_once('Services/LDAP/classes/class.ilLDAPQueryException.php');
				throw new ilLDAPQueryException(__METHOD__.' found multiple distinguished name for: '.$external_account);
			}
			
			$data = $res->get();
			return $this->user_dns[$a_usr_id] = $data['dn'];
	 	}
	 	catch(ilLDAPQueryException $exc)
	 	{
	 		throw $exc;
	 	}
	}
	
	/**
	 * Get LDAPQueryInstance
	 *
	 * @access private
	 * @param
	 * @throws ilLDAPQueryException
	 */
	private function getLDAPQueryInstance($a_server_id,$a_url)
	{
		include_once 'Services/LDAP/classes/class.ilLDAPQuery.php';

	 	if(array_key_exists($a_server_id,$this->query) and 
	 		array_key_exists($a_url,$this->query[$a_server_id]) and 
	 		is_object($this->query[$a_server_id][$a_url]))
	 	{
	 		return $this->query[$a_server_id][$a_url];
	 	}
	 	try
	 	{
		 	$tmp_query = new ilLDAPQuery($this->servers[$a_server_id],$a_url);
		 	$tmp_query->bind(IL_LDAP_BIND_ADMIN);
	 	}
	 	catch(ilLDAPQueryException $exc)
	 	{
	 		throw $exc;
	 	}
	 	return $this->query[$a_server_id][$a_url] = $tmp_query;
	}
	
}


?>