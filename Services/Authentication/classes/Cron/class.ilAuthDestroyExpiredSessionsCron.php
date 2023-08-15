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

class ilAuthDestroyExpiredSessionsCron extends ilCronJob
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
        return true;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function getDefaultScheduleType(): ILIAS\Cron\Schedule\CronJobScheduleType
    {
        return ILIAS\Cron\Schedule\CronJobScheduleType::SCHEDULE_TYPE_IN_HOURS;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return 1;
    }

    public function run(): ilCronJobResult
    {
        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_OK);

        $num_destroyed_sessions = ilSession::_destroyExpiredSessions();
        ilSessionStatistics::aggretateRaw(time());
        $result->setMessage('Number of destroyed sessions: ' . $num_destroyed_sessions);

        return $result;
    }
}
