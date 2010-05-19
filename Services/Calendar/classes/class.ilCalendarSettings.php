<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once('Services/Calendar/classes/class.ilTimeZone.php');

/** 
* Stores all calendar relevant settings.
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesCalendar
*/
class ilCalendarSettings
{
	const WEEK_START_MONDAY = 1;
	const WEEK_START_SUNDAY = 0;
	
	const DEFAULT_DAY_START = 8;
	const DEFAULT_DAY_END = 19;

	const DATE_FORMAT_DMY = 1;
	const DATE_FORMAT_YMD = 2;
	const DATE_FORMAT_MDY = 3;
	
	const TIME_FORMAT_24 = 1;
	const TIME_FORMAT_12 = 2;
	
	const DEFAULT_CACHE_MINUTES = 0;
	const DEFAULT_SYNC_CACHE_MINUTES = 10;
	

	private static $instance = null;

	private $db = null;
	private $storage = null;
	private $timezone = null;
	private $time_format = null;
	private $week_start = 0;
	private $day_start = null;
	private $day_end = null;
	private $enabled = false;
	private $cal_settings_id = 0;
	
	private $cache_enabled = true;
	private $cache_minutes = 1;
	
	private $sync_cache_enabled = true;
	private $sync_cache_minutes = 10;

	/**
	 * singleton contructor
	 *
	 * @access private
	 * 
	 */
	private function __construct()
	{
	 	global $ilDB;

		$this->db = $ilDB;	 	
	 	
	 	$this->initStorage();
		$this->read();	
		$this->readCalendarSettingsId();
	}
	
