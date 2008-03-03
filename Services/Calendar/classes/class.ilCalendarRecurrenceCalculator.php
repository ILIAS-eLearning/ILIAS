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
	
	protected $limit_reached = false;
	protected $valid_dates = null;
	protected $period_start = null;
	protected $period_end = null;
	protected $start = null;

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
	 	// Performance fix: Switching the timezone for many dates seems to be
	 	// quite time consuming.
	 	// Therfore we adjust the timezone of all input dates (start,end, event start)
	 	// to the same tz (UTC for fullday events, Recurrence tz for all others). 
	 	$this->adjustTimeZones($a_start,$a_end);
	 	$this->valid_dates = $this->initDateList();
	 	
	 	// Calculate recurrences based on frequency (e.g. MONTHLY)
	 	$time = microtime(true);
	 	
		if($this->recurrence->getInterval() <= 1)
		{
	 		// I think there is no difference in calculating a daily rule as monthly rule
	 		if($this->recurrence->getFrequenceType() == ilCalendarRecurrence::FREQ_DAILY)
	 		{
		 		$this->recurrence->setFrequenceType(ilCalendarRecurrence::FREQ_MONTHLY);
		 	}
		}


	 	$start = $this->optimizeStartingTime();
	 	
	 	#echo "ZEIT: ADJUST: ".(microtime(true) - $time).'<br>';
	 	$counter = 0;
	 	do
	 	{
		 	++$counter;
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
			
			$freq_res = $this->applyLimits($freq_res);

			$start = $this->incrementByFrequency($start);
			if(ilDateTime::_after($start,$this->period_end) or $this->limit_reached)
			{
				break;
			}

	 	}
	 	while(true);

		$this->valid_dates->sort();
		
		// Restore default timezone
		ilTimeZone::_restoreDefaultTimeZone();
		
	 	return $this->valid_dates;
	}
	
	/**
	 * Adjust timezone
	 *
	 * @access protected
	 */
	protected function adjustTimeZones(ilDateTime $a_start,ilDateTime $a_end)
	{
	 	$this->timezone = $this->event->isFullday() ? ilTimeZone::UTC : $this->recurrence->getTimeZone();
	 	ilTimeZone::_setDefaultTimeZone($this->timezone);
	 	
		$this->period_start = clone $a_start;
		$this->period_end = clone $a_end;
		$this->start = clone $this->event->getStart();
		
		try
		{
			if($this->event->isFullday())
			{
				$this->period_start->switchTimeZone(ilTimeZone::UTC);
				$this->period_end->switchTimeZone(ilTimeZone::UTC);
				$this->start->switchTimeZone(ilTimeZone::UTC);
			}
			else
			{
				echo $this->recurrence->getTimeZone();
				$this->period_start->switchTimeZone($this->recurrence->getTimeZone());
				$this->period_end->switchTimeZone($this->recurrence->getTimeZone());
				$this->start->switchTimeZone($this->recurrence->getTimeZone());
			}
			return true;
		}
		catch(ilDateTimeException $e)
		{
			$this->log->write(__METHOD__.': '.$e->getMessage());
			return false;
		}
	}
	
	/**
	 * optimize starting time
	 *
	 * @access protected
	 */
	protected function optimizeStartingTime()
	{
	 	$time = microtime(true);
	 	
	 	// starting time cannot be optimzed if RRULE UNTIL is given.
	 	// In that case we have to calculate all dates until "UNTIL" is reached.
	 	if($this->recurrence->getFrequenceUntilCount() > 0)
	 	{
			// Switch the date to the original defined timzone for this recurrence 
			return $this->createDate($this->start->get(IL_CAL_UNIX,$this->timezone));
	 	}
	 	$optimized = $start = $this->createDate($this->start->get(IL_CAL_UNIX,$this->timezone));
	 	while(ilDateTime::_before($start,$this->period_start))
	 	{
	 		$optimized = clone $start;
	 		$start = $this->incrementByFrequency($start);
	 	}
	 	
	 	echo "ADJUST: ".(microtime(true) - $time).'<br>';
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
					$month_date = $this->createDate($date->get(IL_CAL_UNIX,'',$this->timezone));
					$month_date->increment(ilDateTime::MONTH,-($date->get(IL_CAL_FKT_DATE,'n',$this->timezone) - $month));
					$month_list->add($month_date);
				}
				elseif($date->get(IL_CAL_FKT_DATE,'n',$this->timezone) == $month)
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
			$weeks_in_year = date('W',mktime(0,0,0,12,28,$seed->get(IL_CAL_FKT_DATE,'Y',$this->timezone)));
			$this->log->write(__METHOD__.': Year '.$seed->get(IL_CAL_FKT_DATE,'Y',$this->timezone).' has '.$weeks_in_year.' weeks');
			foreach($this->recurrence->getBYWEEKNOList() as $week_no)
			{
				$week_no = $week_no < 0 ? ($weeks_in_year + $week_no + 1) : $week_no;
				
				switch($this->frequence_context)
				{
					case ilCalendarRecurrence::FREQ_MONTHLY:
						$this->log->write(__METHOD__.': Handling BYWEEKNO in MONTHLY context');
						// Check if week matches
						if($seed->get(IL_CAL_FKT_DATE,'W',$this->timezone) == $week_no)
						{
							$weeks_list->add($seed);
						}
						break;
						
					case ilCalendarRecurrence::FREQ_YEARLY:
						
						$this->log->write(__METHOD__.': Handling BYWEEKNO in YEARLY context');
						$week_diff = $week_no - $seed->get(IL_CAL_FKT_DATE,'W',$this->timezone);
						
						// TODO: think about tz here
						$new_week = $this->createDate($seed->get(IL_CAL_UNIX,'',$this->timezone));
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
			$num_days = date('z',mktime(0,0,0,12,31,$seed->get(IL_CAL_FKT_DATE,'Y',$this->timezone)));
			$this->log->write(__METHOD__.': Year '.$seed->get(IL_CAL_FKT_DATE,'Y',$this->timezone).' has '.$num_days.' days.');
			
			foreach($this->recurrence->getBYYEARDAYList() as $day_no)
			{
				$day_no = $day_no < 0 ? ($num_days + $day_no + 1) : $day_no;
				
				$day_diff = $day_no - $seed->get(IL_CAL_FKT_DATE,'z',$this->timezone);
				$new_day = $this->createDate($seed->get(IL_CAL_UNIX,'',$this->timezone));
				$new_day->increment(ilDateTime::DAY,$day_diff);

				switch($this->frequence_context)
				{
					case ilCalendarRecurrence::FREQ_DAILY:
						// Check if day matches
						if($seed->get(IL_CAL_FKT_DATE,'z',$this->timezone) == $day_no)
						{
							$days_list->add($new_day);
						}
						break;
					case ilCalendarRecurrence::FREQ_WEEKLY:
						// Check if week matches
						if($seed->get(IL_CAL_FKT_DATE,'W',$this->timezone) == $new_day->get(IL_CAL_FKT_DATE,'W',$this->timezone))
						{
							$days_list->add($new_day);
						}
						break;
					case ilCalendarRecurrence::FREQ_MONTHLY:
						// Check if month matches
						if($seed->get(IL_CAL_FKT_DATE,'n',$this->timezone) == $new_day->get(IL_CAL_FKT_DATE,'n',$this->timezone))
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
				$seed->get(IL_CAL_FKT_DATE,'Y',$this->timezone),
				$seed->get(IL_CAL_FKT_DATE,'n',$this->timezone));
			/*
			$num_days = cal_days_in_month(CAL_GREGORIAN,
				$seed->get(IL_CAL_FKT_DATE,'n',$this->timezone),
				$seed->get(IL_CAL_FKT_DATE,'Y',$this->timezone));
			*/	
			#$this->log->write(__METHOD__.': Month '.$seed->get(IL_CAL_FKT_DATE,'M',$this->timezone).' has '.$num_days.' days.');
			
			foreach($this->recurrence->getBYMONTHDAYList() as $bymonth_no)
			{
				$day_no = $bymonth_no < 0 ? ($num_days + $bymonth_no + 1) : $bymonth_no;
				if($this->frequence_context != ilCalendarRecurrence::FREQ_YEARLY)
				{
					if($day_no < 1 or $day_no > $num_days)
					{
						$this->log->write(__METHOD__.': Ignoring BYMONTHDAY rule: '.$day_no.' for month '.
							$seed->get(IL_CAL_FKT_DATE,'M',$this->timezone));
						continue;
					}
				}
				$day_diff = $day_no - $seed->get(IL_CAL_FKT_DATE,'j',$this->timezone);
				$new_day = $this->createDate($seed->get(IL_CAL_UNIX,'',$this->timezone));
				$new_day->increment(ilDateTime::DAY,$day_diff);
				
				switch($this->frequence_context)
				{
					case ilCalendarRecurrence::FREQ_DAILY:
						// Check if day matches
						#var_dump("<pre>",$seed->get(IL_CAL_FKT_DATE,'z',$this->timezone),$day_no,"</pre>");
						if($seed->get(IL_CAL_FKT_DATE,'j',$this->timezone) == $day_no)
						{
							$days_list->add($new_day);
						}
						break;

					case ilCalendarRecurrence::FREQ_WEEKLY:
						// Check if week matches
						if($seed->get(IL_CAL_FKT_DATE,'W',$this->timezone) == $new_day->get(IL_CAL_FKT_DATE,'W',$this->timezone))
						{
							$days_list->add($new_day);
						}
						break;

					case ilCalendarRecurrence::FREQ_MONTHLY:
						// seed and new day are in the same month.
						$days_list->add($new_day);
						break;

					case ilCalendarRecurrence::FREQ_YEARLY:
						$h = $this->event->isFullday() ? 0 : $seed->get(IL_CAL_FKT_DATE,'H',$this->timezone);
						$i = $this->event->isFullday() ? 0 : $seed->get(IL_CAL_FKT_DATE,'i',$this->timezone);
						$s = $this->event->isFullday() ? 0 : $seed->get(IL_CAL_FKT_DATE,'s',$this->timezone);
						$y = $seed->get(IL_CAL_FKT_DATE,'Y',$this->timezone);

						// TODO: the chosen monthday has to added to all months
						for($month = 1;$month <= 12;$month++)
						{
							#$num_days = cal_days_in_month(CAL_GREGORIAN,
							#	$month,
							#	$y);
							$num_days = ilCalendarUtil::_getMaxDayOfMonth(
								$y,
								$month);
							$day_no = $bymonth_no < 0 ? ($num_days + $bymonth_no + 1) : $bymonth_no;
							if($day_no < 1 or $day_no > $num_days)
							{
								$this->log->write(__METHOD__.': Ignoring BYMONTHDAY rule: '.$day_no.' for month '.$month);
							}
							else
							{
								$tz_obj = ilTimeZone::_getInstance($this->timezone);
								$tz_obj->switchTZ();
								$unix = mktime($h,$i,$s,$month,$day_no,$y);
								$tz_obj->restoreTZ();
								$new_day = $this->createDate($unix);
								$days_list->add($new_day);
							}
						}
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
		// generate a list of e.g all Sundays for the given year
		if($this->frequence_context == ilCalendarRecurrence::FREQ_YEARLY)
		{
			$byday_list = $this->getYearWeekDays();	
		}
		return $list;
	
	}
	
	/**
	 * get a list of year week days according to the BYMONTH rule
	 *
	 * @access protected
	 */
	protected function getYearWeekDays()
	{
	
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
	 * Apply limits (count or until)
	 *
	 * @access public
	 * @param object ilDateList
	 * 
	 */
	public function applyLimits(ilDateList $list)
	{
	 	// Check count if given
	 	if($this->recurrence->getFrequenceUntilCount())
	 	{
	 		$list->sort();
	 		foreach($list->get() as $res)
	 		{
	 			if(count($this->valid_dates->get()) < $this->recurrence->getFrequenceUntilCount())
	 			{
	 				$this->valid_dates->add($res);
	 			}
	 			else
	 			{
	 				$this->valid_dates->add($res);
	 				$this->limit_reached = true;
	 				return false;
	 			}
	 		}
	 		return true;
	 	}
	 	elseif($this->recurrence->getFrequenceUntilDate())
	 	{
	 		$date = $this->recurrence->getFrequenceUntilDate();
	 		$list->sort();
	 		foreach($list->get() as $res)
	 		{
	 			if(ilDateTime::_after($res,$date))
	 			{
	 				$this->limit_reached = true;
	 				return false;
	 			}
	 			$this->valid_dates->add($res);
	 		}
	 		return true;
	 	}
	 	$this->valid_dates->merge($list);
	 	return true;
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
			echo "hier";
			return new ilDate($a_date,IL_CAL_UNIX);
		}
		else
		{
			// TODO: the timezone for this recurrence must be stored in the db
			return new ilDateTime($a_date,IL_CAL_UNIX,$this->timezone);
		}
	}
}


?>