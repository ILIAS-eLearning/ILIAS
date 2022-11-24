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

use Pimple\Container;

/**
 * Re-assign users (according to restart-date).
 * This will result in a new/additional assignment
 */
class ilPrgRestartAssignmentsCronJob extends ilCronJob
{
    private const ID = 'prg_restart_assignments_temporal_progress';
    private const ACTING_USR_ID = -1;

    protected ilComponentLogger $log;
    protected ilLanguage $lng;
    protected ilPRGAssignmentDBRepository $assignment_repo;
    protected ilPrgCronJobAdapter $adapter;

    protected array $prgs = [];

    public function __construct()
    {
        global $DIC;
        $this->log = $DIC['ilLog'];
        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule('prg');

        $dic = ilStudyProgrammeDIC::dic();
        $this->assignment_repo = $dic['repo.assignment'];
        $this->adapter = $dic['cron.restart'];
    }

    public function getTitle(): string
    {
        return $this->lng->txt('prg_restart_assignments_temporal_progress_title');
    }

    public function getDescription(): string
    {
        return $this->lng->txt('prg_restart_assignments_temporal_progress_desc');
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
        $result->setStatus(ilCronJobResult::STATUS_NO_ACTION);

        $programmes_to_reassign = $this->adapter->getRelevantProgrammeIds();
        if (count($programmes_to_reassign) === 0) {
            return $result;
        }

        $today = $this->getNow();
        $programmes_and_due = [];

        foreach ($programmes_to_reassign as $programme_obj_id => $days_offset) {
            $interval = new DateInterval('P' . $days_offset . 'D');
            $due = $today->add($interval);
            $programmes_and_due[$programme_obj_id] = $due;
        }

        $assignments = $this->assignment_repo->getAboutToExpire($programmes_and_due, false);

        if (count($assignments) === 0) {
            return $result;
        }

        foreach ($assignments as $ass) {
            if ($ass->getRestartedAssignmentId() < 0) {
                $prg = $this->getStudyProgramme($ass->getRootId());

                $restart_settings = $prg->getSettings()->getValidityOfQualificationSettings();
                if ($restart_settings->getRestartRecheck()
                    && !$ass->isManuallyAssigned()
                    && !$prg->getApplicableMembershipSourceForUser($ass->getUserId(), null)
                ) {
                    continue;
                }

                $this->log(
                    sprintf(
                        'PRG, RestartAssignments: user %s\'s assignment %s is being restarted (Programme %s)',
                        $ass->getUserId(),
                        $ass->getId(),
                        $ass->getRootId()
                    )
                );

                $restarted = $prg->assignUser($ass->getUserId(), self::ACTING_USR_ID, false);
                $ass = $ass->withRestarted($restarted->getId(), $today);
                $this->assignment_repo->store($ass);

                $this->adapter->actOnSingleAssignment($restarted);

                $result->setStatus(ilCronJobResult::STATUS_OK);
            }
        }

        return $result;
    }

    protected function getStudyProgramme(int $prg_obj_id): ilObjStudyProgramme
    {
        if (!array_key_exists($prg_obj_id, $this->prgs)) {
            $this->prgs[$prg_obj_id] = ilObjStudyProgramme::getInstanceByObjId($prg_obj_id);
        }
        return $this->prgs[$prg_obj_id];
    }

    protected function getNow(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    protected function log(string $msg): void
    {
        $this->log->write($msg);
    }
}
