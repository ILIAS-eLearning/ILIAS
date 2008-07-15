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
	
	protected $schedule = array();
	protected $timezone;
	protected $weekstart;
	protected $hidden_cat = null;
	protected $type = 0;
	
	protected $start = null;
	protected $end = null;
	protected $user = null;
	protected $user_settings = null;
	protected $db = null;
	
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
	 	
	 	if(!$a_user_id)
	 	{
	 		$this->user = $ilUser;
	 	}
	 	
	 	$this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());
	 	$this->weekstart = $this->user_settings->getWeekStart();
	 	$this->timezone = $ilUser->getTimeZone();
	 	
	 	$this->hidden_cat = ilCalendarHidden::_getInstanceByUserId($this->user->getId());
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
	 	return $tmp_schedule ? $tmp_schedule : array();
	}

	
	/**
	 * calculate 
	 *
	 * @access protected
	 */
	public function calculate()
	{
		$counter = 0;
		foreach($this->getEvents() as $event)
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
						$this->schedule[$counter]['event'] = $event;
						$this->schedule[$counter]['dstart'] = $rec_date->get(IL_CAL_UNIX);
						$this->schedule[$counter]['dend'] = $this->schedule[$counter]['dstart'] + $duration; 
						$this->schedule[$counter]['fullday'] = $event->isFullday();
						
						switch($this->type)
						{
							case self::TYPE_DAY:
							case self::TYPE_WEEK:
								// store date info (used for calculation of overlapping events)
								$tmp_date = new ilDateTime($this->schedule[$counter]['dstart'],IL_CAL_UNIX,$this->timezone);
								$this->schedule[$counter]['start_info'] = $tmp_date->get(IL_CAL_FKT_GETDATE);
								
								$tmp_date = new ilDateTime($this->schedule[$counter]['dend'],IL_CAL_UNIX,$this->timezone);
								$this->schedule[$counter]['end_info'] = $tmp_date->get(IL_CAL_FKT_GETDATE);
								break;

							default:
								break;
						}
						$counter++;
					}
				}
			}
			else
			{
				$this->schedule[$counter]['event'] = $event;
				$this->schedule[$counter]['dstart'] = $event->getStart()->get(IL_CAL_UNIX);
				$this->schedule[$counter]['dend'] = $event->getEnd()->get(IL_CAL_UNIX);
				$this->schedule[$counter]['fullday'] = $event->isFullday();
				
				if(!$event->isFullday())
				{
					switch($this->type)
					{
						case self::TYPE_DAY:
						case self::TYPE_WEEK:
							// store date info (used for calculation of overlapping events)
							$tmp_date = new ilDateTime($this->schedule[$counter]['dstart'],IL_CAL_UNIX,$this->timezone);
							$this->schedule[$counter]['start_info'] = $tmp_date->get(IL_CAL_FKT_GETDATE);
							
							$tmp_date = new ilDateTime($this->schedule[$counter]['dend'],IL_CAL_UNIX,$this->timezone);
							$this->schedule[$counter]['end_info'] = $tmp_date->get(IL_CAL_FKT_GETDATE);
							break;
	
						default:
							break;
					}
				}
				$counter++;
			}
		}
	}
	
	/**
	 * get new/changed events
	 *
	 * @access protected
	 * @return
	 */
	public function getChangedEvents()
	{
		global $ilUser;
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		$cats = ilCalendarCategories::_getInstance($ilUser->getId())->getCategories();
		
		if(!count($cats))
		{
			return array();
		}
		
		$start = new ilDate(date('Y-m-d',time()),IL_CAL_DATE);
		$start->increment(IL_CAL_MONTH,-1);
		
		
		$query = "SELECT ce.cal_id AS cal_id FROM cal_entries AS ce  ".
			"JOIN cal_category_assignments AS ca ON ca.cal_id = ce.cal_id ".
			"WHERE last_update > '".$start->get(IL_CAL_DATETIME)."' ".
			"AND ca.cat_id IN (".implode(',',ilUtil::quoteArray($cats)).') '.
			"ORDER BY last_update";
		$res = $this->db->query($query);
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$events[] = new ilCalendarEntry($row->cal_id);
		}

		return $events ? $events : array();
	}
	
	
	/**
	 * Read events (will be moved to another class, since only active and/or visible calendars are shown)
	 *
	 * @access protected
	 */
	protected function getEvents()
	{
		global $ilUser;
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		$cats = ilCalendarCategories::_getInstance($ilUser->getId())->getCategories();
		
		if(!count($cats))
		{
			return array();
		}

		// TODO: optimize
		$query = "SELECT ce.cal_id AS cal_id FROM cal_entries AS ce LEFT JOIN cal_recurrence_rules AS crr USING (cal_id) ".
			"JOIN cal_category_assignments AS ca ON ca.cal_id = ce.cal_id ".
			"WHERE ((start <= ".$this->db->quote($this->end->get(IL_CAL_DATETIME))." ".
			"AND end >= ".$this->db->quote($this->start->get(IL_CAL_DATETIME)).") ".
			"OR (start <= ".$this->db->quote($this->end->get(IL_CAL_DATETIME))." ".
			"AND NOT rule_id IS NULL)) ".
			"AND ca.cat_id IN (".implode(',',ilUtil::quoteArray($cats)).') '.
			"ORDER BY start";
		$res = $this->db->query($query);
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(!$this->hidden_cat->isAppointmentVisible($row->cal_id))
			{
				$events[] = new ilCalendarEntry($row->cal_id);
			}
		}
		/*
		foreach($events as $event)
		{
			var_dump("<pre>",$event->getTitle(),"</pre>");
		}
		*/
		return $events ? $events : array();
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
				$this->start->increment(IL_CAL_DAY,-1);
				$this->end->increment(IL_CAL_DAY,1);
				break;
			
			case self::TYPE_WEEK:
				$this->start = clone $seed;
				$start_info = $this->start->get(IL_CAL_FKT_GETDATE);
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
				$year_month = $seed->get(IL_CAL_FKT_DATE,'Y-m');
				list($year,$month) = explode('-',$year_month);
			
				$this->start = new ilDate($year_month.'-01',IL_CAL_DATE);
				$this->start->increment(IL_CAL_DAY,-6);
				
				$this->end = new ilDate($year_month.'-'.ilCalendarUtil::_getMaxDayOfMonth($year,$month),IL_CAL_DATE);
				$this->end->increment(IL_CAL_DAY,6);
				break;
		}
		
		return true;
	}
}

?>