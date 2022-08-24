<?php

declare(strict_types=1);

/**
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesCalendar
 */
class ilCalendarUserSettings
{
    public const CAL_SELECTION_MEMBERSHIP = 1;
    public const CAL_SELECTION_ITEMS = 2;

    public const CAL_EXPORT_TZ_TZ = 1;
    public const CAL_EXPORT_TZ_UTC = 2;

    public static array $instances = array();

    protected ilObjUser $user;
    protected ilCalendarSettings $settings;

    private int $calendar_selection_type = 1;
    private string $timezone = ilTimeZone::UTC;
    private int $export_tz_type = self::CAL_EXPORT_TZ_TZ;
    private int $weekstart = 0;
    private int $time_format = 0;
    private int $date_format = 0;

    private int $day_start = 0;
    private int $day_end = 0;
    private bool $show_weeks = true;

    private function __construct(int $a_user_id)
    {
        global $DIC;

        $this->user = $DIC->user();

        if ($this->user->getId() !== $a_user_id) {
            $user = ilObjectFactory::getInstanceByObjId($a_user_id, false);
            if ($user instanceof ilObjUser) {
                $this->user = $user;
            } else {
                throw new DomainException('Invalid user id given: ' . $a_user_id);
            }
        }
        $this->settings = ilCalendarSettings::_getInstance();
        $this->read();
    }

    public static function _getInstanceByUserId(int $a_user_id): ilCalendarUserSettings
    {
        if (isset(self::$instances[$a_user_id])) {
            return self::$instances[$a_user_id];
        }
        return self::$instances[$a_user_id] = new ilCalendarUserSettings($a_user_id);
    }

    public static function _getInstance(): ilCalendarUserSettings
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        return self::_getInstanceByUserId($ilUser->getId());
    }

    public function getTimeZone(): string
    {
        return $this->timezone;
    }

    public function setTimeZone(string $a_tz): void
    {
        $this->timezone = $a_tz;
    }

    public function getExportTimeZoneType(): int
    {
        return $this->export_tz_type;
    }

    public function setExportTimeZoneType(int $a_type): void
    {
        $this->export_tz_type = $a_type;
    }

    public function getExportTimeZone(): string
    {
        switch ($this->getExportTimeZoneType()) {
            case self::CAL_EXPORT_TZ_TZ:
                return $this->getTimeZone();

            case self::CAL_EXPORT_TZ_UTC:
                return ilTimeZone::UTC;
        }
        return '';
    }

    public function setWeekStart(int $a_weekstart): void
    {
        $this->weekstart = $a_weekstart;
    }

    public function getWeekStart(): int
    {
        return $this->weekstart;
    }

    public function setDayStart(int $a_start): void
    {
        $this->day_start = $a_start;
    }

    public function getDayStart(): int
    {
        return $this->day_start;
    }

    public function setDayEnd(int $a_end): void
    {
        $this->day_end = $a_end;
    }

    public function getDayEnd(): int
    {
        return $this->day_end;
    }

    public function setDateFormat(int $a_format): void
    {
        $this->date_format = $a_format;
    }

    public function getDateFormat(): int
    {
        return $this->date_format;
    }

    public function setTimeFormat(int $a_format): void
    {
        $this->time_format = $a_format;
    }

    public function getTimeFormat(): int
    {
        return $this->time_format;
    }

    /**
     * get calendar selection type
     * ("MyMembership" or "Selected Items")
     */
    public function getCalendarSelectionType(): int
    {
        return $this->calendar_selection_type;
    }

    /**
     * set calendar selection type
     * @param int $type self::CAL_SELECTION_MEMBERSHIP | self::CAL_SELECTION_ITEM
     * @return void
     */
    public function setCalendarSelectionType(int $a_type)
    {
        $this->calendar_selection_type = $a_type;
    }

    public function setShowWeeks(bool $a_val): void
    {
        $this->show_weeks = $a_val;
    }

    public function getShowWeeks(): bool
    {
        return $this->show_weeks;
    }

    public function save()
    {
        $this->user->writePref('user_tz', $this->getTimeZone());
        $this->user->writePref('export_tz_type', (string) $this->getExportTimeZoneType());
        $this->user->writePref('weekstart', (string) $this->getWeekStart());
        $this->user->writePref('date_format', (string) $this->getDateFormat());
        $this->user->writePref('time_format', (string) $this->getTimeFormat());
        $this->user->writePref('calendar_selection_type', (string) $this->getCalendarSelectionType());
        $this->user->writePref('day_start', (string) $this->getDayStart());
        $this->user->writePref('day_end', (string) $this->getDayEnd());
        $this->user->writePref('show_weeks', (string) $this->getShowWeeks());
    }

    protected function read(): void
    {
        $this->timezone = (string) $this->user->getTimeZone();
        $this->export_tz_type = (int) (
            ($this->user->getPref('export_tz_type') !== false) ?
            $this->user->getPref('export_tz_type') :
            $this->export_tz_type
        );
        $this->date_format = (int) $this->user->getDateFormat();
        $this->time_format = (int) $this->user->getTimeFormat();
        if (($weekstart = $this->user->getPref('weekstart')) === false) {
            $weekstart = $this->settings->getDefaultWeekStart();
        }
        $this->calendar_selection_type = (int) $this->user->getPref('calendar_selection_type') ?
            (int) $this->user->getPref('calendar_selection_type') :
            self::CAL_SELECTION_MEMBERSHIP;

        $this->weekstart = (int) $weekstart;

        $this->setDayStart(
            $this->user->getPref('day_start') ?
                (int) $this->user->getPref('day_start') :
                $this->settings->getDefaultDayStart()
        );
        $this->setDayEnd(
            $this->user->getPref('day_end') ?
                (int) $this->user->getPref('day_end') :
                $this->settings->getDefaultDayEnd()
        );
        $this->setShowWeeks(
            $this->user->getPref('show_weeks') ?
                (bool) $this->user->getPref('show_weeks') :
                $this->settings->getShowWeeks()
        );
    }
}
