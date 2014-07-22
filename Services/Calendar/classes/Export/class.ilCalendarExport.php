<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

include_once './Services/Calendar/classes/class.ilCalendarUserSettings.php';
include_once './Services/Calendar/classes/iCal/class.ilICalWriter.php';
include_once './Services/Calendar/classes/class.ilCalendarCategory.php';
include_once './Services/Calendar/classes/class.ilCalendarEntry.php';
include_once './Services/Calendar/classes/class.ilCalendarCategoryAssignments.php';

/**
 * @classDescription Export calendar(s) to ical format
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * 
 * @ingroup ServicesCalendar
 */
class ilCalendarExport
{
	const EXPORT_CALENDARS = 1;
	const EXPORT_APPOINTMENTS = 2;

	protected $export_type = self::EXPORT_CALENDARS;


	protected $calendars = array();
	protected $user_settings = NULL;
	protected $appointments = array();
	protected $writer = null;
	
	public function __construct($a_calendar_ids = array())
	{
		$this->calendars = $a_calendar_ids;
		$this->writer = new ilICalWriter();
		
		$this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($GLOBALS['ilUser']->getId());
	}
	
	/**
	 * Get user settings
	 * @return ilCalendarUserSettings
	 */
	public function getUserSettings()
	{
		return $this->user_settings;
	}
	

	public function setExportType($a_type)
	{
		$this->export_type = $a_type;
	}

	public function setAppointments($a_apps)
	{
		$this->appointments = $a_apps;
	}

	public function getAppointments()
	{
		return $this->appointments;
	}

	public function setCalendarIds($a_cal_ids)
	{
		$this->calendars = $a_cal_ids;
	}

	public function getCalendarIds()
	{
		return (array) $this->calendars;
	}

	public function getExportType()
	{
		return $this->export_type;
	}
	
	public function export()
	{
		$this->writer->addLine('BEGIN:VCALENDAR');
		$this->writer->addLine('VERSION:2.0');
		$this->writer->addLine('METHOD:PUBLISH');
		$this->writer->addLine('PRODID:-//ilias.de/NONSGML ILIAS Calendar V4.4//EN');
		
		$this->addTimezone();

		switch($this->getExportType())
		{
			case self::EXPORT_CALENDARS:
				$this->addCategories();
				break;

			case self::EXPORT_APPOINTMENTS:
				$this->addAppointments();
				break;
		}
		$this->writer->addLine('END:VCALENDAR');
	}
	
	protected function addTimezone()
	{
		if($this->getUserSettings()->getExportTimeZoneType() == ilCalendarUserSettings::CAL_EXPORT_TZ_UTC)
		{
			return;
		}
		
		$this->writer->addLine('X-WR-TIMEZONE:'.$GLOBALS['ilUser']->getTimeZone());
		
		include_once './Services/Calendar/classes/class.ilCalendarUtil.php';
		$tzid_file = ilCalendarUtil::getZoneInfoFile($GLOBALS['ilUser']->getTimeZone());
		if(!is_file($tzid_file))
		{
			$tzid_file = ilCalendarUtil::getZoneInfoFile('Europe/Berlin');
		}
		$reader = fopen($tzid_file,'r');
		while($line = fgets($reader))
		{
			$line = str_replace("\n", '', $line);
			$this->writer->addLine($line);
		}
	}
	
	protected function addCategories()
	{
		foreach($this->calendars as $category_id)
		{
			foreach(ilCalendarCategoryAssignments::_getAssignedAppointments(array($category_id)) as $app_id)
			{
				$this->addAppointment($app_id);
			}
		}
	}

	protected function addAppointments()
	{
		foreach($this->getAppointments() as $app)
		{
			$this->addAppointment($app);
		}
	}

	protected function addAppointment($a_app_id)
	{
		$app = new ilCalendarEntry($a_app_id);
		if($app->isMilestone())
		{
			$this->createVTODO($app);
		}
		else
		{
			$this->createVEVENT($app);
		}
	}
	
	protected function createVTODO($app)
	{
		// TODO
		return true;
	}
	
