<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/Calendar/classes/class.ilDateTimeException.php');
include_once('Services/Calendar/classes/class.ilTimeZone.php');


define('IL_CAL_DATETIME', 1);
define('IL_CAL_DATE', 2);
define('IL_CAL_UNIX', 3);
define('IL_CAL_FKT_DATE', 4);
define('IL_CAL_FKT_GETDATE', 5);
define('IL_CAL_TIMESTAMP', 6);
define('IL_CAL_ISO_8601', 7);

define('IL_CAL_YEAR', 'year');
define('IL_CAL_MONTH', 'month');
define('IL_CAL_WEEK', 'week');
define('IL_CAL_DAY', 'day');
define('IL_CAL_HOUR', 'hour');
define('IL_CAL_SECOND', 'second');


/**
* @classDescription Date and time handling
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesCalendar
*/
class ilDateTime
{
    const YEAR = 'year';
    const MONTH = 'month';
    const WEEK = 'week';
    const DAY = 'day';
    const HOUR = 'hour';
    const MINUTE = 'minute';
    const SECOND = 'second';

    /**
     * @var \ilLogger
     */
    protected $log;
    
    protected $timezone = null;
    protected $default_timezone = null;

    /**
     * @var DateTime
     */
    protected $dt_obj; // [DateTime]
    
    
    
    /**
     * Create new date object
     *
     * @access public
     * @param mixed integer string following the format given as the second parameter
     * @param int format of date presentation
     * @param
     *
     * @throws ilDateTimeException
     */
    public function __construct($a_date = null, $a_format = 0, $a_tz = '')
    {
        global $DIC;

        $this->log = $DIC->logger()->cal();
        
        try {
            $this->timezone = ilTimeZone::_getInstance($a_tz);
            $this->default_timezone = ilTimeZone::_getInstance('');
            
            $this->setDate($a_date, $a_format);
        } catch (ilTimeZoneException $exc) {
            $this->log->warning($exc->getMessage());
            throw new ilDateTimeException('Unsupported timezone given. Timezone: ' . $a_tz);
        }
    }
    
    public function __clone()
    {
        if ($this->dt_obj) {
            $this->dt_obj = clone $this->dt_obj;
        }
    }
    
    public function __sleep()
    {
        return array('timezone', 'default_timezone', 'dt_obj');
    }
    
    public function __wakeup()
    {
        global $DIC;

        $this->log = $DIC->logger()->cal();
    }
        
    /**
     * Check if a date is null (Datetime == '0000-00-00 00:00:00', unixtime == 0,...)

     * @return bool
     */
    public function isNull()
    {
        return !($this->dt_obj instanceof DateTime);
    }
    
    /**
     * Switch timezone
     *
     * @access public
     * @param string PHP timezone identifier
     * @throws ilDateTimeException
     */
    public function switchTimeZone($a_timezone_identifier = '')
    {
        try {
            $this->timezone = ilTimeZone::_getInstance($a_timezone_identifier);
            return true;
        } catch (ilTimeZoneException $e) {
            $this->log->warning('Unsupported timezone given: ' . $a_timezone_identifier);
            throw new ilDateTimeException('Unsupported timezone given. Timezone: ' . $a_timezone_identifier);
        }
    }
    
    /**
     * get timezone identifier
     *
     * @access public
     *
     */
    public function getTimeZoneIdentifier()
    {
        return $this->timezone->getIdentifier();
    }
    
    /**
     * compare two dates and check start is before end
     * This method does not consider tz offsets.
     * So you have to take care that both dates are defined in the the same timezone
     *
     * @access public
     * @static
     *
     * @param object ilDateTime
     * @param object ilDateTime
     * @param string field used for comparison. E.g <code>IL_CAL_YEAR</code> checks if start is one or more years earlier than end
     * @param string timezone
     * @return bool
     */
    public static function _before(ilDateTime $start, ilDateTime $end, $a_compare_field = '', $a_tz = '')
    {
        if ($start->isNull() || $end->isNull()) {
            return false;
        }
        
        switch ($a_compare_field) {
            case IL_CAL_YEAR:
                return $start->get(IL_CAL_FKT_DATE, 'Y', $a_tz) < $end->get(IL_CAL_FKT_DATE, 'Y', $a_tz);
                
            case IL_CAL_MONTH:
                return (int) $start->get(IL_CAL_FKT_DATE, 'Ym', $a_tz) < $end->get(IL_CAL_FKT_DATE, 'Ym', $a_tz);
            
            case IL_CAL_DAY:
                return (int) $start->get(IL_CAL_FKT_DATE, 'Ymd', $a_tz) < $end->get(IL_CAL_FKT_DATE, 'Ymd', $a_tz);

            case '':
            default:
                return $start->dt_obj < $end->dt_obj;
            
        }
    }
    
