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
include_once('./Services/Calendar/classes/class.ilCalendarUtil.php');

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
	protected $log = null;
	
	protected $valid_dates = null;
	protected $period_start = null;
	protected $period_end = null;

	protected $event = null;
	protected $recurrence = null;
	
	protected $frequence_context = 0;

	/**
	 * 
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct(ilCalendarEntry $entry,ilCalendarRecurrence $rec)
	{
	 	global $ilLog;
	 	
	 	$this->log = $ilLog;
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
	 	$time = microtime(true);
	 	$start = $this->optimizeStartingTime();
	 	echo "ZEIT: ADJUST: ".(microtime(true) - $time).'<br>';
	 	do
	 	{
		 	// initialize context for applied rules
		 	// E.g 
		 	// RRULE:FREQ=YEARLY;BYMONTH=1;BYWEEKNO=1,50	=> context for BYWERKNO is monthly because it filters to the weeks in JAN
		 	// RRULE:FREQ=YEARLY;BYWEEKNO=1,50				=> context for BYWERKNO is yearly because it adds all weeks.
		 	$this->frequence_context = $this->recurrence->getFrequenceType();
		 	
		 	$freq_res = $this->initDateList();
		 	$freq_res->add($start);
		 	
		 	// Fixed sequence of applying rules (RFC 2445 4.3.10)
			$freq_res = $this->applyBYMONTHRules($freq_res);
			#echo "BYMONTH: ".$freq_res;

			$freq_res = $this->applyBYWEEKNORules($freq_res);
			#echo "BYWEEKNO: ".$freq_res;

	 		$freq_res = $this->applyBYYEARDAYRules($freq_res);
			#echo "BYYEARDAY: ".$freq_res;


	 		$freq_res = $this->applyBYMONTHDAYRules($freq_res);
			#echo "BYMONTH: ".$freq_res;

	 		$freq_res = $this->applyBYDAYRules($freq_res);
			#echo "BYDAY: ".$freq_res;


	 		$freq_res = $this->applyBYSETPOSRules($freq_res);
			#echo "BYSETPOS: ".$freq_res;
			
			$this->valid_dates->merge($freq_res);
	 		
			$start = $this->incrementByFrequency($start);
			if(ilDateTime::_after($start,$this->period_end))
			{
				break;
			}

	 	}
	 	while(true);

		$this->valid_dates->sort();
	 	return $this->valid_dates;
	}
	
	/**
	 * optimize starting time
	 *
	 * @access protected
	 */
	protected function optimizeStartingTime()
	{
	 	// starting time cannot be optimzed if RRULE UNTIL is given.
	 	// In that case we have to calculate all dates until "UNTIL" is reached.
	 	if($this->recurrence->getFrequenceUntilCount() > 0)
	 	{
			// Switch the date to the original defined timzone for this recurrence 
			return $this->createDate($this->event->getStart()->get(ilDateTime::FORMAT_UNIX));
	 	}
	 	$optimized = clone $start = $this->createDate($this->event->getStart()->get(ilDateTime::FORMAT_UNIX));
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
	 * @return object ilDateList
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
				// YEARLY rules extend the seed to every month given in the BYMONTH rule
				// Rules < YEARLY must match the month of the seed
				if($this->recurrence->getFrequenceType() == ilCalendarRecurrence::FREQ_YEARLY)
				{
					$month_date = $this->createDate($date->get(ilDateTime::FORMAT_UNIX));
					$month_date->increment(ilDateTime::MONTH,-($date->get(ilDateTime::FORMAT_FKT_DATE,'n') - $month));
					$month_list->add($month_date);
				}
				elseif($date->get(ilDateTime::FORMAT_FKT_DATE,'n') == $month)
				{
					$month_list->add($date);
				}
			}
		}
		// decrease the frequence_context for YEARLY rules
		if($this->recurrence->getFrequenceType() == ilCalendarRecurrence::FREQ_YEARLY)
		{
			$this->frequence_context = ilCalendarRecurrence::FREQ_MONTHLY;
		}
		return $month_list;
	}
	
	/**
	 * Apply BYWEEKNO rules (1 to 53 and -1 to -53).
	 * This rule can only be applied to YEARLY rules (RFC 2445 4.3.10)
	 *
	 * @access protected
	 */
	protected function applyBYWEEKNORules(ilDateList $list)
	{
		if($this->recurrence->getFrequenceType() != ilCalendarRecurrence::FREQ_YEARLY)
		{
			return $list;
		}
		// return unmodified, if no byweekno rules are available
		if(!$this->recurrence->getBYWEEKNOList())
		{
			return $list;
		}
		$weeks_list = $this->initDateList();
		foreach($list->get() as $seed)
		{
			$weeks_in_year = date('W',mktime(0,0,0,12,28,$seed->get(ilDateTime::FORMAT_FKT_DATE,'Y')));
			$this->log->write(__METHOD__.': Year '.$seed->get(ilDateTime::FORMAT_FKT_DATE,'Y').' has '.$weeks_in_year.' weeks');
			foreach($this->recurrence->getBYWEEKNOList() as $week_no)
			{
				$week_no = $week_no < 0 ? ($weeks_in_year + $week_no + 1) : $week_no;
				
				switch($this->frequence_context)
				{
					case ilCalendarRecurrence::FREQ_MONTHLY:
						$this->log->write(__METHOD__.': Handling BYWEEKNO in MONTHLY context');
						// Check if week matches
						if($seed->get(ilDateTime::FORMAT_FKT_DATE,'W') == $week_no)
						{
							$weeks_list->add($seed);
						}
						break;
						
					case ilCalendarRecurrence::FREQ_YEARLY:
						$this->log->write(__METHOD__.': Handling BYWEEKNO in YEARLY context');
						$week_diff = $week_no - $seed->get(ilDateTime::FORMAT_FKT_DATE,'W');
						
						$new_week = $this->createDate($seed->get(ilDateTime::FORMAT_UNIX));
						$new_week->increment(ilDateTime::WEEK,$week_diff);
						$weeks_list->add($new_week);
						break;
						
				}				
			}
		}
		$this->frequence_context = ilCalendarRecurrence::FREQ_WEEKLY;
		
		return $weeks_list;	
	}
	
	/**
	 * Apply BYYEARDAY rules.
	 *
	 * @access protected
	 */
	protected function applyBYYEARDAYRules(ilDateList $list)
	{
		// return unmodified, if no byweekno rules are available
		if(!$this->recurrence->getBYYEARDAYList())
		{
			return $list;
		}
		$days_list = $this->initDateList();
		foreach($list->get() as $seed)
		{
			$num_days = date('z',mktime(0,0,0,12,31,$seed->get(ilDateTime::FORMAT_FKT_DATE,'Y')));
			$this->log->write(__METHOD__.': Year '.$seed->get(ilDateTime::FORMAT_FKT_DATE,'Y').' has '.$num_days.' days.');
			
			foreach($this->recurrence->getBYYEARDAYList() as $day_no)
			{
				$day_no = $day_no < 0 ? ($num_days + $day_no + 1) : $day_no;
				
				$day_diff = $day_no - $seed->get(ilDateTime::FORMAT_FKT_DATE,'z');
				$new_day = $this->createDate($seed->get(ilDateTime::FORMAT_UNIX));
				$new_day->increment(ilDateTime::DAY,$day_diff);

				switch($this->frequence_context)
				{
					case ilCalendarRecurrence::FREQ_DAILY:
						// Check if day matches
						if($seed->get(ilDateTime::FORMAT_FKT_DATE,'z') == $day_no)
						{
							$days_list->add($new_day);
						}
						break;
					case ilCalendarRecurrence::FREQ_WEEKLY:
						// Check if week matches
						if($seed->get(ilDateTime::FORMAT_FKT_DATE,'W') == $new_day->get(ilDateTime::FORMAT_FKT_DATE,'W'))
						{
							$days_list->add($new_day);
						}
						break;
					case ilCalendarRecurrence::FREQ_MONTHLY:
						// Check if month matches
						if($seed->get(ilDateTime::FORMAT_FKT_DATE,'n') == $new_day->get(ilDateTime::FORMAT_FKT_DATE,'n'))
						{
							$days_list->add($new_day);
						}
						break;
					case ilCalendarRecurrence::FREQ_YEARLY:
						// Simply add
						$day_list->add($new_day);
						break;
				}
			}
		}
		
		$this->frequence_context = ilCalendarRecurrence::FREQ_DAILY;
		return $days_list;
	}
	
	/**
	 * Apply BYMONTHDAY rules.
	 *
	 * @access protected
	 */
	protected function applyBYMONTHDAYRules(ilDateList $list)
	{
		// return unmodified, if no byweekno rules are available
		if(!$this->recurrence->getBYMONTHDAYList())
		{
			return $list;
		}
		$days_list = $this->initDateList();
		foreach($list->get() as $seed)
		{
			$num_days = ilCalendarUtil::_getMaxDayOfMonth(
				$seed->get(ilDateTime::FORMAT_FKT_DATE,'Y'),
				$seed->get(ilDateTime::FORMAT_FKT_DATE,'n'));
			$this->log->write(__METHOD__.': Month '.$seed->get(ilDateTime::FORMAT_FKT_DATE,'M').' has '.$num_days.' days.');
			
			foreach($this->recurrence->getBYMONTHDAYList() as $day_no)
			{
				$day_no = $day_no < 0 ? ($num_days + $day_no + 1) : $day_no;
				if($day_no < 1 or $day_no > $num_days)
				{
					$this->log->write(__METHOD__.': Ignoring BYMONTHDAY rule: '.$day_no.' for month '.
						$seed->get(ilDateTime::FORMAT_FKT_DATE,'M'));
					continue;
				}
				$day_diff = $day_no - $seed->get(ilDateTime::FORMAT_FKT_DATE,'j');
				$new_day = $this->createDate($seed->get(ilDateTime::FORMAT_UNIX));
				$new_day->increment(ilDateTime::DAY,$day_diff);
				
				switch($this->frequence_context)
				{
					case ilCalendarRecurrence::FREQ_DAILY:
						// Check if day matches
						if($seed->get(ilDateTime::FORMAT_FKT_DATE,'z') == $day_no)
						{
							$days_list->add($new_day);
						}
						break;
					case ilCalendarRecurrence::FREQ_WEEKLY:
						// Check if week matches
						if($seed->get(ilDateTime::FORMAT_FKT_DATE,'W') == $new_day->get(ilDateTime::FORMAT_FKT_DATE,'W'))
						{
							$days_list->add($new_day);
						}
						break;
					case ilCalendarRecurrence::FREQ_MONTHLY:
						// seed and new day are in the same month.
						$days_list->add($new_day);
						break;
					case ilCalendarRecurrence::FREQ_YEARLY:
						// TODO: the chosen monthday has to added to all months
						// Simply add
						for($i = 1;$i <= 12;$i++)
						{
							
						}
						$day_list->add($new_day);
						break;
				}
			}
		}
		$this->frequence_context = ilCalendarRecurrence::FREQ_DAILY;
		return $days_list;
	}
	
	
	/**
	 * Apply BYDAY rules
	 * 
	 * @access protected
	 * @param object ilDateList
	 * @return object ilDateList
	 */
	protected function applyBYDAYRules(ilDateList $list)
	{
		// return unmodified, if no byday rules are available
		if(!$this->recurrence->getBYDAYList())
		{
			return $list;
		}
		return $list;
	
	}
	
	
	/**
	 * Apply BYSETPOST rules
	 * 
	 * @access protected
	 * @param object ilDateList
	 * @return object ilDateList
	 */
	public function applyBYSETPOSRules(ilDateList $list)
	{
		// return unmodified, if no bysetpos rules are available
		if(!$this->recurrence->getBYSETPOSList())
		{
			return $list;
		}
		$pos_list = $this->initDateList();
		$list->sort();
		$candidates = $list->get();
		$candidates_count = count($candidates);
		foreach($this->recurrence->getBYSETPOSList() as $position)
		{
			if($position > 0 and $date = $list->getAtPosition($position))
			{
				$pos_list->add($date);
			}
			if($position < 0 and $date = $list->getAtPosition($candidates_count + $position + 1))
			{
				$pos_list->add($date);
			}
		}
		return $pos_list;
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
			// TODO: the timezone for this recurrence must be stored in the db
			return new ilDateTime($a_date,ilDateTime::FORMAT_UNIX,'UTC');
		}
	}
}


?>