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
* @defgroup Services/AdvancedMetaData
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
	protected $title;
	protected $description;
	protected $obj_types = array();
	protected $db = null;
	
	/**
	 * Singleton constructor
	 *
	 * @access public
	 * @param int record id
	 * 
	 */
	private function __construct($a_record_id = 0)
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
	 * Get assignable object type
	 *
	 * @access public
 	 * @static 
	 */
	public static function _getAssignableObjectTypes()
	{
	 	return array('crs','grp');
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
	 	$query = "INSERT INTO adv_md_record ".
	 		"SET import_id = ".$this->db->quote($this->getImportId()).", ".
	 		"title = ".$this->db->quote($this->getTitle()).", ".
	 		"description = ".$this->db->quote($this->getDescription())." ";
	 	$this->db->query($query);
	 	$this->record_id = $this->db->getLastInsertId();
	 	
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
	 		"SET import_id = ".$this->db->quote($this->getImportId()).", ".
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
}
?>