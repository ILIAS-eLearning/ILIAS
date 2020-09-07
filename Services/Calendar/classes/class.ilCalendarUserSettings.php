<?php
/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/
include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');
 
class ilCalendarUserSettings
{
    const CAL_SELECTION_MEMBERSHIP = 1;
    const CAL_SELECTION_ITEMS = 2;
    
    const CAL_EXPORT_TZ_TZ = 1;
    const CAL_EXPORT_TZ_UTC = 2;
    
    public static $instances = array();
    
    protected $user;
    protected $settings;
    
    private $calendar_selection_type = 1;
    private $timezone;
    private $export_tz_type = self::CAL_EXPORT_TZ_TZ;
    private $weekstart;
    private $time_format;
    private $date_format;
    
    private $day_start;
    private $day_end;

    /**
     * @var bool
     */
    private $show_weeks = true;
    
    /**
     * Constructor
     *
     * @access private
     * @param
     * @return
     */
    private function __construct($a_user_id)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        if ($ilUser->getId() == $a_user_id) {
            $this->user = $ilUser;
        } else {
            $this->user = ilObjectFactory::getInstanceByObjId($a_user_id, false);
        }
        $this->settings = ilCalendarSettings::_getInstance();
        $this->read();
    }
    
    /**
     * get singleton instance
     *
     * @access public
     * @param int user id
     * @return object ilCalendarUserSettings
     * @static
     */
    public static function _getInstanceByUserId($a_user_id)
    {
        if (isset(self::$instances[$a_user_id])) {
            return self::$instances[$a_user_id];
        }
        return self::$instances[$a_user_id] = new ilCalendarUserSettings($a_user_id);
    }
    
    /**
     * get instance for logged in user
     *
     * @return object ilCalendarUserSettings
     * @static
     */
    public static function _getInstance()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        return self::_getInstanceByUserId($ilUser->getId());
    }
    
    /**
     * get Time zone
     *
     * @access public
     * @param
     * @return
     */
    public function getTimeZone()
    {
        return $this->timezone;
    }
    
    /**
     * set timezone
     *
     * @access public
     * @param
     * @return
     */
    public function setTimeZone($a_tz)
    {
        $this->timezone = $a_tz;
    }
    
    /**
     * Get export timezone setting
     * @return type
     */
    public function getExportTimeZoneType()
    {
        return $this->export_tz_type;
    }
    
    /**
     * Set export timezone type
     * @param type $a_type
     */
    public function setExportTimeZoneType($a_type)
    {
        $this->export_tz_type = $a_type;
    }
    
    /**
     * Get export timezone
     * @return type
     */
    public function getExportTimeZone()
    {
        switch ($this->getExportTimeZoneType()) {
            case self::CAL_EXPORT_TZ_TZ:
                return $this->getTimeZone();
                
            case self::CAL_EXPORT_TZ_UTC:
                include_once './Services/Calendar/classes/class.ilTimeZone.php';
                return ilTimeZone::UTC;
        }
    }
    
    
    
    /**
     * set week start
     *
     * @access public
     * @param
     * @return
     */
    public function setWeekStart($a_weekstart)
    {
        $this->weekstart = $a_weekstart;
    }
    
    /**
     * get weekstart
     *
     * @access public
     * @return
     */
    public function getWeekStart()
    {
        return (int) $this->weekstart;
    }
    
    /**
     * Set start of day
     * @return
     * @param int $a_start
     */
    public function setDayStart($a_start)
    {
        $this->day_start = $a_start;
    }
    
    /**
     * get start of day
     * @return
     */
    public function getDayStart()
    {
        return $this->day_start;
    }
    
    /**
     * Set day end
     * @return
     * @param int $a_end
     */
    public function setDayEnd($a_end)
    {
        $this->day_end = $a_end;
    }
    
    /**
     * Get end of day
     * @return
     */
    public function getDayEnd()
    {
        return $this->day_end;
    }

    /**
     * set date format
     *
     * @access public
     * @param int date
     * @return
     */
    public function setDateFormat($a_format)
    {
        $this->date_format = $a_format;
    }

    /**
     * get date format
     *
     * @access public
     * @return int date format
     */
    public function getDateFormat()
    {
        return $this->date_format;
    }
    
    /**
     * set time format
     *
     * @access public
     * @param int time
     * @return
     */
    public function setTimeFormat($a_format)
    {
        $this->time_format = $a_format;
    }
    
    /**
     * get time format
     *
     * @access public
     * @return int time format
     */
    public function getTimeFormat()
    {
        return $this->time_format;
    }
    
    /**
     * get calendar selection type
     * ("MyMembership" or "Selected Items")
     *
     * @return
     */
    public function getCalendarSelectionType()
    {
        return $this->calendar_selection_type;
    }
    
    /**
     * set calendar selection type
     * @param int $type self::CAL_SELECTION_MEMBERSHIP | self::CAL_SELECTION_ITEM
     * @return void
     */
    public function setCalendarSelectionType($a_type)
    {
        $this->calendar_selection_type = $a_type;
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

    /**
     * save
     *
     * @access public
     */
    public function save()
    {
        $this->user->writePref('user_tz', $this->getTimeZone());
        $this->user->writePref('export_tz_type', $this->getExportTimeZoneType());
        $this->user->writePref('weekstart', $this->getWeekStart());
        $this->user->writePref('date_format', $this->getDateFormat());
        $this->user->writePref('time_format', $this->getTimeFormat());
        $this->user->writePref('calendar_selection_type', $this->getCalendarSelectionType());
        $this->user->writePref('day_start', $this->getDayStart());
        $this->user->writePref('day_end', $this->getDayEnd());
        $this->user->writePref('show_weeks', $this->getShowWeeks());
    }
    
    
    /**
     * read
     *
     * @access protected
     */
    protected function read()
    {
        $this->timezone = $this->user->getTimeZone();
        $this->export_tz_type = (
            ($this->user->getPref('export_tz_type') !== false) ?
                $this->user->getPref('export_tz_type') :
                $this->export_tz_type
        );
        $this->date_format = $this->user->getDateFormat();
        $this->time_format = $this->user->getTimeFormat();
        if (($weekstart = $this->user->getPref('weekstart')) === false) {
            $weekstart = $this->settings->getDefaultWeekStart();
        }
        $this->calendar_selection_type = $this->user->getPref('calendar_selection_type') ?
            $this->user->getPref('calendar_selection_type') :
            self::CAL_SELECTION_MEMBERSHIP;

        $this->weekstart = $weekstart;
        
        $this->setDayStart(
            $this->user->getPref('day_start') !== false ?
            $this->user->getPref('day_start') :
            $this->settings->getDefaultDayStart()
        );
        $this->setDayEnd(
            $this->user->getPref('day_end') !== false ?
            $this->user->getPref('day_end') :
            $this->settings->getDefaultDayEnd()
        );
        $this->setShowWeeks(
            $this->user->getPref('show_weeks') !== false ?
            $this->user->getPref('show_weeks') :
            $this->settings->getShowWeeks()
        );
    }
}
