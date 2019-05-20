<?php



/**
 * CalAuthToken
 */
class CalAuthToken
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var string
     */
    private $hash = '';

    /**
     * @var int
     */
    private $selection = '0';

    /**
     * @var int
     */
    private $calendar = '0';

    /**
     * @var string|null
     */
    private $ical;

    /**
     * @var int
     */
    private $cTime = '0';


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return CalAuthToken
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set hash.
     *
     * @param string $hash
     *
     * @return CalAuthToken
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set selection.
     *
     * @param int $selection
     *
     * @return CalAuthToken
     */
    public function setSelection($selection)
    {
        $this->selection = $selection;

        return $this;
    }

    /**
     * Get selection.
     *
     * @return int
     */
    public function getSelection()
    {
        return $this->selection;
    }

    /**
     * Set calendar.
     *
     * @param int $calendar
     *
     * @return CalAuthToken
     */
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * Get calendar.
     *
     * @return int
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * Set ical.
     *
     * @param string|null $ical
     *
     * @return CalAuthToken
     */
    public function setIcal($ical = null)
    {
        $this->ical = $ical;

        return $this;
    }

    /**
     * Get ical.
     *
     * @return string|null
     */
    public function getIcal()
    {
        return $this->ical;
    }

    /**
     * Set cTime.
     *
     * @param int $cTime
     *
     * @return CalAuthToken
     */
    public function setCTime($cTime)
    {
        $this->cTime = $cTime;

        return $this;
    }

    /**
     * Get cTime.
     *
     * @return int
     */
    public function getCTime()
    {
        return $this->cTime;
    }
}
