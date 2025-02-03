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
 * Calendar schedule filter for consultation hour bookings
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilCalendarScheduleFilterBookings implements ilCalendarScheduleFilter
{
    protected int $user_id;
    protected ilCalendarCategories $cats;
    protected ilObjUser $user;

    public function __construct(int $a_user_id)
    {
        global $DIC;

        $this->user_id = $a_user_id;
        $this->cats = ilCalendarCategories::_getInstance();
        $this->user = $DIC->user();
    }

    /**
     * @inheritDoc
     */
    public function filterCategories(array $a_cats): array
    {
        return $a_cats;
    }

    /**
     * @inheritDoc
     */
    public function modifyEvent(ilCalendarEntry $a_event): ?ilCalendarEntry
    {
        $booking = new ilBookingEntry($a_event->getContextId());
        // do not show bookings of foreign users
        if ($booking->getObjId() != $this->user_id) {
            return null;
        }
        if ($this->cats->getMode() == ilCalendarCategories::MODE_PORTFOLIO_CONSULTATION) {
            $booking->setTargetObjIds(null);
        }

        if (($this->user_id == $this->user->getId() ||
                !$booking->isBookedOut($a_event->getEntryId(), true)) &&
            $booking->isTargetObjectVisible($this->cats->getTargetRefId())) {
            return $a_event;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function addCustomEvents(ilDate $start, ilDate $end, array $a_categories): array
    {
        return [];
    }
}
