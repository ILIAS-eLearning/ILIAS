<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Genearal
 */
class ilECSNodeMappingSettings
{
	private static $instance = null;

	private $storage = null;

	
	private $directory_active = false;
	private $create_empty_containers = false;

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

	/**
	 * Save settings to db
	 */
	public function update()
	{
		$this->getStorage()->set('directory_active', (int) $this->isDirectoryMappingEnabled());
		$this->getStorage()->set('create_empty', $this->isEmptyContainerCreationEnabled());
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
	}
}

?>