<?php

/* Copyright (c) 2019 Denis Klöpfer <denis.kloepfer@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

/**
 Re-assign users (according to restart-date).
 This will result in a new/additional assignment
 */
class ilPrgRestartAssignmentsCronJob extends ilCronJob
{
    const ID = 'prg_restart_assignments_temporal_progress';
    const ACTING_USR_ID = -1;

    /**
     * @var ilStudyProgrammeAssignmentDBRepository
     */
    protected $user_assignments_db;

    /**
     * @var ilLog
     */
    protected $log;

    /**
     * @var ilLanguage
     */
    protected $lng;

    public function __construct()
    {
        global $DIC;
        $this->log = $DIC['ilLog'];
        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule('prg');

        $this->dic = ilStudyProgrammeDIC::dic();
    }


    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->lng->txt('prg_restart_assignments_temporal_progress_title');
    }
    
    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->lng->txt('prg_restart_assignments_temporal_progress_desc');
    }
    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return self::ID;
    }
    
    /**
     * Is to be activated on "installation"
     *
     * @return boolean
     */
    public function hasAutoActivation()
    {
        return true;
    }

    /**
     * Can the schedule be configured?
     *
     * @return boolean
     */
    public function hasFlexibleSchedule()
    {
        return true;
    }
    
    /**
     * Get schedule type
     *
     * @return int
     */
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_IN_DAYS;
    }
    
    /**
     * Get schedule value
     *
     * @return int|array
     */
    public function getDefaultScheduleValue()
    {
        return 1;
    }
        
    /**
     * @return ilCronJobResult
     */
    public function run()
    {
        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_NO_ACTION);

        $programmes_to_reassign = $this->getSettingsRepository()
            ->getProgrammeIdsWithReassignmentForExpiringValidity();

        if (count($programmes_to_reassign) == 0) {
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

        if (count($progresses) == 0) {
            return $result;
        }
    
        $events = $this->getEvents();
        $assignment_repo = $this->getAssignmentRepository();
        foreach ($progresses as $progress) {
            $ass = $assignment_repo->get($progress->getAssignmentId());
            if ($ass->getRestartedAssignmentId() < 0) {
                if ($ass->getRootId() != $progress->getNodeId()) {
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

    protected function getNow() : \DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    protected function getSettingsRepository() : ilStudyProgrammeSettingsDBRepository
    {
        return $this->dic['model.Settings.ilStudyProgrammeSettingsRepository'];
    }

    protected function getProgressRepository() : ilStudyProgrammeProgressDBRepository
    {
        return $this->dic['ilStudyProgrammeUserProgressDB'];
    }

    protected function getAssignmentRepository() : ilStudyProgrammeAssignmentDBRepository
    {
        return $this->dic['ilStudyProgrammeUserAssignmentDB'];
    }

    protected function getEvents()
    {
        return $this->dic['ilStudyProgrammeEvents'];
    }

    protected function log(string $msg) : void
    {
        $this->log->write($msg);
    }
}
