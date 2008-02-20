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
	public function calculateDateList(ilDateTime $start,ilDateTime $end,$a_limit = -1)
	{
	 	$res = new ilDateList($this->event->isFullday() ? ilDateList::TYPE_DATE : ilDateList::TYPE_DATETIME);
	 	
	 	// optimize starting time if recurrence has no "count"
	 	if($this->recurrence->getFrequenceUntilCount() > 1)
	 	{
	 		$start = $this->adjustStartingTime($start);
	 	}
	}
	
	/**
	 * adjust starting time
	 *
	 * @access protected
	 */
	protected function adjustStartingTime($start)
	{
		$res_unix = $base_unix = $this->event->getStart()->get(ilDateTime::FORMAT_UNIX);
		while($base_unix < $start->get(ilDateTime::FORMAT_UNIX))
		{
			$res_unix = $base_unix;
			switch($this->recurrence->getFrequenceType())
			{
				case ilCalendarRecurrence::FREQ_YEARLY:
					$base_unix = ilDateTime::_increment($base_unix,ilDateTime::YEAR,$this->recurrence->getInterval());
					break;
				
				case ilCalendarRecurrence::FREQ_MONTHLY:
					$base_unix = ilDateTime::_increment($base_unix,ilDateTime::MONTH,$this->recurrence->getInterval());
					break;

				case ilCalendarRecurrence::FREQ_WEEKLY:
					$base_unix = ilDateTime::_increment($base_unix,ilDateTime::WEEK,$this->recurrence->getInterval());
					break;
				
				case ilCalendarRecurrence::FREQ_DAILY:
					$base_unix = ilDateTime::_increment($base_unix,ilDateTime::DAY,$this->recurrence->getInterval());
					break;
			}
		}
		return new ilDateTime($res_unix,ilDateTime::FORMAT_UNIX);
	}
}


?>