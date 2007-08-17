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
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesLDAP 
*/
class ilLDAPRoleAssignmentRule
{
	private static $instances = null;
	
	const TYPE_GROUP = 1;
	const TYPE_ATTRIBUTE = 2;
	
	/**
	 * Constructor
	 *
	 * @access private
	 * @param int rule id
	 * 
	 */
	private function __construct($a_id = 0)
	{
	 	global $ilDB;
	 	
	 	$this->db = $ilDB;	

		$this->rule_id = $a_id;
	 	$this->read();
	}
	
	/**
	 * get instance by rule id
	 *
	 * @access public
	 * @static
	 *
	 * @param int rule id
	 */
	public static function _getInstanceByRuleId($a_rule_id)
	{
		if(isset(self::$instances[$a_rule_id]))
		{
			return self::$instances[$a_rule_id];
		}
		return self::$instances[$a_rule_id] = new ilLDAPRoleAssignmentRule($a_rule_id);
	}
	
	
	/**
	 * Get all rules
	 *
	 * @access public
	 * 
	 */
	public function _getRules()
	{
	 	global $ilDB;
	 	
	 	$query = "SELECT rule_id FROM ldap_role_assignments ";
	 	$res = $ilDB->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$rules[] = self::_getInstanceByRuleId($row->rule_id);
	 	}
	 	return $rules ? $rules : array();
	}
	
	/**
	 * set role id
	 *
	 * @access public
	 * @param int role id of global role
	 * 
	 */
	public function setRoleId($a_role_id)
	{
		$this->role_id = $a_role_id; 	
	}
	
	/**
	 * get role id
	 *
	 * @access public
	 * 
	 */
	public function getRoleId()
	{
	 	return $this->role_id;
	}
	
	/**
	 * get id
	 *
	 * @access public
	 * 
	 */
	public function getRuleId()
	{
	 	return $this->rule_id;
	}
	
	/**
	 * set server id
	 *
	 * @access public
	 * @param int server id
	 * 
	 */
	public function setServerId($a_id)
	{
	 	$this->server_id = $a_id;
	}
	
	/**
	 * get server id
	 *
	 * @access public
	 * 
	 */
	public function getServerId()
	{
	 	return $this->server_id;
	}
	
	/**
	 * set type
	 *
	 * @access public
	 * @param int type
	 * 
	 */
	public function setType($a_type)
	{
	 	$this->type = $a_type;
	}
	
	/**
	 * getType
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getType()
	{
	 	return $this->type;
	}
	
	/**
	 * set dn
	 *
	 * @access public
	 * @param string dn
	 * 
	 */
	public function setDN($a_dn)
	{
	 	$this->dn = $a_dn;
	}
	
	/**
	 * get dn
	 *
	 * @access public
	 * 
	 */
	public function getDN()
	{
	 	return $this->dn;
	}
	
	/**
	 * 
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function setMemberAttribute($a_attribute)
	{
	 	$this->member_attribute = $a_attribute;
	}
	
	/**
	 * get attribute
	 *
	 * @access public
	 * 
	 */
	public function getMemberAttribute()
	{
	 	return $this->member_attribute;
	}
	
	/**
	 * set member attribute is dn
	 *
	 * @access public
	 * @param bool status
	 * 
	 */
	public function setMemberIsDN($a_status)
	{
	 	$this->member_is_dn = $a_status;
	}
	
	/**
	 * is member attribute dn
	 *
	 * @access public
	 * 
	 */
	public function isMemberAttributeDN()
	{
	 	return (bool) $this->member_is_dn;
	}
	
	/**
	 * set attribute name
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function setAttributeName($a_name)
	{
	 	$this->attribute_name = $a_name;
	}
	
	/**
	 * get attribute name
	 *
	 * @access public
	 * 
	 */
	public function getAttributeName()
	{
	 	return $this->attribute_name;
	}
	
	/**
	 * set attribute value
	 *
	 * @access public
	 * @param string value
	 * 
	 */
	public function setAttributeValue($a_value)
	{
	 	$this->attribute_value = $a_value;
	}
	
	/**
	 * get atrtibute value
	 *
	 * @access public
	 * 
	 */
	public function getAttributeValue()
	{
	 	return $this->attribute_value;
	}
	
	/**
	 * condition to string
	 *
	 * @access public
	 * 
	 */
	public function conditionToString()
	{
	 	switch($this->getType())
	 	{
	 		case self::TYPE_GROUP:
	 			$dn_arr = explode(',',$this->getDN());
	 			return $dn_arr[0];
	 			
	 		
	 		case self::TYPE_ATTRIBUTE:
	 			return $this->getAttributeName().'='.$this->getAttributeValue();
	 	}
	}
	
	
	/**
	 * create
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function create()
	{
	 	$query = "INSERT INTO ldap_role_assignments ".
	 		"SET server_id = ".$this->db->quote($this->getServerId()).", ".
	 		"type = ".$this->db->quote($this->getType()).", ".
	 		"dn = ".$this->db->quote($this->getDN()).", ".
	 		"attribute = ".$this->db->quote($this->getMemberAttribute()).", ".
	 		"isdn = ".$this->db->quote($this->isMemberAttributeDN()).", ".
	 		"att_name = ".$this->db->quote($this->getAttributeName()).", ".
	 		"att_value = ".$this->db->quote($this->getAttributeValue()).", ".
	 		"role_id = ".(int) $this->getRoleId()." ";
	 	$res = $this->db->query($query);
	 	
	 	$this->rule_id = $this->db->getLastInsertId();
	 	return true;
	}

	/**
	 * update
	 *
	 * @access public
	 * 
	 */
	public function update()
	{
	 	$query = "UPDATE ldap_role_assignments ".
	 		"SET server_id = ".$this->db->quote($this->getServerId()).", ".
	 		"type = ".$this->db->quote($this->getType()).", ".
	 		"dn = ".$this->db->quote($this->getDN()).", ".
	 		"attribute = ".$this->db->quote($this->getMemberAttribute()).", ".
	 		"isdn = ".$this->db->quote($this->isMemberAttributeDN()).", ".
	 		"att_name = ".$this->db->quote($this->getAttributeName()).", ".
	 		"att_value = ".$this->db->quote($this->getAttributeValue()).", ".
	 		"role_id = ".(int) $this->getRoleId()." ".
	 		"WHERE rule_id = ".$this->db->quote($this->getRuleId())." ";
			
	 	$res = $this->db->query($query);
	 	
	 	$this->rule_id = $this->db->getLastInsertId();
	 	return true;
	}
	
	/**
	 * validate
	 *
	 * @access public
	 * 
	 */
	public function validate()
	{
	 	global $ilErr;
	 	
	 	$ilErr->setMessage('');
	 	
	 	if(!$this->getRoleId())
	 	{
			$ilErr->setMessage('fill_out_all_required_fields');
			return false;
	 	}
	 	switch($this->getType())
	 	{
			case self::TYPE_GROUP:
				if(!strlen($this->getDN()) or !strlen($this->getMemberAttribute()))
				{
					$ilErr->setMessage('fill_out_all_required_fields');
					return false;
				}
				break;
			case self::TYPE_ATTRIBUTE:
				if(!strlen($this->getAttributeName()) or !strlen($this->getAttributeValue()))
				{
					$ilErr->setMessage('fill_out_all_required_fields');
					return false;
				}
				break;
				
			default:
				$ilErr->setMessage('ldap_no_type_given');
				return false;
	 	}
		return true;
	}
		
	/**
	 * delete rule
	 *
	 * @access public
	 * 
	 */
	public function delete()
	{
	 	$query = "DELETE FROM ldap_role_assignments ".
	 		"WHERE rule_id = ".$this->db->quote($this->getRuleId())." ";
	 	$this->db->query($query);
	 	return true;
			
	}
	/**
	 * load from db
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
	 	$query = "SELECT * FROM ldap_role_assignments ".
	 		"WHERE rule_id = ".$this->db->quote($this->getRuleId())." ";
		
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
			$this->setServerId($row->server_id);
			$this->setType($row->type);
			$this->setDN($row->dn);
			$this->setMemberAttribute($row->attribute);
			$this->setMemberIsDN($row->isdn);
			$this->setAttributeName($row->att_name);
			$this->setAttributeValue($row->att_value);
			$this->setRoleId($row->role_id);
	 	}
	}
}
?>