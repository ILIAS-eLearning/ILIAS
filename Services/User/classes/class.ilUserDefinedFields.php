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


	/**
	 * Constructor is private -> use getInstance
	 * These definition are used e.g in User XML import.
	 * To avoid instances of this class for every user object during import,
	 * it caches this object in a singleton.
	 *
	 */
	private function __construct()
	{
		global $ilDB;

		$this->db =& $ilDB;

		$this->__read();
	}

	function _getInstance()
	{
		static $udf = null;

		if(!is_object($udf))
		{
			return $udf = new ilUserDefinedFields();
		}
		return $udf;
	}

	function fetchFieldIdFromImportId($a_import_id)
	{
		global $ilSetting;

		if(!strlen($a_import_id))
		{
			return 0;
		}
		$parts = explode('_',$a_import_id);

		if($parts[0] != 'il')
		{
			return 0;
		}
		if($parts[1] != $ilSetting->get('inst_id',0))
		{
			return 0;
		}
		if($parts[2] != 'udf')
		{
			return 0;
		}
		if($parts[3])
		{
			// Check if field exists
			if(is_array($this->definitions["$parts[3]"]))
			{
				return $parts[3];
			}
		}
		return 0;
	}
	function fetchFieldIdFromName($a_name)
	{
		foreach($this->definitions as $definition)
		{
			if($definition['field_name'] == $a_name)
			{
				return $definition['field_id'];
			}
		}
		return 0;
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

	/**
	 * get
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getCourseExportableFields()
	{
		foreach($this->definitions as $id => $definition)
		{
			if($definition['course_export'])
			{
				$cexp_definition[$id] = $definition;
			}
		}
		return $cexp_definition ? $cexp_definition : array();
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
	function enableExport($a_export)
	{
		$this->field_export = $a_export;
	}
	function enabledExport()
	{
		return $this->field_export;
	}
	function enableCourseExport($a_course_export)
	{
		$this->field_course_export = $a_course_export;
	}
	function enabledCourseExport()
	{
		return $this->field_course_export;
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
			"WHERE field_name = ".$this->db->quote($a_field_name)." ";

		$res = $this->db->query($query);

		return (bool) $res->numRows();
	}

	function add()
	{
		// Add definition entry
		$query = "INSERT INTO user_defined_field_definition ".
			"SET field_name = ".$this->db->quote($this->getFieldName()).", ".
			"field_type = ".$this->db->quote($this->getFieldType()).", ".
			"field_values = '".addslashes(serialize($this->getFieldValues()))."', ".
			"visible = '".(int) $this->enabledVisible()."', ".
			"changeable = '".(int) $this->enabledChangeable()."', ".
			"required = '".(int) $this->enabledRequired()."', ".
			"searchable = '".(int) $this->enabledSearchable()."', ".
			"export = '".(int) $this->enabledExport()."', ".
			"course_export = '".(int) $this->enabledCourseExport()."'";

		$this->db->query($query);

		// add table field in usr_defined_data
		$field_id = $this->db->getLastInsertId();

		$query = "ALTER TABLE usr_defined_data ADD `".(int) $field_id."` TEXT NOT NULL";
		$this->db->query($query);

		$this->__read();

		return true;
	}
	function delete($a_id)
	{
		// Delete definitions
		$query = "DELETE FROM user_defined_field_definition ".
			"WHERE field_id = ".$this->db->quote($a_id)." ";
		$this->db->query($query);

		// Delete usr_data entries
		$query = "ALTER TABLE usr_defined_data DROP `".(int) $a_id."`";
		$this->db->query($query);

		$this->__read();

		return true;
	}

	function update($a_id)
	{
		$query = "UPDATE user_defined_field_definition ".
			"SET field_name = ".$this->db->quote($this->getFieldName()).", ".
			"field_type = ".$this->db->quote($this->getFieldType()).", ".
			"field_values = '".addslashes(serialize($this->getFieldValues()))."', ".
			"visible = '".(int) $this->enabledVisible()."', ".
			"changeable = '".(int) $this->enabledChangeable()."', ".
			"required = '".(int) $this->enabledRequired()."', ".
			"searchable = '".(int) $this->enabledSearchable()."', ".
			"export = '".(int) $this->enabledExport()."', ".
			"course_export = '".(int) $this->enabledCourseExport()."' ".
			"WHERE field_id = ".$this->db->quote($a_id)." ";

		$this->db->query($query);
		$this->__read();

		return true;
	}



	// Private
	function __read()
	{
		global $ilSetting;

		$query = "SELECT * FROM user_defined_field_definition ";
		$res = $this->db->query($query);

		$this->definitions = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->definitions[$row->field_id]['field_id'] = $row->field_id;
			$this->definitions[$row->field_id]['field_name'] = $row->field_name;
			$this->definitions[$row->field_id]['field_type'] = $row->field_type;
			$this->definitions[$row->field_id]['il_id'] = 'il_'.$ilSetting->get('inst_id',0).'_udf_'.$row->field_id;

			$tmp = unserialize(stripslashes($row->field_values));
			sort($tmp);
			$this->definitions[$row->field_id]['field_values'] = $tmp;

			$this->definitions[$row->field_id]['visible'] = $row->visible;
			$this->definitions[$row->field_id]['changeable'] = $row->changeable;
			$this->definitions[$row->field_id]['required'] = $row->required;
			$this->definitions[$row->field_id]['searchable'] = $row->searchable;
			$this->definitions[$row->field_id]['export'] = $row->export;
			$this->definitions[$row->field_id]['course_export'] = $row->course_export;

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
			"WHERE field_id = ".$this->db->quote($a_field_id)."";

		$this->db->query($query);

		// Update usr_data
		$query = "UPDATE usr_defined_data ".
			"SET `".$this->db->quote($a_field_id)."` = '' ".
			"WHERE `".$this->db->quote($a_field_id)."` = ".$this->db->quote($old_value)."";
		$this->db->query($query);

		// fianally read data
		$this->__read();

		return true;
	}

	function toXML()
	{
		include_once 'classes/class.ilXmlWriter.php';
		$xml_writer = new ilXmlWriter();

		$this->addToXML ($xml_writer);

		return $xml_writer->xmlDumpMem(false);
	}

	/**
	*	add user defined field data to xml (using usr dtd)
	*	@param*XmlWriter $xml_writer
	*/
	function addToXML($xml_writer)
	{
	    $xml_writer->xmlStartTag ("UDFDefinitions");
		foreach($this->getDefinitions() as $definition)
		{
    	    $attributes = array(
                "Id" => $definition ["il_id"],
                "Type" => $definition["field_type"] == UDF_TYPE_SELECT? "SELECT" : "TEXT",
                "Visible" => $definition["visible"]? "TRUE" : "FALSE",
                "Changeable" => $definition["changeable"]? "TRUE" : "FALSE",
                "Required" => $definition["required"]? "TRUE" : "FALSE",
                "Searchable" => $definition["searchable"]? "TRUE" : "FALSE",
                "CourseExport" => $definition["course_export"]? "TRUE" : "FALSE",
                "Export" => $definition["export"]? "TRUE" : "FALSE",
    	    );
		    $xml_writer->xmlStartTag ("UDFDefinition", $attributes);
		    $xml_writer->xmlElement('UDFName', null, $definition['field_name']);
		    if ($definition["field_type"] == UDF_TYPE_SELECT ) {
		        $field_values = $definition["field_values"];
		        foreach ($field_values as $field_value)
		        {
	   	           $xml_writer->xmlElement('UDFValue', null, $field_value);
		        }
		    }
		    $xml_writer->xmlEndTag ("UDFDefinition");
		}
	    $xml_writer->xmlEndTag ("UDFDefinitions");

	}
	

	function _newInstance()
	{
		static $udf = null;

		return $udf = new ilUserDefinedFields();
	}
	
}
?>