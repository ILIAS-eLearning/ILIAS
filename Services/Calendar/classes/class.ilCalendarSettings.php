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

    const DEFAULT_SHOW_WEEKS = true;

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
    
    private $consultation_hours = false;
    
    private $cache_enabled = true;
    private $cache_minutes = 1;
    
    private $sync_cache_enabled = true;
    private $sync_cache_minutes = 10;
    
    private $notification = false;
    private $notification_user = false;
    
    private $cg_registration = false;
    
    private $course_cal_enabled = true;
    private $group_cal_enabled = true;
    
    private $webcal_sync = false;
    private $webcal_sync_hours = 2;
    private $show_weeks = false;

    private $batch_file_downloads = false;

    /**
     * singleton contructor
     *
     * @access private
     *
     */
    private function __construct()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

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
     * @return ilCalendarSettings
     * @return ilCalendarSettings
     */
    public static function _getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilCalendarSettings();
    }
    
    /**
     *
     * @param type $a_obj_id
     */
    public static function lookupCalendarActivated($a_obj_id)
    {
        if (!ilCalendarSettings::_getInstance()->isEnabled()) {
            return false;
        }
        $type = ilObject::_lookupType($a_obj_id);
        // lookup global setting
        $gl_activated = false;
        switch ($type) {
            case 'crs':
                $gl_activated = ilCalendarSettings::_getInstance()->isCourseCalendarEnabled();
                break;
            
            case 'grp':
                $gl_activated = ilCalendarSettings::_getInstance()->isGroupCalendarEnabled();
                break;
            
            default:
                return false;
        }
        // look individual object setting
        include_once './Services/Container/classes/class.ilContainer.php';
        return ilContainer::_lookupContainerSetting(
            $a_obj_id,
            'cont_show_calendar',
            $gl_activated
        );
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
     * Check if consultation hours are enabled
     * @return
     */
    public function areConsultationHoursEnabled()
    {
        return $this->consultation_hours;
    }
    
    /**
     * En/Disable consultation hours
     * @return
     */
    public function enableConsultationHours($a_status)
    {
        $this->consultation_hours = $a_status;
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
    public function setEnableGroupMilestones($a_enablegroupmilestones)
    {
        $this->enablegroupmilestones = $a_enablegroupmilestones;
    }

    /**
    * Get Enable milestone planning feature for groups.
    *
    * @return	boolean	Enable milestone planning feature for groups
    */
    public function getEnableGroupMilestones()
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
     * Course group notification enabled
     * @return
     */
    public function isNotificationEnabled()
    {
        return (bool) $this->notification;
    }
    
    /**
     * Enable course group notification
     * @param bool $a_status
     * @return
     */
    public function enableNotification($a_status)
    {
        $this->notification = $a_status;
    }

    public function isUserNotificationEnabled()
    {
        return $this->notification_user;
    }

    public function enableUserNotification($a_not)
    {
        $this->notification_user = $a_not;
    }
    
    /**
     * Enable optional registration for courses and groups
     * @param bool $a_status
     * @return
     */
    public function enableCGRegistration($a_status)
    {
        $this->cg_registration = $a_status;
    }
    
    public function isCGRegistrationEnabled()
    {
        return $this->cg_registration;
    }
    
    public function enableCourseCalendar($a_stat)
    {
        $this->course_cal_enabled = $a_stat;
    }
    
    public function isCourseCalendarEnabled()
    {
        return $this->course_cal_enabled;
    }
    
    public function enableGroupCalendar($a_stat)
    {
        $this->group_cal_enabled = $a_stat;
    }
    
    public function isGroupCalendarEnabled()
    {
        return $this->group_cal_enabled;
    }
    
    public function enableWebCalSync($a_stat)
    {
        $this->webcal_sync = $a_stat;
    }
    
    public function isWebCalSyncEnabled()
    {
        return $this->webcal_sync;
    }
    
    public function setWebCalSyncHours($a_hours)
    {
        $this->webcal_sync_hours = $a_hours;
    }
    
    public function getWebCalSyncHours()
    {
        return $this->webcal_sync_hours;
    }

    /**
     * Set show weeks
     *
     * @param bool $a_val show weeks
     */
    public function setShowWeeks($a_val)
    {
        $this->show_weeks = $a_val;
    }
    
    /**
     * Get show weeks
     *
     * @return bool show weeks
     */
    public function getShowWeeks()
    {
        return $this->show_weeks;
    }

    public function enableBatchFileDownloads($a_stat)
    {
        $this->batch_file_downloads = $a_stat;
    }

    public function isBatchFileDownloadsEnabled()
    {
        return $this->batch_file_downloads;
    }
    
    /**
     * save
     *
     * @access public
     */
    public function save()
    {
        $this->storage->set('enabled', (int) $this->isEnabled());
        $this->storage->set('default_timezone', $this->getDefaultTimeZone());
        $this->storage->set('default_week_start', $this->getDefaultWeekStart());
        $this->storage->set('default_date_format', $this->getDefaultDateFormat());
        $this->storage->set('default_time_format', $this->getDefaultTimeFormat());
        $this->storage->set('enable_grp_milestones', (int) $this->getEnableGroupMilestones());
        $this->storage->set('default_day_start', (int) $this->getDefaultDayStart());
        $this->storage->set('default_day_end', (int) $this->getDefaultDayEnd());
        $this->storage->set('cache_minutes', (int) $this->getCacheMinutes());
        $this->storage->set('sync_cache_enabled', (int) $this->isSynchronisationCacheEnabled());
        $this->storage->set('sync_cache_minutes', (int) $this->getSynchronisationCacheMinutes());
        $this->storage->set('cache_enabled', (int) $this->isCacheUsed());
        $this->storage->set('notification', (int) $this->isNotificationEnabled());
        $this->storage->set('consultation_hours', (int) $this->areConsultationHoursEnabled());
        $this->storage->set('cg_registration', (int) $this->isCGRegistrationEnabled());
        $this->storage->set('course_cal', (int) $this->isCourseCalendarEnabled());
        $this->storage->set('group_cal', (int) $this->isGroupCalendarEnabled());
        $this->storage->set('notification_user', (int) $this->isUserNotificationEnabled());
        $this->storage->set('webcal_sync', (int) $this->isWebCalSyncEnabled());
        $this->storage->set('webcal_sync_hours', (int) $this->getWebCalSyncHours());
        $this->storage->set('show_weeks', (int) $this->getShowWeeks());
        $this->storage->set('batch_files', (int) $this->isBatchFileDownloadsEnabled());
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
        $this->setDefaultTimeZone($this->storage->get('default_timezone', ilTimeZone::_getDefaultTimeZone()));
        $this->setDefaultWeekStart($this->storage->get('default_week_start', self::WEEK_START_MONDAY));
        $this->setDefaultDateFormat($this->storage->get('default_date_format', self::DATE_FORMAT_DMY));
        $this->setDefaultTimeFormat($this->storage->get('default_time_format', self::TIME_FORMAT_24));
        $this->setEnableGroupMilestones($this->storage->get('enable_grp_milestones'));
        $this->setDefaultDayStart($this->storage->get('default_day_start', self::DEFAULT_DAY_START));
        $this->setDefaultDayEnd($this->storage->get('default_day_end', self::DEFAULT_DAY_END));
        $this->useCache($this->storage->get('cache_enabled'), $this->cache_enabled);
        $this->setCacheMinutes($this->storage->get('cache_minutes', self::DEFAULT_CACHE_MINUTES));
        $this->enableSynchronisationCache($this->storage->get('sync_cache_enabled'), $this->isSynchronisationCacheEnabled());
        $this->setSynchronisationCacheMinutes($this->storage->get('sync_cache_minutes', self::DEFAULT_SYNC_CACHE_MINUTES));
        $this->enableNotification($this->storage->get('notification', $this->isNotificationEnabled()));
        $this->enableConsultationHours($this->storage->get('consultation_hours', $this->areConsultationHoursEnabled()));
        $this->enableCGRegistration($this->storage->get('cg_registration', $this->isCGRegistrationEnabled()));
        $this->enableCourseCalendar($this->storage->get('course_cal', $this->isCourseCalendarEnabled()));
        $this->enableGroupCalendar($this->storage->get('group_cal', $this->isGroupCalendarEnabled()));
        $this->enableUserNotification($this->storage->get('notification_user', $this->isUserNotificationEnabled()));
        $this->enableWebCalSync($this->storage->get('webcal_sync', $this->isWebCalSyncEnabled()));
        $this->setWebCalSyncHours($this->storage->get('webcal_sync_hours', $this->getWebCalSyncHours()));
        $this->setShowWeeks($this->storage->get('show_weeks', $this->getShowWeeks()));
        $this->enableBatchFileDownloads($this->storage->get('batch_files', $this->isBatchFileDownloadsEnabled()));
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
        $query = "SELECT ref_id FROM object_reference obr " .
            "JOIN object_data obd ON obd.obj_id = obr.obj_id " .
            "WHERE type = 'cals'";
            
        $set = $this->db->query($query);
        $row = $this->db->fetchAssoc($set);
        
        $this->cal_settings_id = $row["ref_id"];
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
