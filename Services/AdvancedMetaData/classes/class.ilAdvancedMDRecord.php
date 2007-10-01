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
* @defgroup ServicesAdvancedMetaData Services/AdvancedMetaData
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesAdvancedMetaData
*/

class ilAdvancedMDRecord
{
	private static $instances = array();
	
	protected $record_id;
	protected $import_id;
	protected $active;
	protected $title;
	protected $description;
	protected $obj_types = array();
	protected $db = null;
	
	/**
	 * Singleton constructor
	 * To create an array of new records (without saving them)
	 * call the constructor directly. Otherwise call getInstance...
	 *
	 * @access public
	 * @param int record id
	 * 
	 */
	public function __construct($a_record_id = 0)
	{
	 	global $ilDB;
	 	
	 	$this->record_id = $a_record_id;
	 	$this->db = $ilDB;
	 	
	 	if($this->getRecordId())
	 	{
	 		$this->read();
	 	}
	}
	
	/**
	 * Get instance by record id
	 *
	 * @access public
	 * @static
	 *
	 * @param int record id
	 */
	public static function _getInstanceByRecordId($a_record_id)
	{
		if(isset(self::$instances[$a_record_id]))
		{
			return self::$instances[$a_record_id];
		}
		return self::$instances[$a_record_id] = new ilAdvancedMDRecord($a_record_id);
	}
	
	/**
	 * Lookup title
	 *
	 * @access public
	 * @static
	 *
	 * @param int record_id
	 */
	public static function _lookupTitle($a_record_id)
	{
		static $title_cache = array();
		
		if(isset($title_cache[$a_record_id]))
		{
			return $title_cache[$a_record_id];
		}
		
		global $ilDB;
		
		$query = "SELECT title FROm adv_md_record ".
			"WHERE record_id = ".$ilDB->quote($a_record_id)." ";
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		return $title_cache[$a_record_id] = $row->title;
	}
	
