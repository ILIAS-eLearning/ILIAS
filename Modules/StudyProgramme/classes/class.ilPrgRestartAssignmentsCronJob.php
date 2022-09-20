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

    protected ilStudyProgrammeAssignmentDBRepository $user_assignments_db;
    protected ilLogger $log;
    protected ilLanguage $lng;
    protected Container $dic;

    public function __construct()
    {
        global $DIC;
        $this->log = $DIC['ilLog'];
        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule('prg');

        $this->dic = ilStudyProgrammeDIC::dic();
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

        $programmes_to_reassign = $this->getSettingsRepository()
            ->getProgrammeIdsWithReassignmentForExpiringValidity();

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

        //TODO: expire for assignment, not progress!!!
        $progresses = $this->getProgressRepository()
            ->getAboutToExpire($programmes_and_due, false);

        if (count($progresses) === 0) {
            return $result;
        }

        $events = $this->getEvents();
        $assignment_repo = $this->getAssignmentRepository();
        foreach ($progresses as $progress) {
            $ass = $assignment_repo->get($progress->getAssignmentId());
            if ($ass->getRestartedAssignmentId() < 0) {
                if ($ass->getRootId() !== $progress->getNodeId()) {
                    $this->log(
                        sprintf(
                            'PRG, RestartAssignments: progress %s is not root of assignment %s. skipping.',
                            $progress->getId(),
                            $ass->getId()
                        )
                    );
                    continue;
                }

                $this->log(
                    sprintf(
                        'PRG, RestartAssignments: user %s\'s assignment %s is being restarted (Programme %s)',
                        $progress->getUserId(),
                        $ass->getId(),
                        $progress->getNodeId()
                    )
                );

                $prg = ilObjStudyProgramme::getInstanceByObjId($ass->getRootId());
                $restarted = $prg->assignUser($ass->getUserId(), self::ACTING_USR_ID);
                $ass = $ass->withRestarted($restarted->getId(), $today);

                $assignment_repo->update($ass);

                $events->userReAssigned($restarted);
                $result->setStatus(ilCronJobResult::STATUS_OK);
            }
        }

        return $result;
    }

    protected function getNow(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    protected function getSettingsRepository(): ilStudyProgrammeSettingsDBRepository
    {
        return $this->dic['model.Settings.ilStudyProgrammeSettingsRepository'];
    }

    protected function getProgressRepository(): ilStudyProgrammeProgressDBRepository
    {
        return $this->dic['ilStudyProgrammeUserProgressDB'];
    }

    protected function getAssignmentRepository(): ilStudyProgrammeAssignmentDBRepository
    {
        return $this->dic['ilStudyProgrammeUserAssignmentDB'];
    }

    protected function getEvents(): ilStudyProgrammeEvents
    {
        return $this->dic['ilStudyProgrammeEvents'];
    }

    protected function log(string $msg): void
    {
        $this->log->write($msg);
    }
}
