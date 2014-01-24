<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCourseMappingRule 
{
	const SUBDIR_ATTRIBUTE_NAME = 1;
	const SUBDIR_VALUE = 2;
	
	private $rid;
	private $sid;
	private $mid;
	private $attribute;
	private $ref_id;
	private $is_filter = false;
	private $filter;
	private $create_subdir = true;
	private $subdir_type = self::SUBDIR_VALUE;
	private $directory = '';
	
	
	/**
	 * Constructor
	 * @param int $a_rid
	 */
	public function __construct($a_rid = 0)
	{
		$this->rid = $a_rid;
		$this->read();
	}
	
	/**
	 * Lookup existing attributes
	 * @param type $a_attributes
	 * @return array
	 */
	public static function lookupLastExistingAttribute($a_sid,$a_mid,$a_ref_id)
	{
		global $ilDB;
		
		$query = 'SELECT attribute FROM ecs_cmap_rule '.
				'WHERE sid = '.$ilDB->quote($a_sid,'integer').' '.
				'AND mid = '.$ilDB->quote($a_mid,'integer').' '.
				'AND ref_id = '.$ilDB->quote($a_ref_id,'integer').' '.
				'ORDER BY rid ';
		$res = $ilDB->query($query);
		
		$attributes = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$attributes = $row->attribute;
		}
		return $attributes;
	}
	
	/**
	 * 
	 * @param type $a_sid
	 * @param type $a_mid
	 */
	public static function getRuleRefIds($a_sid, $a_mid)
	{
		global $ilDB;
		
		$query = 'SELECT DISTINCT(ref_id) ref_id, rid FROM ecs_cmap_rule '.
				'WHERE sid = '.$ilDB->quote($a_sid,'integer').' '.
				'AND mid = '.$ilDB->quote($a_mid,'integer').' '.
				'GROUP BY ref_id'.' '.
				'ORDER BY rid';
		
		$res = $ilDB->query($query);
		$ref_ids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ref_ids[] = $row->ref_id;
		}
		return $ref_ids;
	}
	
	/**
	 * Get all rule of ref_id
	 * @global type $ilDB
	 * @param type $a_sid
	 * @param type $a_mid
	 * @param type $a_ref_id
	 */
	public static function getRulesOfRefId($a_sid, $a_mid, $a_ref_id)
	{
		global $ilDB;
		
		$query = 'SELECT rid FROM ecs_cmap_rule '.
				'WHERE sid = '.$ilDB->quote($a_sid,'integer').' '.
				'AND mid = '.$ilDB->quote($a_mid,'integer').' '.
				'AND ref_id = '.$ilDB->quote($a_ref_id,'integer');
		$res = $ilDB->query($query);
		$rids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rids = $row->rid;
		}
		return (array) $rids;
	}
	
	public static function hasRules($a_sid, $a_mid, $a_ref_id)
	{
		global $ilDB;
		
		$query = 'SELECT ref_id FROM ecs_cmap_rule '.
				'WHERE sid = '.$ilDB->quote($a_sid,'integer').' '.
				'AND mid = '.$ilDB->quote($a_mid,'integer').' '.
				'AND ref_id = '.$ilDB->quote($a_ref_id,'integer');
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false;
	}
	
	/**
	 * Check if rule matches
	 * @param type $course
	 * @param type $a_start_rule_id
	 */
	public static function isMatching($course, $a_sid, $a_mid, $a_ref_id)
	{
		global $ilDB;
		
		$query = 'SELECT rid FROM ecs_cmap_rule '.
				'WHERE sid = '.$ilDB->quote($a_sid,'integer'). ' '.
				'AND mid = '.$ilDB->quote($a_mid,'integer').' '.
				'AND ref_id = '.$ilDB->quote($a_ref_id,'integer').' '.
				'ORDER BY rid';
		$res = $ilDB->query($query);
		$matches = false;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rule = new ilECSCourseMappingRule($row->rid);
			if(!$rule->matches($course))
			{
				return false;
			}
			else
			{
				$matches = true;
			}
		}
		return $matches;
	}
	
	/**
	 * 
	 * @param type $course
	 * @param type $a_sid
	 * @param type $a_mid
	 * @param type $a_ref_id
	 */
	public static function doMappings($course,$a_sid,$a_mid, $a_ref_id)
	{
		global $ilDB;
		
		$query = 'SELECT rid FROM ecs_cmap_rule '.
				'WHERE sid = '.$ilDB->quote($a_sid,'integer'). ' '.
				'AND mid = '.$ilDB->quote($a_mid,'integer').' '.
				'AND ref_id = '.$ilDB->quote($a_ref_id,'integer').' '.
				'ORDER BY rid';
		$res = $ilDB->query($query);
		
		$first = true;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rule = new ilECSCourseMappingRule($row->rid);
			if($first)
			{
				$parent_ref = $rule->getRefId();
			}
			$parent_ref = $rule->doMapping($course,$parent_ref);
			$first = false;
		}
		return $parent_ref;
	}
	
	/**
	 * Do mapping
	 * @param type $course
	 * @param type $parent_ref
	 */
	public function doMapping($course,$parent_ref)
	{
		global $tree;
		
		if(!$this->isSubdirCreationEnabled())
		{
			return $parent_ref;
		}
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSMappingUtils.php';
		$value = ilECSMappingUtils::getCourseValueByMappingAttribute($course, $this->getAttribute());
		
		$childs = $tree->getChildsByType($parent_ref,'cat');
		
		$existing_ref = 0;
		foreach((array) $childs as $child)
		{
			if(strcmp($child['title'], $value) === 0)
			{
				$existing_ref = $child['child'];
				break;
			}
		}
		if(!$existing_ref)
		{
			// Create category
			include_once './Modules/Category/classes/class.ilObjCategory.php';
			$cat = new ilObjCategory();
			$cat->setTitle($value);
			$cat->create();
			$cat->createReference();
			$cat->putInTree($parent_ref);
			$cat->setPermissions($parent_ref);
			$cat->deleteTranslation($GLOBALS['lng']->getDefaultLanguage());
			$cat->addTranslation(
					$value,
					$cat->getLongDescription(),
					$GLOBALS['lng']->getDefaultLanguage(),
					1
			);
			return $cat->getRefId();
		}
		return $existing_ref;
	}


	/**
	 * Check if rule matches
	 * @param type $course
	 * @return boolean
	 */
	public function matches($course)
	{
		if($this->isFilterEnabled())
		{
			include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSMappingUtils.php';
			$value = ilECSMappingUtils::getCourseValueByMappingAttribute($course, $this->getAttribute());
			$GLOBALS['ilLog']->write(__METHOD__.': Comparing '. $value . ' with ' . $this->getFilter());
			return strcmp($value, $this->getFilter()) === 0;
		}
		return true;
	}
	
	
	/**
	 * Get rule instance by attribute 
	 * @global type $ilDB
	 * @param type $a_sid
	 * @param type $a_mid
	 * @param type $a_ref_id
	 * @param type $a_att
	 * @return \ilECSCourseMappingRule
	 */
	public static function getInstanceByAttribute($a_sid,$a_mid,$a_ref_id,$a_att)
	{
		global $ilDB;
		
		$query = 'SELECT rid FROM ecs_cmap_rule '.
				'WHERE sid = '.$ilDB->quote($a_sid,'integer').' '.
				'AND mid = '.$ilDB->quote($a_mid,'integer').' '.
				'AND ref_id = '.$ilDB->quote($a_ref_id,'integer').' '.
				'AND attribute = '.$ilDB->quote($a_att,'text');

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return new ilECSCourseMappingRule($row->rid);
		}
		return new ilECSCourseMappingRule();
	}
	
	public function setRuleId($a_rule_id)
	{
		$this->rid = $a_rule_id;
	}
	
	public function getRuleId()
	{
		return $this->rid;
	}
	
	public function setServerId($a_server_id)
	{
		$this->sid = $a_server_id;
	}
	
	public function getServerId()
	{
		return $this->sid;
	}

	public function setMid($a_mid)
	{
		$this->mid = $a_mid;
	}
	
	public function getMid()
	{
		return $this->mid;
	}
	
	public function setAttribute($a_att)
	{
		$this->attribute = $a_att;
	}
	
	public function getAttribute()
	{
		return $this->attribute;
	}
	
	public function setRefId($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}
	
	public function getRefId()
	{
		return $this->ref_id;
	}
	
	public function enableFilter($a_status)
	{
		$this->is_filter = $a_status;
	}
	
	public function isFilterEnabled()
	{
		return $this->is_filter;
	}
	
	public function setFilter($a_filter)
	{
		$this->filter = $a_filter;
	}
	
	public function getFilter()
	{
		return $this->filter;
	}
	
	public function enableSubdirCreation($a_stat)
	{
		$this->create_subdir = $a_stat;
	}
	
	public function isSubdirCreationEnabled()
	{
		return $this->create_subdir;
	}
	
	public function setSubDirectoryType($a_type)
	{
		$this->subdir_type = $a_type;
	}
	
	public function getSubDirectoryType()
	{
		return self::SUBDIR_VALUE;
	}
	
	public function setDirectory($a_dir)
	{
		$this->directory = $a_dir;
	}
	
	public function getDirectory()
	{
		return $this->directory;
	}
	
	public function delete()
	{
		global $ilDB;
		
		$query = 'DELETE from ecs_cmap_rule '.
				'WHERE rid = '.$ilDB->quote($this->getRuleId(),'integer');
		$ilDB->manipulate($query);
		return true;
	}
	
	/**
	 * Save a new rule
	 * @global type $ilDB
	 * @return boolean
	 */
	public function save()
	{
		global $ilDB;
		
		$this->setRuleId($ilDB->nextId('ecs_cmap_rule'));
		$query = 'INSERT INTO ecs_cmap_rule '.
				'(rid,sid,mid,attribute,ref_id,is_filter,filter,create_subdir,subdir_type,directory) '.
				'VALUES ('.
				$ilDB->quote($this->getRuleId(),'integer').', '.
				$ilDB->quote($this->getServerId(),'integer').', '.
				$ilDB->quote($this->getMid(),'integer').', '.
				$ilDB->quote($this->getAttribute(),'text').', '.
				$ilDB->quote($this->getRefId(),'integer').', '.
				$ilDB->quote($this->isFilterEnabled(),'integer').', '.
				$ilDB->quote($this->getFilter(),'text').', '.
				$ilDB->quote($this->isSubdirCreationEnabled(),'integer').', '.
				$ilDB->quote($this->getSubDirectoryType(),'integer').', '.
				$ilDB->quote($this->getDirectory(),'text').' '.
				')';
		$ilDB->manipulate($query);
		return $this->getRuleId();
	}
	
	/**
	 * Update mapping rule
	 * @global type $ilDB
	 */
	public function update()
	{
		global $ilDB;
		
		$query = 'UPDATE ecs_cmap_rule '.' '.
				'SET '.
				'attribute = '.$ilDB->quote($this->getAttribute(),'text').', '.
				'ref_id = '.$ilDB->quote($this->getRefId(),'integer').', '.
				'is_filter = '.$ilDB->quote($this->isFilterEnabled(),'integer').', '.
				'filter = '.$ilDB->quote($this->getFilter(),'text').', '.
				'create_subdir = '.$ilDB->quote($this->isSubdirCreationEnabled(),'integer').', '.
				'subdir_type = '.$ilDB->quote($this->getSubDirectoryType(),'integer').', '.
				'directory = '.$ilDB->quote($this->getDirectory(),'text').' '.
				'WHERE rid = '.$ilDB->quote($this->getRuleId(),'integer');
		$ilDB->manipulate($query);
	}

	/**
	 * Read db entries
	 */
	protected function read()
	{
		global $ilDB;
		
		if(!$this->getRuleId())
		{
			return true;
		}
		$query = 'SELECT * from ecs_cmap_rule '.' '.
				'WHERE rid = '.$ilDB->quote($this->getRuleId(),'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setServerId($row->sid);
			$this->setMid($row->mid);
			$this->setRefId($row->ref_id);
			$this->setAttribute($row->attribute);
			$this->enableFilter($row->is_filter);
			$this->setFilter($row->filter);
			$this->enableSubdirCreation($row->create_subdir);
			$this->setSubDirectoryType($row->subdir_type);
			$this->setDirectory($row->directory);
		}
	}
}
?>