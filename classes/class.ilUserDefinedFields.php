<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Class ilLPObjSettings
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @package common
*
*/
define('UDF_TYPE_TEXT',1);
define('UDF_TYPE_SELECT',2);
define('UDF_NO_VALUES',1);
define('UDF_DUPLICATE_VALUES',2);

class ilUserDefinedFields
{
	var $db = null;
	var $definitions = array();
	

	function ilUserDefinedFields()
	{
		global $ilDB;

		$this->db =& $ilDB;

		$this->__read();
	}
	
	function getDefinitions()
	{
		return $this->definitions ? $this->definitions : array();
	}

	function getDefinition($a_id)
	{
		return is_array($this->definitions[$a_id]) ? $this->definitions[$a_id] : array();
	}

	function getVisibleDefinitions()
	{
		foreach($this->definitions as $id => $definition)
		{
			if($definition['visible'])
			{
				$visible_definition[$id] = $definition;
			}
		}
		return $visible_definition ? $visible_definition : array();
	}

	function getSearchableDefinitions()
	{
		foreach($this->definitions as $id => $definition)
		{
			if($definition['searchable'])
			{
				$searchable_definition[$id] = $definition;
			}
		}
		return $searchable_definition ? $searchable_definition : array();
	}


	function setFieldName($a_name)
	{
		$this->field_name = $a_name;
	}
	function getFieldName()
	{
		return $this->field_name;
	}
	function setFieldType($a_type)
	{
		$this->field_type = $a_type;
	}
	function getFieldType()
	{
		return $this->field_type;
	}
	function setFieldValues($a_values)
	{
		$this->field_values = array();
		foreach($a_values as $value)
		{
			if(strlen($value))
			{
				$this->field_values[] = $value;
			}
		}
	}
	function getFieldValues()
	{
		return $this->field_values ? $this->field_values : array();
	}

	function enableVisible($a_visible)
	{
		$this->field_visible = $a_visible;
	}
	function enabledVisible()
	{
		return $this->field_visible;
	}
	function enableChangeable($a_changeable)
	{
		$this->field_changeable = $a_changeable;
	}
	function enabledChangeable()
	{
		return $this->field_changeable;
	}
	function enableRequired($a_required)
	{
		$this->field_required = $a_required;
	}
	function enabledRequired()
	{
		return $this->field_required;
	}
	function enableSearchable($a_searchable)
	{
		$this->field_searchable = $a_searchable;
	}
	function enabledSearchable()
	{
		return $this->field_searchable;
	}


	function fieldValuesToSelectArray($a_values)
	{
		foreach($a_values as $value)
		{
			$values[$value] = $value;
		}
		return $values ? $values : array();
	}

	function validateValues()
	{
		$number = 0;
		$unique = array();
		foreach($this->getFieldValues() as $value)
		{
			if(!strlen($value))
			{
				continue;
			}
			$number++;
			$unique[$value] = $value;
		}

		if(!count($unique))
		{
			return UDF_NO_VALUES;
		}
		if($number != count($unique))
		{
			return UDF_DUPLICATE_VALUES;
		}
		return 0;
	}

	function nameExists($a_field_name)
	{
		$query = "SELECT * FROM user_defined_field_definition ".
			"WHERE field_name = '".$a_field_name."'";

		$res = $this->db->query($query);
		
		return (bool) $res->numRows();
	}

	function add()
	{
		// Add definition entry
		$query = "INSERT INTO user_defined_field_definition ".
			"SET field_name = '".$this->getFieldName()."', ".
			"field_type = '".$this->getFieldType()."', ".
			"field_values = '".addslashes(serialize($this->getFieldValues()))."', ".
			"visible = '".(int) $this->enabledVisible()."', ".
			"changeable = '".(int) $this->enabledChangeable()."', ".
			"required = '".(int) $this->enabledRequired()."', ".
			"searchable = '".(int) $this->enabledSearchable()."'";

		$this->db->query($query);

		// add table field in usr_defined_data
		$field_id = $this->db->getLastInsertId();

		$query = "ALTER TABLE usr_defined_data ADD `".$field_id."` TEXT NOT NULL";
		$this->db->query($query);

		$this->__read();

		return true;
	}
	function delete($a_id)
	{
		// Delete definitions
		$query = "DELETE FROM user_defined_field_definition ".
			"WHERE field_id = '".$a_id."'";
		$this->db->query($query);

		// Delete usr_data entries
		$query = "ALTER TABLE usr_defined_data DROP `".$a_id."`";
		$this->db->query($query);

		$this->__read();

		return true;
	}

	function update($a_id)
	{
		$query = "UPDATE user_defined_field_definition ".
			"SET field_name = '".$this->getFieldName()."', ".
			"field_type = '".$this->getFieldType()."', ".
			"field_values = '".addslashes(serialize($this->getFieldValues()))."', ".
			"visible = '".(int) $this->enabledVisible()."', ".
			"changeable = '".(int) $this->enabledChangeable()."', ".
			"required = '".(int) $this->enabledRequired()."', ".
			"searchable = '".(int) $this->enabledSearchable()."' ".
			"WHERE field_id = '".$a_id."'";

		$this->db->query($query);
		$this->__read();

		return true;
	}


			
	// Private
	function __read()
	{
		$query = "SELECT * FROM user_defined_field_definition ";
		$res = $this->db->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->definitions[$row->field_id]['field_id'] = $row->field_id;
			$this->definitions[$row->field_id]['field_name'] = $row->field_name;
			$this->definitions[$row->field_id]['field_type'] = $row->field_type;

			$tmp = unserialize(stripslashes($row->field_values));
			sort($tmp);
			$this->definitions[$row->field_id]['field_values'] = $tmp;

			$this->definitions[$row->field_id]['visible'] = $row->visible;
			$this->definitions[$row->field_id]['changeable'] = $row->changeable;
			$this->definitions[$row->field_id]['required'] = $row->required;
			$this->definitions[$row->field_id]['searchable'] = $row->searchable;

		}

		return true;
	}

	function deleteValue($a_field_id,$a_value_id)
	{
		$definition = $this->getDefinition($a_field_id);

		$counter = 0;
		$new_values = array();
		foreach($definition['field_values'] as $value)
		{
			if($counter++ != $a_value_id)
			{
				$new_values[] = $value;
			}
			else
			{
				$old_value = $value;
			}
		}
		$query = "UPDATE user_defined_field_definition ".
			"SET field_values = '".addslashes(serialize($new_values))."' ".
			"WHERE field_id = '".$a_field_id."'";

		$this->db->query($query);

		// Update usr_data
		$query = "UPDATE usr_defined_data ".
			"SET `".$a_field_id."` = '' ".
			"WHERE `".$a_field_id."` = '".$old_value."'";
		$this->db->query($query);

		// fianally read data
		$this->__read();

		return true;
	}
}
?>