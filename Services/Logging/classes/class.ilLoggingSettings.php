<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* @defgroup ServicesLogging Services/Logging
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* 
* @ingroup ServicesLogging
*/
class ilLoggingSettings
{
	
	protected static $instance = null;

	
	/**
	 * Singleton contructor
	 *
	 * @access private
	 */
	private function __construct()
	{

	}

	/**
	 * Get instance
	 * @param int $a_server_id
	 * @return ilLoggingSettings
	 */
	public static function getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new self();
	}

	/**
	 * Update setting
	 */
	public function update()
	{
		global $ilSetting;
	}

	
	/**
	 * Read settings
	 *
	 * @access private
	 */
	private function read()
	{
		global $ilSetting;
	}
}
?>