    /**
     * Check if two date are equal
     *
     * @access public
     * @static
     *
     * @param object ilDateTime
     * @param object ilDateTime
     * @param string field used for comparison. E.g <code>IL_CAL_YEAR</code> checks if start is the same years than end
     * @param string timzone
     * @return bool
     */
    public static function _equals(ilDateTime $start, ilDateTime $end, $a_compare_field = '', $a_tz = '')
    {
        if ($start->isNull() || $end->isNull()) {
            return false;
        }
        
        switch ($a_compare_field) {
            case IL_CAL_YEAR:
                return $start->get(IL_CAL_FKT_DATE, 'Y', $a_tz) == $end->get(IL_CAL_FKT_DATE, 'Y', $a_tz);

            case IL_CAL_MONTH:
                return (int) $start->get(IL_CAL_FKT_DATE, 'Ym', $a_tz) == $end->get(IL_CAL_FKT_DATE, 'Ym', $a_tz);

            case IL_CAL_DAY:
                return (int) $start->get(IL_CAL_FKT_DATE, 'Ymd', $a_tz) == $end->get(IL_CAL_FKT_DATE, 'Ymd', $a_tz);

            case '':
            default:
                return $start->dt_obj == $end->dt_obj;
            
        }
    }

    /**
     * compare two dates and check start is after end
     * This method does not consider tz offsets.
     * So you have to take care that both dates are defined in the the same timezone
     *
     * @access public
     * @param object ilDateTime
     * @param object ilDateTime
     * @param string field used for comparison. E.g <code>IL_CAL_YEAR</code> checks if start is one or more years after than end
     * @param string timezone
     * @static
     */
    public static function _after(ilDateTime $start, ilDateTime $end, $a_compare_field = '', $a_tz = '')
    {
        if ($start->isNull() || $end->isNull()) {
            return false;
        }
        
        switch ($a_compare_field) {
            case IL_CAL_YEAR:
                return $start->get(IL_CAL_FKT_DATE, 'Y', $a_tz) > $end->get(IL_CAL_FKT_DATE, 'Y', $a_tz);

            case IL_CAL_MONTH:
                return (int) $start->get(IL_CAL_FKT_DATE, 'Ym', $a_tz) > $end->get(IL_CAL_FKT_DATE, 'Ym', $a_tz);

            case IL_CAL_DAY:
                return (int) $start->get(IL_CAL_FKT_DATE, 'Ymd', $a_tz) > $end->get(IL_CAL_FKT_DATE, 'Ymd', $a_tz);

            case '':
            default:
                return $start->dt_obj > $end->dt_obj;
            
        }
    }
    
    /**
     * Check whether an date is within a date duration given by start and end
     * @param ilDateTime $dt
     * @param ilDateTime $start
     * @param ilDateTime $end
     * @param type $a_compare_field
     * @param type $a_tz
     */
    public static function _within(ilDateTime $dt, ilDateTime $start, ilDateTime $end, $a_compare_field = '', $a_tz = '')
    {
        return
            (ilDateTime::_after($dt, $start, $a_compare_field, $a_tz) or ilDateTime::_equals($dt, $start, $a_compare_field, $a_tz)) &&
            (ilDateTime::_before($dt, $end, $a_compare_field, $a_tz) or ilDateTime::_equals($dt, $end, $a_compare_field, $a_tz));
    }
    
