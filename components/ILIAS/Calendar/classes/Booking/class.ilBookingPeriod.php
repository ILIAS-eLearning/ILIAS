<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);
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
    public function getEnd(): ?ilDateTime
    {
        return $this->end;
    }

    /**
     * @see ilDatePeriod::getStart()
     */
    public function getStart(): ?ilDateTime
    {
        return $this->start;
    }

    /**
     * @see ilDatePeriod::isFullday()
     */
    public function isFullday(): bool
    {
        return false;
    }
}
