<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Genearal
 */
class ilECSNodeMappingSettings
{
	private static $instance = null;

	private $storage = null;

	
	/**
	 * Directory allocation
	 * @var type 
	 */
	private $directory_active = false;
	private $create_empty_containers = false;
	
	/**
	 * Course allocation
	 */
	private $course_active = false;
	private $default_cat = 0;
	private $allinone = false;
	private $allinone_cat = 0;
	private $attributes = false;
	private $role_mappings = array();

	/**
	 * Singeleton constructor
	 */
	protected function  __construct()
	{
		$this->initStorage();
		$this->read();
	}

	/**
	 * Get singeleton instance
	 * @return ilECSNodeMappingSettings
	 */
	public static function getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilECSNodeMappingSettings();
	}

	/**
	 * Check if node mapping is enabled
	 * @return bool
	 */
	public function isDirectoryMappingEnabled()
	{
		return $this->directory_active;
	}

	/**
	 * Enable node mapping
	 * @param bool $a_status
	 */
	public function enableDirectoryMapping($a_status)
	{
		$this->directory_active = $a_status;
	}

	/**
	 * enable creation of empty containers
	 * @param bool $a_status 
	 */
	public function enableEmptyContainerCreation($a_status)
	{
		$this->create_empty_containers = $a_status;
	}

	/**
	 * Check if the creation of empty containers (pathes without courses) is enabled
	 * @return bool
	 */
	public function isEmptyContainerCreationEnabled()
	{
		return $this->create_empty_containers;
	}
	
	public function enableCourseAllocation($a_stat)
	{
		$this->course_active = $a_stat;
	}

	public function isCourseAllocationEnabled()
	{
		return $this->course_active;
	}
	
	public function setDefaultCourseCategory($a_def)
	{
		$this->default_cat = $a_def;
	}
	
	public function getDefaultCourseCategory()
	{
		return $this->default_cat;
	}
	
	public function isAllInOneCategoryEnabled()
	{
		return $this->allinone;
	}
	
	public function enableAllInOne($a_stat)
	{
		$this->allinone = $a_stat;
	}
	
	public function setAllInOneCategory($a_cat)
	{
		$this->allinone_cat = $a_cat;
	}
	
	public function getAllInOneCategory()
	{
		return $this->allinone_cat;
	}
	
	public function enableAttributeMapping($a_stat)
	{
		$this->attributes = $a_stat;
	}
	
	public function isAttributeMappingEnabled()
	{
		return $this->attributes;
	}
	
	public function setRoleMappings($a_mappings)
	{
		$this->role_mappings = $a_mappings;
	}
	
	public function getRoleMappings()
	{
		return $this->role_mappings;
	}

	/**
	 * Save settings to db
	 */
	public function update()
	{
		$this->getStorage()->set('directory_active', (int) $this->isDirectoryMappingEnabled());
		$this->getStorage()->set('create_empty', $this->isEmptyContainerCreationEnabled());
		$this->getStorage()->set('course_active', $this->isCourseAllocationEnabled());
		$this->getStorage()->set('default_category', $this->getDefaultCourseCategory());
		$this->getStorage()->set('allinone', $this->isAllInOneCategoryEnabled());
		$this->getStorage()->set('allinone_cat', $this->getAllInOneCategory());
		$this->getStorage()->set('attributes', $this->isAttributeMappingEnabled());
		$this->getStorage()->set('role_mappings',serialize($this->getRoleMappings()));
		return true;
	}

	/**
	 * Get storage
	 * @return ilSetting
	 */
	protected function getStorage()
	{
		return $this->storage;
	}

	/**
	 * Init storage
	 */
	protected function initStorage()
	{
		global $ilSetting;

		$this->storage = new ilSetting('ecs_node_mapping');
	}

	/**
	 * Read settings from db
	 */
	protected function read()
	{
		$this->enableDirectoryMapping($this->getStorage()->get('active', $this->directory_active));
		$this->enableEmptyContainerCreation($this->getStorage()->get('create_empty'),$this->create_empty_containers);
		$this->enableCourseAllocation($this->getStorage()->get('course_active'),$this->course_active);
		$this->setDefaultCourseCategory($this->getStorage()->get('default_category'),$this->default_cat);
		$this->enableAllInOne($this->getStorage()->get('allinone'),$this->allinone);
		$this->setAllInOneCategory($this->getStorage()->get('allinone_cat'),$this->allinone_cat);
		$this->enableAttributeMapping($this->getStorage()->get('attributes'),$this->attributes);
		$this->setRoleMappings(unserialize($this->getStorage()->get('role_mappings')),serialize($this->role_mappings));
	}
}

?>