    /**
     * increment
     *
     * @access public
     * @param int type
     * @param int count
     *
     */
    public function increment($a_type, $a_count = 1)
    {
        if ($this->isNull()) {
            return;
        }
        
        $sub = ($a_count < 0);
        $count_str = abs($a_count);
        
        switch ($a_type) {
            case self::YEAR:
                $count_str .= 'year';
                break;

            case self::MONTH:
                $count_str .= 'month';
                break;
                
            case self::WEEK:
                $count_str .= 'week';
                break;
                
            case self::DAY:
                $count_str .= 'day';
                break;
                
            case self::HOUR:
                $count_str .= 'hour';
                break;
                
            case self::MINUTE:
                $count_str .= 'minute';
                break;

            case self::SECOND:
                $count_str .= 'second';
                break;
        }
        
        $interval = date_interval_create_from_date_string($count_str);
        if (!$sub) {
            $this->dt_obj->add($interval);
        } else {
            $this->dt_obj->sub($interval);
        }
        
        // ???
        return $this->getUnixTime();
    }
    
    /**
     * get unix time
     *
     * @access public
     *
     */
    public function getUnixTime()
    {
        if (!$this->isNull()) {
            return $this->dt_obj->getTimestamp();
        }
    }
    
    /**
     * get UTC offset
     *
     * @access public
     * @return offset to utc in seconds
     */
    public function getUTCOffset()
    {
        if (!$this->isNull()) {
            // already correct/current timezone?
            $offset = $this->dt_obj->getOffset();

            // TODO: This is wrong: calculate UTC offset of given date
            // $offset = mktime(0,0,0,2,1,1970) - gmmktime(0,0,0,2,1,1970);
        }
        return $offset;
    }
    
    protected function parsePartsToDate($a_year, $a_month, $a_day, $a_hour = null, $a_min = null, $a_sec = null, $a_timezone = null)
    {
        $a_year = (int) $a_year;
        $a_month = (int) $a_month;
        $a_day = (int) $a_day;
        
        if (!$a_year) {
            return;
        }
        
        try {
            $a_hour = (int) $a_hour;
            $a_min = (int) $a_min;
            $a_sec = (int) $a_sec;
            
            $format = $a_year . '-' . $a_month . '-' . $a_day;
                                
            if ($a_hour !== null) {
                $format .= ' ' . (int) $a_hour . ':' . (int) $a_min . ':' . (int) $a_sec;
                
                // use current timezone if no other given
                if (!$a_timezone) {
                    $a_timezone = $this->getTimeZoneIdentifier();
                }

                $date = new DateTime($format, new DateTimeZone($a_timezone));
            } else {
                $date = new DateTime($format);
            }
        } catch (Exception $ex) {
            // :TODO: do anything?
        }
        return ($date instanceof DateTime)
            ? $date
            : null;
    }
    
    /**
     * Set date
     *
     * @access public
     * @param mixed date
     * @param int format
     *
     * @throws ilDateTimeException
     *
     */
    public function setDate($a_date, $a_format)
    {
        $this->dt_obj = null;
        
        if (!$a_date) {
            return;
        }
        
        switch ($a_format) {
            case IL_CAL_UNIX:
                try {
                    $this->dt_obj = new DateTime('@' . $a_date);
                    $this->dt_obj->setTimezone(new DateTimeZone($this->getTimeZoneIdentifier()));
                } catch (Exception $ex) {
                    $message = 'Cannot parse date: ' . $a_date . ' with format ' . $a_format;
                    $this->log->warning($message);
                    throw new ilDateTimeException($message);
                }
                break;
                
            case IL_CAL_DATETIME:
                $matches = preg_match('/^(\d{4})-?(\d{2})-?(\d{2})([T\s]?(\d{2}):?(\d{2}):?(\d{2})(\.\d+)?(Z|[\+\-]\d{2}:?\d{2})?)$/i', $a_date, $d_parts);
                if ($matches < 1) {
                    $this->log->warning('Cannot parse date: ' . $a_date);
                    $this->log->warning(print_r($matches, true));
                    $this->log->logStack(ilLogLevel::WARNING);
                    throw new ilDateTimeException('Cannot parse date: ' . $a_date);
                }
                
                $tz_id = ($d_parts[9] == 'Z')
                    ? 'UTC'
                    : $this->getTimeZoneIdentifier();
                $this->dt_obj = $this->parsePartsToDate(
                    $d_parts[1],
                    $d_parts[2],
                    $d_parts[3],
                    $d_parts[5],
                    $d_parts[6],
                    $d_parts[7],
                    $tz_id
                );
                break;

            case IL_CAL_DATE:
                try {
                    // Pure dates are not timezone sensible.
                    $this->dt_obj = new DateTime($a_date, new DateTimeZone('UTC'));
                } catch (Exception $ex) {
                    $this->log->warning('Cannot parse date : ' . $a_date);
                    throw new ilDateTimeException('Cannot parse date: ' . $a_date);
                }
                break;
                
            case IL_CAL_FKT_GETDATE:
                // Format like getdate parameters
                $this->dt_obj = $this->parsePartsToDate(
                    $a_date['year'],
                    $a_date['mon'],
                    $a_date['mday'],
                    $a_date['hours'],
                    $a_date['minutes'],
                    $a_date['seconds'],
                    $this->getTimeZoneIdentifier()
                );
                break;
                
            case IL_CAL_TIMESTAMP:
                if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $a_date, $d_parts) == false) {
                    $this->log->warning('Cannot parse date: ' . $a_date);
                    throw new ilDateTimeException('Cannot parse date.');
                }
                $this->dt_obj = $this->parsePartsToDate(
                    $d_parts[1],
                    $d_parts[2],
                    $d_parts[3],
                    $d_parts[4],
                    $d_parts[5],
                    $d_parts[6],
                    $this->getTimeZoneIdentifier()
                );
                break;
                
