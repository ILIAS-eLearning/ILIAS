<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;

/**
 * Download submissions and feedback for exercises.
 * @author Jesús López <lopez@leifos.com>
 */
class ilDownloadSubmissionsBackgroundTask
{
    /**
     * @var int
     */
    protected $exc_ref_id;

    /**
     * @var int
     */
    protected $exc_id;

    /**
     * @var int|null
     */
    protected $ass_id;

    /**
     * @var int|null
     */
    protected $participant_id;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var \ILIAS\BackgroundTasks\Task\TaskFactory
     */
    protected $task_factory = null;

    /**
     * @var \ILIAS\BackgroundTasks\TaskManager
     */
    protected $task_manager = null;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var
     */
    private $logger = null;

    /**
     * Constructor
     * @param integer $a_usr_id
     * @param integer $a_exc_ref_id
     * @param integer $a_exc_id
     * @param integer $a_ass_id
     * @param integer $a_participant_id
     */
    public function __construct($a_usr_id, $a_exc_ref_id, $a_exc_id, $a_ass_id, $a_participant_id)
    {
        global $DIC;

        $this->user_id = $a_usr_id;
        $this->exc_ref_id = $a_exc_ref_id;
        $this->exc_id = $a_exc_id;
        $this->ass_id = $a_ass_id;
        $this->participant_id = $a_participant_id;

        $this->task_factory = $DIC->backgroundTasks()->taskFactory();
        $this->task_manager = $DIC->backgroundTasks()->taskManager();
        $this->logger = $DIC->logger()->exc();
    }

    public function run()
    {
        $bucket = new BasicBucket();
        $bucket->setUserId($this->user_id);

        $this->logger->debug("* Create task 'collect_data_job' using the following values:");
        $this->logger->debug("job class = " . ilExerciseManagementCollectFilesJob::class);
        $this->logger->debug("exc_id = " . $this->exc_id . ", exc_ref_id = " . $this->exc_ref_id . ", ass_id = " . (int) $this->ass_id . ", participant_id = " . (int) $this->participant_id . ", user_id = " . (int) $this->user_id);

        $collect_data_job = $this->task_factory->createTask(
            ilExerciseManagementCollectFilesJob::class,
            [
                (int) $this->exc_id,
                (int) $this->exc_ref_id,
                (int) $this->ass_id,
                (int) $this->participant_id,
                (int) $this->user_id
            ]
        );

        $this->logger->debug("* Create task 'zip job' using the following values:");
        $this->logger->debug("job class = " . ilSubmissionsZipJob::class);
        $this->logger->debug("sending as input the task called->collect_data_job");

        $zip_job = $this->task_factory->createTask(ilSubmissionsZipJob::class, [$collect_data_job]);

        if ($this->participant_id > 0) {
            $download_name = ilExSubmission::getDirectoryNameFromUserData($this->participant_id);
            $bucket->setTitle($this->getParticipantBucketTitle());
        } else {
            $download_name = ilUtil::getASCIIFilename(ilExAssignment::lookupTitle($this->ass_id));
            $bucket->setTitle($download_name);
        }


        $this->logger->debug("* Create task 'download_interaction' using the following values:");
        $this->logger->debug("job class = " . ilExDownloadSubmissionsZipInteraction::class);
        $this->logger->debug("download_name which is the same as bucket title = " . $download_name . " + the zip_job task");
        // see comments here -> https://github.com/leifos-gmbh/ILIAS/commit/df6fc44a4c85da33bd8dd5b391a396349e7fa68f
        $download_interaction = $this->task_factory->createTask(ilExDownloadSubmissionsZipInteraction::class, [$zip_job, $download_name]);

        //download name
        $bucket->setTask($download_interaction);
        $this->task_manager->run($bucket);
        return true;
    }

    protected function getParticipantBucketTitle()
    {
        $name = ilObjUser::_lookupName($this->participant_id);
        $title = ucfirst($name['lastname']) . ", " . ucfirst($name['firstname']);
        return $title;
    }
}
