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
	 * read settings
	 */
	protected function readSettings()
	{
		foreach($this->object_definition->getOrgUnitPermissionTypes() as $type)
		{
			$this->position_settings[] = new ilOrgUnitObjectPositionSetting($type);
		}
	}
}
?>