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

use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Cron\AbstractCronJob;

/*
 * This invalidates a successful progress if validityOfQualification is reached.
 *
 * This is deprecated, I think.
 * It is perfectly feasible to raise some event for this, though,
 * but invalidation is reached by a date rather than a flag set by a cron job.
 */
class ilPrgInvalidateExpiredProgressesCronJob extends AbstractCronJob
{
    private const ID = 'prg_invalidate_expired_progresses';

    protected ilLogger $log;
    protected ilPRGAssignmentDBRepository $assignment_repo;
    protected ilStudyProgrammeSettingsDBRepository $settings_repo;

    public function init(): void
    {
        global $DIC;

        $this->language->loadLanguageModule('prg');

        $this->log = $DIC->logger()->root();

        $dic = ilStudyProgrammeDIC::dic();
        $this->assignment_repo = $dic['repo.assignment'];
        $this->settings_repo = $dic['model.Settings.ilStudyProgrammeSettingsRepository'];
    }

    public function getTitle(): string
    {
        return $this->language->txt('prg_invalidate_expired_progresses_title');
    }

    public function getDescription(): string
    {
        return $this->language->txt('prg_invalidate_expired_progresses_desc');
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function hasAutoActivation(): bool
    {
        return true;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function getDefaultScheduleType(): JobScheduleType
    {
        return JobScheduleType::IN_DAYS;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return 1;
    }

    public function run(): JobResult
    {
        $result = new JobResult();
        $expired = $this->assignment_repo->getExpiredAndNotInvalidated();

        if ($expired === []) {
            $result->setStatus(JobResult::STATUS_NO_ACTION);
            return $result;
        }

        foreach ($expired as $assignment) {
            try {
                $assignment = $assignment->invalidate($this->settings_repo);
                $this->assignment_repo->store($assignment);
            } catch (ilException $e) {
                $this->log->write('an error occured: ' . $e->getMessage());
                $result->setStatus(JobResult::STATUS_FAIL);
                return $result;
            }
        }

        $result->setStatus(JobResult::STATUS_OK);
        return $result;
    }
}
