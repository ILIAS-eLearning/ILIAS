<?php



/**
 * BookingSchedule
 */
class BookingSchedule
{
    /**
     * @var int
     */
    private $bookingScheduleId = '0';

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var int
     */
    private $poolId = '0';

    /**
     * @var int|null
     */
    private $deadline;

    /**
     * @var int|null
     */
    private $rentMin;

    /**
     * @var int|null
     */
    private $rentMax;

    /**
     * @var int|null
     */
    private $raster;

    /**
     * @var int|null
     */
    private $autoBreak;

    /**
     * @var int|null
     */
    private $avFrom;

    /**
     * @var int|null
     */
    private $avTo;


    /**
     * Get bookingScheduleId.
     *
     * @return int
     */
    public function getBookingScheduleId()
    {
        return $this->bookingScheduleId;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return BookingSchedule
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set poolId.
     *
     * @param int $poolId
     *
     * @return BookingSchedule
     */
    public function setPoolId($poolId)
    {
        $this->poolId = $poolId;

        return $this;
    }

    /**
     * Get poolId.
     *
     * @return int
     */
    public function getPoolId()
    {
        return $this->poolId;
    }

    /**
     * Set deadline.
     *
     * @param int|null $deadline
     *
     * @return BookingSchedule
     */
    public function setDeadline($deadline = null)
    {
        $this->deadline = $deadline;

        return $this;
    }

    /**
     * Get deadline.
     *
     * @return int|null
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * Set rentMin.
     *
     * @param int|null $rentMin
     *
     * @return BookingSchedule
     */
    public function setRentMin($rentMin = null)
    {
        $this->rentMin = $rentMin;

        return $this;
    }

    /**
     * Get rentMin.
     *
     * @return int|null
     */
    public function getRentMin()
    {
        return $this->rentMin;
    }

    /**
     * Set rentMax.
     *
     * @param int|null $rentMax
     *
     * @return BookingSchedule
     */
    public function setRentMax($rentMax = null)
    {
        $this->rentMax = $rentMax;

        return $this;
    }

    /**
     * Get rentMax.
     *
     * @return int|null
     */
    public function getRentMax()
    {
        return $this->rentMax;
    }

    /**
     * Set raster.
     *
     * @param int|null $raster
     *
     * @return BookingSchedule
     */
    public function setRaster($raster = null)
    {
        $this->raster = $raster;

        return $this;
    }

    /**
     * Get raster.
     *
     * @return int|null
     */
    public function getRaster()
    {
        return $this->raster;
    }

    /**
     * Set autoBreak.
     *
     * @param int|null $autoBreak
     *
     * @return BookingSchedule
     */
    public function setAutoBreak($autoBreak = null)
    {
        $this->autoBreak = $autoBreak;

        return $this;
    }

    /**
     * Get autoBreak.
     *
     * @return int|null
     */
    public function getAutoBreak()
    {
        return $this->autoBreak;
    }

    /**
     * Set avFrom.
     *
     * @param int|null $avFrom
     *
     * @return BookingSchedule
     */
    public function setAvFrom($avFrom = null)
    {
        $this->avFrom = $avFrom;

        return $this;
    }

    /**
     * Get avFrom.
     *
     * @return int|null
     */
    public function getAvFrom()
    {
        return $this->avFrom;
    }

    /**
     * Set avTo.
     *
     * @param int|null $avTo
     *
     * @return BookingSchedule
     */
    public function setAvTo($avTo = null)
    {
        $this->avTo = $avTo;

        return $this;
    }

    /**
     * Get avTo.
     *
     * @return int|null
     */
    public function getAvTo()
    {
        return $this->avTo;
    }
}
