<?php



/**
 * EventAppointment
 */
class EventAppointment
{
    /**
     * @var int
     */
    private $appointmentId = '0';

    /**
     * @var int
     */
    private $eventId = '0';

    /**
     * @var \DateTime|null
     */
    private $eStart;

    /**
     * @var \DateTime|null
     */
    private $eEnd;

    /**
     * @var int
     */
    private $startingTime = '0';

    /**
     * @var int
     */
    private $endingTime = '0';

    /**
     * @var bool
     */
    private $fulltime = '0';


    /**
     * Get appointmentId.
     *
     * @return int
     */
    public function getAppointmentId()
    {
        return $this->appointmentId;
    }

    /**
     * Set eventId.
     *
     * @param int $eventId
     *
     * @return EventAppointment
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Get eventId.
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Set eStart.
     *
     * @param \DateTime|null $eStart
     *
     * @return EventAppointment
     */
    public function setEStart($eStart = null)
    {
        $this->eStart = $eStart;

        return $this;
    }

    /**
     * Get eStart.
     *
     * @return \DateTime|null
     */
    public function getEStart()
    {
        return $this->eStart;
    }

    /**
     * Set eEnd.
     *
     * @param \DateTime|null $eEnd
     *
     * @return EventAppointment
     */
    public function setEEnd($eEnd = null)
    {
        $this->eEnd = $eEnd;

        return $this;
    }

    /**
     * Get eEnd.
     *
     * @return \DateTime|null
     */
    public function getEEnd()
    {
        return $this->eEnd;
    }

    /**
     * Set startingTime.
     *
     * @param int $startingTime
     *
     * @return EventAppointment
     */
    public function setStartingTime($startingTime)
    {
        $this->startingTime = $startingTime;

        return $this;
    }

    /**
     * Get startingTime.
     *
     * @return int
     */
    public function getStartingTime()
    {
        return $this->startingTime;
    }

    /**
     * Set endingTime.
     *
     * @param int $endingTime
     *
     * @return EventAppointment
     */
    public function setEndingTime($endingTime)
    {
        $this->endingTime = $endingTime;

        return $this;
    }

    /**
     * Get endingTime.
     *
     * @return int
     */
    public function getEndingTime()
    {
        return $this->endingTime;
    }

    /**
     * Set fulltime.
     *
     * @param bool $fulltime
     *
     * @return EventAppointment
     */
    public function setFulltime($fulltime)
    {
        $this->fulltime = $fulltime;

        return $this;
    }

    /**
     * Get fulltime.
     *
     * @return bool
     */
    public function getFulltime()
    {
        return $this->fulltime;
    }
}
