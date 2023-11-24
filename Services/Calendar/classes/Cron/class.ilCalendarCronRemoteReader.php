<?php

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

declare(strict_types=1);

class ilCalendarCronRemoteReader extends ilCronJob
{
    private const DEFAULT_SYNC_HOURS = 1;

    private ilLanguage $lng;
    private ilLogger $logger;

    private ?ilCalendarSettings $calendar_settings = null;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->logger = $DIC->logger()->cal();
        $this->calendar_settings = ilCalendarSettings::_getInstance();
    }

    public function getId(): string
    {
        return 'cal_remote_reader';
    }

    public function getTitle(): string
    {
        $this->lng->loadLanguageModule('dateplaner');
        return $this->lng->txt('cal_cronjob_remote_title');
    }

    public function getDescription(): string
    {
        $this->lng->loadLanguageModule('dateplaner');
        return $this->lng->txt('cal_cronjob_remote_description');
    }

    public function hasAutoActivation(): bool
    {
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function addToExternalSettingsForm(int $a_form_id, array &$a_fields, bool $a_is_active): void
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_CALENDAR:
                $a_fields['cal_webcal_sync'] = $a_is_active ?
                    $this->lng->txt('enabled') :
                    $this->lng->txt('disabled');
                break;
        }
    }

    public function hasCustomSettings(): bool
    {
        return true;
    }

    public function getDefaultScheduleType(): int
    {
        return self::SCHEDULE_TYPE_IN_HOURS;
    }

    public function getDefaultScheduleValue(): ?int
    {
        if ($this->calendar_settings === null) {
            return self::DEFAULT_SYNC_HOURS;
        }
        return $this->calendar_settings->getWebCalSyncHours();
    }

    public function run(): ilCronJobResult
    {
        $status = ilCronJobResult::STATUS_NO_ACTION;

        $counter = 0;
        foreach (ilCalendarCategories::lookupRemoteCalendars() as $remoteCalendar) {
            $status = ilCronJobResult::STATUS_CRASHED;

            $reader = new ilCalendarRemoteReader($remoteCalendar->getRemoteUrl());
            $reader->setUser($remoteCalendar->getRemoteUser());
            $reader->setPass($remoteCalendar->getRemotePass());
            try {
                $reader->read();
                $reader->import($remoteCalendar);
            } catch (Exception $e) {
                $this->logger->warning('Remote Calendar: ' . $remoteCalendar->getCategoryID());
                $this->logger->warning('Reading remote calendar failed with message: ' . $e->getMessage());
            }
            $remoteCalendar->setRemoteSyncLastExecution(new ilDateTime(time(), IL_CAL_UNIX));
            $remoteCalendar->update();
            $status = ilCronJobResult::STATUS_OK;
            ++$counter;
        }
        $result = new ilCronJobResult();
        $result->setStatus($status);
        return $result;
    }


}
