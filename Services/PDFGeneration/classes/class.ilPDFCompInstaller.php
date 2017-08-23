<?php

class ilPDFCompInstaller
{
	const PURPOSE_CONF_TABLE 		= "pdfgen_conf";
	const PURPOSE_MAP_TABLE 		= "pdfgen_map";
	const PURPOSE_PURPOSES_TABLE 	= "pdfgen_purposes";
	const RENDERER_TABLE			= "pdfgen_renderer";
	const RENDERER_AVAIL_TABLE 		= "pdfgen_renderer_avail";

	/**
	 * @param string $service
	 * @param string $purpose
	 * @param string $preferred
	 *
	 * @return void
	 */
	public static function registerPurpose($service, $purpose, $preferred)
	{
		global $DIC;
		/** @var ilDB $ilDB */
		$ilDB = $DIC['ilDB'];

		$ilDB->insert(self::PURPOSE_PURPOSES_TABLE, array(
				'purpose_id'	=>	array('int',	$ilDB->nextId(self::PURPOSE_PURPOSES_TABLE)),
				'service' 		=>	array('text', 	$service),
				'purpose' 		=>	array('text', 	$purpose),
			)
		);

		$ilDB->insert(self::PURPOSE_MAP_TABLE, array(
				'map_id'		=>	array('int',	$ilDB->nextId(self::PURPOSE_MAP_TABLE)),
				'service' 		=>	array('text', 	$service),
				'purpose' 		=>	array('text', 	$purpose),
				'preferred' 	=>	array('text', 	$preferred),
				'selected' 		=>	array('text', 	$preferred)
			)
		);
	}

	/**
	 * @param string $service
	 * @param string $identifier
	 *
	 * @return void
	 */
	public static function unregisterPurpose($service, $identifier)
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];

		// @TODO: Implement
	}

	/**
	 * @param string $service
	 *
	 * @return void
	 */
	public static function flushPurposes($service)
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];

		// @TODO : Implement
	}

	/**
	 * @param string $service
	 * @param string $purpose
	 *
	 * @return boolean
	 */
	public static function isPurposeRegistered($service, $purpose)
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];

		$query = 'SELECT count(*) num FROM ' . self::PURPOSE_CONF_TABLE . ' WHERE service = '
			. $ilDB->quote($service, 'text') . ' AND purpose = ' . $ilDB->quote($purpose, 'text');
		$result = $ilDB->query($query);
		$row = $ilDB->fetchAssoc($result);
		if($row['num'] != 0)
		{
			return true;
		}
		return false;
	}

	/**
	 * @param string $service
	 *
	 * @return string[]
	 */
	public static function getPurposesByService($service)
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];

		// @TODO : Implement
	}

	/**
	 * @return string[]
	 */
	public static function getServices()
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];

		// @TODO : Implement
	}

	public static function updateFromXML($service, $purpose, $preferred)
	{
		$parts = explode('/', $service);
		$service = $parts[1];

		if(!self::isPurposeRegistered($service, $purpose))
		{
			self::registerPurpose($service, $purpose, $preferred);
		}
	}

	public static function registerRenderer($renderer, $path)
	{
		global $DIC;
		/** @var ilDB $ilDB */
		$ilDB = $DIC['ilDB'];

		$ilDB->insert(self::RENDERER_TABLE, array(
					  'renderer_id'	=>	array('int',	$ilDB->nextId(self::RENDERER_TABLE)),
					  'renderer' 	=>	array('text', 	$renderer),
					  'path' 		=>	array('text', 	$path)
				  )
		);
	}

	public static function registerRendererAvailability($renderer, $service, $purpose)
	{
		global $DIC;
		/** @var ilDB $ilDB */
		$ilDB = $DIC['ilDB'];

		$ilDB->insert(self::RENDERER_AVAIL_TABLE, array(
					  'availability_id'	=>	array('int',	$ilDB->nextId(self::RENDERER_AVAIL_TABLE)),
					  'service' 		=>	array('text', 	$service),
					  'purpose' 		=>	array('text', 	$purpose),
					  'renderer' 		=>	array('text', 	$renderer)
				  )
		);
	}
}