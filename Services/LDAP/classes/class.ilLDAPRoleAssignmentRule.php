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
* @author Stefan Meyer <meyer@leifos.com>
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
	const TYPE_PLUGIN = 3;
	
	private $server_id = 0;
	private $plugin_active = false;
	private $add_on_update = false;
	private $remove_on_update = false;
	private $plugin_id = 0;
	
	
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
	 * Check if there any rule for updates
	 * @return 
	 */
	public static function hasRulesForUpdate()
	{
		global $ilDB;
		
		$query = 'SELECT COUNT(*) num FROM ldap_role_assignments '.
			'WHERE add_on_update = 1 '.
			'OR remove_on_update = 1 ';
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		return $row->num > 0;
	}
	
	/**
	 * Check if a rule matches
	 * @return 
	 * @param object $a_user_data
	 */
	public function matches($a_user_data)
	{
		global $ilLog;
		
		switch($this->getType())
		{
			case self::TYPE_PLUGIN:
				include_once './Services/LDAP/classes/class.ilLDAPRoleAssignmentRules.php';
				return ilLDAPRoleAssignmentRules::callPlugin($this->getPluginId(), $a_user_data);
				
			case self::TYPE_ATTRIBUTE:
				
				$attn = strtolower($this->getAttributeName());
				
				if(!isset($a_user_data[$attn]))
				{
					return false;
				}

				if(!is_array($a_user_data[$attn]))
				{
					$attribute_val = array(0 => $a_user_data[$attn]);
				}
				else
				{
					$attribute_val = $a_user_data[$attn];
				}
				
				foreach($attribute_val as $value)
				{
					if($this->wildcardCompare(trim($this->getAttributeValue()),trim($value)))
					{
				 		$ilLog->write(__METHOD__.': Found role mapping: '.ilObject::_lookupTitle($this->getRoleId()));
						return true;
					}
					/*					
					if(trim($value) == trim($this->getAttributeValue()))
					{
				 		$ilLog->write(__METHOD__.': Found role mapping: '.ilObject::_lookupTitle($this->getRoleId()));
						return true;
					}
					*/
				}
				return false;

			case self::TYPE_GROUP:
				return $this->isGroupMember($a_user_data);
				
		}
	}
	
	protected function wildcardCompare($a_str1, $a_str2)
	{
		$pattern = str_replace('*','.*?', $a_str1);
		$GLOBALS['ilLog']->write(__METHOD__.': Replace pattern:'. $pattern.' => '.$a_str2);
		return (bool) preg_match('/^'.$pattern.'$/i',$a_str2);
	}
	
	/**
	 * Check if user is member of specific group
	 *
	 * @access private
	 * @param array user data
	 * @param array user_data
	 * 
	 */
	private function isGroupMember($a_user_data)
	{
		global $ilLog;
		
		
		if($this->isMemberAttributeDN())
		{
			$user_cmp = $a_user_data['dn'];
		}
		else
		{
			$user_cmp = $a_user_data['ilExternalAccount'];
		}
		
 		include_once('Services/LDAP/classes/class.ilLDAPQuery.php');
 		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
				
		$server = ilLDAPServer::getInstanceByServerId(ilLDAPServer::_getFirstActiveServer());
 		
 		try
 		{
	 		$query = new ilLDAPQuery($server);
	 		$query->bind();
	 		$res = $query->query($this->getDN(),
								sprintf('(%s=%s)',
								$this->getMemberAttribute(),
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
	
	public function enableAddOnUpdate($a_status)
	{
		$this->add_on_update = $a_status;
	}
	
	public function isAddOnUpdateEnabled()
	{
		return (bool) $this->add_on_update;
	}
	
	public function enableRemoveOnUpdate($a_status)
	{
		$this->remove_on_update = $a_status;
	}
	
	public function isRemoveOnUpdateEnabled()
	{
		return (bool) $this->remove_on_update;
	}
	
	public function setPluginId($a_id)
	{
		$this->plugin_id = $a_id;
	}
	
	public function getPluginId()
	{
		return $this->plugin_id;
	}
	
	public function isPluginActive()
	{
		return (bool) $this->getType() == self::TYPE_PLUGIN;
	}
	
	
	/**
	 * condition to string
	 *
	 * @access public
	 * 
	 */
	public function conditionToString()
	{
	 	global $lng;
		
		switch($this->getType())
	 	{
	 		case self::TYPE_PLUGIN:
				return $lng->txt('ldap_plugin_id').': '.$this->getPluginId();
			
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
		global $ilDB;
		
		$next_id = $ilDB->nextId('ldap_role_assignments');

	 	$query = "INSERT INTO ldap_role_assignments (server_id,rule_id,type,dn,attribute,isdn,att_name,att_value,role_id, ".
			"add_on_update, remove_on_update, plugin_id ) ".
	 		"VALUES( ".
			$this->db->quote($this->getServerId(),'integer').", ".
			$this->db->quote($next_id,'integer').", ".
	 		$this->db->quote($this->getType(),'integer').", ".
	 		$this->db->quote($this->getDN(),'text').", ".
	 		$this->db->quote($this->getMemberAttribute(),'text').", ".
	 		$this->db->quote($this->isMemberAttributeDN(),'integer').", ".
	 		$this->db->quote($this->getAttributeName(),'text').", ".
	 		$this->db->quote($this->getAttributeValue(),'text').", ".
	 		$this->db->quote($this->getRoleId(),'integer').", ".
			$this->db->quote($this->isAddOnUpdateEnabled(), 'integer').', '.
			$this->db->quote($this->isRemoveOnUpdateEnabled(), 'integer').', '.
			$this->db->quote($this->getPluginId(),'integer').' '.
	 		")";
		$res = $ilDB->manipulate($query);
		$this->rule_id = $next_id;
			 	
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
	 	global $ilDB;
	 	
	 	$query = "UPDATE ldap_role_assignments ".
	 		"SET server_id = ".$this->db->quote($this->getServerId(),'integer').", ".
	 		"type = ".$this->db->quote($this->getType(),'integer').", ".
	 		"dn = ".$this->db->quote($this->getDN(),'text').", ".
	 		"attribute = ".$this->db->quote($this->getMemberAttribute(),'text').", ".
	 		"isdn = ".$this->db->quote($this->isMemberAttributeDN(),'integer').", ".
	 		"att_name = ".$this->db->quote($this->getAttributeName(),'text').", ".
	 		"att_value = ".$this->db->quote($this->getAttributeValue(),'text').", ".
	 		"role_id = ".$this->db->quote($this->getRoleId(),'integer').", ".
			"add_on_update = ".$this->db->quote($this->isAddOnUpdateEnabled(),'integer').', '.
			'remove_on_update = '.$this->db->quote($this->isRemoveOnUpdateEnabled(),'integer').', '.
			'plugin_id = '.$this->db->quote($this->getPluginId(),'integer').' '.
	 		"WHERE rule_id = ".$this->db->quote($this->getRuleId(),'integer')." ";
		$res = $ilDB->manipulate($query);			
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
				
			case self::TYPE_PLUGIN:
				if(!$this->getPluginId())
				{
					$ilErr->setMessage('ldap_err_missing_plugin_id');
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
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM ldap_role_assignments ".
	 		"WHERE rule_id = ".$this->db->quote($this->getRuleId(),'integer')." ";
		$res = $ilDB->manipulate($query);
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
	 	global $ilDB;
	 	
	 	$query = "SELECT * FROM ldap_role_assignments ".
	 		"WHERE rule_id = ".$this->db->quote($this->getRuleId(),'integer')." ";
		
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
			$this->enableAddOnUpdate($row->add_on_update);
			$this->enableRemoveOnUpdate($row->remove_on_update);
			$this->setPluginId($row->plugin_id);
	 	}
	}
}
?>