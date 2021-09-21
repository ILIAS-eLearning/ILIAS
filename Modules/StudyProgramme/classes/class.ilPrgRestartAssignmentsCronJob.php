<?php

/* Copyright (c) 2019 Denis Klöpfer <denis.kloepfer@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

class ilPrgRestartAssignmentsCronJob extends ilCronJob
{
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

        $this->user_assignments_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserAssignmentDB'];
        $this->events = ilStudyProgrammeDIC::dic()['ilStudyProgrammeEvents'];
        $this->log = $DIC['ilLog'];
        $this->lng = $DIC['lng'];
        /**ilStudyProgrammeAssignmentRepository*/
        $this->assignment_repository = ilStudyProgrammeDIC::dic()['model.Assignment.ilStudyProgrammeAssignmentRepository'];
    }


    const ID = 'prg_restart_assignments_temporal_progress';

    public function getTitle() : string
    {
        return $this->lng->txt('prg_restart_assignments_temporal_progress_title');
    }
    
    public function getDescription() : string
    {
        return $this->lng->txt('prg_restart_assignments_temporal_progress_desc');
    }

    public function getId() : string
    {
        return self::ID;
    }
    
    public function hasAutoActivation() : bool
    {
        return true;
    }

    public function hasFlexibleSchedule() : bool
    {
        return true;
    }
    
    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_IN_DAYS;
    }
    
    public function getDefaultScheduleValue() : ?int
    {
        return 1;
    }

    public function run() : ilCronJobResult
    {
        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_OK);
        foreach ($this->user_assignments_db->getDueToRestartInstances() as $assignment) {
            try {
                $prg = ilObjStudyProgramme::getInstanceByObjId($assignment->getRootId());
                $restarted = $prg->assignUser($this->getUserId(), $this->getUserId());
                $restarted = $restarted->setRestartedAssignmentId(
                    $restarted->getId()
                );

                $this->assignment_repository->update($restarted);
                $this->events->userReAssigned($restarted);
            } catch (ilException $e) {
                $this->log->write('an error occured: ' . $e->getMessage());
            }
        }
        return $result;
    }
}
