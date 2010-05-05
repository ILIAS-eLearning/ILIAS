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
* Shibboleth role assignment rule
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup AuthShibboleth
*/
class ilShibbolethRoleAssignmentRule
{
	const ERR_MISSING_NAME = 'shib_missing_attr_name';
	const ERR_MISSING_VALUE = 'shib_missing_attr_value';
	const ERR_MISSING_ROLE = 'shib_missing_role';
	const ERR_MISSING_PLUGIN_ID = 'shib_missing_plugin_id';
	
	protected $db = null;
	
	private $rule_id = 0;
	private $role_id = 0;
	private $attribute_name = '';
	private $attribute_value = '';
	private $plugin_active = false;
	private $add_on_update = false;
	private $remove_on_update = false;
	private $plugin_id = 0;

	public function __construct($a_rule_id = 0)
	{
		global $ilDB;
		
		$this->db = $ilDB;
		
		$this->rule_id = $a_rule_id;
		$this->read();
	}
	
	public function setRuleId($a_id)
	{
		$this->rule_id = $a_id;
	}
	
	public function getRuleId()
	{
		return $this->rule_id;
	}
	
	public function setRoleId($a_id)
	{
		$this->role_id = $a_id;
	}
	
	public function getRoleId()
	{
		return $this->role_id;
	}
	
	public function setName($a_name)
	{
		$this->attribute_name = $a_name;
	}
	
	public function getName()
	{
		return $this->attribute_name;
	}
	
	public function setValue($a_value)
	{
		$this->attribute_value = $a_value;
	}
	
	public function getValue()
	{
		return $this->attribute_value;
	}
	
	public function enablePlugin($a_status)
	{
		$this->plugin_active = $a_status;
	}
	
	public function isPluginActive()
	{
		return (bool) $this->plugin_active;
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
	
	public function conditionToString()
	{
		global $lng;
		
		if($this->isPluginActive())
		{
			return $lng->txt('shib_plugin_id').': '.$this->getPluginId();
		}
		else
		{
			return $this->getName().'='.$this->getValue();	
		}
	}
	
	public function validate()
	{
		if(!$this->getRoleId())
		{
			return self::ERR_MISSING_ROLE;
		}
		
		if(!$this->isPluginActive())
		{
			if(!$this->getName())
			{
				return self::ERR_MISSING_NAME;
			}
			if(!$this->getValue())
			{
				return self::ERR_MISSING_VALUE;
			}
		}
		else
		{
			// check plugin id is given
			if(!$this->getPluginId())
			{
				return self::ERR_MISSING_PLUGIN_ID;
			}
		}
		
		return '';
	}
	
	public function delete()
	{
		global $ilDB;;
		
		$query = "DELETE FROM shib_role_assignment ".
			"WHERE rule_id = ".$this->db->quote($this->getRuleId() , 'integer');
		$ilDB->manipulate($query);
		return true;
	}
	
	public function add()
	{
		global $ilDB;
		
		$next_id = $ilDB->nextId('shib_role_assignment');
		$query = "INSERT INTO shib_role_assignment (rule_id,role_id,name,value,plugin,plugin_id,add_on_update,remove_on_update ) ".
			"VALUES( ". 
			$ilDB->quote($next_id,'integer').', '.
			$this->db->quote($this->getRoleId(),'integer').', '.
			$this->db->quote($this->getName(),'text').', '.
			$this->db->quote($this->getValue(),'text').', '.
			$this->db->quote((int) $this->isPluginActive(),'integer').', '.
			$this->db->quote((int) $this->getPluginId(),'integer').', '.
			$this->db->quote((int) $this->isAddOnUpdateEnabled(),'integer').', '.
			$this->db->quote((int) $this->isRemoveOnUpdateEnabled(),'integer').
			') ';
		$ilDB->manipulate($query);
		
		$this->setRuleId($this->db->getLastInsertId());
		return true;
	}
	
	public function update()
	{
		global $ilDB;
		
		$query = "UPDATE shib_role_assignment ".
			"SET role_id = ".$this->db->quote($this->getRoleId(),'integer').', '.
			"name = ".$this->db->quote($this->getName(),'text').', '.
			"value = ".$this->db->quote($this->getValue(),'text').', '.
			"plugin = ".$this->db->quote((int) $this->isPluginActive(),'integer').', '.
			"plugin_id = ".$this->db->quote((int) $this->getPluginId(),'integer').', '.
			"add_on_update = ".$this->db->quote((int) $this->isAddOnUpdateEnabled(),'integer').', '.
			"remove_on_update = ".$this->db->quote((int) $this->isRemoveOnUpdateEnabled(),'integer').' '.
			"WHERE rule_id = ".$this->db->quote($this->getRuleId(),'integer');
		$ilDB->manipulate($query);
		
		return true;
	}
	
	public function matches($a_data)
	{
		if($this->isPluginActive())
		{
			include_once './Services/AuthShibboleth/classes/class.ilShibbolethRoleAssignmentRules.php';
			return ilShibbolethRoleAssignmentRules::callPlugin($this->getPluginId(),$a_data);
		}
		// No value
		if(!isset($a_data[$this->getName()]))
		{
			return false;
		}
		
		$values = $a_data[$this->getName()];
		
		if(is_array($values))
		{
			return in_array($this->getValue(),$values);
		}
		else
		{
			return $this->wildcardCompare($this->getValue(),$values);
			#return $this->getValue() == $values;
		}
	}
	
	protected function wildcardCompare($a_str1, $a_str2)
	{
		$pattern = str_replace('*','.*?', $a_str1);
		$GLOBALS['ilLog']->write(__METHOD__.': Replace pattern:'. $pattern.' => '.$a_str2);
		return (bool) preg_match('/^'.$pattern.'$/i',$a_str2);
	}
	
	
	private function read()
	{
		if(!$this->getRuleId())
		{
			return true;
		}

		$query = "SELECT * FROM shib_role_assignment ".
			"WHERE rule_id = ".$this->db->quote($this->getRuleId(),'integer');
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setRoleId($row->role_id);
			$this->setName($row->name);
			$this->setValue($row->value);
			$this->enablePlugin($row->plugin);
			$this->setPluginId($row->plugin_id);
			$this->enableAddOnUpdate($row->add_on_update);
			$this->enableRemoveOnUpdate($row->remove_on_update);
		}
	}
	
}
?>