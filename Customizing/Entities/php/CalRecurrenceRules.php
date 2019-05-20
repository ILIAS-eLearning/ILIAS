<?php



/**
 * CalRecurrenceRules
 */
class CalRecurrenceRules
{
    /**
     * @var int
     */
    private $ruleId = '0';

    /**
     * @var int
     */
    private $calId = '0';

    /**
     * @var int
     */
    private $calRecurrence = '0';

    /**
     * @var string|null
     */
    private $freqType;

    /**
     * @var \DateTime|null
     */
    private $freqUntilDate;

    /**
     * @var int
     */
    private $freqUntilCount = '0';

    /**
     * @var int
     */
    private $intervall = '0';

    /**
     * @var string|null
     */
    private $byday;

    /**
     * @var string|null
     */
    private $byweekno = '0';

    /**
     * @var string|null
     */
    private $bymonth;

    /**
     * @var string|null
     */
    private $bymonthday;

    /**
     * @var string|null
     */
    private $byyearday;

    /**
     * @var string|null
     */
    private $bysetpos = '0';

    /**
     * @var string|null
     */
    private $weekstart;


    /**
     * Get ruleId.
     *
     * @return int
     */
    public function getRuleId()
    {
        return $this->ruleId;
    }

    /**
     * Set calId.
     *
     * @param int $calId
     *
     * @return CalRecurrenceRules
     */
    public function setCalId($calId)
    {
        $this->calId = $calId;

        return $this;
    }

    /**
     * Get calId.
     *
     * @return int
     */
    public function getCalId()
    {
        return $this->calId;
    }

    /**
     * Set calRecurrence.
     *
     * @param int $calRecurrence
     *
     * @return CalRecurrenceRules
     */
    public function setCalRecurrence($calRecurrence)
    {
        $this->calRecurrence = $calRecurrence;

        return $this;
    }

    /**
     * Get calRecurrence.
     *
     * @return int
     */
    public function getCalRecurrence()
    {
        return $this->calRecurrence;
    }

    /**
     * Set freqType.
     *
     * @param string|null $freqType
     *
     * @return CalRecurrenceRules
     */
    public function setFreqType($freqType = null)
    {
        $this->freqType = $freqType;

        return $this;
    }

    /**
     * Get freqType.
     *
     * @return string|null
     */
    public function getFreqType()
    {
        return $this->freqType;
    }

    /**
     * Set freqUntilDate.
     *
     * @param \DateTime|null $freqUntilDate
     *
     * @return CalRecurrenceRules
     */
    public function setFreqUntilDate($freqUntilDate = null)
    {
        $this->freqUntilDate = $freqUntilDate;

        return $this;
    }

    /**
     * Get freqUntilDate.
     *
     * @return \DateTime|null
     */
    public function getFreqUntilDate()
    {
        return $this->freqUntilDate;
    }

    /**
     * Set freqUntilCount.
     *
     * @param int $freqUntilCount
     *
     * @return CalRecurrenceRules
     */
    public function setFreqUntilCount($freqUntilCount)
    {
        $this->freqUntilCount = $freqUntilCount;

        return $this;
    }

    /**
     * Get freqUntilCount.
     *
     * @return int
     */
    public function getFreqUntilCount()
    {
        return $this->freqUntilCount;
    }

    /**
     * Set intervall.
     *
     * @param int $intervall
     *
     * @return CalRecurrenceRules
     */
    public function setIntervall($intervall)
    {
        $this->intervall = $intervall;

        return $this;
    }

    /**
     * Get intervall.
     *
     * @return int
     */
    public function getIntervall()
    {
        return $this->intervall;
    }

    /**
     * Set byday.
     *
     * @param string|null $byday
     *
     * @return CalRecurrenceRules
     */
    public function setByday($byday = null)
    {
        $this->byday = $byday;

        return $this;
    }

    /**
     * Get byday.
     *
     * @return string|null
     */
    public function getByday()
    {
        return $this->byday;
    }

    /**
     * Set byweekno.
     *
     * @param string|null $byweekno
     *
     * @return CalRecurrenceRules
     */
    public function setByweekno($byweekno = null)
    {
        $this->byweekno = $byweekno;

        return $this;
    }

    /**
     * Get byweekno.
     *
     * @return string|null
     */
    public function getByweekno()
    {
        return $this->byweekno;
    }

    /**
     * Set bymonth.
     *
     * @param string|null $bymonth
     *
     * @return CalRecurrenceRules
     */
    public function setBymonth($bymonth = null)
    {
        $this->bymonth = $bymonth;

        return $this;
    }

    /**
     * Get bymonth.
     *
     * @return string|null
     */
    public function getBymonth()
    {
        return $this->bymonth;
    }

    /**
     * Set bymonthday.
     *
     * @param string|null $bymonthday
     *
     * @return CalRecurrenceRules
     */
    public function setBymonthday($bymonthday = null)
    {
        $this->bymonthday = $bymonthday;

        return $this;
    }

    /**
     * Get bymonthday.
     *
     * @return string|null
     */
    public function getBymonthday()
    {
        return $this->bymonthday;
    }

    /**
     * Set byyearday.
     *
     * @param string|null $byyearday
     *
     * @return CalRecurrenceRules
     */
    public function setByyearday($byyearday = null)
    {
        $this->byyearday = $byyearday;

        return $this;
    }

    /**
     * Get byyearday.
     *
     * @return string|null
     */
    public function getByyearday()
    {
        return $this->byyearday;
    }

    /**
     * Set bysetpos.
     *
     * @param string|null $bysetpos
     *
     * @return CalRecurrenceRules
     */
    public function setBysetpos($bysetpos = null)
    {
        $this->bysetpos = $bysetpos;

        return $this;
    }

    /**
     * Get bysetpos.
     *
     * @return string|null
     */
    public function getBysetpos()
    {
        return $this->bysetpos;
    }

    /**
     * Set weekstart.
     *
     * @param string|null $weekstart
     *
     * @return CalRecurrenceRules
     */
    public function setWeekstart($weekstart = null)
    {
        $this->weekstart = $weekstart;

        return $this;
    }

    /**
     * Get weekstart.
     *
     * @return string|null
     */
    public function getWeekstart()
    {
        return $this->weekstart;
    }
}