	/**
	 * get singleton instance
	 *
	 * @access public
	 * @static
	 *
	 */
	public static function _getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilCalendarSettings();
	}
	
	/**
	 * Enable cache
	 * @param object $a_status
	 * @return 
	 */
	public function useCache($a_status)
	{
		$this->cache_enabled = $a_status;
	}
	
	/**
	 * Check if cache is used
	 * @return 
	 */
	public function isCacheUsed()
	{
		return $this->cache_enabled;
	}
	
	/**
	 * Set time of cache storage
	 * @param int $a_min
	 * @return 
	 */
	public function setCacheMinutes($a_min)
	{
		$this->cache_minutes = $a_min;
	}
	
	/**
	 * Get cache minutes
	 * @return 
	 */
	public function getCacheMinutes()
	{
		return (int) $this->cache_minutes;
	}

	/**
	 * set enabled
	 *
	 * @access public
	 * 
	 */
	public function setEnabled($a_enabled)
	{
	 	$this->enabled = $a_enabled;
	}
	
	/**
	 * is calendar enabled
	 *
	 * @access public
	 * 
	 */
	public function isEnabled()
	{
	 	return (bool) $this->enabled;
	}
	
	/**
	 * set week start
	 *
	 * @access public
	 * 
	 */
	public function setDefaultWeekStart($a_start)
	{
	 	$this->week_start = $a_start;
	}
	
	/**
	 * get default week start
	 *
	 * @access public
	 * 
	 */
	public function getDefaultWeekStart()
	{
	 	return $this->week_start;
	}
	
	/**
	 * set default timezone
	 *
	 * @access public
	 */
	public function setDefaultTimeZone($a_zone)
	{
	 	$this->timezone = $a_zone;
	}
	
	/**
	 * get derfault time zone
	 *
	 * @access public
	 */
	public function getDefaultTimeZone()
	{
	 	return $this->timezone;
	}

	/**
	 * set default date format
	 *
	 * @access public
	 * @param int date format
	 * @return
	 */
	public function setDefaultDateFormat($a_format)
	{
		$this->date_format = $a_format;
	}

	/**
	 * get default date format
	 *
	 * @access public
	 * @return int date format
	 */
	public function getDefaultDateFormat()
	{
		return $this->date_format;
	}
	
	/**
	 * set default time format
	 *
	 * @access public
	 * @param int time format
	 * @return
	 */
	public function setDefaultTimeFormat($a_format)
	{
		$this->time_format = $a_format;
	}
	
	/**
	 * get default time format
	 *
	 * @access public
	 * @return int time format
	 */
	public function getDefaultTimeFormat()
	{
		return $this->time_format;
	}
	
	/**
	 * Get default end of day
	 * @return 
	 */
	public function getDefaultDayStart()
	{
		return $this->day_start;
	}
	
	/**
	 * Set default start of day
	 * @return 
	 * @param object $a_start
	 */
	public function setDefaultDayStart($a_start)
	{
		$this->day_start = $a_start;
	}
	
	/**
	 * Get default end of day
	 * @return 
	 */
	public function getDefaultDayEnd()
	{
		return $this->day_end;
	}
	
	/**
	 * set default end of day
	 * @return 
	 * @param object $a_end
	 */
	public function setDefaultDayEnd($a_end)
	{
		$this->day_end = $a_end;
	}
	

	/**
	 * Get calendar settings id
	 * (Used for permission checks)
	 *
	 * @access public
	 * @return
	 */
	public function getCalendarSettingsId()
	{
		return $this->cal_settings_id;
	}

	/**
	* Set Enable milestone planning feature for groups.
	*
	* @param	boolean	$a_enablegroupmilestones	Enable milestone planning feature for groups
	*/
	function setEnableGroupMilestones($a_enablegroupmilestones)
	{
		$this->enablegroupmilestones = $a_enablegroupmilestones;
	}

	/**
	* Get Enable milestone planning feature for groups.
	*
	* @return	boolean	Enable milestone planning feature for groups
	*/
	function getEnableGroupMilestones()
	{
		return $this->enablegroupmilestones;
	}
	
	/**
	 * Check if cache is active for calendar synchronisation
	 * @return 
	 */
	public function isSynchronisationCacheEnabled()
	{
		return (bool) $this->sync_cache_enabled;
	}
	
	/**
	 * En/Disable synchronisation cache
	 * @return 
	 */
	public function enableSynchronisationCache($a_status)
	{
		$this->sync_cache_enabled = $a_status;
	}
	
	/**
	 * Set synchronisation cache minutes
	 * @param object $a_min
	 * @return 
	 */
	public function setSynchronisationCacheMinutes($a_min)
	{
		$this->sync_cache_minutes = $a_min;
	}
	
	/**
	 * get synchronisation cache minutes
	 * @return 
	 */
	public function getSynchronisationCacheMinutes()
	{
		return $this->sync_cache_minutes;
	}
	
	/**
	 * save 
	 *
	 * @access public
	 */
	public function save()
	{
	 	$this->storage->set('enabled',(int) $this->isEnabled());
	 	$this->storage->set('default_timezone',$this->getDefaultTimeZone());
	 	$this->storage->set('default_week_start',$this->getDefaultWeekStart());
	 	$this->storage->set('default_date_format',$this->getDefaultDateFormat());
	 	$this->storage->set('default_time_format',$this->getDefaultTimeFormat());
		$this->storage->set('enable_grp_milestones',(int) $this->getEnableGroupMilestones());
		$this->storage->set('default_day_start',(int) $this->getDefaultDayStart());
		$this->storage->set('default_day_end',(int) $this->getDefaultDayEnd());
		$this->storage->set('cache_minutes',(int) $this->getCacheMinutes());
		$this->storage->set('sync_cache_enabled',(int) $this->isSynchronisationCacheEnabled());
		$this->storage->set('sync_cache_minutes',(int) $this->getSynchronisationCacheMinutes());
		$this->storage->set('cache_enabled',(int) $this->isCacheUsed());
	}

	/**
	 * Read settings
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function read()
	{
		$this->setEnabled($this->storage->get('enabled'));
		$this->setDefaultTimeZone($this->storage->get('default_timezone',ilTimeZone::_getDefaultTimeZone()));
		$this->setDefaultWeekStart($this->storage->get('default_week_start',self::WEEK_START_MONDAY));
		$this->setDefaultDateFormat($this->storage->get('default_date_format',self::DATE_FORMAT_DMY));
		$this->setDefaultTimeFormat($this->storage->get('default_time_format',self::TIME_FORMAT_24));
		$this->setEnableGroupMilestones($this->storage->get('enable_grp_milestones'));
		$this->setDefaultDayStart($this->storage->get('default_day_start',self::DEFAULT_DAY_START));
		$this->setDefaultDayEnd($this->storage->get('default_day_end',self::DEFAULT_DAY_END));
		$this->useCache($this->storage->get('cache_enabled'),$this->cache_enabled);
		$this->setCacheMinutes($this->storage->get('cache_minutes',self::DEFAULT_CACHE_MINUTES));
		$this->enableSynchronisationCache($this->storage->get('sync_cache_enabled'),$this->isSynchronisationCacheEnabled());
		$this->setSynchronisationCacheMinutes($this->storage->get('sync_cache_minutes',self::DEFAULT_SYNC_CACHE_MINUTES));
	}
	
	/**
	 * Read ref_id of calendar settings
	 *
	 * @access private
	 * @param
	 * @return
	 */
	private function readCalendarSettingsId()
	{
		global $ilDB;
		
		$query = "SELECT ref_id FROM object_reference obr ".
			"JOIN object_data obd ON obd.obj_id = obr.obj_id ".
			"WHERE type = 'cals'";
			
		$res = $this->db->query($query);
		$row = $res->fetchRow();
		
		$this->cal_settings_id = $row[0];
		return true;
	}
	
	/**
	 * Init storage class (ilSetting)
	 * @access private
	 * 
	 */
	private function initStorage()
	{
	 	include_once('./Services/Administration/classes/class.ilSetting.php');
	 	$this->storage = new ilSetting('calendar');
	}
}
?>
