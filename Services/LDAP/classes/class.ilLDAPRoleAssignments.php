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
	
	private $server_id;
	private $default_role;
	private $all_roles = array();
	private $att_mappings = array();
	
	protected $db;

	/**
	 * Singleton
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function __construct($a_server_id)
	{
	 	global $ilDB;
	 	
	 	$this->server_id = $a_server_id;
	 	$this->db = $ilDB;
	 	
	 	$this->fetchAttributeMappings();
	 	$this->fetchDefaultRole();
	}
	
	/**
	 * get instance by server_id
	 *
	 * @access public
	 * @static
	 *
	 * @param int ldap server id
	 */
	public static function _getInstanceByServerId($a_server_id)
	{
		if(isset(self::$instances[$a_server_id]))
		{
			return self::$instances[$a_server_id];
		}
		return self::$instances[$a_server_id] = new ilLDAPRoleAssignments($a_server_id);
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
		
		$default_role = array('id' => $this->default_role,
	 			'type' => 'Global',
	 			'action' => 'Attach');
		
	 	if(!count($this->att_mappings))
	 	{
	 		$ilLog->write(__METHOD__.': Using default role');
	 		return $default_role;
	 	}
	 	$roles = array();
	 	foreach($this->att_mappings as $name => $values)
	 	{
	 		if(!isset($a_user_att[$name]))
	 		{
	 			continue;
	 		}
	 		$user_val = strtolower($a_user_att[$name]);
	 		if(!isset($this->att_mappings[$name][$user_val]))
	 		{
	 			continue;
	 		}
	 		
	 		$role = $this->att_mappings[$name][$user_val];
	 		$ilLog->write(__METHOD__.': Found role mapping for '.$a_external_name.' => '.ilObject::_lookupTitle($role));
	 		$roles[] = array('id' => $role,
	 			'type' => 'Global',
	 			'action' => 'Attach');
	 	}
	 	return $roles ? $roles : $default_role;
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