<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Global settings for org units
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilOrgUnitGlobalSettings
{
	/**
	 * @var ilOrgUnitGlobalSettings
	 */
	private static $instance = null;
	
	/**
	 * @var ilObjectDefinition
	 */
	protected $object_definition = null;
	
	
	/**
	 * @var ilOrgUnitObjectPositionSetting[]
	 */
	private $position_settings = [];
	
	/**
	 * Singelton constructor
	 */
	protected function __construct()
	{
		$this->object_definition = $GLOBALS['DIC']['objDefinition'];
		$this->read();
	}
	
	/**
	 * Get instance
	 * @return ilOrgUnitGlobalSettings
	 */
	public static function getInstance()
	{
		if(!self::$instance)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Get object position settings by type
	 * @param string $a_obj_type
	 * @return ilOrgUnitObjectPositionSetting
	 * @throws \InvalidArgumentException
	 */
	public function getObjectPositionSettingsByType($a_obj_type)
	{
		if(!isset($this->position_settings[$a_obj_type]))
		{
			throw new \InvalidArgumentException('Object type passed does not support position settings: ' . $a_obj_type);
		}
		return $this->position_settings[$a_obj_type];
	}
	
	/**
	 * read settings
	 */
	protected function readSettings()
	{
		foreach($this->object_definition->getOrgUnitPermissionTypes() as $type)
		{
			$this->position_settings[$type] = new ilOrgUnitObjectPositionSetting($type);
		}
	}
}
?>