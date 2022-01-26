<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Calendar schedule filter for booking pool reservations
 * @author  Jesús López <lopez@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilCalendarScheduleFilterBookingPool implements ilCalendarScheduleFilter
{
    protected int $user_id;
    protected ilCalendarCategories $cats;

    public function __construct(int $a_user_id)
    {
        $this->user_id = $a_user_id;
        $this->cats = ilCalendarCategories::_getInstance();
    }

    /**
     * @inheritDoc
     */
    public function filterCategories(array $a_cats) : array
    {
        return $a_cats;
    }

    /**
     * @inheritDoc
     */
    public function modifyEvent(ilCalendarEntry $a_event) : ?ilCalendarEntry
    {
        $category = $this->isBookingPoolCategory(ilCalendarCategoryAssignments::_lookupCategory($a_event->getEntryId()));

        if ($category) {
            /**
             * booking pool reservations are stored in the database with ending time excluded. Keeping it 1 second
             * under the entered end date.
             * e.g. Event from 10:00 to 11:00 is stored 10:00 to 10:59 using timestamps.
             * incrementing 1 second avoid titles/zip folders using this -1 second representation.
             */
            $end = $a_event->getEnd()->get(IL_CAL_UNIX);
            $new_end = new ilDateTime($end + 1, IL_CAL_UNIX);

            $a_event->setEnd($new_end);
        }

        return $a_event;
    }

    /**
     * @inheritDoc
     */
    public function addCustomEvents(ilDate $start, ilDate $end, array $a_categories) : array
    {
        return [];
    }

    /**
     * @param $a_cat_id
     * @return null|ilCalendarCategory
     */
    protected function isBookingPoolCategory(int $a_cat_id) : ?ilCalendarCategory
    {
        $category = ilCalendarCategory::getInstanceByCategoryId($a_cat_id);

        $cat_type = $category->getType();

        if ($cat_type === ilCalendarCategory::TYPE_BOOK) {
            return $category;
        }
        return null;
    }
}
