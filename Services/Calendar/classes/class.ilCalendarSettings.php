<?php declare(strict_types=1);
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

/**
 * Stores all calendar relevant settings.
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilCalendarSettings
{
    public const WEEK_START_MONDAY = 1;
    public const WEEK_START_SUNDAY = 0;

    public const DEFAULT_DAY_START = 8;
    public const DEFAULT_DAY_END = 19;

    public const DATE_FORMAT_UNDEFINED = 0;
    public const DATE_FORMAT_DMY = 1;
    public const DATE_FORMAT_YMD = 2;
    public const DATE_FORMAT_MDY = 3;

    public const DEFAULT_CAL_DAY = 1;
    public const DEFAULT_CAL_WEEK = 2;
    public const DEFAULT_CAL_MONTH = 3;
    public const DEFAULT_CAL_LIST = 4;

    public const TIME_FORMAT_24 = 1;
    public const TIME_FORMAT_12 = 2;

    public const DEFAULT_CACHE_MINUTES = 0;
    public const DEFAULT_SYNC_CACHE_MINUTES = 10;

    public const DEFAULT_SHOW_WEEKS = true;

    private static ?ilCalendarSettings $instance = null;

    protected ilDBInterface $db;
    protected ilSetting $storage;

    private string $timezone = ilTimeZone::UTC;
    private int $time_format = self::TIME_FORMAT_12;
    private int $week_start = self::WEEK_START_SUNDAY;
    private int $day_start = self::DEFAULT_DAY_START;
    private int $day_end = self::DEFAULT_DAY_END;
    private bool $enabled = false;
    private int $cal_settings_id = 0;
    private bool $consultation_hours = false;
    private int $date_format = 0;
    private int $default_cal = self::DEFAULT_CAL_LIST;
    private int $default_period = 2;
    private bool $cache_enabled = true;
    private int $cache_minutes = 1;
    private bool $sync_cache_enabled = true;
    private int $sync_cache_minutes = 10;
    private bool $notification = false;
    private bool $notification_user = false;
    private bool $cg_registration = false;
    private bool $course_cal_enabled = true;
    private bool $group_cal_enabled = true;
    private bool $course_cal_visible = true;
    private bool $group_cal_visible = true;
    private bool $webcal_sync = false;
    private int $webcal_sync_hours = 2;
    private bool $show_weeks = false;
    private bool $batch_file_downloads = false;
    private bool $enablegroupmilestones = false;

    private function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->initStorage();
        $this->read();
        $this->readCalendarSettingsId();
    }

    public static function _getInstance() : ilCalendarSettings
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilCalendarSettings();
    }

    public static function lookupCalendarContentPresentationEnabled(int $obj_id) : bool
    {
        if (!self::lookupCalendarActivated($obj_id)) {
            return false;
        }
        $settings = self::_getInstance();
        $type = ilObject::_lookupType($obj_id);
        $default = $settings->isObjectCalendarVisible($type);
        return (bool) ilContainer::_lookupContainerSetting(
            $obj_id,
            'cont_show_calendar',
            (string) $default
        );
    }

    public static function lookupCalendarActivated(int $a_obj_id) : bool
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
        return (bool) ilContainer::_lookupContainerSetting(
            $a_obj_id,
            'cont_activation_calendar',
            (string) $gl_activated
        );
    }

    public function useCache(bool $a_status) : void
    {
        $this->cache_enabled = $a_status;
    }

    public function isCacheUsed() : bool
    {
        return $this->cache_enabled;
    }

    public function setCacheMinutes(int $a_min) : void
    {
        $this->cache_minutes = $a_min;
    }

    public function getCacheMinutes() : int
    {
        return $this->cache_minutes;
    }

    public function setEnabled(bool $a_enabled) : void
    {
        $this->enabled = $a_enabled;
    }

    public function isEnabled() : bool
    {
        return $this->enabled;
    }

    public function setDefaultWeekStart(int $a_start) : void
    {
        $this->week_start = $a_start;
    }

    public function getDefaultWeekStart() : int
    {
        return $this->week_start;
    }

    public function getDefaultCal() : int
    {
        return $this->default_cal;
    }

    public function setDefaultCal(int $default_cal) : void
    {
        $this->default_cal = $default_cal;
    }

    public function getDefaultPeriod() : int
    {
        return $this->default_period;
    }

    public function setDefaultPeriod(int $default_period) : void
    {
        $this->default_period = $default_period;
    }

    public function setDefaultTimeZone(string $a_zone) : void
    {
        $this->timezone = $a_zone;
    }

    public function getDefaultTimeZone() : string
    {
        return $this->timezone;
    }

    public function setDefaultDateFormat(int $a_format) : void
    {
        $this->date_format = $a_format;
    }

    public function getDefaultDateFormat() : int
    {
        return $this->date_format;
    }

    public function setDefaultTimeFormat(int $a_format) : void
    {
        $this->time_format = $a_format;
    }

    public function getDefaultTimeFormat() : int
    {
        return $this->time_format;
    }

    public function getDefaultDayStart() : int
    {
        return $this->day_start;
    }

    public function setDefaultDayStart(int $a_start) : void
    {
        $this->day_start = $a_start;
    }

    public function getDefaultDayEnd() : int
    {
        return $this->day_end;
    }

    public function setDefaultDayEnd(int $a_end) : void
    {
        $this->day_end = $a_end;
    }

    public function areConsultationHoursEnabled() : bool
    {
        return $this->consultation_hours;
    }

    public function enableConsultationHours(bool $a_status) : void
    {
        $this->consultation_hours = $a_status;
    }

    public function getCalendarSettingsId() : int
    {
        return $this->cal_settings_id;
    }

    public function setEnableGroupMilestones(bool $a_enablegroupmilestones) : void
    {
        $this->enablegroupmilestones = $a_enablegroupmilestones;
    }

    public function getEnableGroupMilestones() : bool
    {
        return $this->enablegroupmilestones;
    }

    public function isSynchronisationCacheEnabled() : bool
    {
        return $this->sync_cache_enabled;
    }

    public function enableSynchronisationCache(bool $a_status) : void
    {
        $this->sync_cache_enabled = $a_status;
    }

    public function setSynchronisationCacheMinutes(int $a_min) : void
    {
        $this->sync_cache_minutes = $a_min;
    }

    public function getSynchronisationCacheMinutes() : int
    {
        return $this->sync_cache_minutes;
    }

    public function isNotificationEnabled() : bool
    {
        return $this->notification;
    }

    public function enableNotification(bool $a_status) : void
    {
        $this->notification = $a_status;
    }

    public function isUserNotificationEnabled() : bool
    {
        return $this->notification_user;
    }

    public function enableUserNotification(bool $a_not) : void
    {
        $this->notification_user = $a_not;
    }

    public function enableCGRegistration(bool $a_status) : void
    {
        $this->cg_registration = $a_status;
    }

    public function isCGRegistrationEnabled() : bool
    {
        return $this->cg_registration;
    }

    public function enableCourseCalendar(bool $a_stat) : void
    {
        $this->course_cal_enabled = $a_stat;
    }

    public function isCourseCalendarEnabled() : bool
    {
        return $this->course_cal_enabled;
    }

    public function isCourseCalendarVisible() : bool
    {
        return $this->course_cal_visible;
    }

    public function setCourseCalendarVisible(bool $status) : void
    {
        $this->course_cal_visible = $status;
    }

    public function isObjectCalendarVisible(string $type) : bool
    {
        switch ($type) {
            case 'crs':
                return $this->isCourseCalendarVisible();
            case 'grp':
                return $this->isGroupCalendarVisible();
        }
        return false;
    }

    public function enableGroupCalendar(bool $a_stat) : void
    {
        $this->group_cal_enabled = $a_stat;
    }

    public function isGroupCalendarEnabled() : bool
    {
        return $this->group_cal_enabled;
    }

    public function isGroupCalendarVisible() : bool
    {
        return $this->group_cal_visible;
    }

    public function setGroupCalendarVisible(bool $status) : void
    {
        $this->group_cal_visible = $status;
    }

    public function enableWebCalSync(bool $a_stat) : void
    {
        $this->webcal_sync = $a_stat;
    }

    public function isWebCalSyncEnabled() : bool
    {
        return $this->webcal_sync;
    }

    public function setWebCalSyncHours(int $a_hours) : void
    {
        $this->webcal_sync_hours = $a_hours;
    }

    public function getWebCalSyncHours() : int
    {
        return $this->webcal_sync_hours;
    }

    public function setShowWeeks(bool $a_val) : void
    {
        $this->show_weeks = $a_val;
    }

    public function getShowWeeks() : bool
    {
        return $this->show_weeks;
    }

    public function enableBatchFileDownloads(bool $a_stat) : void
    {
        $this->batch_file_downloads = $a_stat;
    }

    public function isBatchFileDownloadsEnabled() : bool
    {
        return $this->batch_file_downloads;
    }

    public function save()
    {
        $this->storage->set('enabled', (string) (int) $this->isEnabled());
        $this->storage->set('default_timezone', $this->getDefaultTimeZone());
        $this->storage->set('default_week_start', (string) $this->getDefaultWeekStart());
        $this->storage->set('default_date_format', (string) $this->getDefaultDateFormat());
        $this->storage->set('default_time_format', (string) $this->getDefaultTimeFormat());
        $this->storage->set('enable_grp_milestones', (string) (int) $this->getEnableGroupMilestones());
        $this->storage->set('default_day_start', (string) $this->getDefaultDayStart());
        $this->storage->set('default_day_end', (string) $this->getDefaultDayEnd());
        $this->storage->set('cache_minutes', (string) $this->getCacheMinutes());
        $this->storage->set('sync_cache_enabled', (string) (int) $this->isSynchronisationCacheEnabled());
        $this->storage->set('sync_cache_minutes', (string) $this->getSynchronisationCacheMinutes());
        $this->storage->set('cache_enabled', (string) (int) $this->isCacheUsed());
        $this->storage->set('notification', (string) (int) $this->isNotificationEnabled());
        $this->storage->set('consultation_hours', (string) (int) $this->areConsultationHoursEnabled());
        $this->storage->set('cg_registration', (string) (int) $this->isCGRegistrationEnabled());
        $this->storage->set('course_cal', (string) (int) $this->isCourseCalendarEnabled());
        $this->storage->set('course_cal_visible', (string) (int) $this->isCourseCalendarVisible());
        $this->storage->set('group_cal', (string) (int) $this->isGroupCalendarEnabled());
        $this->storage->set('group_cal_visible', (string) (int) $this->isGroupCalendarVisible());
        $this->storage->set('notification_user', (string) (int) $this->isUserNotificationEnabled());
        $this->storage->set('webcal_sync', (string) (int) $this->isWebCalSyncEnabled());
        $this->storage->set('webcal_sync_hours', (string) $this->getWebCalSyncHours());
        $this->storage->set('show_weeks', (string) (int) $this->getShowWeeks());
        $this->storage->set('batch_files', (string) (int) $this->isBatchFileDownloadsEnabled());
        $this->storage->set('default_calendar_view', (string) $this->getDefaultCal());
        $this->storage->set('default_period', (string) $this->getDefaultPeriod());
    }

    private function read()
    {
        $this->setEnabled((bool) $this->storage->get('enabled'));
        $this->setDefaultTimeZone($this->storage->get('default_timezone', ilTimeZone::_getDefaultTimeZone()));
        $this->setDefaultWeekStart((int) $this->storage->get('default_week_start', (string) self::WEEK_START_MONDAY));
        $this->setDefaultDateFormat((int) $this->storage->get('default_date_format', (string) self::DATE_FORMAT_DMY));
        $this->setDefaultTimeFormat((int) $this->storage->get('default_time_format', (string) self::TIME_FORMAT_24));
        $this->setEnableGroupMilestones((bool) $this->storage->get('enable_grp_milestones'));
        $this->setDefaultDayStart((int) $this->storage->get('default_day_start', (string) self::DEFAULT_DAY_START));
        $this->setDefaultDayEnd((int) $this->storage->get('default_day_end', (string) self::DEFAULT_DAY_END));
        $this->useCache((bool) $this->storage->get('cache_enabled', (string) $this->cache_enabled));
        $this->setCacheMinutes((int) $this->storage->get('cache_minutes', (string) self::DEFAULT_CACHE_MINUTES));
        $this->enableSynchronisationCache((bool) $this->storage->get(
            'sync_cache_enabled',
            (string) $this->isSynchronisationCacheEnabled()
        ));
        $this->setSynchronisationCacheMinutes((int) $this->storage->get(
            'sync_cache_minutes',
            (string) self::DEFAULT_SYNC_CACHE_MINUTES
        ));
        $this->enableNotification((bool) $this->storage->get('notification', (string) $this->isNotificationEnabled()));
        $this->enableConsultationHours((bool) $this->storage->get(
            'consultation_hours',
            (string) $this->areConsultationHoursEnabled()
        ));
        $this->enableCGRegistration((bool) $this->storage->get(
            'cg_registration',
            (string) $this->isCGRegistrationEnabled()
        ));
        $this->enableCourseCalendar((bool) $this->storage->get(
            'course_cal',
            (string) $this->isCourseCalendarEnabled()
        ));
        $this->setCourseCalendarVisible((bool) $this->storage->get(
            'course_cal_visible',
            (string) $this->isCourseCalendarVisible()
        ));
        $this->enableGroupCalendar((bool) $this->storage->get('group_cal', (string) $this->isGroupCalendarEnabled()));
        $this->setGroupCalendarVisible((bool) $this->storage->get(
            'group_cal_visible',
            (string) $this->isGroupCalendarVisible()
        ));
        $this->enableUserNotification((bool) $this->storage->get(
            'notification_user',
            (string) $this->isUserNotificationEnabled()
        ));
        $this->enableWebCalSync((bool) $this->storage->get('webcal_sync', (string) $this->isWebCalSyncEnabled()));
        $this->setWebCalSyncHours((int) $this->storage->get('webcal_sync_hours', (string) $this->getWebCalSyncHours()));
        $this->setShowWeeks((bool) $this->storage->get('show_weeks', (string) $this->getShowWeeks()));
        $this->enableBatchFileDownloads((bool) $this->storage->get(
            'batch_files',
            (string) $this->isBatchFileDownloadsEnabled()
        ));
        $this->setDefaultCal((int) $this->storage->get('default_calendar_view', (string) $this->getDefaultCal()));
        $this->setDefaultPeriod((int) $this->storage->get('default_period', (string) $this->getDefaultPeriod()));
    }

    private function readCalendarSettingsId() : void
    {
        $query = "SELECT ref_id FROM object_reference obr " .
            "JOIN object_data obd ON obd.obj_id = obr.obj_id " .
            "WHERE type = 'cals'";

        $set = $this->db->query($query);
        $row = $this->db->fetchAssoc($set);

        $this->cal_settings_id = (int) $row["ref_id"];
    }

    private function initStorage()
    {
        $this->storage = new ilSetting('calendar');
    }
}
