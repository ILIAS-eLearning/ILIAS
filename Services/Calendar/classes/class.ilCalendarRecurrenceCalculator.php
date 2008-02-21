<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once('./Services/Calendar/classes/class.ilDateList.php');
include_once('./Services/Calendar/classes/class.ilTimeZone.php');

/** 
* Calculates an <code>ilDateList</code> for a given calendar entry and recurrence rule.
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesCalendar 
*/

class ilCalendarRecurrenceCalculator
{
	protected $timezone = null;
	
	protected $valid_dates = null;
	protected $period_start = null;
	protected $period_end = null;

	protected $event = null;
	protected $recurrence = null;

	/**
	 * 
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct(ilCalendarEntry $entry,ilCalendarRecurrence $rec)
	{
	 	$this->event = $entry;
	 	$this->recurrence = $rec;
	}
	
	/**
	 * calculate date list
	 *
	 * @access public
	 * @param object ilDateTime start of period
	 * @param object ilDateTime end of period
	 * @param int limit number of returned dates
	 * @return object ilDateList 
	 */
	public function calculateDateList(ilDateTime $a_start,ilDateTime $a_end,$a_limit = -1)
	{
	 	$this->valid_dates = $this->initDateList();
	 	$this->period_start = $a_start;
	 	$this->period_end = $a_end;
	 	
	 	// Calculate recurrences based on frequency (e.g. MONTHLY)
	 	// TODO: fix tz
	 	$start = $this->optimizeStartingTime();
	 	do
	 	{
		 	$freq_res = $this->initDateList();
		 	$freq_res->add($start);
	 		$freq_res = $this->applyBYMONTHRules($freq_res);
	 		$freq_res = $this->applyBYDAYRules($freq_res);

			$start = $this->incrementByFrequency($start);
			if(ilDateTime::_after($start,$this->period_end))
			{
				break;
			}
			echo $freq_res;
	 	}
	 	while(true);

	 	return $freq_res;
	}
	
	/**
	 * optimize starting time
	 *
	 * @access protected
	 */
	protected function optimizeStartingTime()
	{
	 	// starting time cannot be optimzed if RRULE UNTIL is given.
	 	// In that case we have to calculate all date until "UNTIL" is reached.
	 	if($this->recurrence->getFrequenceUntilCount() > 0)
	 	{
			return clone $this->event->getStart();
	 	}
	 	
	 	$optimized = clone $start = clone $this->event->getStart();
	 	while(ilDateTime::_before($start,$this->period_start))
	 	{
	 		$optimized = clone $start;
	 		$start = $this->incrementByFrequency($start);
	 	}
	 	return $optimized;
	}
	
	/**
	 * increment starting time by frequency
	 *
	 * @access protected
	 */
	protected function incrementByFrequency($start)
	{
		switch($this->recurrence->getFrequenceType())
		{
			case ilCalendarRecurrence::FREQ_YEARLY:
				$start->increment(ilDateTime::YEAR,$this->recurrence->getInterval());
				break;
			
			case ilCalendarRecurrence::FREQ_MONTHLY:
				$start->increment(ilDateTime::MONTH,$this->recurrence->getInterval());
				break;

			case ilCalendarRecurrence::FREQ_WEEKLY:
				$start->increment(ilDateTime::WEEK,$this->recurrence->getInterval());
				break;
			
			case ilCalendarRecurrence::FREQ_DAILY:
				$start->increment(ilDateTime::DAY,$this->recurrence->getInterval());
				break;
		}
		return $start;
	}
	
	/**
	 * Apply BYMONTH rules
	 *
	 * @access protected
	 */
	protected function applyBYMONTHRules(ilDateList $list)
	{
		// return unmodified, if no bymonth rules are available
		if(!$this->recurrence->getBYMONTHList())
		{
			return $list;
		}
		$month_list = $this->initDateList();
		foreach($list->get() as $date)
		{
			foreach($this->recurrence->getBYMONTHList() as $month)
			{
				$month_date = $this->createDate($date->get(ilDateTime::FORMAT_UNIX));
				$month_date->increment(ilDateTime::MONTH,-($date->get(ilDateTime::FORMAT_FKT_DATE,'n') - $month));
				$month_list->add($month_date);
			}
		}
		return $month_list;
	}
	
	/**
	 * Apply BYDAY rules
	 * 
	 * @access public
	 * @param
	 * @return
	 */
	public function applyBYDAYRules(ilDateList $list)
	{
		// return unmodified, if no byday rules are available
		if(!$this->recurrence->getBYDAYList())
		{
			return $list;
		}
		return $list;
	
	}
	
	/**
	 * init date list
	 *
	 * @access protected
	 */
	protected function initDateList()
	{
	 	return new ilDateList($this->event->isFullday() ? ilDateList::TYPE_DATE : ilDateList::TYPE_DATETIME);
	}
	
	/**
	 * create date
	 *
	 * @access protected
	 */
	protected function createDate($a_date)
	{
		if($this->event->isFullday())
		{
			return new ilDate($a_date,ilDateTime::FORMAT_UNIX);
		}
		else
		{
			return new ilDateTime($a_date,ilDateTime::FORMAT_UNIX,'UTC');
		}
	}
}


?>