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
 * Calendar schedule filter for hidden categories
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilCalendarScheduleFilterHidden implements ilCalendarScheduleFilter
{
    protected int $user_id;
    protected ilCalendarVisibility $hidden_cat;

    public function __construct(int $a_user_id)
    {
        $this->user_id = $a_user_id;
        $this->hidden_cat = ilCalendarVisibility::_getInstanceByUserId(
            $this->user_id,
            ilCalendarCategories::_getInstance($this->user_id)->getSourceRefId()
        );
    }

    /**
     * @ineritDoc
     */
    public function filterCategories(array $a_cats): array
    {
        return $this->hidden_cat->filterHidden(
            $a_cats,
            ilCalendarCategories::_getInstance($this->user_id)->getCategoriesInfo()
        );
    }

    /**
     * @inheritDoc
     */
    public function modifyEvent(ilCalendarEntry $a_event): ?ilCalendarEntry
    {
        // the not is ok since isAppointmentVisible return false for visible appointments
        if (!$this->hidden_cat->isAppointmentVisible($a_event->getEntryId())) {
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
