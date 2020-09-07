<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Calendar schedule filter interface
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesCalendar
 */
interface ilCalendarScheduleFilter
{
    public function filterCategories(array $a_cats);
    
    public function modifyEvent(ilCalendarEntry $a_event);
    
    public function addCustomEvents(ilDate $start, ilDate $end, array $a_categories);
}
