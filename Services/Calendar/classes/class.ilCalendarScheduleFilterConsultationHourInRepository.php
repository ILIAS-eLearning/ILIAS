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

class ilCalendarScheduleFilterConsultationHourInRepository implements ilCalendarScheduleFilter
{
    /**
     * @var ilCalendarCategories
     */
    protected $cats;
    
    public function __construct()
    {
        $this->cats = ilCalendarCategories::_getInstance();
    }
    
    public function filterCategories(array $a_cats) : array
    {
        return $a_cats;
    }
    
    public function modifyEvent(ilCalendarEntry $a_event) : ?ilCalendarEntry
    {
        /*
         * Do not filter if not in repository object context, or if
         * the entry is not from a consultation hour category.
         */
        if (
            $this->cats->getMode() !== ilCalendarCategories::MODE_REPOSITORY ||
            !$this->cats->getSourceRefId()
        ) {
            return $a_event;
        }
        foreach (ilCalendarCategoryAssignments::_lookupCategories($a_event->getEntryId()) as $category_id) {
            if (((int) $this->cats->getCategoryInfo($category_id)['type']) !== ilCalendarCategory::TYPE_CH) {
                return $a_event;
            }
        }

        $booking = new ilBookingEntry($a_event->getContextId());

        /*
         *  Only show consultation hour entries assigned to the current object,
         *  or those without assignment.
         */
        if ($booking->isTargetObjectVisible($this->cats->getSourceRefId())) {
            return $a_event;
        }
        return null;
    }
    
    public function addCustomEvents(ilDate $start, ilDate $end, array $a_categories) : array
    {
        return [];
    }
}
