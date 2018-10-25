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
	 * @var \ILIAS\BackgroundTasks\Task\TaskFactory
	 */
	protected $task_factory = null;

	/**
	 * Constructor
	 * @param integer $a_usr_id
	 * @param integer $a_exc_id
	 * @param integer $a_ass_id
	 * @param integer $a_participant_id
	 */
	public function __construct($a_usr_id, $a_exc_id, $a_ass_id, $a_participant_id)
	{
		global $DIC;

		$this->user_id = $a_usr_id;
		$this->exc_id = $a_exc_id;
		$this->ass_id = $a_ass_id;
		$this->participant_id = $a_participant_id;

		$this->task_factory = $DIC->backgroundTasks()->taskFactory();
		$this->task_manager = $DIC->backgroundTasks()->taskManager();
		$this->lng = $DIC->language();
	}

	public function run()
	{
		$bucket = new BasicBucket();
		$bucket->setUserId($this->user_id);

		include_once './Modules/Exercise/classes/BackgroundTasks/class.ilExerciseManagementCollectFilesJob.php';
		include_once './Modules/Exercise/classes/BackgroundTasks/class.ilSubmissionsZipJob.php';
		include_once './Modules/Exercise/classes/BackgroundTasks/class.ilExDownloadSubmissionsZipInteraction.php';

		$collect_data_job = $this->task_factory->createTask(ilExerciseManagementCollectFilesJob::class,[$this->exc_id, (int)$this->ass_id, (int)$this->participant_id]);

		$zip_job = $this->task_factory->createTask(ilSubmissionsZipJob::class, [$collect_data_job]);

		if($this->participant_id > 0) {
			$download_name = ilExSubmission::getDirectoryNameFromUserData($this->participant_id);
			$bucket->setTitle($this->getParticipantBucketTitle());
		} else {
			$download_name = ilUtil::getASCIIFilename(ilExAssignment::lookupTitle($this->ass_id));
			$bucket->setTitle($download_name);
		}


		ilLoggerFactory::getRootLogger()->debug("*** Interaction task ::: 1st parameter :: Download name should be the directory name => ".$download_name);
		$download_interaction = $this->task_factory->createTask(ilExDownloadSubmissionsZipInteraction::class,[$download_name, $zip_job]);

		 //download name
		$bucket->setTask($download_interaction);
		$this->task_manager->run($bucket);
		return true;
	}

	protected function getParticipantBucketTitle()
	{
		$name = ilObjUser::_lookupName($this->participant_id);
		$title = ucfirst($name['lastname']).", ".ucfirst($name['firstname']);
		return $title;
	}

}