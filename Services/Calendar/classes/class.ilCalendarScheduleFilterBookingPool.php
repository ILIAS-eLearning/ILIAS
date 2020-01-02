<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Calendar schedule filter for booking pool reservations
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesCalendar
 */
class ilCalendarScheduleFilterBookingPool implements ilCalendarScheduleFilter
{
    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var ilCalendarCategories
     */
    protected $cats;

    /**
     * ilCalendarScheduleFilterBookingPool constructor.
     * @param int $a_user_id
     */
    public function __construct(int $a_user_id)
    {
        $this->user_id = $a_user_id;
        $this->cats = ilCalendarCategories::_getInstance();
    }

    /**
     * @param array $a_cats
     * @return array
     */
    public function filterCategories(array $a_cats) : array
    {
        return $a_cats;
    }

    /**
     * @param ilCalendarEntry $a_event
     * @return ilCalendarEntry
     * @throws ilDateTimeException
     */
    public function modifyEvent(ilCalendarEntry $a_event) : ilCalendarEntry
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
     * @param ilDate $start
     * @param ilDate $end
     * @param array $a_categories
     */
    public function addCustomEvents(ilDate $start, ilDate $end, array $a_categories)
    {
        //TODO if necessary.
    }

    /**
     * @param $a_cat_id
     * @return null|ilCalendarCategory
     */
    protected function isBookingPoolCategory($a_cat_id)
    {
        $category = ilCalendarCategory::getInstanceByCategoryId($a_cat_id);

        $cat_type = (int) $category->getType();

        if ($cat_type === ilCalendarCategory::TYPE_BOOK) {
            return $category;
        }

        return null;
    }
}
