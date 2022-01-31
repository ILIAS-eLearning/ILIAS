<?php declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Booking period
 * Used for calculation of recurring events
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesBooking
 */
class ilBookingPeriod implements ilDatePeriod
{
    private ?ilDateTime $start = null;
    private ?ilDateTime $end = null;

    /**
     * Constructor
     */
    public function __construct(ilDateTime $start, ilDateTime $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @see ilDatePeriod::getEnd()
     */
    public function getEnd() : ?ilDateTime
    {
        return $this->end;
    }

    /**
     * @see ilDatePeriod::getStart()
     */
    public function getStart() : ?ilDateTime
    {
        return $this->start;
    }

    /**
     * @see ilDatePeriod::isFullday()
     */
    public function isFullday() : bool
    {
        return false;
    }
}
