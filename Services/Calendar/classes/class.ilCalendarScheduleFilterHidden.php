<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Calendar/interfaces/interface.ilCalendarScheduleFilter.php';
include_once 'Services/Calendar/classes/class.ilCalendarVisibility.php';

/**
 * Calendar schedule filter for hidden categories
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesCalendar
 */
class ilCalendarScheduleFilterHidden implements ilCalendarScheduleFilter
{
    protected $user_id; // [int]
    protected $hidden_cat; // [ilCalendarVisibility]
    
    public function __construct($a_user_id)
    {
        $this->user_id = $a_user_id;
        $this->hidden_cat = ilCalendarVisibility::_getInstanceByUserId(
            $this->user_id,
            ilCalendarCategories::_getInstance($this->user_id)->getSourceRefId()
        );
    }
    
    public function filterCategories(array $a_cats)
    {
        return $this->hidden_cat->filterHidden(
            $a_cats,
            ilCalendarCategories::_getInstance($this->user_id)->getCategoriesInfo()
        );
    }
    
    public function modifyEvent(ilCalendarEntry $a_event)
    {
        // the not is ok since isAppointmentVisible return false for visible appointments
        if (!$this->hidden_cat->isAppointmentVisible($a_event->getEntryId())) {
            return $a_event;
        }
        return false;
    }

    public function addCustomEvents(ilDate $start, ilDate $end, array $a_categories)
    {
    }
}
