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
* This class stores the settings that define the mapping between LDAP attribute and user profile fields. 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesLDAP 
*/
class ilLDAPAttributeMapping
{
	private static $instances = array();
	private $server_id = null;
	private $db = null;
	private $mapping_rules = array();
	private $rules_for_update = array();
	private $lng;

	/**
	 * Private constructor (Singleton for each server_id)
	 *
	 * @access private
	 * 
	 */
	private function __construct($a_server_id)
	{
		global $ilDB,$lng;
		
		$this->db = $ilDB;
		$this->lng = $lng;
		$this->server_id = $a_server_id;
		$this->read(); 	
	}
	
	/**
	 * Get instance of class
	 *
	 * @access public
	 * @param int server_id
	 * 
	 */
	public static function _getInstanceByServerId($a_server_id)
	{
	 	if(array_key_exists($a_server_id,self::$instances) and is_object(self::$instances[$a_server_id]))
	 	{
	 		return self::$instances[$a_server_id];
	 	}
		return self::$instances[$a_server_id] = new ilLDAPAttributeMapping($a_server_id);
	}
	

	/**
	 * Delete mapping rules by server id
	 *
	 * @access public
	 * @param int server id
	 * 
	 */
	public static function _delete($a_server_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM ldap_attribute_mapping ".
			"WHERE server_id =".$ilDB->quote($a_server_id);
		$res = $ilDB->query($query);
	}
	
	/**
	 * Lookup global role assignment
	 *
	 * @access public
	 * @param
	 * 
	 */
	public static function _lookupGlobalRole($a_server_id)
	{
	 	global $ilDB;
	 	
	 	$query = "SELECT value FROM ldap_attribute_mapping ".
	 		"WHERE server_id =".$ilDB->quote($a_server_id)." ".
	 		"AND keyword = ".$ilDB->quote('global_role');

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return (int) $row->value;		
		}
		return 0;
	}

	/**
	 * Set mapping rule
	 *
	 * @access public
	 * @param string ILIAS user attribute
	 * @param string ldap attribute
	 * @param bool perform update
	 * 
	 */
	public function setRule($a_field_name,$a_ldap_attribute,$a_perform_update)
	{
	 	$this->mapping_rules[$a_field_name]['value'] = $a_ldap_attribute;
	 	$this->mapping_rules[$a_field_name]['performUpdate'] = $a_perform_update;
	}
	
	/**
	 * Get all mapping rules with option 'update'
	 *
	 * @access public
	 * @return array mapping rules. E.g. array('firstname' => 'name',...)
	 * 
	 */
	public function getRulesForUpdate()
	{
	 	return $this->rules_for_update ? $this->rules_for_update : array();
	}
	
	/**
	 * Get field names of all mapping rules with option 'update' 
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getFieldsForUpdate()
	{
		foreach($this->rules_for_update as $field => $rule)
		{
			if(!strlen($rule['value']))
			{
				continue;
			}
			if(strpos($rule['value'],',') === false)
			{
				$fields[] = strtolower($rule['value']);
				continue;
			}
		 	$tmp_fields = explode(',',$rule['value']);
			$value = '';
		 	foreach($tmp_fields as $tmp_field)
	 		{
				$fields[] = trim(strtolower($tmp_field));
	 		}
	 	}
		return $fields ? $fields : array();
	}
	
	/**
	 * Get all mapping fields
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getFields()
	{
	 	foreach($this->mapping_rules as $field => $rule)
	 	{
			if(!strlen($rule['value']))
			{
				continue;
			}
			if(strpos($rule['value'],',') === false)
			{
				$fields[] = strtolower($rule['value']);
				continue;
			}
		 	$tmp_fields = explode(',',$rule['value']);
			$value = '';
		 	foreach($tmp_fields as $tmp_field)
	 		{
				$fields[] = trim(strtolower($tmp_field));
	 		}
	 	}
		return $fields ? $fields : array();
	}
	
	/**
	 * Get all rules
	 *
	 * @access public
	 * @return array mapping rules. E.g. array('firstname' => 'name',...)
	 * 
	 */
	public function getRules()
	{
		return $this->mapping_rules;
	}
	
	/**
	 * Clear rules => Does not perform an update
	 *
	 * @access public
	 * 
	 */
	public function clearRules()
	{
	 	$this->mapping_rules = array();
	}
	
	/**
	 * Save mapping rules to db
	 *
	 * @access public
	 * 
	 */
	public function save()
	{
	 	$this->delete();
	 	
	 	foreach($this->mapping_rules as $keyword => $options)
	 	{
	 		$query = "INSERT INTO ldap_attribute_mapping SET ".
	 			"server_id =".$this->db->quote($this->server_id).", ".
	 			"keyword = ".$this->db->quote($keyword).", ".
	 			"value = ".$this->db->quote($options['value']).", ".
	 			"perform_update = ".$this->db->quote($options['performUpdate']);
	 		$this->db->query($query);
	 	}
	}
	
	/**
	 * Delete all entries
	 *
	 * @access public
	 * 
	 */
	public function delete()
	{
		self::_delete($this->server_id);
	}
	
	/**
	 * Check whether an update should be performed on a specific user attribute or not
	 *
	 * @access public
	 * @param string ILIAS user attribute
	 * @return bool
	 * 
	 */
	public function enabledUpdate($a_field_name)
	{
	 	if(array_key_exists($a_field_name,$this->mapping_rules))
	 	{
	 		return (bool) $this->mapping_rules[$a_field_name]['performUpdate'];
	 	}
	 	return false;
	}
	
	/**
	 * Get LDAP attribute name by given ILIAS profile field
	 *
	 * @access public
	 * @param string ILIAS user attribute
	 * @return string LDAP attribute name
	 */
	public function getValue($a_field_name)
	{
	 	if(array_key_exists($a_field_name,$this->mapping_rules))
	 	{
	 		return $this->mapping_rules[$a_field_name]['value'];
	 	}
	 	return '';
	}	
	
	/**
	 * Read mapping setttings from db
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function read()
	{
	 	$query = "SELECT * FROM ldap_attribute_mapping ".
	 		"WHERE server_id =".$this->db->quote($this->server_id)." ";
	 		
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->mapping_rules[$row->keyword]['value'] = $row->value;
			$this->mapping_rules[$row->keyword]['performUpdate'] = (bool) $row->perform_update;
			
			if($row->perform_update)
			{
				$this->rules_for_update[$row->keyword]['value'] = $row->value;
			}
		}			
	}
}
?>