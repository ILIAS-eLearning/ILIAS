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
    protected const BYTE_LIMIT = 1000000;
    const EXPORT_CALENDARS = 1;
    const EXPORT_APPOINTMENTS = 2;

    /**
     * @var int
     */
    protected $export_type = self::EXPORT_CALENDARS;
    /**
     * @var ilLogger
     */
    protected $logger = null;
    /**
     * @var ilObjUser
     */
    protected $il_user;
    /**
     * @var int[]
     */
    protected $calendars;
    /**
     * @var ilCalendarUserSettings
     */
    protected $user_settings;
    /**
     * @var array
     */
    protected $appointments;
    /**
     * @var ilICalWriter
     */
    protected $str_writer_export;
    /**
     * @var bool
     */
    protected $is_export_limited;

    /**
     * @param int[] $a_calendar_ids
     */
    public function __construct(array $a_calendar_ids = [], bool $is_export_limited = false)
    {
        global $DIC;
        $this->il_user = $DIC->user();
        $this->logger = $DIC->logger()->cal();
        $this->calendars = $a_calendar_ids;
        $this->is_export_limited = $is_export_limited;
        $this->appointments = [];
        $this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->il_user->getId());
        $this->str_writer_export = new ilICalWriter();
    }

    public function getUserSettings() : ilCalendarUserSettings
    {
        return $this->user_settings;
    }

    public function setAppointments($a_apps) : void
    {
        $this->appointments = $a_apps;
    }

    public function getAppointments() : array
    {
        return $this->appointments;
    }

    /**
     * @param int[] $a_cal_ids
     */
    public function setCalendarIds(array $a_cal_ids) : void
    {
        $this->calendars = $a_cal_ids;
    }

    /**
     * @return int[]
     */
    public function getCalendarIds() : array
    {
        return $this->calendars;
    }

    public function setExportType(int $a_type) : void
    {
        $this->export_type = $a_type;
    }

    public function getExportType() : int
    {
        return $this->export_type;
    }
    
    public function export() : void
    {
        $this->str_writer_export->clear();
        $str_writer_prefix = new ilICalWriter();
        $str_writer_prefix->addLine('BEGIN:VCALENDAR');
        $str_writer_prefix->addLine('VERSION:2.0');
        $str_writer_prefix->addLine('METHOD:PUBLISH');
        $str_writer_prefix->addLine('PRODID:-//ilias.de/NONSGML ILIAS Calendar V4.4//EN');
        $str_writer_prefix->append($this->createTimezones());
        $str_writer_suffix = new ilICalWriter();
        $str_writer_suffix->addLine('END:VCALENDAR');
        $this->str_writer_export->append($str_writer_prefix);
        switch ($this->getExportType()) {
            case self::EXPORT_CALENDARS:
                $byte_sum = $str_writer_prefix->byteCount() + $str_writer_suffix->byteCount();
                $remaining_bytes = self::BYTE_LIMIT - $byte_sum;
                $str_builder_body = $this->addCategories($remaining_bytes);
                $this->str_writer_export->append($str_builder_body);
                break;

            case self::EXPORT_APPOINTMENTS:
                $str_builder_body = $this->addAppointments();
                $this->str_writer_export->append($str_builder_body);
                break;
        }
        $this->str_writer_export->append($str_writer_suffix);
    }
    
    protected function createTimezones() : ilICalWriter
    {
        $str_writer = new ilICalWriter();
        if ($this->getUserSettings()->getExportTimeZoneType() == ilCalendarUserSettings::CAL_EXPORT_TZ_UTC) {
            return $str_writer;
        }
        
        $str_writer->addLine('X-WR-TIMEZONE:' . $this->il_user->getTimeZone());
        
        include_once './Services/Calendar/classes/class.ilCalendarUtil.php';
        $tzid_file = ilCalendarUtil::getZoneInfoFile($this->il_user->getTimeZone());
        if (!is_file($tzid_file)) {
            $tzid_file = ilCalendarUtil::getZoneInfoFile('Europe/Berlin');
        }
        $reader = fopen($tzid_file, 'r');
        while ($line = fgets($reader)) {
            $line = str_replace("\n", '', $line);
            $str_writer->addLine($line);
        }
        return $str_writer;
    }
    
    protected function addCategories(int $remaining_bytes) : ilICalWriter
    {
        $single_appointments = [];
        $str_writer_appointments = new ilICalWriter();

        foreach ($this->calendars as $category_id) {
            foreach (ilCalendarCategoryAssignments::_getAssignedAppointments(array($category_id)) as $app_id) {
                $appointment = new ilCalendarEntry($app_id);
                if ($this->isRepeatingAppointment($appointment)) {
                    $str_writer_appointment = $this->createAppointment($appointment);
                    $str_writer_appointments->append($str_writer_appointment);
                    continue;
                }
                $single_appointments[] = $appointment;
            }
        }

        usort($single_appointments, function (ilCalendarEntry $a, ilCalendarEntry $b) {
            return $a->getStart() > $b->getStart();
        });

        // Apply a filter on limited exports only
        if ($this->is_export_limited) {
            $single_appointments = array_filter($single_appointments, function (ilCalendarEntry $a) {
                $time_now = new ilDateTime(time(), IL_CAL_UNIX);
                $str_time_now = $time_now->get(IL_CAL_FKT_DATE, 'Ymd', ilTimeZone::UTC);
                $str_time_start = $a->getStart()->get(IL_CAL_FKT_DATE, 'Ymd', $this->il_user->getTimeZone());
                $start = new DateTimeImmutable($str_time_start);
                $now = new DateTimeImmutable($str_time_now);
                $lower_bound = $now->sub(new DateInterval('P30D'));
                return $lower_bound <= $start;
            });
        }

        foreach ($single_appointments as $appointment) {
            $str_writer_appointment = $this->createAppointment($appointment);
            // Check byte count for limited exports only
            if (
                $this->is_export_limited &&
                ($str_writer_appointments->byteCount() + $str_writer_appointment->byteCount()) > $remaining_bytes
            ) {
                break;
            }
            $str_writer_appointments->append($str_writer_appointment);
        }

        return $str_writer_appointments;
    }

    protected function isRepeatingAppointment(ilCalendarEntry $appointment) : bool
    {
        return count(ilCalendarRecurrences::_getRecurrences($appointment->getEntryId())) > 0;
    }

    protected function addAppointments() : ilICalWriter
    {
        $str_builder_appointments = new ilICalWriter();
        foreach ($this->getAppointments() as $app) {
            $str_writer_appointment = $this->createAppointment($app);
            $str_builder_appointments->append($str_writer_appointment);
        }
        return $str_builder_appointments;
    }

    protected function createAppointment(ilCalendarEntry $appointment) : ilICalWriter
    {
        if ($appointment->isMilestone()) {
            return $this->createVTODO($appointment);
        } else {
            return $this->createVEVENT($appointment);
        }
    }

    protected function createVTODO(ilCalendarEntry $app) : ilICalWriter
    {
        return new ilICalWriter();
    }

    protected function createVEVENT(ilCalendarEntry $app) : ilICalWriter
    {
        $str_writer = new ilICalWriter();
        if (!$app->getStart() instanceof ilDateTime) {
            $this->logger->notice('Cannot create appointment for app_id: ' . $app->getEntryId());
            return $str_writer;
        }
        $test_date = $app->getStart()->get(IL_CAL_FKT_DATE, 'Ymd');
        if (!strlen((string) $test_date)) {
            return $str_writer;
        }
        $now = new ilDateTime(time(), IL_CAL_UNIX);

        $str_writer->addLine('BEGIN:VEVENT');
        $str_writer->addLine('DTSTAMP:'
            . $now->get(IL_CAL_FKT_DATE, 'Ymd\THis\Z', ilTimeZone::UTC));
        $str_writer->addLine('UID:' . ilICalWriter::escapeText(
            $app->getEntryId() . '_' . CLIENT_ID . '@' . ILIAS_HTTP_PATH
        ));
            
        $last_mod = $app->getLastUpdate()->get(IL_CAL_FKT_DATE, 'Ymd\THis\Z', ilTimeZone::UTC);
        $str_writer->addLine('LAST-MODIFIED:' . $last_mod);

        $startInit = $app->getStart();
        $endInit = $app->getEnd();

        // begin-patch aptar
        if ($app->isFullday()) {
            // According to RFC 5545 3.6.1 DTEND is not inclusive.
            // But ILIAS stores inclusive dates in the database.
            $endInit->increment(IL_CAL_DAY, 1);
            $start = $startInit->get(IL_CAL_FKT_DATE, 'Ymd', $this->il_user->getTimeZone());
            $end = $endInit->get(IL_CAL_FKT_DATE, 'Ymd', $this->il_user->getTimeZone());
            $str_writer->addLine('DTSTART;VALUE=DATE:' . $start);
            $str_writer->addLine('DTEND;VALUE=DATE:' . $end);
        } else {
            if ($this->getUserSettings()->getExportTimeZoneType() == ilCalendarUserSettings::CAL_EXPORT_TZ_UTC) {
                $start = $app->getStart()->get(IL_CAL_FKT_DATE, 'Ymd\THis\Z', ilTimeZone::UTC);
                $end = $app->getEnd()->get(IL_CAL_FKT_DATE, 'Ymd\THis\Z', ilTimeZone::UTC);
                $str_writer->addLine('DTSTART:' . $start);
                $str_writer->addLine('DTEND:' . $end);
            } else {
                $start = $startInit->get(IL_CAL_FKT_DATE, 'Ymd\THis', $this->il_user->getTimeZone());
                $end = $endInit->get(IL_CAL_FKT_DATE, 'Ymd\THis', $this->il_user->getTimeZone());
                $str_writer->addLine('DTSTART;TZID=' . $this->il_user->getTimezone() . ':' . $start);
                $str_writer->addLine('DTEND;TZID=' . $this->il_user->getTimezone() . ':' . $end);
            }
        }
        // end-patch aptar

        $str_writer->append($this->createRecurrences($app));
        $str_writer->addLine('SUMMARY:' . ilICalWriter::escapeText($app->getPresentationTitle(false)));
        if (strlen($app->getDescription())) {
            $str_writer->addLine('DESCRIPTION:' . ilICalWriter::escapeText($app->getDescription()));
        }
        if (strlen($app->getLocation())) {
            $str_writer->addLine('LOCATION:' . ilICalWriter::escapeText($app->getLocation()));
        }

        // TODO: URL
        $str_writer->append($this->buildAppointmentUrl($app));
        $str_writer->addLine('END:VEVENT');
        return $str_writer;
    }
    
    protected function createRecurrences(ilCalendarEntry $app) : ilICalWriter
    {
        $str_writer = new ilICalWriter();
        include_once './Services/Calendar/classes/class.ilCalendarRecurrences.php';
        foreach (ilCalendarRecurrences::_getRecurrences($app->getEntryId()) as $rec) {
            foreach (ilCalendarRecurrenceExclusions::getExclusionDates($app->getEntryId()) as $excl) {
                $str_writer->addLine($excl->toICal());
            }
            $recurrence_ical = $rec->toICal($this->il_user->getId());
            if (strlen($recurrence_ical)) {
                $str_writer->addLine($recurrence_ical);
            }
        }
        return $str_writer;
    }
    
    public function getExportString() : string
    {
        return $this->str_writer_export->__toString();
    }

    protected function buildAppointmentUrl(ilCalendarEntry $entry) : ilICalWriter
    {
        $str_writer = new ilICalWriter();
        $cat = ilCalendarCategory::getInstanceByCategoryId(
            current(ilCalendarCategoryAssignments::_lookupCategories($entry->getEntryId()))
        );
        if ($cat->getType() != ilCalendarCategory::TYPE_OBJ) {
            $str_writer->addLine('URL;VALUE=URI:' . ILIAS_HTTP_PATH);
        } else {
            $refs = ilObject::_getAllReferences($cat->getObjId());
            include_once './Services/Link/classes/class.ilLink.php';
            $str_writer->addLine('URL;VALUE=URI:' . ilLink::_getLink(current($refs)));
        }
        return $str_writer;
    }
}
