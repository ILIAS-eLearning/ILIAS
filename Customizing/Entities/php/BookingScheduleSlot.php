<?php



/**
 * BookingScheduleSlot
 */
class BookingScheduleSlot
{
    /**
     * @var int
     */
    private $bookingScheduleId = '0';

    /**
     * @var string
     */
    private $dayId = '';

    /**
     * @var bool
     */
    private $slotId = '0';

    /**
     * @var string
     */
    private $times = '';


    /**
     * Set bookingScheduleId.
     *
     * @param int $bookingScheduleId
     *
     * @return BookingScheduleSlot
     */
    public function setBookingScheduleId($bookingScheduleId)
    {
        $this->bookingScheduleId = $bookingScheduleId;

        return $this;
    }

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
     * Set dayId.
     *
     * @param string $dayId
     *
     * @return BookingScheduleSlot
     */
    public function setDayId($dayId)
    {
        $this->dayId = $dayId;

        return $this;
    }

    /**
     * Get dayId.
     *
     * @return string
     */
    public function getDayId()
    {
        return $this->dayId;
    }

    /**
     * Set slotId.
     *
     * @param bool $slotId
     *
     * @return BookingScheduleSlot
     */
    public function setSlotId($slotId)
    {
        $this->slotId = $slotId;

        return $this;
    }

    /**
     * Get slotId.
     *
     * @return bool
     */
    public function getSlotId()
    {
        return $this->slotId;
    }

    /**
     * Set times.
     *
     * @param string $times
     *
     * @return BookingScheduleSlot
     */
    public function setTimes($times)
    {
        $this->times = $times;

        return $this;
    }

    /**
     * Get times.
     *
     * @return string
     */
    public function getTimes()
    {
        return $this->times;
    }
}
