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
	
	public function isValidEvent(ilCalendarEntry $a_event);
}

?>