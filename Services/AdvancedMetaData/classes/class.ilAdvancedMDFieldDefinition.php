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
* @ingroup ServicesAdvancedMetaData 
*/
class ilAdvancedMDFieldDefinition
{
	const TYPE_SELECT = 1;
	const TYPE_TEXT = 2;
	const TYPE_DATE = 3;
	const TYPE_DATETIME = 4;

	private static $instances = array();
	
	protected $db = null;
	
	protected $record_id;
	protected $field_id;
	protected $import_id;
	protected $position;
	protected $field_type;
	protected $field_values = array();
	protected $title;
	protected $description;
	protected $searchable;
	protected $required = false;
	
	
	/**
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_field_id = 0)
	{
		global $ilDB;
		
		$this->db = $ilDB;
		
		$this->field_id = $a_field_id;
		$this->read();
	}
	
	/**
	 * Lookup import id
	 *
	 * @access public
	 * @static
	 *
	 * @param int field_id
	 */
	public static function _lookupImportId($a_field_id)
	{
		global $ilDB;
		
		$query = "SELECT import_id FROM adv_mdf_definition ".
			"WHERE field_id = ".$ilDB->quote($a_field_id,'integer')." ";
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return $row['import_id'] ? $row['import_id'] : '';
	}
	
	/**
	 * Lookup field id
	 *
	 * @access public
	 * @static
	 *
	 * @param string import_id
	 */
	public static function _lookupFieldId($a_import_id)
	{
		global $ilDB;
		
		$query = "SELECT field_id FROM adv_mdf_definition ".
			"WHERE import_id = ".$ilDB->quote($a_import_id,'text')." ";
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return $row['field_id'] ? $row['field_id'] : 0;
	}
	
