<?php declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

const IL_CAL_DATETIME = 1;
const IL_CAL_DATE = 2;
const IL_CAL_UNIX = 3;
const IL_CAL_FKT_DATE = 4;
const IL_CAL_FKT_GETDATE = 5;
const IL_CAL_TIMESTAMP = 6;
const IL_CAL_ISO_8601 = 7;

const IL_CAL_YEAR = 'year';
const IL_CAL_MONTH = 'month';
const IL_CAL_WEEK = 'week';
const IL_CAL_DAY = 'day';
const IL_CAL_HOUR = 'hour';
const IL_CAL_SECOND = 'second';

/**
 * @classDescription Date and time handling
 * @author           Stefan Meyer <meyer@leifos.com>
 * @version          $Id$
 * @ingroup          ServicesCalendar
 */
class ilDateTime
{
    public const YEAR = 'year';
    public const MONTH = 'month';
    public const WEEK = 'week';
    public const DAY = 'day';
    public const HOUR = 'hour';
    public const MINUTE = 'minute';
    public const SECOND = 'second';

    protected ilLogger $log;
    protected ?ilTimeZone $timezone = null;
    protected ?ilTimeZone $default_timezone = null;
    protected ?DateTime $dt_obj = null;

    /**
     * Create new date object
     * @param int|string following the format given as the second parameter
     * @param int format
     * @param string
     * @throws ilDateTimeException
     */
    public function __construct($a_date = null, int $a_format = 0, string $a_tz = '')
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
    public function isNull() : bool
    {
        return !($this->dt_obj instanceof DateTime);
    }

    /**
     * Switch timezone
     * @param string PHP timezone identifier
     * @throws ilDateTimeException
     */
    public function switchTimeZone(string $a_timezone_identifier = '') : void
    {
        try {
            $this->timezone = ilTimeZone::_getInstance($a_timezone_identifier);
            return;
        } catch (ilTimeZoneException $e) {
            $this->log->warning('Unsupported timezone given: ' . $a_timezone_identifier);
            throw new ilDateTimeException('Unsupported timezone given. Timezone: ' . $a_timezone_identifier);
        }
    }

    public function getTimeZoneIdentifier() : string
    {
        return $this->timezone->getIdentifier();
    }

