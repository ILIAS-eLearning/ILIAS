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

use ILIAS\Cron\CronJob;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Cron\Job\Schedule\JobScheduleType;
use Random\RandomException;

/**
 * Cron job "LTI Grade Service": reports the learning progress of LTI users whose status changed since
 * its last run, for the changes that raise no event, like new learning progress settings.
 *
 * The name and the id are fixed: the table cron_job stores them.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTICronOutcomeService extends CronJob
{
    private const string ID = 'lti_outcome';

    private readonly ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('lti');
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function getTitle(): string
    {
        return $this->lng->txt('lti_cron_title');
    }

    public function getDescription(): string
    {
        return $this->lng->txt('lti_cron_title_desc');
    }

    public function hasAutoActivation(): bool
    {
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function getDefaultScheduleType(): JobScheduleType
    {
        return JobScheduleType::DAILY;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return 1;
    }

    /**
     * Looks back to the previous run, whose result is stored once it ends; the first run looks back one day.
     *
     * @throws ilDateTimeException
     * @throws RandomException
     */
    public function run(): JobResult
    {
        global $DIC;

        $last_run = (int) ($DIC->cron()->repository()->getCronJobData(self::ID)[0]['job_result_ts'] ?? 0);
        ilLTIAppEventListener::reportChangesSince(
            new ilDateTime($last_run > 0 ? $last_run : time() - 24 * 3600, IL_CAL_UNIX)
        );

        $result = new JobResult();
        $result->setStatus(JobResult::STATUS_OK);

        return $result;
    }
}