	/**
	 * Lookup field type
	 *
	 * @access public
	 * @static
	 *
	 * @param int field_id
	 */
	public static function _lookupFieldType($a_field_id)
	{
		global $ilDB;
		
		$query = "SELECT field_type FROM adv_mdf_definition ".
			"WHERE field_id = ".$ilDB->quote($a_field_id ,'integer')." ";
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return $row['field_type'] ? $row['field_type'] : 0;
	}
	
	
	/**
	 * Lookup datetime fields
	 *
	 * @access public
	 * @static
	 *
	 * @param
	 */
	public static function _lookupDateTimeFields()
	{
		global $ilDB;
		
		$query = "SELECT field_id FROM adv_mdf_definition ".
			"WHERE field_type = ".$ilDB->quote(self::TYPE_DATETIME ,'integer')." ";
		$res = $ilDB->query($query);
		
		$date_fields = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$date_fields[] = $row->field_id; 
		}
		return $date_fields;
	}
	
	/**
	 * Lookup date fields
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _lookupDateFields()
	{
		global $ilDB;
		
		$query = "SELECT field_id FROM adv_mdf_definition ".
			"WHERE field_type = ".$ilDB->quote(self::TYPE_DATE ,'integer')." ";
		$res = $ilDB->query($query);
		
		$date_fields = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$date_fields[] = $row->field_id; 
		}
		return $date_fields;
	}
	
	/**
	 * Get instance by field_id
	 *
	 * @access public
	 * @static
	 *
	 * @param
	 */
	public static function _getInstanceByFieldId($a_field_id)
	{
		if(isset(self::$instances[$a_field_id]))
		{
			return self::$instances[$a_field_id];
		}
		return self::$instances[$a_field_id] = new ilAdvancedMDFieldDefinition($a_field_id);
	}
	

	/**
	 * get definitions
	 *
	 * @access public
	 * @static
	 *
	 * @param int record_id
	 * @return array array(object) field definition objects
	 */
	public static function _getDefinitionsByRecordId($a_record_id)
	{
		global $ilDB;
		
		$query = "SELECT field_id FROM adv_mdf_definition ".
			"WHERE record_id = ".$ilDB->quote($a_record_id ,'integer')." ".
			"ORDER BY position ";
		$res = $ilDB->query($query);
		$defs = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$defs[] = self::_getInstanceByFieldId($row->field_id);
		}
		return $defs ? $defs : array();	
	}
	
	/**
	 * get active definitions by obj type
	 *
	 * @access public
	 * @static
	 *
	 * @param string obj_type
	 */
	public static function _getActiveDefinitionsByObjType($a_type)
	{
		global $ilDB;
		
		$query = "SELECT field_id FROM adv_md_record_objs aro ".
			"JOIN adv_md_record amr ON aro.record_id = amr.record_id ".
			"JOIN adv_mdf_definition amf ON aro.record_id = amf.record_id ".
			"WHERE active = 1 ".
			"AND obj_type = ".$ilDB->quote($a_type,'text')." ".
			"ORDER BY aro.record_id,position ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$field_ids[] = $row->field_id;
		}
		return $field_ids ? $field_ids : array();
	}
	
	/**
	 * Get searchable definition ids
	 *
	 * @access public
	 * @static
	 */
	public static function _getSearchableDefinitionIds()
	{
		global $ilDB;
		
		$query = "SELECT field_id FROM adv_md_record amr ".
			"JOIN adv_mdf_definition amfd ON amr.record_id = amfd.record_id ".
			"WHERE active = 1 ".
			"AND searchable = 1";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$field_ids[] = $row->field_id;
		}
		return $field_ids ? $field_ids : array();
	}
	
	
	/**
	 * Delete all fields by record_id
	 *
	 * @access public
	 * @static
	 *
	 * @param int record_id
	 */
	public static function _deleteByRecordId($a_record_id)
	{
		global $ilDB;
		
		$query = "SELECT field_id FROM adv_mdf_definition ".
			"WHERE record_id = ".$ilDB->quote($a_record_id ,'integer');
		$res = $ilDB->query($query);	
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
		 	// Delete values
		 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
		 	ilAdvancedMDValues::_deleteByFieldId($row->field_id);
		}		

		// Delete definitions
		$query = "DELETE FROM adv_mdf_definition ".
			"WHERE record_id  = ".$ilDB->quote($a_record_id,'integer')." ";
		$res = $ilDB->manipulate($query);
	}
	
	/**
	 * is deleted
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function isDeleted()
	{
	 	return $this->record_id ? false : true;
	}

	// Setter, Getter...
	/**
	 * set record id
	 *
	 * @access public
	 * @param int record id
	 * 
	 */
	public function setRecordId($a_id)
	{
	 	$this->record_id = $a_id;
	}
	
	/**
	 * get record id
	 *
	 * @access public
	 */
	public function getRecordId()
	{
	 	return $this->record_id;
	}
	
	/**
	 * get field_id
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getFieldId()
	{
	 	return $this->field_id;
	}
	
	/**
	 * set import id
	 *
	 * @access public
	 * @param string import id
	 * 
	 */
	public function setImportId($a_id_string)
	{
	 	$this->import_id = $a_id_string;
	}
	
	/**
	 * get import_id
	 *
	 * @access public
	 * 
	 */
	public function getImportId()
	{
	 	return $this->import_id;
	}
	
	/**
	 * set position
	 *
	 * @access public
	 * @param int position
	 * 
	 */
	public function setPosition($a_pos)
	{
	 	$this->position = $a_pos;
	}
	
	/**
	 * get position
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getPosition()
	{
	 	return $this->position;
	}
	
	/**
	 * set field type
	 *
	 * @access public
	 * @param int field type 
	 * 
	 */
	public function setFieldType($a_type_id)
	{
	 	$this->field_type = $a_type_id;
	}
	
	/**
	 * get field type
	 *
	 * @access public
	 * 
	 */
	public function getFieldType()
	{
	 	return $this->field_type;
	}
	
	/**
	 * set field values
	 *
	 * @access public
	 * @param array array(string) valid field values
	 * 
	 */
	public function setFieldValues($a_values)
	{
	 	$this->field_values = $a_values;
	}
	
	/**
	 * Append field value
	 *
	 * @access public
	 * @param string value
	 * 
	 */
	public function appendFieldValue($a_value)
	{
	 	if(strlen(trim($a_value)))
	 	{
	 		$this->field_values[] = trim($a_value);
	 	}
	}
	
	/**
	 * get field values
	 *
	 * @access public
	 * 
	 */
	public function getFieldValues()
	{
	 	return $this->field_values;
	}
	
	/**
	 * get field values for select
	 *
	 * @access public
	 * 
	 */
	public function getFieldValuesForSelect()
	{
	 	global $lng;
	 	
	 	$values = array(0 => $lng->txt('select_one'));
	 	foreach($this->field_values as $value)
	 	{
	 		$values[$value] = $value;
	 	}
	 	return $values;
	}
	
	/**
	 * set title
	 *
	 * @access public
	 * @param string title
	 * 
	 */
	public function setTitle($a_title)
	{
	 	$this->title = $a_title;
	}
	
	/**
	 * get title
	 *
	 * @access public
	 */
	public function getTitle()
	{
	 	return $this->title;
	}
	
	/**
	 * set description
	 *
	 * @access public
	 * @param string description
	 * 
	 */
	public function setDescription($a_desc)
	{
	 	$this->description = $a_desc;
	}
	
	/**
	 * get description
	 *
	 * @access public
	 */
	public function getDescription()
	{
	 	return $this->description;
	}
	
	/**
	 * enable searchable
	 *
	 * @access public
	 * @param bool searchable
	 * 
	 */
	public function enableSearchable($a_status)
	{
	 	$this->searchable = (bool) $a_status;
	}
	
	/**
	 * is searchable
	 *
	 * @access public
	 * 
	 */
	public function isSearchable()
	{
	 	return (bool) $this->searchable;
	}
	
	/**
	 * is required field
	 *
	 * @access public
	 * 
	 */
	public function isRequired()
	{
	 	return $this->required;
	}
	
	/**
	 * delete field
	 *
	 * @access public
	 */
	public function delete()
	{
	 	global $ilDB;
	 	
	 	$query = "DELETE FROM adv_mdf_definition ".
	 		"WHERE field_id = ".$this->db->quote($this->getFieldId() ,'integer')." ";
		$res = $ilDB->manipulate($query);
	 	
	 	// Also delete all values
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
	 	ilAdvancedMDValues::_deleteByFieldId($this->getFieldId());
		return true;	
	}
	
	/**
	 * add new field
	 *
	 * @access public
	 */
	public function add()
	{
	 	global $ilDB;
	 	
	 	sort($values = $this->getFieldValues(),SORT_STRING);
	 	
	 	$position = $this->getLastPosition();
	 	$next_id = $ilDB->nextId('adv_mdf_definition');
	 	
	 	$query = "INSERT INTO adv_mdf_definition (field_id,record_id,import_id,position,field_type, ".
			"field_values,title,description,searchable,required) ".
			"VALUES( ".
			$ilDB->quote($next_id,'integer').",".
	 		$this->db->quote($this->getRecordId(),'integer').", ".
	 		$this->db->quote($this->getImportId(),'text').", ".
	 		$this->db->quote($position + 1 ,'integer').", ".
	 		$this->db->quote($this->getFieldType() ,'integer').", ".
			$ilDB->quote(serialize($values),'text').", ".
	 		$this->db->quote($this->getTitle() ,'text').", ".
	 		$this->db->quote($this->getDescription() ,'text').", ".
	 		$ilDB->quote($this->isSearchable(),'integer').", ".
	 		$ilDB->quote($this->isRequired(),'integer')." ".
	 		")";
		$res = $ilDB->manipulate($query);
		$this->field_id = $next_id;
	 	
	 	if(!strlen($this->getImportId()))
	 	{
	 		$query = "UPDATE adv_mdf_definition ".
	 			"SET import_id = ".$this->db->quote($this->generateImportId(),'text')." ".
	 			"WHERE field_id = ".$this->db->quote($this->field_id,'integer')." ";
			$res = $ilDB->manipulate($query);			
	 	}
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
	 	global $ilErr,$lng;
	 	
	 	if(!strlen($this->getTitle()) or !$this->getFieldType())
	 	{
	 		$ilErr->setMessage('fill_out_all_required_fields');
	 		return false;
	 	}
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
	 	
	 	$query = "UPDATE adv_mdf_definition ".
	 		"SET record_id = ".$this->db->quote($this->getRecordId() ,'integer').", ".
	 		"import_id = ".$this->db->quote($this->getImportId() ,'text').", ".
	 		"position = ".$this->db->quote($this->getPosition() ,'integer').", ".
	 		"field_type = ".$this->db->quote($this->getFieldType() ,'integer').", ".
			"field_values = ".$ilDB->quote(serialize($this->getFieldValues()),'text').", ".
	 		"title = ".$this->db->quote($this->getTitle() ,'text').", ".
	 		"description = ".$this->db->quote($this->getDescription() ,'text').", ".
	 		"searchable = ".$ilDB->quote($this->isSearchable() ,'integer').", ".
	 		"required = ".$ilDB->quote($this->isRequired(),'integer')." ".
	 		"WHERE field_id = ".$this->db->quote($this->getFieldId() ,'integer')." ";
		$res = $ilDB->manipulate($query);				 		
		return true;
	}
	
	/**
	 * To Xml.
	 * This method writes only the subset Field
	 * Use class.ilAdvancedMDRecordXMLWriter to generate a complete xml presentation.
	 *
	 * @access public
	 * @param object ilXmlWriter
	 * 
	 */
	 public function toXML(ilXmlWriter $writer)
	 {
	 	switch($this->getFieldType())
	 	{
	 		case self::TYPE_TEXT:
	 			$type = 'Text';
	 			break;
	 			
	 		case self::TYPE_SELECT:
	 			$type = 'Select';
	 			break;
	 			
	 		case self::TYPE_DATE:
	 			$type = 'Date';
	 			break;
	 			
	 		case self::TYPE_DATETIME:
	 			$type = 'DateTime';
	 			break;
	 	}
	 	
	 	
	 	$writer->xmlStartTag('Field',array(
	 		'id' => $this->generateImportId(),
			'searchable' => ($this->isSearchable() ? 'Yes' : 'No'),
			'fieldType'	 => $type));
		
		$writer->xmlElement('FieldTitle',null,$this->getTitle());
		$writer->xmlElement('FieldDescription',null,$this->getDescription());
		$writer->xmlElement('FieldPosition',null,$this->getPosition());
		
		foreach($this->getFieldValues() as $value)
		{
			if(strlen($value))
			{
				$writer->xmlElement('FieldValue',null,$value);
			}
		}
		
		$writer->xmlEndTag('Field');
	 }
	
	
	/**
	 * read field definition
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
		global $ilDB;
		
		if(!$this->field_id)
		{
			return false;
		}
		$query = "SELECT * FROM adv_mdf_definition ".
			"WHERE field_id = ".$this->db->quote($this->getFieldId() ,'integer')." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->record_id = $row->record_id;
			$this->import_id = $row->import_id;
			$this->position = $row->position;
			$this->field_type = $row->field_type;
			$this->field_values = unserialize(stripslashes($row->field_values));
			$this->title = $row->title;
			$this->description = $row->description;
			$this->searchable = $row->searchable;
			$this->required = $row->required;
		}
	}
	
	/**
	 * get last position of record
	 *
	 * @access private
	 * 
	 */
	private function getLastPosition()
	{
		$query = "SELECT max(position) pos FROM adv_mdf_definition ".
			"WHERE record_id = ".$this->db->quote($this->getRecordId() ,'integer')." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->pos;
		}
		return 0;
	}
	
	/**
	 * generate unique record id
	 *
	 * @access protected
	 * @return
	 */
	protected function generateImportId()
	{
		return 'il_'.IL_INST_ID.'_adv_md_field_'.$this->getFieldId();
	}
	
}
?>