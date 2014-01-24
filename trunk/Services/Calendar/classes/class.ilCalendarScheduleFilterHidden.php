<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Calendar/interfaces/interface.ilCalendarScheduleFilter.php';
include_once 'Services/Calendar/classes/class.ilCalendarHidden.php';

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
	protected $hidden_cat; // [ilCalendarHidden]
	
	public function __construct($a_user_id)
	{
		$this->user_id = $a_user_id;
		$this->hidden_cat = ilCalendarHidden::_getInstanceByUserId($this->user_id);
	}
	
	public function filterCategories(array $a_cats)
	{			
		return $this->hidden_cat->filterHidden($a_cats,
			ilCalendarCategories::_getInstance($this->user_id)->getCategoriesInfo());
	}
	
	public function isValidEvent(ilCalendarEntry $a_event)
	{		
		return (!$this->hidden_cat->isAppointmentVisible($a_event->getEntryId()));				
	}
}

?>