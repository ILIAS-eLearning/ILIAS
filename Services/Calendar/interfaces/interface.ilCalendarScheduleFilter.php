<?php declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Calendar schedule filter interface
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ingroup ServicesCalendar
 */
interface ilCalendarScheduleFilter
{
    /**
     * @param array $a_cats
     * @return array
     */
    public function filterCategories(array $a_cats) : array;

    /**
     * Modifies event properties. Return null to hide the event.
     * @param ilCalendarEntry $a_event
     * @return ilCalendarEntry|null
     */
    public function modifyEvent(ilCalendarEntry $a_event) : ?ilCalendarEntry;

    /**
     * Add (return) an array of custom ilCalendarEntry's
     * @param ilDate $start
     * @param ilDate $end
     * @param array  $a_categories
     * @return ilCalendarEntry[]
     */
    public function addCustomEvents(ilDate $start, ilDate $end, array $a_categories) : array;
}
