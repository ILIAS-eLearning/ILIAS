<?php



/**
 * BookingSettings
 */
class BookingSettings
{
    /**
     * @var int
     */
    private $bookingPoolId = '0';

    /**
     * @var bool|null
     */
    private $publicLog;

    /**
     * @var bool|null
     */
    private $poolOffline;

    /**
     * @var int|null
     */
    private $slotsNo = '0';

    /**
     * @var bool
     */
    private $scheduleType = '1';

    /**
     * @var bool|null
     */
    private $ovlimit;

    /**
     * @var int|null
     */
    private $rsvFilterPeriod;

    /**
     * @var bool
     */
    private $reminderStatus = '0';

    /**
     * @var int
     */
    private $reminderDay = '0';

    /**
     * @var int
     */
    private $lastRemindTs = '0';


    /**
     * Get bookingPoolId.
     *
     * @return int
     */
    public function getBookingPoolId()
    {
        return $this->bookingPoolId;
    }

    /**
     * Set publicLog.
     *
     * @param bool|null $publicLog
     *
     * @return BookingSettings
     */
    public function setPublicLog($publicLog = null)
    {
        $this->publicLog = $publicLog;

        return $this;
    }

    /**
     * Get publicLog.
     *
     * @return bool|null
     */
    public function getPublicLog()
    {
        return $this->publicLog;
    }

    /**
     * Set poolOffline.
     *
     * @param bool|null $poolOffline
     *
     * @return BookingSettings
     */
    public function setPoolOffline($poolOffline = null)
    {
        $this->poolOffline = $poolOffline;

        return $this;
    }

    /**
     * Get poolOffline.
     *
     * @return bool|null
     */
    public function getPoolOffline()
    {
        return $this->poolOffline;
    }

    /**
     * Set slotsNo.
     *
     * @param int|null $slotsNo
     *
     * @return BookingSettings
     */
    public function setSlotsNo($slotsNo = null)
    {
        $this->slotsNo = $slotsNo;

        return $this;
    }

    /**
     * Get slotsNo.
     *
     * @return int|null
     */
    public function getSlotsNo()
    {
        return $this->slotsNo;
    }

    /**
     * Set scheduleType.
     *
     * @param bool $scheduleType
     *
     * @return BookingSettings
     */
    public function setScheduleType($scheduleType)
    {
        $this->scheduleType = $scheduleType;

        return $this;
    }

    /**
     * Get scheduleType.
     *
     * @return bool
     */
    public function getScheduleType()
    {
        return $this->scheduleType;
    }

    /**
     * Set ovlimit.
     *
     * @param bool|null $ovlimit
     *
     * @return BookingSettings
     */
    public function setOvlimit($ovlimit = null)
    {
        $this->ovlimit = $ovlimit;

        return $this;
    }

    /**
     * Get ovlimit.
     *
     * @return bool|null
     */
    public function getOvlimit()
    {
        return $this->ovlimit;
    }

    /**
     * Set rsvFilterPeriod.
     *
     * @param int|null $rsvFilterPeriod
     *
     * @return BookingSettings
     */
    public function setRsvFilterPeriod($rsvFilterPeriod = null)
    {
        $this->rsvFilterPeriod = $rsvFilterPeriod;

        return $this;
    }

    /**
     * Get rsvFilterPeriod.
     *
     * @return int|null
     */
    public function getRsvFilterPeriod()
    {
        return $this->rsvFilterPeriod;
    }

    /**
     * Set reminderStatus.
     *
     * @param bool $reminderStatus
     *
     * @return BookingSettings
     */
    public function setReminderStatus($reminderStatus)
    {
        $this->reminderStatus = $reminderStatus;

        return $this;
    }

    /**
     * Get reminderStatus.
     *
     * @return bool
     */
    public function getReminderStatus()
    {
        return $this->reminderStatus;
    }

    /**
     * Set reminderDay.
     *
     * @param int $reminderDay
     *
     * @return BookingSettings
     */
    public function setReminderDay($reminderDay)
    {
        $this->reminderDay = $reminderDay;

        return $this;
    }

    /**
     * Get reminderDay.
     *
     * @return int
     */
    public function getReminderDay()
    {
        return $this->reminderDay;
    }

    /**
     * Set lastRemindTs.
     *
     * @param int $lastRemindTs
     *
     * @return BookingSettings
     */
    public function setLastRemindTs($lastRemindTs)
    {
        $this->lastRemindTs = $lastRemindTs;

        return $this;
    }

    /**
     * Get lastRemindTs.
     *
     * @return int
     */
    public function getLastRemindTs()
    {
        return $this->lastRemindTs;
    }
}