	protected function createVEVENT($app)
	{
		global $ilUser;

		$this->writer->addLine('BEGIN:VEVENT');
		// TODO only domain
		$this->writer->addLine('UID:'.ilICalWriter::escapeText(
			$app->getEntryId().'_'.CLIENT_ID.'@'.ILIAS_HTTP_PATH));
			
		$last_mod = $app->getLastUpdate()->get(IL_CAL_FKT_DATE,'Ymd\THis\Z',ilTimeZone::UTC);
		#$last_mod = $app->getLastUpdate()->get(IL_CAL_FKT_DATE,'Ymd\THis\Z',$ilUser->getTimeZone());
		$this->writer->addLine('LAST-MODIFIED:'.$last_mod);

		// begin-patch aptar
		include_once './Services/Calendar/classes/class.ilCalendarRecurrences.php';
		if($rec = ilCalendarRecurrences::_getFirstRecurrence($app->getEntryId()))
		{
			// Set starting time to first appointment that matches the recurrence rule
			include_once './Services/Calendar/classes/class.ilCalendarRecurrenceCalculator.php';
			$calc = new ilCalendarRecurrenceCalculator($app,$rec);

			$pStart = $app->getStart();
			$pEnd = clone $app->getStart();
			$pEnd->increment(IL_CAL_YEAR,5);
			$appDiff = $app->getEnd()->get(IL_CAL_UNIX) - $app->getStart()->get(IL_CAL_UNIX);
			$recs = $calc->calculateDateList($pStart, $pEnd);
			foreach($recs as $dt)
			{
				$startInit = $dt;
				$endInit = clone($dt);
				$endInit->setDate($startInit->get(IL_CAL_UNIX) + $appDiff,IL_CAL_UNIX);
				break;
			}

		}
		else
		{
			$startInit = $app->getStart();
			$endInit = $app->getEnd();
		}

		
		if($app->isFullday())
		{
			// According to RFC 5545 3.6.1 DTEND is not inklusive.
			// But ILIAS stores inklusive dates in the database.
			#$app->getEnd()->increment(IL_CAL_DAY,1);
			$endInit->increment(IL_CAL_DATE,1);

			#$start = $app->getStart()->get(IL_CAL_FKT_DATE,'Ymd\Z',ilTimeZone::UTC);
			#$start = $app->getStart()->get(IL_CAL_FKT_DATE,'Ymd',$ilUser->getTimeZone());
			$start = $startInit->get(IL_CAL_FKT_DATE,'Ymd',$ilUser->getTimeZone());
			#$end = $app->getEnd()->get(IL_CAL_FKT_DATE,'Ymd\Z',ilTimeZone::UTC);
			#$end = $app->getEnd()->get(IL_CAL_FKT_DATE,'Ymd',$ilUser->getTimeZone());
			$endInit->increment(IL_CAL_DAY,1);
			$end = $endInit->get(IL_CAL_FKT_DATE,'Ymd',$ilUser->getTimeZone());
			
			$this->writer->addLine('DTSTART;VALUE=DATE:' . $start);
			$this->writer->addLine('DTEND;VALUE=DATE:'.$end);
		}
		else
		{
			if($this->getUserSettings()->getExportTimeZoneType() == ilCalendarUserSettings::CAL_EXPORT_TZ_UTC)
			{
				$start = $app->getStart()->get(IL_CAL_FKT_DATE,'Ymd\THis\Z',ilTimeZone::UTC);
				$end = $app->getEnd()->get(IL_CAL_FKT_DATE,'Ymd\THis\Z',ilTimeZone::UTC);
				$this->writer->addLine('DTSTART:'. $start);
				$this->writer->addLine('DTEND:'.$end);
				
			}
			else
			{
				$start = $startInit->get(IL_CAL_FKT_DATE,'Ymd\THis',$ilUser->getTimeZone());
				$end = $endInit->get(IL_CAL_FKT_DATE,'Ymd\THis',$ilUser->getTimeZone());
				$this->writer->addLine('DTSTART;TZID='.$ilUser->getTimezone().':'. $start);
				$this->writer->addLine('DTEND;TZID='.$ilUser->getTimezone().':'.$end);
			}
		}
		// end-patch aptar

		$this->createRecurrences($app);
		
		$this->writer->addLine('SUMMARY:'.ilICalWriter::escapeText($app->getPresentationTitle(false)));
		if(strlen($app->getDescription()))
			$this->writer->addLine('DESCRIPTION:'.ilICalWriter::escapeText($app->getDescription()));
		if(strlen($app->getLocation()))
			$this->writer->addLine('LOCATION:'.ilICalWriter::escapeText($app->getLocation()));

		// TODO: URL
		$this->buildAppointmentUrl($app);

		$this->writer->addLine('END:VEVENT');
		
	}
	
	protected function createRecurrences($app)
	{
		global $ilUser;

		include_once './Services/Calendar/classes/class.ilCalendarRecurrences.php';
		foreach(ilCalendarRecurrences::_getRecurrences($app->getEntryId()) as $rec)
		{
			foreach(ilCalendarRecurrenceExclusions::getExclusionDates($app->getEntryId()) as $excl)
			{
				$this->writer->addLine($excl->toICal());
			}
			$this->writer->addLine($rec->toICal($ilUser->getId()));
		}
	}
	
	
	public function getExportString()
	{
		return $this->writer->__toString();
	}

	/**
	 * Build url from calendar entry
	 * @param ilCalendarEntry $entry
	 * @return string
	 */
	protected function buildAppointmentUrl(ilCalendarEntry $entry)
	{
		$cat = ilCalendarCategory::getInstanceByCategoryId(
			current((array) ilCalendarCategoryAssignments::_lookupCategories($entry->getEntryId()))
		);

		if($cat->getType() != ilCalendarCategory::TYPE_OBJ)
		{
			$this->writer->addLine('URL;VALUE=URI:'.ILIAS_HTTP_PATH);
		}
		else
		{
			$refs = ilObject::_getAllReferences($cat->getObjId());

			include_once './Services/Link/classes/class.ilLink.php';
			$this->writer->addLine(
				'URL;VALUE=URI:'.ilLink::_getLink(current((array) $refs))
			);
		}
	}
}