            case IL_CAL_ISO_8601:
                $this->dt_obj = DateTime::createFromFormat(
                    DateTime::ISO8601,
                    $a_date,
                    new DateTimeZone($this->getTimeZoneIdentifier())
                );
                break;
        }

        // remove set timezone since it does not influence the internal date.
        // the tz must be passed in the moment of the creation of the date object.
        return true;
    }
    
    /**
     * get formatted date
     *
     * @access public
     * @param int format type
     * @param string format string
     * @param string a specific timezone
     * @return string|int
     */
    public function get($a_format, $a_format_str = '', $a_tz = '')
    {
        if ($this->isNull()) {
            return;
        }
            
        if ($a_tz) {
            try {
                $timezone = ilTimeZone::_getInstance($a_tz);
            } catch (ilTimeZoneException $exc) {
                $this->log->warning('Invalid timezone given. Timezone: ' . $a_tz);
            }
        } else {
            $timezone = $this->default_timezone;
        }
            
        $out_date = clone($this->dt_obj);
        $out_date->setTimezone(new DateTimeZone($timezone->getIdentifier()));

        switch ($a_format) {
            case IL_CAL_UNIX:
                // timezone unrelated
                $date = $this->getUnixTime();
                break;
            
            case IL_CAL_DATE:
                $date = $out_date->format('Y-m-d');
                break;
            
            case IL_CAL_DATETIME:
                $date = $out_date->format('Y-m-d H:i:s');
                break;
            
            case IL_CAL_FKT_DATE:
                $date = $out_date->format($a_format_str);
                break;
                
            case IL_CAL_FKT_GETDATE:
                $date = array(
                    'seconds' => (int) $out_date->format('s')
                    ,'minutes' => (int) $out_date->format('i')
                    ,'hours' => (int) $out_date->format('G')
                    ,'mday' => (int) $out_date->format('j')
                    ,'wday' => (int) $out_date->format('w')
                    ,'mon' => (int) $out_date->format('n')
                    ,'year' => (int) $out_date->format('Y')
                    ,'yday' => (int) $out_date->format('z')
                    ,'weekday' => $out_date->format('l')
                    ,'month' => $out_date->format('F')
                    ,'isoday' => (int) $out_date->format('N')
                );
                break;
            
            case IL_CAL_ISO_8601:
                $date = $out_date->format('c');
                break;
                
            case IL_CAL_TIMESTAMP:
                $date = $out_date->format('YmdHis');
                break;
        }
        
        return $date;
    }
    
    /**
     * to string for date time objects
     * Output is user time zone
     *
     * @access public
     * @param
     * @return
     */
    public function __toString()
    {
        return $this->get(IL_CAL_DATETIME) . '<br>';
    }
}
