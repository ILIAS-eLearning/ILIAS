<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* @defgroup ServicesAdvancedMetaData Services/AdvancedMetaData
* 
* @author Stefan Meyer <meyer@leifos.com>
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
	 * Get active searchable records 
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _getActiveSearchableRecords()
	{
		global $ilDB;
		
		$query = "SELECT DISTINCT(amr.record_id) FROM adv_md_record amr ".
			"JOIN adv_mdf_definition amfd ON amr.record_id = amfd.record_id ".
			"WHERE searchable = 1 AND active = 1 ";
			
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$records[] = self::_getInstanceByRecordId($row->record_id);
		}
		return $records ? $records : array();
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
		
		$query = "SELECT title FROM adv_md_record ".
			"WHERE record_id = ".$ilDB->quote($a_record_id ,'integer')." ";
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
			"WHERE import_id = ".$ilDB->quote($a_ilias_id ,'text')." ";
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
	public static function _getAssignableObjectTypes($a_include_text = false)
	{
		global $objDefinition, $lng;
		
		$types = array();
		$amet_types = $objDefinition->getAdvancedMetaDataTypes();

		foreach ($amet_types as $at)
		{
			if ($a_include_text)
			{
				$text = $lng->txt("obj_".$at["obj_type"]);
				if ($at["sub_type"] != "")
				{
					$lng->loadLanguageModule($at["obj_type"]);
					$text.= ": ".$lng->txt($at["obj_type"]."_".$at["sub_type"]);
				}
				else
				{
					$at["sub_type"] = "-";
				}
				$at["text"] = $text;
			}
			
			$types[] = $at;
		}

		return $types;
	 	return array('cat','crs','rcrs');
	}
	
	/**
	 * get activated obj types
	 *
	 * @access public
	 * @static
	 *
	 * @param string obj types
	 */
	public static function _getActivatedObjTypes()
	{
		global $ilDB;
		
		$query = "SELECT DISTINCT(obj_type) FROM adv_md_record_objs amo ".
			"JOIN adv_md_record amr ON amo.record_id = amr.record_id ".
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
	 * Note: this returns only records with no sub types!
	 * @access public
	 * @static
	 * @param
	 * 
	 */
	public static function _getAllRecordsByObjectType()
	{
		global $ilDB;
		
		$records = array();
		
		$query = "SELECT * FROM adv_md_record_objs WHERE sub_type=".$ilDB->quote("-", "text");
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
	public static function _getActivatedRecordsByObjectType($a_obj_type, $a_sub_type = "")
	{
		global $ilDB;		

		$records = array();
		
		if ($a_sub_type == "")
		{
			$a_sub_type = "-";
		}
		
		$query = "SELECT amro.record_id record_id FROM adv_md_record_objs amro ".
			"JOIN adv_md_record amr ON amr.record_id = amro.record_id ".
			"WHERE active = 1 ".
			"AND obj_type = ".$ilDB->quote($a_obj_type ,'text')." ".
			"AND sub_type = ".$ilDB->quote($a_sub_type ,'text');

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$records[] = self::_getInstanceByRecordId($row->record_id);
		}

		return $records;
	}
	
	/**
	 * Get selected records by object
	 *
	 * @param string $a_obj_type object type
	 * @param string $a_obj_id object id
	 * @param string $a_sub_type sub type
	 */
	public static function _getSelectedRecordsByObject($a_obj_type, $a_obj_id, $a_sub_type = "")
	{
		global $ilDB;		

		$records = array();
		
		if ($a_sub_type == "")
		{
			$a_sub_type = "-";
		}
		
		$query = "SELECT amro.record_id record_id FROM adv_md_record_objs amro ".
			"JOIN adv_md_record amr ON (amr.record_id = amro.record_id) ".
			"JOIN adv_md_obj_rec_select os ON (amr.record_id = os.rec_id AND amro.sub_type = os.sub_type) ".
			"WHERE active = 1 ".
			"AND amro.obj_type = ".$ilDB->quote($a_obj_type ,'text')." ".
			"AND amro.sub_type = ".$ilDB->quote($a_sub_type ,'text')." ".
			"AND os.obj_id = ".$ilDB->quote($a_obj_id ,'integer')
			;

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
		foreach(ilAdvancedMDFieldDefinition::getInstancesByRecordId($a_record_id) as $field)
		{
			$field->delete();
		}
		
		$query = "DELETE FROM adv_md_record ".
			"WHERE record_id = ".$ilDB->quote($a_record_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
		
		$query = "DELETE FROM adv_md_record_objs ".
			"WHERE record_id = ".$ilDB->quote($a_record_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
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
	 	global $ilDB;
	 	
	 	// Save import id if given
	 	$next_id = $ilDB->nextId('adv_md_record');
	 	
	 	$query = "INSERT INTO adv_md_record (record_id,import_id,active,title,description) ".
	 		"VALUES(".
	 		$ilDB->quote($next_id,'integer').", ".
			$this->db->quote($this->getImportId(),'text').", ".
	 		$this->db->quote($this->isActive() ,'integer').", ".
	 		$this->db->quote($this->getTitle() ,'text').", ".
	 		$this->db->quote($this->getDescription() ,'text')." ".
	 		")";
		$res = $ilDB->manipulate($query);
	 	$this->record_id = $next_id;

	 	if(!strlen($this->getImportId()))
	 	{
		 	// set import id to default value
		 	$query = "UPDATE adv_md_record ".
		 		"SET import_id = ".$this->db->quote($this->generateImportId() ,'text')." ".
		 		"WHERE record_id = ".$this->db->quote($this->record_id ,'integer')." ";
			$res = $ilDB->manipulate($query);
	 	}

	 	foreach($this->getAssignedObjectTypes() as $type)
	 	{
	 		global $ilDB;

	 		$query = "INSERT INTO adv_md_record_objs (record_id,obj_type,sub_type) ".
	 			"VALUES( ".
	 			$this->db->quote($this->getRecordId() ,'integer').", ".
	 			$this->db->quote($type["obj_type"] ,'text').", ".
	 			$this->db->quote($type["sub_type"] ,'text')." ".
	 			")";
			$res = $ilDB->manipulate($query);
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
	 	global $ilDB;
	 	
	 	$query = "UPDATE adv_md_record ".
	 		"SET active = ".$this->db->quote($this->isActive() ,'integer').", ".
	 		"title = ".$this->db->quote($this->getTitle() ,'text').", ".
	 		"description = ".$this->db->quote($this->getDescription() ,'text')." ".
	 		"WHERE record_id = ".$this->db->quote($this->getRecordId() ,'integer')." ";
		$res = $ilDB->manipulate($query);
				
		// Delete assignments
	 	$query = "DELETE FROM adv_md_record_objs ".
	 		"WHERE record_id = ".$this->db->quote($this->getRecordId() ,'integer')." ";
		$res = $ilDB->manipulate($query);
			
	 	// Insert assignments
	 	foreach($this->getAssignedObjectTypes() as $type)
	 	{
	 		$query = "INSERT INTO adv_md_record_objs (record_id,obj_type, sub_type) ".
	 			"VALUES ( ".
	 			$this->db->quote($this->getRecordId() ,'integer').", ".
	 			$this->db->quote($type["obj_type"] ,'text').", ".
	 			$this->db->quote($type["sub_type"] ,'text')." ".
	 			")";
			$res = $ilDB->manipulate($query);
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
	public function appendAssignedObjectType($a_obj_type, $a_sub_type)
	{
	 	$this->obj_types[] = array("obj_type"=>$a_obj_type, "sub_type"=>$a_sub_type);
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
	 * Is assigned object type?
	 *
	 * @param
	 * @return
	 */
	function isAssignedObjectType($a_obj_type, $a_sub_type)
	{
		foreach ($this->getAssignedObjectTypes() as $t)
		{
			if ($t["obj_type"] == $a_obj_type &&
				$t["sub_type"] == $a_sub_type)
			{
				return true;
			}
		}
		return false;
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
	 		'id' => $this->generateImportId()));
	 	$writer->xmlElement('Title',null,$this->getTitle());
	 	$writer->xmlElement('Description',null,$this->getDescription());
	 	
	 	foreach($this->getAssignedObjectTypes() as $obj_type)
	 	{
	 		if ($obj_type["sub_type"] == "")
	 		{
	 			$writer->xmlElement('ObjectType',null,$obj_type["obj_type"]);
	 		}
	 		else
	 		{
	 			$writer->xmlElement('ObjectType',null,$obj_type["obj_type"].":".$obj_type["sub_type"]);
	 		}
	 	}
	 	
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
	 	foreach(ilAdvancedMDFieldDefinition::getInstancesByRecordId($this->getRecordId()) as $definition)
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
	 	global $ilDB;
	 	
	 	$query = "SELECT * FROM adv_md_record ".
	 		"WHERE record_id = ".$this->db->quote($this->getRecordId() ,'integer')." ";
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setImportId($row->import_id);
			$this->setActive($row->active);
			$this->setTitle($row->title);
			$this->setDescription($row->description);
		}
		$query = "SELECT * FROM adv_md_record_objs ".
	 		"WHERE record_id = ".$this->db->quote($this->getRecordId() ,'integer')." ";
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->obj_types[] = array("obj_type" => $row->obj_type,
	 			"sub_type" => $row->sub_type);
	 	}
	}
	
	/**
	 * generate unique record id
	 *
	 * @access protected
	 * @return
	 */
	protected function generateImportId()
	{
		return 'il_'.IL_INST_ID.'_adv_md_record_'.$this->getRecordId();
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
	
	/**
	 * Save repository object record selection
	 *
	 * @param integer $a_obj_id object id if repository object
	 * @param array $a_records array of record ids that are selected (in use) by the object
	 */
	static function saveObjRecSelection($a_obj_id, $a_sub_type = "", $a_records)
	{
		global $ilDB;
		
		if ($a_sub_type == "")
		{
			$a_sub_type = "-";
		}

		$ilDB->manipulate("DELETE FROM adv_md_obj_rec_select WHERE ".
			" obj_id = ".$ilDB->quote($a_obj_id, "integer").
			" AND sub_type = ".$ilDB->quote($a_sub_type, "text"));
		
		if (is_array($a_records))
		{
			foreach ($a_records as $r)
			{
				if ($r > 0)
				{
					$ilDB->manipulate("INSERT INTO adv_md_obj_rec_select ".
						"(obj_id, rec_id, sub_type) VALUES (".
						$ilDB->quote($a_obj_id, "integer").",".
						$ilDB->quote($r, "integer").",".
						$ilDB->quote($a_sub_type, "text").
						")");
				}
			}
		}
	}
	
	/**
	 * Get repository object record selection
	 *
	 * @param integer $a_obj_id object id if repository object
	 * @param array $a_records array of record ids that are selected (in use) by the object
	 */
	static function getObjRecSelection($a_obj_id, $a_sub_type = "")
	{
		global $ilDB;
		
		if ($a_sub_type == "")
		{
			$a_sub_type = "-";
		}
		
		$recs = array();
		$set = $ilDB->query($r = "SELECT * FROM adv_md_obj_rec_select ".
			" WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer").
			" AND sub_type = ".$ilDB->quote($a_sub_type, "text")
			);
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$recs[] = $rec["rec_id"];
		}
		return $recs;
	}

}
?>