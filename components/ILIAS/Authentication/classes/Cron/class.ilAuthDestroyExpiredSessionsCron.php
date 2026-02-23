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

use ILIAS\Cron\Job\JobResult;
use ILIAS\Cron\CronJob;

class ilAuthDestroyExpiredSessionsCron extends CronJob
{
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('auth');
    }

    public function getId(): string
    {
        return 'auth_destroy_expired_sessions';
    }

    public function getTitle(): string
    {
        return $this->lng->txt('auth_cron_destroy_expired_sessions');
    }

    public function getDescription(): string
    {
        return $this->lng->txt('auth_cron_destroy_expired_sessions_desc');
    }

    public function hasAutoActivation(): bool
    {
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function getValidScheduleTypes(): array
    {
        return [
            \ILIAS\Cron\Job\Schedule\JobScheduleType::IN_MINUTES,
            \ILIAS\Cron\Job\Schedule\JobScheduleType::IN_HOURS
        ];
    }

    public function getDefaultScheduleType(): \ILIAS\Cron\Job\Schedule\JobScheduleType
    {
        return \ILIAS\Cron\Job\Schedule\JobScheduleType::IN_MINUTES;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return 1;
    }

    public function isManuallyExecutable(): bool
    {
        return false;
    }

    public function run(): JobResult
    {
        $result = new JobResult();
        $result->setStatus(JobResult::STATUS_OK);

        $num_destroyed_sessions = ilSession::_destroyExpiredSessions();
        ilSessionStatistics::aggregateRaw(time());
        $result->setMessage('Number of destroyed sessions: ' . $num_destroyed_sessions);

        return $result;
    }
}