    /**
     * compare two dates and check start is before end
     * This method does not consider tz offsets.
     * So you have to take care that both dates are defined in the the same timezone
     * @param ilDateTime
     * @param ilDateTime
     * @param string field used for comparison. E.g <code>IL_CAL_YEAR</code> checks if start is one or more years earlier than end
     * @param string timezone
     * @return bool
     */
    public static function _before(
        ilDateTime $start,
        ilDateTime $end,
        string $a_compare_field = '',
        string $a_tz = ''
    ) : bool {
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
     * @param ilDateTime
     * @param ilDateTime
     * @param string field used for comparison. E.g <code>IL_CAL_YEAR</code> checks if start is the same years than end
     * @param string timzone
     * @return bool
     */
    public static function _equals(
        ilDateTime $start,
        ilDateTime $end,
        string $a_compare_field = '',
        string $a_tz = ''
    ) : bool {
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
     * @access public
     * @param ilDateTime
     * @param ilDateTime
     * @param string field used for comparison. E.g <code>IL_CAL_YEAR</code> checks if start is one or more years after than end
     * @param string timezone
     * @return bool
     */
    public static function _after(
        ilDateTime $start,
        ilDateTime $end,
        string $a_compare_field = '',
        string $a_tz = ''
    ) : bool {
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
     */
    public static function _within(
        ilDateTime $dt,
        ilDateTime $start,
        ilDateTime $end,
        string $a_compare_field = '',
        string $a_tz = ''
    ) : bool {
        return
            (ilDateTime::_after($dt, $start, $a_compare_field, $a_tz) or ilDateTime::_equals(
                $dt,
                $start,
                $a_compare_field,
                $a_tz
            )) &&
            (ilDateTime::_before($dt, $end, $a_compare_field, $a_tz) or ilDateTime::_equals(
                $dt,
                $end,
                $a_compare_field,
                $a_tz
            ));
    }

    /**
     * @param string $a_type
     * @param int    $a_count
     * @return int|null
     * @todo refactor return type
     */
    public function increment(string $a_type, int $a_count = 1) : ?int
    {
        if ($this->isNull()) {
            return null;
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
        return $this->getUnixTime();
    }

    public function getUnixTime() : ?int
    {
        if (!$this->isNull()) {
            return $this->dt_obj->getTimestamp();
        }
        return null;
    }

    protected function parsePartsToDate(
        int $a_year,
        int $a_month,
        int $a_day,
        ?int $a_hour = null,
        ?int $a_min = null,
        ?int $a_sec = null,
        ?string $a_timezone = null
    ) : ?DateTime {
        $a_year = $a_year;
        $a_month = $a_month;
        $a_day = $a_day;

        if (!$a_year) {
            return null;
        }
        $date = null;
        try {
            $a_hour = (int) $a_hour;
            $a_min = (int) $a_min;
            $a_sec = (int) $a_sec;

            $format = $a_year . '-' . $a_month . '-' . $a_day;

            if ($a_hour !== null) {
                $format .= ' ' . $a_hour . ':' . $a_min . ':' . $a_sec;

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
     * @throws ilDateTimeException
     * @todo fix ISO_8601 support
     */
    public function setDate($a_date, int $a_format) : void
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
                $matches = preg_match(
                    '/^(\d{4})-?(\d{2})-?(\d{2})([T\s]?(\d{2}):?(\d{2}):?(\d{2})(\.\d+)?(Z|[\+\-]\d{2}:?\d{2})?)$/i',
                    $a_date,
                    $d_parts
                );
                if ($matches < 1) {
                    $this->log->warning('Cannot parse date: ' . $a_date);
                    $this->log->warning(print_r($matches, true));
                    $this->log->logStack(ilLogLevel::WARNING);
                    throw new ilDateTimeException('Cannot parse date: ' . $a_date);
                }

                $tz_id = (isset($d_parts[9]) && $d_parts[9] === 'Z')
                    ? 'UTC'
                    : $this->getTimeZoneIdentifier();
                $this->dt_obj = $this->parsePartsToDate(
                    (int) $d_parts[1],
                    (int) $d_parts[2],
                    (int) $d_parts[3],
                    (int) $d_parts[5],
                    (int) $d_parts[6],
                    (int) $d_parts[7],
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
                    (int) $a_date['year'],
                    (int) $a_date['mon'],
                    (int) $a_date['mday'],
                    (int) $a_date['hours'],
                    (int) $a_date['minutes'],
                    (int) ($a_date['seconds'] ?? 0),
                    $this->getTimeZoneIdentifier()
                );
                break;

            case IL_CAL_TIMESTAMP:
                if (!preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $a_date, $d_parts)) {
                    $this->log->warning('Cannot parse date: ' . $a_date);
                    throw new ilDateTimeException('Cannot parse date.');
                }
                $this->dt_obj = $this->parsePartsToDate(
                    (int) $d_parts[1],
                    (int) $d_parts[2],
                    (int) $d_parts[3],
                    (int) $d_parts[4],
                    (int) $d_parts[5],
                    (int) $d_parts[6],
                    $this->getTimeZoneIdentifier()
                );
                break;

            case IL_CAL_ISO_8601:
                /**
                 * False "DateTime::ATOM was removed 7.2 warning" is a false positve
                 */
                /** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
                $this->dt_obj = DateTime::createFromFormat(
                    DateTime::ATOM,
                    $a_date,
                    new DateTimeZone($this->getTimeZoneIdentifier())
                );
                break;
        }
        // remove set timezone since it does not influence the internal date.
        // the tz must be passed in the moment of the creation of the date object.
    }

    /**
     * get formatted date
     * @return string|int|array|null
     */
    public function get(int $a_format, string $a_format_str = '', string $a_tz = '')
    {
        if ($this->isNull()) {
            return null;
        }

        $timezone = $this->default_timezone;
        if ($a_tz) {
            try {
                $timezone = ilTimeZone::_getInstance($a_tz);
            } catch (ilTimeZoneException $exc) {
                $this->log->warning('Invalid timezone given. Timezone: ' . $a_tz);
            }
        }
        $out_date = clone($this->dt_obj);
        $out_date->setTimezone(new DateTimeZone($timezone->getIdentifier()));

        $date = null;
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
                    ,
                    'minutes' => (int) $out_date->format('i')
                    ,
                    'hours' => (int) $out_date->format('G')
                    ,
                    'mday' => (int) $out_date->format('j')
                    ,
                    'wday' => (int) $out_date->format('w')
                    ,
                    'mon' => (int) $out_date->format('n')
                    ,
                    'year' => (int) $out_date->format('Y')
                    ,
                    'yday' => (int) $out_date->format('z')
                    ,
                    'weekday' => $out_date->format('l')
                    ,
                    'month' => $out_date->format('F')
                    ,
                    'isoday' => (int) $out_date->format('N')
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
     * @access public
     * @param
     * @return
     */
    public function __toString() : string
    {
        return $this->get(IL_CAL_DATETIME) . '<br>';
    }
}
