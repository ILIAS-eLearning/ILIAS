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

/*
 * This invalidates a successful progress if validityOfQualification is reached.
 *
 * This is deprecated, I think.
 * It is perfectly feasible to raise some event for this, though,
 * but invalidation is reached by a date rather than a flag set by a cron job.
 */
class ilPrgInvalidateExpiredProgressesCronJob extends ilCronJob
{
    private const ID = 'prg_invalidate_expired_progresses';

    protected ilComponentLogger $log;
    protected ilLanguage $lng;
    protected ilPRGAssignmentDBRepository $assignment_repo;
    protected ilStudyProgrammeSettingsDBRepository $settings_repo;

    public function __construct()
    {
        global $DIC;
        $this->log = $DIC['ilLog'];
        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule('prg');

        $dic = ilStudyProgrammeDIC::dic();
        $this->assignment_repo = $dic['repo.assignment'];
        $this->settings_repo = $dic['model.Settings.ilStudyProgrammeSettingsRepository'];
    }

    public function getTitle(): string
    {
        return $this->lng->txt('prg_invalidate_expired_progresses_title');
    }

    public function getDescription(): string
    {
        return $this->lng->txt('prg_invalidate_expired_progresses_desc');
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

    public function getDefaultScheduleType(): int
    {
        return self::SCHEDULE_TYPE_IN_DAYS;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return 1;
    }

    public function run(): ilCronJobResult
    {
        $result = new ilCronJobResult();
        foreach ($this->assignment_repo->getExpiredAndNotInvalidated() as $assignment) {
            try {
                $assignment = $assignment->invalidate($this->settings_repo);
                $this->assignment_repo->store($assignment);
            } catch (ilException $e) {
                $this->log->write('an error occured: ' . $e->getMessage());
            }
        }
        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }
}
