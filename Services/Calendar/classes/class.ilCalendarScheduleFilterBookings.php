<?php

declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Calendar schedule filter for consultation hour bookings
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilCalendarScheduleFilterBookings implements ilCalendarScheduleFilter
{
    protected int $user_id;
    protected ?array $group_ids = [];
    protected ilCalendarCategories $cats;
    protected ilObjUser $user;

    public function __construct(int $a_user_id, ?array $a_consultation_hour_group_ids = null)
    {
        global $DIC;

        $this->user_id = $a_user_id;
        $this->group_ids = $a_consultation_hour_group_ids;
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
        // portfolio embedded: filter by consultation hour groups?
        if (!is_array($this->group_ids) ||
            in_array($booking->getBookingGroup(), $this->group_ids)) {
            // do not filter against course/group in portfolio
            if ($this->cats->getMode() == ilCalendarCategories::MODE_PORTFOLIO_CONSULTATION) {
                $booking->setTargetObjIds(null);
            }

            if (($this->user_id == $this->user->getId() ||
                    !$booking->isBookedOut($a_event->getEntryId(), true)) &&
                $booking->isTargetObjectVisible($this->cats->getTargetRefId())) {
                return $a_event;
            }
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
