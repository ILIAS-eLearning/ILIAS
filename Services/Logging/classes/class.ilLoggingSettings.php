<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Logging/classes/public/class.ilLogLevel.php';

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
		$this->read();
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
	 * Get log level
	 * @return type
	 */
	public function getLevel()
	{
		return ilLogLevel::INFO;
	}
	
	/**
	 * 
	 * @global type $ilDB
	 */
	public static function readLogComponents()
	{
		global $ilDB;
		
		$query = 'SELECT * FROM il_component ';
		$res = $ilDB->query($query);
		
		$components = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$components[$row->id] = $row->name;
		}
		return $components;
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