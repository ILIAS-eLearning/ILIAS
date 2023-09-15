<?php

declare(strict_types=1);
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

/**
 * @classDescription Export calendar(s) to ical format
 * @author           Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup          ServicesCalendar
 */
class ilCalendarExport
{
    public const EXPORT_CALENDARS = 1;
    public const EXPORT_APPOINTMENTS = 2;

    protected int $export_type = self::EXPORT_CALENDARS;

    private ilLogger $logger;
    protected ilObjUser $user;

    protected array $calendars = array();
    protected ?ilCalendarUserSettings $user_settings;
    protected array $appointments = array();
    protected ilICalWriter $writer;

    /**
     * ilCalendarExport constructor.
     * @param int[] $a_calendar_ids
     */
    public function __construct(array $a_calendar_ids = array())
    {
        global $DIC;

        $this->logger = $DIC->logger()->cal();
        $this->calendars = $a_calendar_ids;
        $this->writer = new ilICalWriter();
        $this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($DIC->user()->getId());
        $this->user = $DIC->user();
    }

    public function getUserSettings(): ilCalendarUserSettings
    {
        return $this->user_settings;
    }

    public function setExportType(int $a_type): void
    {
        $this->export_type = $a_type;
    }

    /**
     * @param int[] $a_apps
     */
    public function setAppointments(array $a_apps): void
    {
        $this->appointments = $a_apps;
    }

    /**
     * @return int[]
     */
    public function getAppointments(): array
    {
        return $this->appointments;
    }

    /**
     * @param int[] $a_cal_ids
     */
    public function setCalendarIds(array $a_cal_ids): void
    {
        $this->calendars = $a_cal_ids;
    }

    /**
     * @return int[]
     */
    public function getCalendarIds(): array
    {
        return $this->calendars;
    }

    public function getExportType(): int
    {
        return $this->export_type;
    }

    public function export(): void
    {
        $this->writer->addLine('BEGIN:VCALENDAR');
        $this->writer->addLine('VERSION:2.0');
        $this->writer->addLine('METHOD:PUBLISH');
        $this->writer->addLine('PRODID:-//ilias.de/NONSGML ILIAS Calendar V4.4//EN');

        $this->addTimezone();

        switch ($this->getExportType()) {
            case self::EXPORT_CALENDARS:
                $this->addCategories();
                break;

            case self::EXPORT_APPOINTMENTS:
                $this->addAppointments();
                break;
        }
        $this->writer->addLine('END:VCALENDAR');
    }

    protected function addTimezone(): void
    {
        if ($this->getUserSettings()->getExportTimeZoneType() == ilCalendarUserSettings::CAL_EXPORT_TZ_UTC) {
            return;
        }

        $this->writer->addLine('X-WR-TIMEZONE:' . $GLOBALS['DIC']['ilUser']->getTimeZone());

        $tzid_file = ilCalendarUtil::getZoneInfoFile($GLOBALS['DIC']['ilUser']->getTimeZone());
        if (!is_file($tzid_file)) {
            $tzid_file = ilCalendarUtil::getZoneInfoFile('Europe/Berlin');
        }
        $reader = fopen($tzid_file, 'r');
        while ($line = fgets($reader)) {
            $line = str_replace("\n", '', $line);
            $this->writer->addLine($line);
        }
    }

    protected function addCategories(): void
    {
        foreach ($this->calendars as $category_id) {
            foreach (ilCalendarCategoryAssignments::_getAssignedAppointments(array($category_id)) as $app_id) {
                $this->addAppointment($app_id);
            }
        }
    }

    protected function addAppointments(): void
    {
        foreach ($this->getAppointments() as $app) {
            $this->addAppointment($app);
        }
    }

    protected function addAppointment(int $a_app_id): void
    {
        $app = new ilCalendarEntry($a_app_id);
        if ($app->isMilestone()) {
            $this->createVTODO($app);
        } else {
            $this->createVEVENT($app);
        }
    }

    protected function createVTODO(ilCalendarEntry $app): void
    {
    }

