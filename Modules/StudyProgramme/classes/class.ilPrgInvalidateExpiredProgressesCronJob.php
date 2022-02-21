<?php declare(strict_types=1);

/* Copyright (c) 2019 Denis Klöpfer <denis.kloepfer@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/*
 * This invalidates a successful progress if validityOfQualification is reached.
 *
 * This is deprecated, I think.
 * It is perfectly feasible to raise some event for this, though,
 * but invalidation is reached by a date rather than a flag set by a cron job.
 */
 class ilPrgInvalidateExpiredProgressesCronJob extends ilCronJob
 {
     const ID = 'prg_invalidate_expired_progresses';

     protected ilStudyProgrammeProgressRepository $user_progress_db;
     protected ilLogger $log;
     protected ilLanguage $lng;

     public function __construct()
     {
         global $DIC;

         $this->user_progress_db = ilStudyProgrammeDIC::dic()['model.Progress.ilStudyProgrammeProgressRepository'];
         $this->log = $DIC['ilLog'];
         $this->lng = $DIC['lng'];
         $this->lng->loadLanguageModule('prg');
     }

     public function getTitle() : string
     {
         return $this->lng->txt('prg_invalidate_expired_progresses_title');
     }

     public function getDescription() : string
     {
         return $this->lng->txt('prg_invalidate_expired_progresses_desc');
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
         foreach ($this->user_progress_db->getExpiredSuccessfull() as $progress) {
             try {
                 $progress->invalidate();
             } catch (ilException $e) {
                 $this->log->write('an error occured: ' . $e->getMessage());
             }
         }
         $result->setStatus(ilCronJobResult::STATUS_OK);
         return $result;
     }
 }
