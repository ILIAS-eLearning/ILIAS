<?php declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    public function filterCategories(array $a_cats) : array
    {
        return $this->hidden_cat->filterHidden(
            $a_cats,
            ilCalendarCategories::_getInstance($this->user_id)->getCategoriesInfo()
        );
    }

    /**
     * @inheritDoc
     */
    public function modifyEvent(ilCalendarEntry $a_event) : ?ilCalendarEntry
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
    public function addCustomEvents(ilDate $start, ilDate $end, array $a_categories) : array
    {
        return [];
    }
}
