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

/** 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @ingroup ServicesLDAP 
*/

include_once('Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php');


class ilLDAPRoleAssignments
{
	private static $instances = array();
	
	private $server = null;
	private $server_id;
	private $default_role;
	private $all_roles = array();
	private $att_mappings = array();
	private $grp_mappings = array();
	
	protected $db;

	/**
	 * Singleton
	 *
	 * @access private
	 * @param object ilLDAPServer
	 * 
	 */
	private function __construct($a_server)
	{
	 	global $ilDB;
	 	
	 	$this->server = $a_server;
	 	$this->server_id = $this->server->getServerId();
	 	$this->db = $ilDB;
	 	
	 	$this->fetchAttributeMappings();
	 	$this->fetchGroupMappings();
	 	$this->fetchDefaultRole();
	}
	
	/**
	 * get instance by server_id
	 *
	 * @access public
	 * @static
	 *
	 * @param object ldap server
	 */
	public static function _getInstanceByServer(ilLDAPServer $a_server)
	{
		$a_server_id = $a_server->getServerId();
		
		if(isset(self::$instances[$a_server_id]))
		{
			return self::$instances[$a_server_id];
		}
		return self::$instances[$a_server_id] = new ilLDAPRoleAssignments($a_server);
	}
	
	/**
	 * Get distinct attribute names 
	 *
	 * @param int LDAP server id
	 * @access public
	 * @static
	 *
	 */
	public static function _getDistinctAttributeNamesByServerId($a_server_id)
	{
		global $ilDB;
		
		$query = "SELECT DISTINCT(att_name) as att FROM ldap_role_assignments ".
			"WHERE type = ".ilLDAPRoleAssignmentRule::TYPE_ATTRIBUTE." ".
			"AND server_id = ".$ilDB->quote($a_server_id)." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$attributes[] = strtolower(trim($row->att));
		}
		return $attributes ? $attributes : array();
	}

	/**
	 * Get possible roles
	 * this array is used for ilUserImportParser::setRoleAssignment
	 *
	 * @access public
	 *
	 * @param array array role_id => role_id
	 */
	public function getPossibleRoles()
	{
		return $this->all_roles ? $this->all_roles : array();
	}
	
	/**
	 * get assigned roles for a specific user
	 *
	 * @access public
	 * @param string external username
	 * @param array aray of ldap user attributes
	 * 
	 */
	public function assignedRoles($a_external_name,$a_user_att)
	{
		global $ilLog;
		
		$default_roles[] = array('id' => $this->default_role,
	 			'type' => 'Global',
	 			'action' => 'Attach');
	 	$ilLog->write(__METHOD__.': Fetch assignable roles...');
	 	foreach($this->att_mappings as $name => $values)
	 	{
	 		if(!isset($a_user_att[$name]))
	 		{
	 			continue;
	 		}
			
			if(!is_array($a_user_att[$name]))
			{
				$attribute_val = array(0 => $a_user_att[$name]);
			}
			else
			{
				$attribute_val = $a_user_att[$name];
			}

			foreach($attribute_val as $value)
			{
				$value = strtolower($value);
		 		if(!isset($this->att_mappings[$name][$value]))
		 		{
	 				continue;
	 			}
	 			else
	 			{
			 		$role = $this->att_mappings[$name][$value];
			 		$ilLog->write(__METHOD__.': Found role mapping for '.$a_external_name.' => '.ilObject::_lookupTitle($role));
			 		$roles[] = array('id' => $role,
			 			'type' => 'Global',
			 			'action' => 'Attach');
			 		break;
	 			}
			}
	 	}
	 	// Check group membership
	 	foreach($this->grp_mappings as $dn => $mapping_data)
	 	{
	 		if($this->isGroupMember($dn,$a_external_name,$a_user_att))
	 		{
		 		$ilLog->write(__METHOD__.': Found LDAP group => role mapping for '.$a_external_name.' => '.ilObject::_lookupTitle($mapping_data['role']));
		 		$roles[] = array('id' => $mapping_data['role'],
	 				'type' => 'Global',
	 				'action' => 'Attach');
	 			
	 		}
	 	}
	 	
	 	return $roles ? $roles : $default_roles;
	}
	
	
	/**
	 * Check if user is member
	 *
	 * @access private
	 * @param string group dn
	 * @param string ldap account
	 * @param array user_data
	 * 
	 */
	private function isGroupMember($a_dn,$a_ldap_account,$a_user_data)
	{
		global $ilLog;
		
		if($this->grp_mappings[$a_dn]['isdn'])
		{
			$user_cmp = $a_user_data['dn'];
		}
		else
		{
			$user_cmp = $a_ldap_account;
		}
		
 		include_once('Services/LDAP/classes/class.ilLDAPQuery.php');
 		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
				
 		
 		try
 		{
	 		$query = new ilLDAPQuery($this->server);
	 		$query->bind();
	 		$res = $query->query($a_dn,
							sprintf('(%s=%s)',
								$this->grp_mappings[$a_dn]['attribute'],
								$user_cmp),
							IL_LDAP_SCOPE_BASE,
							array('dn'));

			return $res->numRows() ? true : false;
 		}
		catch(ilLDAPQueryException $e)
		{
			$ilLog->write(__METHOD__.': Caught Exception: '.$e->getMessage());
			return false;
		}
	}
	
	/**
	 * fetch attribute mappings
	 *
	 * @access private
	 * 
	 */
	private function fetchAttributeMappings()
	{
	 	$query = "SELECT * FROM ldap_role_assignments ".
	 		"WHERE server_id = ".$this->db->quote($this->server_id)." ".
	 		"AND type = ".ilLDAPRoleAssignmentRule::TYPE_ATTRIBUTE." ";
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->att_mappings[strtolower($row->att_name)][strtolower($row->att_value)] = $row->role_id;
	 		$this->all_roles[$row->role_id] = $row->role_id;
	 	}
	}
	
	/**
	 * Fetch group mappings 
	 *
	 * @access private
	 * 
	 */
	private function fetchGroupMappings()
	{
	 	$query = "SELECT * FROM ldap_role_assignments ".
	 		"WHERE server_id = ".$this->db->quote($this->server_id)." ".
	 		"AND type = ".ilLDAPRoleAssignmentRule::TYPE_GROUP." ";
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->grp_mappings[strtolower($row->dn)]['attribute'] = strtolower($row->attribute);
	 		$this->grp_mappings[strtolower($row->dn)]['isdn'] = $row->isdn;
	 		$this->grp_mappings[strtolower($row->dn)]['role'] = $row->role_id;
	 		
	 		$this->all_roles[$row->role_id] = $row->role_id;
	 	}
	 	
	}
	
	
	/**
	 * fetch default role
	 *
	 * @access private
	 * 
	 */
	private function fetchDefaultRole()
	{
	 	include_once('Services/LDAP/classes/class.ilLDAPAttributeMapping.php');
	 	
	 	$this->default_role = ilLDAPAttributeMapping::_lookupGlobalRole($this->server_id);
	 	$this->all_roles[$this->default_role] = $this->default_role;
	}
}

?>