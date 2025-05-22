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
    public function filterCategories(array $a_cats): array;

    /**
     * Modifies event properties. Return null to hide the event.
     * @param ilCalendarEntry $a_event
     * @return ilCalendarEntry|null
     */
    public function modifyEvent(ilCalendarEntry $a_event): ?ilCalendarEntry;

    /**
     * Add (return) an array of custom ilCalendarEntry's
     * @param ilDate $start
     * @param ilDate $end
     * @param array  $a_categories
     * @return ilCalendarEntry[]
     */
    public function addCustomEvents(ilDate $start, ilDate $end, array $a_categories): array;
}
