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
	 * Singleton constructor
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function __construct($a_field_id = 0)
	{
		global $ilDB;
		
		$this->db = $ilDB;
		
		$this->field_id = $a_field_id;
		$this->read();
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
		
		$query = "SELECT field_id FROM adv_md_field_definition ".
			"WHERE record_id = ".$ilDB->quote($a_record_id)." ".
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
		
		$query = "DELETE FROM adv_md_field_definition ".
			"WHERE record_id  = ".$ilDB->quote($a_record_id)." ";
		$res = $ilDB->query($query);	
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
	 	$query = "DELETE FROM adv_md_field_definition ".
	 		"WHERE field_id = ".$this->db->quote($this->getFieldId())." ";
	 	$res = $this->db->query($query);
		return true;	
	}
	
	/**
	 * add new field
	 *
	 * @access public
	 */
	public function add()
	{
	 	sort($values = $this->getFieldValues(),SORT_STRING);
	 	
	 	$position = $this->getLastPosition();
	 	
	 	$query = "INSERT INTO adv_md_field_definition ".
	 		"SET record_id = ".$this->db->quote($this->getRecordId()).", ".
	 		"import_id = ".$this->db->quote($this->getImportId()).", ".
	 		"position = ".$this->db->quote($position + 1).", ".
	 		"field_type = ".$this->db->quote($this->getFieldType()).", ".
			"field_values = '".addslashes(serialize($values))."', ".
	 		"title = ".$this->db->quote($this->getTitle()).", ".
	 		"description = ".$this->db->quote($this->getDescription()).", ".
	 		"searchable = ".(int) $this->isSearchable().", ".
	 		"required = ".(int) $this->isRequired();
	 	$this->db->query($query);
	 	$this->field_id = $this->db->getLastInsertId();
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
	 	$query = "UPDATE adv_md_field_definition ".
	 		"SET record_id = ".$this->db->quote($this->getRecordId()).", ".
	 		"import_id = ".$this->db->quote($this->getImportId()).", ".
	 		"position = ".$this->db->quote($this->getPosition()).", ".
	 		"field_type = ".$this->db->quote($this->getFieldType()).", ".
			"field_values = '".addslashes(serialize($this->getFieldValues()))."', ".
	 		"title = ".$this->db->quote($this->getTitle()).", ".
	 		"description = ".$this->db->quote($this->getDescription()).", ".
	 		"searchable = ".(int) $this->isSearchable().", ".
	 		"required = ".(int) $this->isRequired()." ".
	 		"WHERE field_id = ".$this->db->quote($this->getFieldId())." ";
				 		
	 	$this->db->query($query);
		return true;
	}
	
	/**
	 * read field definition
	 *
	 * @access private
	 * 
	 */
	private function read()
	{
		if(!$this->field_id)
		{
			return false;
		}
		$query = "SELECT * FROM adv_md_field_definition ".
			"WHERE field_id = ".$this->db->quote($this->getFieldId())." ";
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
		$query = "SELECT max(position) as pos FROM adv_md_field_definition ".
			"WHERE record_id = ".$this->db->quote($this->getRecordId())." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->pos;
		}
		return 0;
	}
}
?>