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
 
use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\BackgroundTasks\Task\TaskFactory;
use ILIAS\BackgroundTasks\TaskManager;

/**
 * Download submissions and feedback for exercises.
 * @author Jesús López <lopez@leifos.com>
 */
class ilDownloadSubmissionsBackgroundTask
{
    protected int $exc_ref_id;
    protected int $exc_id;
    protected ?int $ass_id;
    protected ?int $participant_id;
    protected int $user_id;
    protected ?TaskFactory $task_factory = null;
    protected ?TaskManager $task_manager = null;
    protected ilLanguage $lng;
    protected ?ilLogger $logger = null;

    public function __construct(
        int $a_usr_id,
        int $a_exc_ref_id,
        int $a_exc_id,
        int $a_ass_id,
        int $a_participant_id
    ) {
        global $DIC;

        $this->user_id = $a_usr_id;
        $this->exc_ref_id = $a_exc_ref_id;
        $this->exc_id = $a_exc_id;
        $this->ass_id = $a_ass_id;
        $this->participant_id = $a_participant_id;

        $this->task_factory = $DIC->backgroundTasks()->taskFactory();
        $this->task_manager = $DIC->backgroundTasks()->taskManager();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->logger = $DIC->logger()->exc();
    }

    public function run() : bool
    {
        $bucket = new BasicBucket();
        $bucket->setUserId($this->user_id);

        $this->logger->debug("* Create task 'collect_data_job' using the following values:");
        $this->logger->debug("job class = " . ilExerciseManagementCollectFilesJob::class);
        $this->logger->debug("exc_id = " . $this->exc_id . ", exc_ref_id = " . $this->exc_ref_id . ", ass_id = " . (int) $this->ass_id . ", participant_id = " . (int) $this->participant_id . ", user_id = " . $this->user_id);

        $collect_data_job = $this->task_factory->createTask(
            ilExerciseManagementCollectFilesJob::class,
            [
                $this->exc_id,
                $this->exc_ref_id,
                (int) $this->ass_id,
                (int) $this->participant_id,
                $this->user_id
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
            $download_name = ilFileUtils::getASCIIFilename(ilExAssignment::lookupTitle($this->ass_id));
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

    protected function getParticipantBucketTitle() : string
    {
        $name = ilObjUser::_lookupName($this->participant_id);
        return ucfirst($name['lastname']) . ", " . ucfirst($name['firstname']);
    }
}
