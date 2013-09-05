<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Calendar/interfaces/interface.ilCalendarScheduleFilter.php';
include_once 'Services/Calendar/classes/class.ilCalendarCategories.php';
include_once 'Services/Booking/classes/class.ilBookingEntry.php';

/**
 * Calendar schedule filter for consultation hour bookings
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesCalendar
 */
class ilCalendarScheduleFilterBookings implements ilCalendarScheduleFilter
{	
	protected $user_id; // [int]
	protected $group_ids; // [array]
	protected $cats; // [ilCalendarCategories]
	
	public function __construct($a_user_id, $a_consultation_hour_group_ids = null)
	{
		$this->user_id = $a_user_id;
		$this->group_ids = $a_consultation_hour_group_ids;	
		$this->cats = ilCalendarCategories::_getInstance();
	}
	
	public function filterCategories(array $a_cats)
	{
		return $a_cats;
	}
	
	public function isValidEvent(ilCalendarEntry $a_event)
	{
		global $ilUser;
		
		$booking = new ilBookingEntry($a_event->getContextId());
		
		// portfolio embedded: filter by consultation hour groups?
		if(!is_array($this->group_ids) ||
			in_array($booking->getBookingGroup(), $this->group_ids)) 
		{						
			// do not filter against course/group in portfolio
			if($this->cats->getMode() == ilCalendarCategories::MODE_PORTFOLIO_CONSULTATION)
			{
				$booking->setTargetObjIds(null);
			}
			
			if(($this->user_id == $ilUser->getId() ||
				!$booking->isBookedOut($a_event->getEntryId(), true)) &&
				$booking->isTargetObjectVisible($this->cats->getTargetRefId()))
			{				
				return true;
			}
		}
		
		return false;		
	}
}

?>