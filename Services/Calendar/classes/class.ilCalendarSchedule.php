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

include_once('Services/Calendar/classes/class.ilDateTime.php');
include_once('Services/Calendar/classes/class.ilCalendarRecurrenceCalculator.php');
include_once('Services/Calendar/classes/class.ilCalendarEntry.php');

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
	protected $schedule = array();
	protected $timezone;
	
	protected $start = null;
	protected $end = null;
	protected $user = null;
	protected $db = null;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * 
	 */
	public function __construct(ilDate $start,ilDate $end,$a_user_id = 0)
	{
	 	global $ilUser,$ilDB;
	 	
	 	$this->db = $ilDB;
	 	$this->start = clone $start;
	 	$this->end = clone $end;
	 	
	 	if(!$a_user_id)
	 	{
	 		$this->user = $ilUser;
	 	}
	 	
	 	$this->timezone = $ilUser->getUserTimeZone();
	}

	/**
	 * get byday
	 *
	 * @access public
	 * @param ilDate start
	 * 
	 */
	public function getByDay(ilDate $start)
	{
		$start = clone $start;
		
		$unix_start = $start->get(IL_CAL_UNIX);
		$start->increment(ilDateTime::DAY,1);
		$unix_end = $start->get(IL_CAL_UNIX);
		
		$counter = 0;
	 	foreach($this->schedule as $schedule)
	 	{
	 		if(($unix_start <= $schedule['dstart']) and ($unix_end > $schedule['dend']))
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
			$this->schedule[$counter]['event'] = $event;
			$this->schedule[$counter]['dstart'] = $event->getStart()->get(IL_CAL_UNIX);
			$this->schedule[$counter]['dend'] = $event->getEnd()->get(IL_CAL_UNIX);
			$counter++;
		}
	}
	
	/**
	 * Read events (will be moved to another class, since only active and/or visible calendars are shown)
	 *
	 * @access protected
	 */
	protected function getEvents()
	{
		$query = "SELECT cal_id FROM cal_entries ".
			"WHERE start <= ".$this->db->quote($this->end->get(IL_CAL_DATE))." ".
			"AND end >= ".$this->db->quote($this->start->get(IL_CAL_DATE))." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$events[] = new ilCalendarEntry($row->cal_id);
		}
		return $events ? $events : array();
	}
}

?>