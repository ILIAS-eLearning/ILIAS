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

include_once('./Services/Calendar/classes/class.ilCalendarUserSettings.php');
include_once('./Services/Calendar/classes/class.ilDateTime.php');
include_once('./Services/Calendar/classes/class.ilCalendarRecurrenceCalculator.php');
include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
include_once('./Services/Calendar/classes/class.ilCalendarHidden.php');

/** 
* Represents a list of calendar appointments (including recurring events) for a specific user
* in a given time range.
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* 
* @ingroup ServicesCalendar
*/

class ilCalendarSchedule
{
	const TYPE_DAY = 1;
	const TYPE_WEEK = 2;
	const TYPE_MONTH = 3;
	const TYPE_INBOX = 4;
	const TYPE_PD_UPCOMING = 5;	
	
	protected $limit_events = -1;
	protected $schedule = array();
	protected $timezone;
	protected $weekstart;
	protected $type = 0;
	
	protected $subitems_enabled = false;
	
	protected $start = null;
	protected $end = null;
	protected $user = null;
	protected $user_settings = null;
	protected $db = null;
	protected $filters = array();
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param ilDate seed date
	 * @param int type of schedule (TYPE_DAY,TYPE_WEEK or TYPE_MONTH)
	 * @param int user_id
	 * 
	 */
	public function __construct(ilDate $seed,$a_type,$a_user_id = 0)
	{
	 	global $ilUser,$ilDB;
	 	
	 	$this->db = $ilDB;

		$this->type = $a_type;
		$this->initPeriod($seed);

	 	if(!$a_user_id || $a_user_id == $ilUser->getId())
	 	{
	 		$this->user = $ilUser;
	 	}
		else
		{
			$this->user = new ilObjUser($a_user_id);
		}
	 	$this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());
	 	$this->weekstart = $this->user_settings->getWeekStart();
	 	$this->timezone = $this->user->getTimeZone();
	 					
		
		// category / event filters
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		
		// portfolio does custom filter handling (booking group ids)		
		if(ilCalendarCategories::_getInstance()->getMode() != ilCalendarCategories::MODE_PORTFOLIO_CONSULTATION)
		{
			// consultation hour calendar views do not mind calendar category visibility
			if(ilCalendarCategories::_getInstance()->getMode() != ilCalendarCategories::MODE_CONSULTATION)
			{
				// this is the "default" filter which handles currently hidden categories for the user
				include_once('./Services/Calendar/classes/class.ilCalendarScheduleFilterHidden.php');
				$this->addFilter(new ilCalendarScheduleFilterHidden($this->user->getId()));		
			}
			else
			{
				// handle booking visibility (target object, booked out)
				include_once('./Services/Calendar/classes/class.ilCalendarScheduleFilterBookings.php');
				$this->addFilter(new ilCalendarScheduleFilterBookings($this->user->getId()));		
			}
			
			// exercise 
			include_once './Services/Calendar/classes/class.ilCalendarScheduleFilterExercise.php';
			$this->addFilter(new ilCalendarScheduleFilterExercise($this->user->getId()));
		}
		
	}
	
	/**
	 * Check if events are limited
	 * @return type
	 */
	protected function areEventsLimited()
	{
		return $this->limit_events != -1;
	}
	
	/**
	 * get current limit of events
	 * @return type
	 */
	public function getEventsLimit()
	{
		return $this->limit_events;
	}
	
	/**
	 * Set events limit
	 * @param type $a_limit
	 */
	public function setEventsLimit($a_limit)
	{
		$this->limit_events = $a_limit;
	}
	
	/**
	 * Enable subitem calendars (session calendars for courses)
	 * @param
	 * @return
	 */
	public function addSubitemCalendars($a_status)
	{
		$this->subitems_enabled = $a_status;
	}
	
	/**
	 * Are subitem calendars enabled 
	 * @return
	 */
	public function enabledSubitemCalendars()
	{
		return (bool) $this->subitems_enabled;
	}
	
	/**
	 * Add filter
	 * 
	 * @param ilCalendarScheduleFilter $a_filter
	 */
	public function addFilter(ilCalendarScheduleFilter $a_filter)
	{
		$this->filters[] = $a_filter;
	}
	
	/**
	 * get byday
	 *
	 * @access public
	 * @param ilDate start
	 * 
	 */
	public function getByDay(ilDate $a_start,$a_timezone)
	{
		$start = new ilDateTime($a_start->get(IL_CAL_DATETIME),IL_CAL_DATETIME,$this->timezone);
		$fstart = new ilDate($a_start->get(IL_CAL_UNIX),IL_CAL_UNIX);
		$fend = clone $fstart;
		
		$f_unix_start = $fstart->get(IL_CAL_UNIX);
		$fend->increment(ilDateTime::DAY,1);
		$f_unix_end = $fend->get(IL_CAL_UNIX);
		
		$unix_start = $start->get(IL_CAL_UNIX);
		$start->increment(ilDateTime::DAY,1);
		$unix_end = $start->get(IL_CAL_UNIX);
		
		$counter = 0;
		
		$tmp_date = new ilDateTime($unix_start,IL_CAL_UNIX,$this->timezone);
		$tmp_schedule = array();
	 	foreach($this->schedule as $schedule)
	 	{
	 		if($schedule['fullday'])
	 		{
		 		if(($f_unix_start == $schedule['dstart']) or
		 			$f_unix_start == $schedule['dend'] or
		 			($f_unix_start > $schedule['dstart'] and $f_unix_end <= $schedule['dend']))
	 			{
		 			$tmp_schedule[] = $schedule;
	 			}
	 		}
	 		elseif(($schedule['dstart'] == $unix_start) or
	 			(($schedule['dstart'] <= $unix_start) and ($schedule['dend'] > $unix_start)) or
	 			(($schedule['dstart'] >= $unix_start) and ($schedule['dstart'] < $unix_end)))
	 		{
	 			$tmp_schedule[] = $schedule;
	 		}
	 	}
	 	return $tmp_schedule;
	}

	
	/**
	 * calculate 
	 *
	 * @access protected
	 */
	public function calculate()
	{		
		$events = $this->getEvents();

		// we need category type for booking handling
		$ids = array();
		foreach($events as $event)
		{
			$ids[] = $event->getEntryId();
		}
		
		include_once('Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
		$cat_map = ilCalendarCategoryAssignments::_getAppointmentCalendars($ids);
		include_once('Services/Calendar/classes/class.ilCalendarCategory.php');
		$cat_types = array();
		foreach(array_unique($cat_map) as $cat_id)
		{
			$cat = new ilCalendarCategory($cat_id);
			$cat_types[$cat_id] = $cat->getType();
		}
		
		$counter = 0;
		foreach($events as $event)
		{
			// Calculdate recurring events
			include_once('Services/Calendar/classes/class.ilCalendarRecurrences.php');
			if($recs = ilCalendarRecurrences::_getRecurrences($event->getEntryId()))
			{
				$duration = $event->getEnd()->get(IL_CAL_UNIX) - $event->getStart()->get(IL_CAL_UNIX);
				foreach($recs as $rec)
				{
					$calc = new ilCalendarRecurrenceCalculator($event,$rec);
					foreach($calc->calculateDateList($this->start,$this->end)->get() as $rec_date)
					{
						if($this->type == self::TYPE_PD_UPCOMING &&
							$rec_date->get(IL_CAL_UNIX) < time())
						{
							continue;
						}						
						
						$this->schedule[$counter]['event'] = $event;
						$this->schedule[$counter]['dstart'] = $rec_date->get(IL_CAL_UNIX);
						$this->schedule[$counter]['dend'] = $this->schedule[$counter]['dstart'] + $duration;
						$this->schedule[$counter]['fullday'] = $event->isFullday();
						$this->schedule[$counter]['category_id'] = $cat_map[$event->getEntryId()];
						$this->schedule[$counter]['category_type'] = $cat_types[$cat_map[$event->getEntryId()]];
						
						switch($this->type)
						{
							case self::TYPE_DAY:
							case self::TYPE_WEEK:
								// store date info (used for calculation of overlapping events)
								$tmp_date = new ilDateTime($this->schedule[$counter]['dstart'],IL_CAL_UNIX,$this->timezone);
								$this->schedule[$counter]['start_info'] = $tmp_date->get(IL_CAL_FKT_GETDATE,'',$this->timezone);
								
								$tmp_date = new ilDateTime($this->schedule[$counter]['dend'],IL_CAL_UNIX,$this->timezone);
								$this->schedule[$counter]['end_info'] = $tmp_date->get(IL_CAL_FKT_GETDATE,'',$this->timezone);
								break;

							default:
								break;
						}
						$counter++;
						if($this->type != self::TYPE_PD_UPCOMING &&
							$this->areEventsLimited() && $counter >= $this->getEventsLimit())
						{
							break;
						}
						
					}
				}
			}
			else
			{
				$this->schedule[$counter]['event'] = $event;
				$this->schedule[$counter]['dstart'] = $event->getStart()->get(IL_CAL_UNIX);
				$this->schedule[$counter]['dend'] = $event->getEnd()->get(IL_CAL_UNIX);
				$this->schedule[$counter]['fullday'] = $event->isFullday();
				$this->schedule[$counter]['category_id'] = $cat_map[$event->getEntryId()];
				$this->schedule[$counter]['category_type'] = $cat_types[$cat_map[$event->getEntryId()]];
				
				if(!$event->isFullday())
				{
					switch($this->type)
					{
						case self::TYPE_DAY:
						case self::TYPE_WEEK:
							// store date info (used for calculation of overlapping events)
							$tmp_date = new ilDateTime($this->schedule[$counter]['dstart'],IL_CAL_UNIX,$this->timezone);
							$this->schedule[$counter]['start_info'] = $tmp_date->get(IL_CAL_FKT_GETDATE,'',$this->timezone);

							$tmp_date = new ilDateTime($this->schedule[$counter]['dend'],IL_CAL_UNIX,$this->timezone);
							$this->schedule[$counter]['end_info'] = $tmp_date->get(IL_CAL_FKT_GETDATE,'',$this->timezone);
							break;
	
						default:
							break;
					}
				}
				$counter++;
				if($this->type != self::TYPE_PD_UPCOMING &&
					$this->areEventsLimited() && $counter >= $this->getEventsLimit())
				{
					break;
				}
			}
		}		
		
		if($this->type == self::TYPE_PD_UPCOMING)
		{
			$this->schedule = ilUtil::sortArray($this->schedule, "dstart", "asc", true);
			if($this->areEventsLimited() && sizeof($this->schedule) >= $this->getEventsLimit())
			{
				$this->schedule = array_slice($this->schedule, 0, $this->getEventsLimit());
			}
		}
	}
	
	public function getScheduledEvents()
	{
		return (array) $this->schedule;
	}

	protected function filterCategories(array $a_cats)
	{
		if(!sizeof($a_cats))
		{
			return;
		}
		
		foreach($this->filters as $filter)
		{			
			if(sizeof($a_cats))
			{
				$a_cats = $filter->filterCategories($a_cats);
			}
		}
		
		return $a_cats;
	}
	
	protected function modifyEventByFilters(ilCalendarEntry $event)
	{
		foreach($this->filters as $filter)
		{
			$res = $filter->modifyEvent($event);
			if(!$res)
			{
				ilLoggerFactory::getLogger('crs')->debug('filtering failed for ' . get_class($filter));
				return FALSE;
			}
			$event = $res;
		}
		return $event;
	}
	
	protected function addCustomEvents(ilDate $start, ilDate $end, array $categories)
	{
		$new_events = array();
		foreach($this->filters as $filter)
		{
			$events_by_filter = $filter->addCustomEvents($start, $end, $categories);
			if($events_by_filter)
			{
				$new_events = array_merge($new_events, $events_by_filter);
			}
		}
		return $new_events;
	}

	/**
	 * get new/changed events
	 *
	 * @param bool $a_include_subitem_calendars E.g include session calendars of courses.
	 * @return object $events[] Array of changed events
	 * @access protected
	 * @return
	 */
	public function getChangedEvents($a_include_subitem_calendars = false)
	{
		global $ilDB;
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		$cats = ilCalendarCategories::_getInstance($this->user->getId())->getCategories($a_include_subitem_calendars);
		$cats = $this->filterCategories($cats);
				
		if(!count($cats))
		{
			return array();
		}
		
		$start = new ilDate(date('Y-m-d',time()),IL_CAL_DATE);
		$start->increment(IL_CAL_MONTH,-1);
		
		$query = "SELECT ce.cal_id cal_id FROM cal_entries ce  ".
			"JOIN cal_cat_assignments ca ON ca.cal_id = ce.cal_id ".
			"WHERE last_update > ".$ilDB->quote($start->get(IL_CAL_DATETIME),'timestamp')." ".
			"AND ".$ilDB->in('ca.cat_id',$cats,false,'integer').' '.
			"ORDER BY last_update";
		$res = $this->db->query($query);
		
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{						
			$event = new ilCalendarEntry($row->cal_id);			
			$valid_event = $this->modifyEventByFilters($event);
			if($valid_event)
			{
				$events[] = $valid_event;
			}
		}
		
		foreach($this->addCustomEvents($this->start, $this->end, $cats) as $event)
		{
			$events[] = $event;
		}
		
		return $events ? $events : array();
	}
	
	
	/**
	 * Read events (will be moved to another class, since only active and/or visible calendars are shown)
	 *
	 * @access protected
	 */
	public function getEvents()
	{
		global $ilDB;
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		$cats = ilCalendarCategories::_getInstance($this->user->getId())->getCategories($this->enabledSubitemCalendars());
		$cats = $this->filterCategories($cats);
		
		if(!count($cats))
		{
			return array();
		}

		// TODO: optimize
		$query = "SELECT ce.cal_id cal_id".
			" FROM cal_entries ce".
			" LEFT JOIN cal_recurrence_rules crr ON (ce.cal_id = crr.cal_id)".
			" JOIN cal_cat_assignments ca ON (ca.cal_id = ce.cal_id)";

		if($this->type != self::TYPE_INBOX)
		{
			$query .= " WHERE ((starta <= ".$this->db->quote($this->end->get(IL_CAL_DATETIME,'','UTC'),'timestamp').
				" AND enda >= ".$this->db->quote($this->start->get(IL_CAL_DATETIME,'','UTC'),'timestamp').")".
				" OR (starta <= ".$this->db->quote($this->end->get(IL_CAL_DATETIME,'','UTC'),'timestamp').
				" AND NOT rule_id IS NULL))";
		}
		else
	    {
			$date = new ilDateTime(mktime(0, 0, 0), IL_CAL_UNIX);
			$query .= " WHERE starta >= ".$this->db->quote($date->get(IL_CAL_DATETIME,'','UTC'),'timestamp');
		}

		$query .= " AND ".$ilDB->in('ca.cat_id',$cats,false,'integer').
			" ORDER BY starta";

		$res = $this->db->query($query);
				
		$events = array();
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$event = new ilCalendarEntry($row->cal_id);			
			$valid_event = $this->modifyEventByFilters($event);
			if($valid_event)
			{
				$events[] = $valid_event;
			}	
		}		
		
		foreach($this->addCustomEvents($this->start, $this->end, $cats) as $event)
		{
			$events[] = $event;
		}
			
		return $events;
	}
	
	/**
	 * init period of events
	 *
	 * @access protected
	 * @param ilDate seed
	 * @return
	 */
	protected function initPeriod(ilDate $seed)
	{
		switch($this->type)
		{
			case self::TYPE_DAY:
				$this->start = clone $seed;
				$this->end = clone $seed;
				$this->start->increment(IL_CAL_DAY,-2);
				$this->end->increment(IL_CAL_DAY,2);
				break;
			
			case self::TYPE_WEEK:
				$this->start = clone $seed;
				$start_info = $this->start->get(IL_CAL_FKT_GETDATE,'','UTC');
				$day_diff = $this->weekstart - $start_info['isoday'];
				if($day_diff == 7)
				{
					$day_diff = 0;
				}
				$this->start->increment(IL_CAL_DAY,$day_diff);
				$this->start->increment(IL_CAL_DAY,-1);
				$this->end = clone $this->start;
				$this->end->increment(IL_CAL_DAY,9);
				break;
			
			case self::TYPE_MONTH:
				$year_month = $seed->get(IL_CAL_FKT_DATE,'Y-m','UTC');
				list($year,$month) = explode('-',$year_month);
			
				$this->start = new ilDate($year_month.'-01',IL_CAL_DATE);
				$this->start->increment(IL_CAL_DAY,-6);
				
				$this->end = new ilDate($year_month.'-'.ilCalendarUtil::_getMaxDayOfMonth($year,$month),IL_CAL_DATE);
				$this->end->increment(IL_CAL_DAY,6);
				break;
			
			case self::TYPE_PD_UPCOMING:
			case self::TYPE_INBOX:
				$this->start = $seed;
				$this->end = clone $this->start;
				$this->end->increment(IL_CAL_MONTH,3);
				break;
		}
		
		return true;
	}
}
?>