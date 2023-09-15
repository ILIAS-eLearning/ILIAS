<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Model for a calendar entry.
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilCalendarEntry implements ilDatePeriod
{
    public const TRANSLATION_NONE = 0;
    public const TRANSLATION_SYSTEM = 1;

    protected ilLogger $log;
    protected ilDBInterface $db;
    protected ilLanguage $lng;
    protected ilErrorHandling $error;

    protected int $entry_id = 0;
    protected ?ilDateTime $last_update = null;
    protected string $title = '';
    protected string $presentation_style = '';
    protected string $subtitle = '';
    protected string $description = '';
    protected string $location = '';
    protected string $further_informations = '';
    protected ?ilDateTime $start = null;
    protected bool $fullday = false;
    protected ?ilDateTime $end = null;
    protected bool $is_auto_generated = false;
    protected int $context_id = 0;
    protected string $context_info = '';
    protected int $translation_type = ilCalendarEntry::TRANSLATION_NONE;
    protected bool $is_milestone = false;
    protected int $completion = 0;
    protected bool $notification = false;
    protected array $responsible_users = [];

    public function __construct(int $a_id = 0)
    {
        global $DIC;

        $this->log = $DIC->logger()->cal();
        $this->lng = $DIC->language();
        $this->db = $DIC->database();
        $this->error = $DIC['ilErr'];
        $this->entry_id = $a_id;
        if ($this->entry_id > 0) {
            $this->read();
        }
    }

    /**
     * clone instance
     */
    public function __clone()
    {
        $this->entry_id = 0;
    }

    public static function _delete(int $a_entry_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        ilCalendarRecurrence::_delete($a_entry_id);

        $query = "DELETE FROM cal_entries " .
            "WHERE cal_id = " . $ilDB->quote($a_entry_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    public function setContextInfo(string $a_info): void
    {
        $this->context_info = $a_info;
    }

    public function getContextInfo(): string
    {
        return $this->context_info;
    }

    public function getEntryId(): int
    {
        return $this->entry_id;
    }

    public function getLastUpdate(): ilDateTime
    {
        return $this->last_update ?: new ilDateTime(time(), IL_CAL_UNIX);
    }

    public function setLastUpdate(ilDateTime $a_date): void
    {
        $this->last_update = $a_date;
    }

    public function getStart(): ?ilDateTime
    {
        return $this->start;
    }

    public function setStart(ilDateTime $a_start): void
    {
        $this->start = $a_start;
    }

    public function getEnd(): ?ilDateTime
    {
        return $this->end;
    }

    public function setEnd(ilDateTime $a_end): void
    {
        $this->end = $a_end;
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPresentationTitle(bool $a_shorten = true): string
    {
        if ($this->getTranslationType() == ilCalendarEntry::TRANSLATION_NONE) {
            $title = $this->getTitle();
        } elseif (strlen($this->getSubtitle())) {
            // parse dynamic title?
            if (preg_match("/#([a-z]+)#/", $this->getSubtitle(), $matches)) {
                $subtitle = $this->parseDynamicTitle($matches[1]);
            } else {
                $subtitle = $this->lng->txt($this->getSubtitle());
            }
            $title = $this->getTitle() .
                (strlen($subtitle)
                    ? ' (' . $subtitle . ')'
                    : '');
        } else {
            $title = $this->lng->txt($this->getTitle());
        }

        if ($a_shorten) {
            return ilStr::shortenTextExtended(ilStr::shortenWords($title, 20), 40, true);
        }
        return $title;
    }

    protected function parseDynamicTitle(string $a_type): string
    {
        $title = $style = "";
        switch ($a_type) {
            case "consultationhour":
                $entry = new ilBookingEntry($this->getContextId());
                if ($entry) {
                    if ($entry->isOwner()) {
                        $max = $entry->getNumberOfBookings();
                        $current = $entry->getCurrentNumberOfBookings($this->getEntryId());
                        if (!$current) {
                            $style = ';border-left-width: 5px; border-left-style: solid; border-left-color: green';
                            $title = $this->lng->txt('cal_book_free');
                        } elseif ($current >= $max) {
                            $style = ';border-left-width: 5px; border-left-style: solid; border-left-color: red';
                            $title = $this->lng->txt('cal_booked_out');
                        } else {
                            $style = ';border-left-width: 5px; border-left-style: solid; border-left-color: yellow';
                            $title = $current . '/' . $max;
                        }
                    } else {
                        $apps = ilConsultationHourAppointments::getAppointmentIds(
                            $entry->getObjId(),
                            $this->getContextId(),
                            $this->getStart()
                        );
                        $orig_event = $apps[0];
                        $max = $entry->getNumberOfBookings();
                        $current = $entry->getCurrentNumberOfBookings($this->getEntryId());
                        if ($entry->hasBooked($orig_event)) {
                            $title = $this->lng->txt('cal_date_booked');
                        } elseif ($current >= $max) {
                            $style = ';border-left-width: 5px; border-left-style: solid; border-left-color: red';
                            $title = $this->lng->txt('cal_booked_out');
                        } else {
                            $style = ';border-left-width: 5px; border-left-style: solid; border-left-color: green';
                            $title = $this->lng->txt('cal_book_free');
                        }
                    }
                }
                break;
        }
        if (strlen($style)) {
            $this->presentation_style = $style;
        }

        return $title;
    }

    public function getPresentationStyle(): string
    {
        return $this->presentation_style;
    }

    /**
     * set subtitle
     * Used for automatic generated appointments.
     * Will be appended to the title.
     */
    public function setSubtitle(string $a_subtitle): void
    {
        $this->subtitle = $a_subtitle;
    }

    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    public function setDescription(string $a_description): void
    {
        $this->description = $a_description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setLocation(string $a_location): void
    {
        $this->location = $a_location;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setFurtherInformations(string $a_informations): void
    {
        $this->further_informations = $a_informations;
    }

    public function getFurtherInformations(): string
    {
        return $this->further_informations;
    }

    /**
     * set fullday event
     * Fullday events do not change their time in different timezones.
     * It is possible to create fullday events with a duration of more than one day.
     */
    public function setFullday(bool $a_fullday): void
    {
        $this->fullday = $a_fullday;
    }

    public function isFullday(): bool
    {
        return $this->fullday;
    }

    public function isAutoGenerated(): bool
    {
        return $this->is_auto_generated;
    }

    public function setAutoGenerated(bool $a_status): void
    {
        $this->is_auto_generated = $a_status;
    }

    public function isMilestone(): bool
    {
        return $this->is_milestone;
    }

    public function setMilestone(bool $a_status): void
    {
        $this->is_milestone = $a_status;
    }

    public function setCompletion(int $a_completion): void
    {
        $this->completion = $a_completion;
    }

    public function getCompletion(): int
    {
        return $this->completion;
    }

    public function setContextId(int $a_context_id): void
    {
        $this->context_id = $a_context_id;
    }

    public function getContextId(): int
    {
        return $this->context_id;
    }

    public function setTranslationType(int $a_type): void
    {
        $this->translation_type = $a_type;
    }

    public function getTranslationType(): int
    {
        return $this->translation_type;
    }

    public function enableNotification(bool $a_status): void
    {
        $this->notification = $a_status;
    }

    public function isNotificationEnabled(): bool
    {
        return $this->notification;
    }

    public function update(): void
    {
        $now = new ilDateTime(time(), IL_CAL_UNIX);
        $utc_timestamp = $now->get(IL_CAL_DATETIME, '', ilTimeZone::UTC);
        $query = "UPDATE cal_entries " .
            "SET title = " . $this->db->quote($this->getTitle(), 'text') . ", " .
            "last_update = " . $this->db->quote($utc_timestamp, 'timestamp') . ", " .
            "subtitle = " . $this->db->quote($this->getSubtitle(), 'text') . ", " .
            "description = " . $this->db->quote($this->getDescription(), 'text') . ", " .
            "location = " . $this->db->quote($this->getLocation(), 'text') . ", " .
            "fullday = " . $this->db->quote($this->isFullday() ? 1 : 0, 'integer') . ", " .
            "starta = " . $this->db->quote($this->getStart()->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp') . ", " .
            "enda = " . $this->db->quote($this->getEnd()->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp') . ", " .
            "informations = " . $this->db->quote($this->getFurtherInformations(), 'text') . ", " .
            "auto_generated =  " . $this->db->quote($this->isAutoGenerated(), 'integer') . ", " .
            "translation_type = " . $this->db->quote($this->getTranslationType(), 'integer') . ", " .
            "context_id = " . $this->db->quote($this->getContextId(), 'integer') . ", " .
            'context_info = ' . $this->db->quote($this->getContextInfo(), 'text') . ', ' .
            "completion = " . $this->db->quote($this->getCompletion(), 'integer') . ", " .
            "is_milestone = " . $this->db->quote($this->isMilestone() ? 1 : 0, 'integer') . ", " .
            'notification = ' . $this->db->quote($this->isNotificationEnabled() ? 1 : 0, 'integer') . ' ' .
            "WHERE cal_id = " . $this->db->quote($this->getEntryId(), 'integer') . " ";
        $res = $this->db->manipulate($query);
    }

    public function save(): void
    {
        $next_id = $this->db->nextId('cal_entries');
        $now = new ilDateTime(time(), IL_CAL_UNIX);
        $utc_timestamp = $now->get(IL_CAL_DATETIME, '', ilTimeZone::UTC);

        $query = "INSERT INTO cal_entries (cal_id,title,last_update,subtitle,description,location,fullday,starta,enda, " .
            "informations,auto_generated,context_id,context_info,translation_type, completion, is_milestone, notification) " .
            "VALUES( " .
            $this->db->quote($next_id, 'integer') . ", " .
            $this->db->quote($this->getTitle(), 'text') . ", " .
            $this->db->quote($utc_timestamp, 'timestamp') . ", " .
            $this->db->quote($this->getSubtitle(), 'text') . ", " .
            $this->db->quote($this->getDescription(), 'text') . ", " .
            $this->db->quote($this->getLocation(), 'text') . ", " .
            $this->db->quote($this->isFullday() ? 1 : 0, 'integer') . ", " .
            $this->db->quote($this->getStart()->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp') . ", " .
            $this->db->quote($this->getEnd()->get(IL_CAL_DATETIME, '', 'UTC'), 'timestamp') . ", " .
            $this->db->quote($this->getFurtherInformations(), 'text') . ", " .
            $this->db->quote($this->isAutoGenerated(), 'integer') . ", " .
            $this->db->quote($this->getContextId(), 'integer') . ", " .
            $this->db->quote($this->getContextInfo(), 'text') . ', ' .
            $this->db->quote($this->getTranslationType(), 'integer') . ", " .
            $this->db->quote($this->getCompletion(), 'integer') . ", " .
            $this->db->quote($this->isMilestone() ? 1 : 0, 'integer') . ", " .
            $this->db->quote($this->isNotificationEnabled() ? 1 : 0, 'integer') . ' ' .
            ")";
        $res = $this->db->manipulate($query);

        $this->entry_id = $next_id;
    }

    public function delete(): void
    {
        ilCalendarRecurrence::_delete($this->getEntryId());

        $query = "DELETE FROM cal_entries " .
            "WHERE cal_id = " . $this->db->quote($this->getEntryId(), 'integer') . " ";
        $res = $this->db->manipulate($query);

        ilCalendarCategoryAssignments::_deleteByAppointmentId($this->getEntryId());
    }

    public function validate(): bool
    {
        $success = true;
        $this->error->setMessage('');
        if (!strlen($this->getTitle())) {
            $success = false;
            $this->error->appendMessage($this->lng->txt('err_missing_title'));
        }
        if (!$this->getStart() || !$this->getEnd()) {
            $success = false;
        } elseif (ilDateTime::_before($this->getEnd(), $this->getStart(), '')) {
            $success = false;
            $this->error->appendMessage($this->lng->txt('err_end_before_start'));
        }
        return $success;
    }

    protected function read(): void
    {
        $query = "SELECT * FROM cal_entries WHERE cal_id = " . $this->db->quote($this->getEntryId(), 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setLastUpdate(new ilDateTime((string) $row->last_update, IL_CAL_DATETIME, 'UTC'));
            $this->setTitle((string) $row->title);
            $this->setSubtitle((string) $row->subtitle);
            $this->setDescription((string) $row->description);
            $this->setLocation((string) $row->location);
            $this->setFurtherInformations((string) $row->informations);
            $this->setFullday((bool) $row->fullday);
            $this->setAutoGenerated((bool) $row->auto_generated);
            $this->setContextId((int) $row->context_id);
            $this->setContextInfo((string) $row->context_info);
            $this->setTranslationType((int) $row->translation_type);
            $this->setCompletion((int) $row->completion);
            $this->setMilestone((bool) $row->is_milestone);
            $this->enableNotification((bool) $row->notification);

            if ($this->isFullday()) {
                $this->start = new ilDate((string) $row->starta, IL_CAL_DATETIME);
                $this->end = new ilDate((string) $row->enda, IL_CAL_DATETIME);
            } else {
                $this->start = new ilDateTime((string) $row->starta, IL_CAL_DATETIME, 'UTC');
                $this->end = new ilDateTime((string) $row->enda, IL_CAL_DATETIME, 'UTC');
            }
        }
    }

    public function appointmentToMailString(ilLanguage $lng): string
    {
        $body = $lng->txt('cal_details');
        $body .= "\n\n";
        $body .= $lng->txt('title') . ': ' . $this->getTitle() . "\n";

        ilDatePresentation::setUseRelativeDates(false);
        $body .= $lng->txt('date') . ': ' . ilDatePresentation::formatPeriod($this->getStart(), $this->getEnd()) . "\n";
        ilDatePresentation::setUseRelativeDates(true);

        if (strlen($this->getLocation())) {
            $body .= $lng->txt('cal_where') . ': ' . $this->getLocation() . "\n";
        }

        if (strlen($this->getDescription())) {
            $body .= $lng->txt('description') . ': ' . $this->getDescription() . "\n";
        }
        return $body;
    }

    public function writeResponsibleUsers(array $a_users): void
    {
        $this->db->manipulateF(
            "DELETE FROM cal_entry_responsible WHERE cal_id = %s",
            array("integer"),
            array($this->getEntryId())
        );

        if (is_array($a_users)) {
            foreach ($a_users as $user_id) {
                $this->db->manipulateF(
                    "INSERT INTO cal_entry_responsible (cal_id, user_id) " .
                    " VALUES (%s,%s)",
                    array("integer", "integer"),
                    array($this->getEntryId(), $user_id)
                );
            }
        }

        $this->responsible_users = $a_users;
    }

    public function readResponsibleUsers(): array
    {
        $set = $this->db->queryF(
            "SELECT * FROM cal_entry_responsible WHERE cal_id = %s",
            array("integer"),
            array($this->getEntryId())
        );

        $return = array();
        while ($rec = $this->db->fetchAssoc($set)) {
            $n = ilObjUser::_lookupName((int) $rec["user_id"]);
            $return[] = array_merge(
                $n,
                array("login" => ilObjUser::_lookupLogin((int) $rec["user_id"]))
            );
        }
        return $return;
    }
}
