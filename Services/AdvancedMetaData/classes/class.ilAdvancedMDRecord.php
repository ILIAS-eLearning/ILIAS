<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordScope.php';

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

	/**
	 * @var int
	 */
	protected $global_position;

	protected $import_id;
	protected $active;
	protected $title;
	protected $description;
	protected $obj_types = array();
	protected $db = null;
	protected $parent_obj; // [int]
	protected $scope_enabled = false;

	/**
	 * @var ilAdvancedMDRecordScope[]
	 */
	protected $scopes = [];
	

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
	 	global $DIC;

	 	$ilDB = $DIC['ilDB'];
	 	
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
	 * @return ilAdvancedMDRecord
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
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		$query = "SELECT DISTINCT(amr.record_id) FROM adv_md_record amr ".
			"JOIN adv_mdf_definition amfd ON amr.record_id = amfd.record_id ".
			"WHERE searchable = 1 AND active = 1 ";
			
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
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
		
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		$query = "SELECT title FROM adv_md_record ".
			"WHERE record_id = ".$ilDB->quote($a_record_id ,'integer')." ";
		$res = $ilDB->query($query);
		$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
		
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
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		$query = "SELECT record_id FROM adv_md_record ".
			"WHERE import_id = ".$ilDB->quote($a_ilias_id ,'text')." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
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
		global $DIC;

		$objDefinition = $DIC['objDefinition'];
		$lng = $DIC['lng'];
		
		$types = array();
		$filter = array();
		$amet_types = $objDefinition->getAdvancedMetaDataTypes();

		if(!ilECSSetting::ecsConfigured()){
			$filter = array_merge($filter ,ilECSUtils::getPossibleRemoteTypes(false));
			$filter[] = 'rtst';
		}

		foreach ($amet_types as $at)
		{
			if(in_array($at["obj_type"],$filter)) {
				continue;
			}

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

		sort($types);
		return $types;
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
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		$query = "SELECT DISTINCT(obj_type) FROM adv_md_record_objs amo ".
			"JOIN adv_md_record amr ON amo.record_id = amr.record_id ".
			"WHERE active = 1 ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
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
	 * @return ilAdvancedMDRecord[]
	 */
	public static function _getRecords()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		$query = "SELECT record_id FROM adv_md_record ORDER BY gpos ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
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
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		$records = array();
		
		$query = "SELECT * FROM adv_md_record_objs WHERE sub_type=".$ilDB->quote("-", "text");
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$records[$row->obj_type][] = self::_getInstanceByRecordId($row->record_id);
		}
		// #13359 hide ecs if not configured
		if(!ilECSSetting::ecsConfigured()){
			$filter = ilECSUtils::getPossibleRemoteTypes(false);
			$filter[] = 'rtst';
			$records = array_diff_key($records, array_flip($filter));
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
	 * @return ilAdvancedMDRecord[]
	 */
	public static function _getActivatedRecordsByObjectType($a_obj_type, $a_sub_type = "", $a_only_optional = false)
	{
		global $DIC;		

		$ilDB = $DIC['ilDB'];

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
		
		if($a_only_optional)
		{
			$query .= " AND optional =".$ilDB->quote(1, 'integer');
		}
		
		// #16428
		$query .= "ORDER by parent_obj DESC, record_id";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$records[] = self::_getInstanceByRecordId($row->record_id);
		}

		return $records;
	}
	
	/**
	 * Get selected records by object
	 *
	 * @param string $a_obj_type object type
	 * @param string $a_ref_id reference id
	 * @param string $a_sub_type sub type
	 */
	public static function _getSelectedRecordsByObject($a_obj_type, $a_ref_id, $a_sub_type = "")
	{		
		$records = array();
//		ilUtil::printBacktrace(10);
//		var_dump($a_obj_type."-".$a_ref_id."-".$a_sub_type); exit;
		if ($a_sub_type == "")
		{
			$a_sub_type = "-";
		}
		
		$a_obj_id = ilObject::_lookupObjId($a_ref_id);
		
		// object-wide metadata configuration setting		
		include_once 'Services/Container/classes/class.ilContainer.php';
		include_once 'Services/Object/classes/class.ilObjectServiceSettingsGUI.php';			
		$config_setting = ilContainer::_lookupContainerSetting(
			$a_obj_id,
			ilObjectServiceSettingsGUI::CUSTOM_METADATA,
			false);		
		
		$optional = array();
		foreach(self::_getActivatedRecordsByObjectType($a_obj_type, $a_sub_type) as $record)
		{
			// check scope
			if(self::isFilteredByScope($a_ref_id, $record->getScopes()))
			{
				continue;
			}
			
			foreach($record->getAssignedObjectTypes() as $item)
			{				
				if($record->getParentObject())
				{
					// only matching local records
					if($record->getParentObject() != $a_obj_id)
					{
						continue;
					}
					// if object-wide setting is off, ignore local records
					else if(!$config_setting)
					{
						continue;
					}
				}
				
				if($item['obj_type'] == $a_obj_type &&
					$item['sub_type'] == $a_sub_type)
				{
					if($item['optional'])
					{
						$optional[] = $record->getRecordId();
					}
					$records[$record->getRecordId()] = $record;
				}
			}
		}
		
		if($optional)
		{
			if(!$config_setting && !in_array($a_sub_type, array("orgu_type", "prg_type"))) //#16925 + #17777
			{
				$selected = array();
			}
			else
			{
				$selected = self::getObjRecSelection($a_obj_id, $a_sub_type);
			}
			foreach($optional as $record_id)
			{
				if(!in_array($record_id, $selected))
				{
					unset($records[$record_id]);
				}
			}
		}


		$orderings = new ilAdvancedMDRecordObjectOrderings();
		$records = $orderings->sortRecords($records, $a_obj_id);
		
		return $records;
	}
	
	/**
	 * Check if a given ref id is not filtered by scope restriction.
	 * @param type $a_ref_id
	 * @param ilAdvancedMDRecordScope[] $scopes
	 */
	public static function isFilteredByScope($a_ref_id, array $scopes)
	{
		$tree = $GLOBALS['DIC']->repositoryTree();
		$logger = $GLOBALS['DIC']->logger()->amet();
		
		if(!count($scopes))
		{
			$logger->debug('No md scope restrictions.');
			return false;
		}
		foreach($scopes as $scope)
		{
			$logger->debug('Comparing: ' . $a_ref_id .' with: ' . $scope->getRefId());
			if($scope->getRefId() == $a_ref_id)
			{
				$logger->debug('Elements are equal. No scope restrictions.');
				return false;
			}
			if($tree->getRelation($scope->getRefId(), $a_ref_id) == ilTree::RELATION_PARENT)
			{
				$logger->debug('Node is child node. No scope restrictions.');
				return false;
			}
		}
		$logger->info('Scope filter matches.');
		
		return true;
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
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
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
		ilAdvancedMDRecordScope::deleteByRecordI($this->getRecordId());
	}
	
	/**
	 * Is scope enabled
	 * @return scope
	 */
	public function enabledScope()
	{
		return $this->scope_enabled;
	}
	
	/**
	 * Enable scope restrictions
	 * @param bool $a_stat
	 */
	public function enableScope($a_stat)
	{
		$this->scope_enabled = $a_stat;
	}
	
	/**
	 * Set scopes
	 * @param array $a_scopes
	 */
	public function setScopes(array $a_scopes)
	{
		$this->scopes = $a_scopes;
	}
	
	/**
	 * Get scopes
	 * @return ilAdvancedMDRecordScope[]
	 */
	public function getScopes()
	{
		return $this->scopes;
	}
	
	/**
	 * Get scope gef_ids
	 * @return type
	 */
	public function getScopeRefIds()
	{
		$ref_ids = [];
		foreach($this->scopes as $scope)
		{
			$ref_ids[] = $scope->getRefId();
		}
		return $ref_ids;
	}
	
	/**
	 * save
	 *
	 * @access public
	 * 
	 */
	public function save()
	{
	 	global $DIC;

	 	$ilDB = $DIC['ilDB'];
	 	
	 	// Save import id if given
	 	$next_id = $ilDB->nextId('adv_md_record');
	 	
	 	$query = "INSERT INTO adv_md_record (record_id,import_id,active,title,description,parent_obj) ".
	 		"VALUES(".
	 		$ilDB->quote($next_id,'integer').", ".
			$this->db->quote($this->getImportId(),'text').", ".
	 		$this->db->quote($this->isActive() ,'integer').", ".
	 		$this->db->quote($this->getTitle() ,'text').", ".
	 		$this->db->quote($this->getDescription() ,'text').", ".
	 		$this->db->quote($this->getParentObject() ,'integer')." ".
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
	 		global $DIC;

	 		$ilDB = $DIC['ilDB'];

	 		$query = "INSERT INTO adv_md_record_objs (record_id,obj_type,sub_type,optional) ".
	 			"VALUES( ".
	 			$this->db->quote($this->getRecordId() ,'integer').", ".
	 			$this->db->quote($type["obj_type"] ,'text').", ".
	 			$this->db->quote($type["sub_type"] ,'text').", ".
	 			$this->db->quote($type["optional"] ,'integer')." ".
	 			")";
			$res = $ilDB->manipulate($query);
	 	}
		
		foreach($this->getScopes() as $scope)
		{
			$scope->setRecordId($this->getRecordId());
			$scope->save();
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
	 	global $DIC;

	 	$ilDB = $DIC['ilDB'];
	 	
	 	$query = "UPDATE adv_md_record ".
	 		"SET active = ".$this->db->quote($this->isActive() ,'integer').", ".
	 		"title = ".$this->db->quote($this->getTitle() ,'text').", ".
	 		"description = ".$this->db->quote($this->getDescription() ,'text').", ".
			'gpos = '  . $this->db->quote($this->getGlobalPosition(),'integer').' '.
	 		"WHERE record_id = ".$this->db->quote($this->getRecordId() ,'integer')." ";
		$res = $ilDB->manipulate($query);
				
		// Delete assignments
	 	$query = "DELETE FROM adv_md_record_objs ".
	 		"WHERE record_id = ".$this->db->quote($this->getRecordId() ,'integer')." ";
		$res = $ilDB->manipulate($query);
			
	 	// Insert assignments
	 	foreach($this->getAssignedObjectTypes() as $type)
	 	{
	 		$query = "INSERT INTO adv_md_record_objs (record_id,obj_type,sub_type,optional) ".
	 			"VALUES ( ".
	 			$this->db->quote($this->getRecordId() ,'integer').", ".
	 			$this->db->quote($type["obj_type"] ,'text').", ".
	 			$this->db->quote($type["sub_type"] ,'text').", ".
	 			$this->db->quote($type["optional"] ,'integer')." ".
	 			")";
			$res = $ilDB->manipulate($query);
	 	}
		ilAdvancedMDRecordScope::deleteByRecordI($this->getRecordId());
		foreach($this->getScopes() as $scope)
		{
			$scope->setRecordId($this->getRecordId());
			$scope->save();
		}
	}
	
	/**
	 * Validate settings
	 *
	 * @access public
	 * 
	 */
	public function validate()
	{	 	
	 	if(!strlen($this->getTitle()))
	 	{	 		
	 		return false;
	 	}
	 	return true;
	}

	/**
	 * Set global position
	 * @param int $position
	 */
	public function setGlobalPosition(int $position)
	{
		$this->global_position = $position;
	}

	/**
	 * @return int
	 */
	public function getGlobalPosition() : int
	{
		return $this->global_position;
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
	public function appendAssignedObjectType($a_obj_type, $a_sub_type, $a_optional = false)
	{
	 	$this->obj_types[] = array(
			"obj_type"=>$a_obj_type, 
			"sub_type"=>$a_sub_type,
			"optional"=>(bool)$a_optional
		);
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
	
	function setParentObject($a_obj_id)
	{		
		$this->parent_obj = $a_obj_id;
	}
	
	function getParentObject()
	{		
		return $this->parent_obj;
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
			$optional = array("optional"=>$obj_type["optional"]);
	 		if ($obj_type["sub_type"] == "")
	 		{
	 			$writer->xmlElement('ObjectType',$optional,$obj_type["obj_type"]);
	 		}
	 		else
	 		{
	 			$writer->xmlElement('ObjectType',$optional,$obj_type["obj_type"].":".$obj_type["sub_type"]);
	 		}
	 	}
		
		// scopes
		if(count($this->getScopeRefIds()))
		{
			$writer->xmlStartTag('Scope');
		}
		foreach($this->getScopeRefIds() as $ref_id)
		{
			$type = ilObject::_lookupType(ilObject::_lookupObjId($ref_id));
			$writer->xmlElement('ScopeEntry',['id' => 'il_'.IL_INST_ID.'_'.$type.'_'.$ref_id]);
		}
		if(count($this->getScopeRefIds()))
		{
			$writer->xmlEndTag('Scope');
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
	 	global $DIC;

	 	$ilDB = $DIC['ilDB'];
	 	
	 	$query = "SELECT * FROM adv_md_record ".
	 		"WHERE record_id = ".$this->db->quote($this->getRecordId() ,'integer')." ";
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$this->setImportId($row->import_id);
			$this->setActive($row->active);
			$this->setTitle($row->title);
			$this->setDescription($row->description);
			$this->setParentObject($row->parent_obj);
			$this->setGlobalPosition((int) $row->rpos);
		}
		$query = "SELECT * FROM adv_md_record_objs ".
	 		"WHERE record_id = ".$this->db->quote($this->getRecordId() ,'integer')." ";
	 	$res = $this->db->query($query);
	 	while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
	 	{
	 		$this->obj_types[] = array(
				"obj_type" => $row->obj_type,
	 			"sub_type" => $row->sub_type,
				"optional" => (bool)$row->optional
			);
	 	}
		
		$query = 'SELECT scope_id FROM adv_md_record_scope '.
			'WHERE record_id = '.$ilDB->quote($this->record_id);
		$res = $ilDB->query($query);
		$this->scope_enabled = false;
		$this->scopes = [];
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$this->scope_enabled = true;
			$this->scopes[] = new ilAdvancedMDRecordScope($row->scope_id);
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
	 * @param int $a_obj_id object id if repository object
	 * @param string $a_sub_type subtype
	 * @param int[] $a_records array of record ids that are selected (in use) by the object
	 * @param bool $a_delete_before delete before update
	 *
	 */
	public static function saveObjRecSelection($a_obj_id, $a_sub_type = "", array $a_records = null, $a_delete_before = true)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		if ($a_sub_type == "")
		{
			$a_sub_type = "-";
		}

		if((bool)$a_delete_before)
		{
			$ilDB->manipulate("DELETE FROM adv_md_obj_rec_select WHERE ".
				" obj_id = ".$ilDB->quote($a_obj_id, "integer").
				" AND sub_type = ".$ilDB->quote($a_sub_type, "text"));
		}
		
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
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
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
	
	/**
	 * Clone record
	 * 
	 * @param array &$a_fields_map
	 * @param type $a_parent_obj_id
	 * @return self
	 */
	public function _clone(array &$a_fields_map, $a_parent_obj_id = null)
	{		
		$new_obj = new self();
		$new_obj->setActive($this->isActive());
		$new_obj->setTitle($this->getTitle());
		$new_obj->setDescription($this->getDescription());				
		$new_obj->setParentObject($a_parent_obj_id
			? $a_parent_obj_id
			: $this->getParentObject());
		$new_obj->setAssignedObjectTypes($this->getAssignedObjectTypes());
		$new_obj->save();
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
		foreach(ilAdvancedMDFieldDefinition::getInstancesByRecordId($this->getRecordId()) as $definition)
		{
			$new_def = $definition->_clone($new_obj->getRecordId());			
			$a_fields_map[$definition->getFieldId()] = $new_def->getFieldId();
		}	

		return $new_obj;
	}
		
	/**
	 * Get shared records
	 * 
	 * @param int $a_obj1_id
	 * @param int $a_obj2_id
	 * @param string $a_sub_type
	 * @return array
	 */
	public static function getSharedRecords($a_obj1_id, $a_obj2_id, $a_sub_type = "-")
	{
		$obj_type = ilObject::_lookupType($a_obj1_id);
		$sel = array_intersect(
			ilAdvancedMDRecord::getObjRecSelection($a_obj1_id, $a_sub_type),
			ilAdvancedMDRecord::getObjRecSelection($a_obj2_id, $a_sub_type)
		);
		
		$res = array();
		
		foreach(self::_getRecords() as $record)
		{	
			// local records cannot be shared
			if($record->getParentObject())
			{
				continue;
			}
			
			// :TODO: inactive records can be ignored?
			if(!$record->isActive())
			{
				continue;
			}
			
			// parse assigned types
			foreach($record->getAssignedObjectTypes() as $item)
			{
				if($item["obj_type"] == $obj_type &&
					$item["sub_type"] == $a_sub_type)
				{				
					// mandatory
					if(!$item["optional"])
					{
						$res[] = $record->getRecordId();
					}
					// optional
					else if(in_array($record->getRecordId(), $sel))
					{
						$res[] = $record->getRecordId();
					}
				}
			}					
		}
		
		return $res;
	}
}

?>