    /**
     * Create VEVENT entry
     */
    protected function createVEVENT(ilCalendarEntry $app): void
    {
        if (!$app->getStart() instanceof ilDateTime) {
            $this->logger->notice('Cannot create appointment for app_id: ' . $app->getEntryId());
            return;
        }
        $test_date = $app->getStart()->get(IL_CAL_FKT_DATE, 'Ymd');
        if ($test_date === null || $test_date === '') {
            return;
        }

        $this->writer->addLine('BEGIN:VEVENT');

        $now = new ilDateTime(time(), IL_CAL_UNIX);
        $this->writer->addLine('DTSTAMP:' . $now->get(IL_CAL_FKT_DATE, 'Ymd\THis\Z', ilTimeZone::UTC));

        $this->writer->addLine('UID:' . ilICalWriter::escapeText(
            $app->getEntryId() . '_' . CLIENT_ID . '@' . ILIAS_HTTP_PATH
        ));

        $last_mod = $app->getLastUpdate()->get(IL_CAL_FKT_DATE, 'Ymd\THis\Z', ilTimeZone::UTC);
        $this->writer->addLine('LAST-MODIFIED:' . $last_mod);

        // begin-patch aptar
        if ($rec = ilCalendarRecurrences::_getFirstRecurrence($app->getEntryId())) {
            // Set starting time to first appointment that matches the recurrence rule
            $calc = new ilCalendarRecurrenceCalculator($app, $rec);

            $pStart = $app->getStart();
            $pEnd = clone $app->getStart();
            $pEnd->increment(IL_CAL_YEAR, 5);
            $appDiff = $app->getEnd()->get(IL_CAL_UNIX) - $app->getStart()->get(IL_CAL_UNIX);
            $recs = $calc->calculateDateList($pStart, $pEnd);

            // defaults
            $startInit = $app->getStart();
            $endInit = $app->getEnd();
            foreach ($recs as $dt) {
                $startInit = $dt;
                $endInit = clone($dt);
                $endInit->setDate($startInit->get(IL_CAL_UNIX) + $appDiff, IL_CAL_UNIX);
                break;
            }
        } else {
            $startInit = $app->getStart();
            $endInit = $app->getEnd();
        }

        if ($app->isFullday()) {
            // According to RFC 5545 3.6.1 DTEND is not inclusive.
            // But ILIAS stores inclusive dates in the database.
            $endInit->increment(IL_CAL_DAY, 1);

            $start = $startInit->get(IL_CAL_FKT_DATE, 'Ymd', $this->user->getTimeZone());
            $end = $endInit->get(IL_CAL_FKT_DATE, 'Ymd', $this->user->getTimeZone());

            $this->writer->addLine('DTSTART;VALUE=DATE:' . $start);
            $this->writer->addLine('DTEND;VALUE=DATE:' . $end);
        } else {
            if ($this->getUserSettings()->getExportTimeZoneType() == ilCalendarUserSettings::CAL_EXPORT_TZ_UTC) {
                $start = $app->getStart()->get(IL_CAL_FKT_DATE, 'Ymd\THis\Z', ilTimeZone::UTC);
                $end = $app->getEnd()->get(IL_CAL_FKT_DATE, 'Ymd\THis\Z', ilTimeZone::UTC);
                $this->writer->addLine('DTSTART:' . $start);
                $this->writer->addLine('DTEND:' . $end);
            } else {
                $start = $startInit->get(IL_CAL_FKT_DATE, 'Ymd\THis', $this->user->getTimeZone());
                $end = $endInit->get(IL_CAL_FKT_DATE, 'Ymd\THis', $this->user->getTimeZone());
                $this->writer->addLine('DTSTART;TZID=' . $this->user->getTimeZone() . ':' . $start);
                $this->writer->addLine('DTEND;TZID=' . $this->user->getTimeZone() . ':' . $end);
            }
        }
        // end-patch aptar

        $this->createRecurrences($app);

        $this->writer->addLine('SUMMARY:' . ilICalWriter::escapeText($app->getPresentationTitle(false)));
        if (strlen($app->getDescription())) {
            $this->writer->addLine('DESCRIPTION:' . ilICalWriter::escapeText($app->getDescription()));
        }
        if (strlen($app->getLocation())) {
            $this->writer->addLine('LOCATION:' . ilICalWriter::escapeText($app->getLocation()));
        }
        $this->buildAppointmentUrl($app);
        $this->writer->addLine('END:VEVENT');
    }

    protected function createRecurrences(ilCalendarEntry $app): void
    {
        foreach (ilCalendarRecurrences::_getRecurrences($app->getEntryId()) as $rec) {
            foreach (ilCalendarRecurrenceExclusions::getExclusionDates($app->getEntryId()) as $excl) {
                $this->writer->addLine($excl->toICal());
            }
            $recurrence_ical = $rec->toICal($this->user->getId());
            if (strlen($recurrence_ical)) {
                $this->writer->addLine($recurrence_ical);
            }
        }
    }

    public function getExportString(): string
    {
        return $this->writer->__toString();
    }

    /**
     * Build url from calendar entry
     */
    protected function buildAppointmentUrl(ilCalendarEntry $entry): void
    {
        $cat = ilCalendarCategory::getInstanceByCategoryId(
            current(ilCalendarCategoryAssignments::_lookupCategories($entry->getEntryId()))
        );

        if ($cat->getType() != ilCalendarCategory::TYPE_OBJ) {
            $this->writer->addLine('URL;VALUE=URI:' . ILIAS_HTTP_PATH);
        } else {
            $refs = ilObject::_getAllReferences($cat->getObjId());

            $this->writer->addLine(
                'URL;VALUE=URI:' . ilLink::_getLink(current((array) $refs))
            );
        }
    }
}