	/**
	 * Lookup record Id by import id
	 *
	 * @access public
	 * @static
	 *
	 * @param string ilias id
	 */
	public static function _lookupRecordIdByImportId($a_ilias_id)
	{
		global $ilDB;
		
		$query = "SELECT record_id FROM adv_md_record ".
			"WHERE import_id = ".$ilDB->quote($a_ilias_id)." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->record_id;
		}
		return 0;
	}
	
	/**
	 * Get assignable object type
	 *
	 * @access public
 	 * @static 
	 */
	public static function _getAssignableObjectTypes()
	{
	 	return array('crs','cat');
	 	return array('crs','rcrs');
	}
	
	/**
	 * get activate obj types
	 *
	 * @access public
	 * @static
	 *
	 * @param string obj types
	 */
	public static function _getActivatedObjTypes()
	{
		global $ilDB;
		
		$query = "SELECT DISTINCT(obj_type) FROM adv_md_record_objs AS amo ".
			"JOIN adv_md_record AS amr ON amo.record_id = amr.record_id ".
			"WHERE active = 1 ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$obj_types[] = $row->obj_type; 
		}
		return $obj_types ? $obj_types : array(); 
	}
	
	/**
	 * Get records
	 *
	 * @access public
	 * @static
	 *
	 * @param array array of record objects
	 */
	public static function _getRecords()
	{
		global $ilDB;
		
		$query = "SELECT record_id FROM adv_md_record ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$records[] = ilAdvancedMDRecord::_getInstanceByRecordId($row->record_id);
		}
		return $records ? $records : array();
	}
	
	/**
	 * Get records by obj_type
	 *
	 * @access public
	 * @static
	 * @param
	 * 
	 */
	public static function _getAllRecordsByObjectType()
	{
		global $ilDB;
		
		$records = array();
		
		$query = "SELECT * FROM adv_md_record_objs ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$records[$row->obj_type][] = self::_getInstanceByRecordId($row->record_id);
		}
		return $records;
	}
	
	/**
	 * Get activated records by object type
	 *
	 * @access public
	 * @static
	 *
	 * @param string obj_type
	 */
	public static function _getActivatedRecordsByObjectType($a_obj_type)
	{
		global $ilDB;		

		$records = array();
		
		$query = "SELECT amro.record_id AS record_id FROM adv_md_record_objs AS amro ".
			"JOIN adv_md_record AS amr ON amr.record_id = amro.record_id ".
			"WHERE active = 1 ".
			"AND obj_type = ".$ilDB->quote($a_obj_type)." ";
		
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$records[] = self::_getInstanceByRecordId($row->record_id);
		}
		return $records;
	}
	

	
	/**
	 * Delete record and all related data
	 *
	 * @access public
	 * @static
	 *
	 * @param int record id
	 */
	public static function _delete($a_record_id)
	{
		global $ilDB;
		
		// Delete fields
		ilAdvancedMDFieldDefinition::_deleteByRecordId($a_record_id);
		
		$query = "DELETE FROM adv_md_record ".
			"WHERE record_id = ".$ilDB->quote($a_record_id)." ";
		$ilDB->query($query);
		
		$query = "DELETE FROM adv_md_record_objs ".
			"WHERE record_id = ".$ilDB->quote($a_record_id)." ";
		$ilDB->query($query);
	}
	
	
	/**
	 * Delete
	 *
	 * @access public
	 * 
	 */
	public function delete()
	{
	 	ilAdvancedMDRecord::_delete($this->getRecordId());
	}
	
	/**
	 * save
	 *
	 * @access public
	 * 
	 */
	public function save()
	{
	 	// Save import id if given
	 	$query = "INSERT INTO adv_md_record ".
	 		"SET import_id = ".$this->db->quote($this->getImportId()).", ".
	 		"active = ".$this->db->quote($this->isActive()).", ".
	 		"title = ".$this->db->quote($this->getTitle()).", ".
	 		"description = ".$this->db->quote($this->getDescription())." ";
	 	$this->db->query($query);
	 	$this->record_id = $this->db->getLastInsertId();

	 	if(!strlen($this->getImportId()))
	 	{
		 	// set import id to default value
		 	$query = "UPDATE adv_md_record ".
		 		"SET import_id = 'il_".(IL_INST_ID.'_adv_md_record_'.$this->record_id)."' ".
		 		"WHERE record_id = ".$this->db->quote($this->record_id)." ";
		 	$res = $this->db->query($query);
	 	}

	 	foreach($this->getAssignedObjectTypes() as $type)
	 	{
	 		$query = "INSERT INTO adv_md_record_objs ".
	 			"SET record_id = ".$this->db->quote($this->getRecordId()).", ".
	 			"obj_type = ".$this->db->quote($type)." ";
	 		$this->db->query($query);
	 	}
	}
	
	/**
	 * update
	 *
	 * @access public
	 * 
	 */
	public function update()
	{
	 	$query = "UPDATE adv_md_record ".
	 		"SET active = ".$this->db->quote($this->isActive()).", ".
	 		"title = ".$this->db->quote($this->getTitle()).", ".
	 		"description = ".$this->db->quote($this->getDescription())." ".
	 		"WHERE record_id = ".$this->db->quote($this->getRecordId())." ";
		$this->db->query($query);
		
		// Delete assignments
	 	$query = "DELETE FROM adv_md_record_objs ".
	 		"WHERE record_id = ".$this->db->quote($this->getRecordId())." ";
	 	$this->db->query($query);
			
	 	// Insert assignments
	 	foreach($this->getAssignedObjectTypes() as $type)
	 	{
	 		$query = "INSERT INTO adv_md_record_objs ".
	 			"SET record_id = ".$this->db->quote($this->getRecordId()).", ".
	 			"obj_type = ".$this->db->quote($type)." ";
	 		$this->db->query($query);
	 	}
	}
	
	/**
	 * Validate settings
	 * Write error message to ilErr 
	 *
	 * @access public
	 * 
	 */
	public function validate()
	{
	 	global $ilErr,$lng;
	 	
	 	if(!strlen($this->getTitle()))
	 	{
	 		$ilErr->setMessage('fill_out_all_required_fields');
	 		return false;
	 	}
	 	return true;
	}
	
	/**
	 * Get record id
	 *
	 * @access public
	 * 
	 */
	public function getRecordId()
	{
	 	return $this->record_id;
	}
	
	/**
	 * Set active
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function setActive($a_active)
	{
	 	$this->active = $a_active;
	}
	
	/**
	 * Check if record is active
	 *
	 * @access public
	 * 
	 */
	public function isActive()
	{
	 	return (bool) $this->active;
	}
	
	/**
	 * Set title
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
	 * 
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
	public function setDescription($a_description)
	{
	 	$this->description = $a_description;
	}
	
	/**
	 * get description
	 *
	 * @access public
	 * 
	 */
	public function getDescription()
	{
	 	return $this->description;
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
	 * get import id
	 *
	 * @access public
	 * 
	 */
	public function getImportId()
	{
		return $this->import_id; 	
	}
	
	/**
	 * Set assigned object types
	 *
	 * @access public
	 * @param array array(string) array of object types. E.g array('crs','crsl')
	 * 
	 */
	public function setAssignedObjectTypes($a_obj_types)
	{
	 	$this->obj_types = $a_obj_types;
	}
	
	/**
	 * append assigned object types
	 *
	 * @access public
	 * @param string ilias object type
	 * 
	 */
	public function appendAssignedObjectType($a_obj_type)
	{
	 	$this->obj_types[] = $a_obj_type;
	}
	
	/**
	 * Get assigned object types 
	 *
	 * @access public
	 * 
	 */
	public function getAssignedObjectTypes()
	{
	 	return $this->obj_types ? $this->obj_types : array();
	}
	
	/**
	 * To Xml.
	 * This method writes only the subset Record (including all fields)
	 * Use class.ilAdvancedMDRecordXMLWriter to generate a complete xml presentation.
	 *
	 * @access public
	 * @param object ilXmlWriter
	 * 
	 */
	public function toXML(ilXmlWriter $writer)
	{
	 	$writer->xmlStartTag('Record',array('active' => $this->isActive() ? 1 : 0,
	 		'id' => $this->getImportId()));
	 	$writer->xmlElement('Title',null,$this->getTitle());
	 	$writer->xmlElement('Description',null,$this->getDescription());
	 	
	 	foreach($this->getAssignedObjectTypes() as $obj_type)
	 	{
	 		$writer->xmlElement('ObjectType',null,$obj_type);
	 	}
	 	
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
	 	foreach(ilAdvancedMDFieldDefinition::_getDefinitionsByRecordId($this->getRecordId()) as $definition)
	 	{
	 		$definition->toXML($writer);
	 	}
	 	$writer->xmlEndTag('Record');
	}
	
	/**
	 * read record and assiged object types
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function read()
	{
	 	$query = "SELECT * FROM adv_md_record ".
	 		"WHERE record_id = ".$this->db->quote($this->getRecordId())." ";
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setImportId($row->import_id);
			$this->setActive($row->active);
			$this->setTitle($row->title);
			$this->setDescription($row->description);
		}
		$query = "SELECT * FROM adv_md_record_objs ".
	 		"WHERE record_id = ".$this->db->quote($this->getRecordId())." ";
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->obj_types[] = $row->obj_type;
	 	}
	}
	
	/**
	 * Destructor
	 *
	 * @access public
	 * 
	 */
	public function __destruct()
	{
	 	unset(self::$instances[$this->getRecordId()]);
	}
}
?>