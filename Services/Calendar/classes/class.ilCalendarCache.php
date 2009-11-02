<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Cache/classes/class.ilCache.php");
include_once './Services/Calendar/classes/class.ilCalendarSettings.php';

/**
 * Calendar cache
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilCalendarCache extends ilCache
{
	private static $instance = null;
	
	/**
	 * Singleton constructor
	 * @return 
	 */
	public function __construct()
	{
		parent::__construct('ServicesCalendar','Calendar',true);
		$this->setExpiresAfter(60 * ilCalendarSettings::_getInstance()->getCacheMinutes());
	}
	
	/**
	 * get singleton instance
	 * @return object ilCalendarCache
	 */
	public static function getInstance()
	{
		if(isset(self::$instance) and self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilCalendarCache();
	}
	
	/**
	 * Get cahed entry if cache is active
	 * @param object $a_entry_id
	 * @return 
	 */
	public function readEntry($a_entry_id)
	{
		if(!ilCalendarSettings::_getInstance()->isCacheUsed())
		{
			return null;
		}
		return parent::readEntry($a_entry_id);
	}
	
	/**
	 * Store an entry
	 * @param object $a_entry_id
	 * @param object $a_value
	 * @return 
	 */
	public function storeEntry($a_entry_id,$a_value,$a_key1 = 0,$a_key2 = 0,$a_key3 = '',$a_key4 = '')
	{
		if(!ilCalendarSettings::_getInstance()->isCacheUsed())
		{
			return null;
		}
		parent::storeEntry($a_entry_id, $a_value, $a_key1, $a_key2, $a_key3, $a_key4);
	}
	
	/**
	 * Store an entry without an expired time
	 * @param object $a_entry_id
	 * @param object $a_value
	 * @return 
	 */
	public function storeUnlimitedEntry($a_entry_id,$a_value,$a_key1 = 0,$a_key2 = 0,$a_key3 = '',$a_key4 = '')
	{
		if(!ilCalendarSettings::_getInstance()->isCacheUsed())
		{
			return null;
		}
		// Unlimited is a year
		$this->setExpiresAfter(60 * 60 * 24 * 365);
		parent::storeEntry($a_entry_id,$a_value, $a_key1, $a_key2, $a_key3, $a_key4);
		$this->setExpiresAfter(ilCalendarSettings::_getInstance()->getCacheMinutes());
		return true;
	}
}
?>