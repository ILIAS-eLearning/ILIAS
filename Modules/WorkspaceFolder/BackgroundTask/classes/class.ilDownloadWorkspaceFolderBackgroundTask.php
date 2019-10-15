<?php

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Background task for downloads
 *
 * @author killing@leifos.de
 */
class ilDownloadWorkspaceFolderBackgroundTask {

	/**
	 * @var ilLogger
	 */
	private $logger = null;
	/**
	 * @var int
	 */
	protected $user_id;
	/**
	 * @var int[]
	 */
	protected $object_wsp_ids;
	/**
	 * determines whether the task has been initiated by a folder's action drop-down to prevent a folder duplicate inside the zip.
	 *
	 * @var bool
	 */
	protected $initiated_by_folder_action = false;
	/**
	 * @var \ILIAS\BackgroundTasks\Task\TaskFactory
	 */
	protected $task_factory = null;
	/**
	 * title of the task showed in the main menu.
	 *
	 * @var string
	 */
	protected $bucket_title;
	/**
	 * if the task has collected files to create the ZIP file.
	 *
	 * @var bool
	 */
	protected $has_files = false;


	/**
	 * Constructor
	 *
	 * @param int $a_usr_id
	 * @param int[] $a_object_wsp_ids
	 * @param bool $a_initiated_by_folder_action
	 */
	public function __construct(int $a_usr_id, array $a_object_wsp_ids, bool $a_initiated_by_folder_action = false) {
		global $DIC;
		$this->logger = ilLoggerFactory::getLogger("pwsp");
		$this->user_id = $a_usr_id;
		$this->object_wsp_ids = $a_object_wsp_ids;
		$this->initiated_by_folder_action = $a_initiated_by_folder_action;
		$this->task_factory = $DIC->backgroundTasks()->taskFactory();
		$this->lng = $DIC->language();
	}


	/**
	 * set bucket title.
	 *
	 * @param $a_title
	 */
	public function setBucketTitle($a_title) {
		$this->bucket_title = $a_title;
	}


	/**
	 * return bucket title.
	 *
	 * @return string
	 */
	public function getBucketTitle() {
		//TODO: fix ilUtil zip stuff
		// Error If name starts "-"
		// error massage from ilUtil->execQuoted = ["","zip error: Invalid command arguments (short option 'a' not supported)"]
		if (substr($this->bucket_title, 0, 1) === "-") {
			$this->bucket_title = ltrim($this->bucket_title, "-");
		}

		return $this->bucket_title;
	}


	/**
	 * Run task
	 *
	 * @return bool
	 */
	public function run() {
		// This is our Bucket
		$this->logger->info('Started download workspace files background task');
		$bucket = new BasicBucket();
		$bucket->setUserId($this->user_id);
		$this->logger->debug('Created bucket and set the following user id: ' . $this->user_id);

		// Copy Definition
		$definition = new ilWorkspaceCopyDefinition();
		$normalized_name = ilUtil::getASCIIFilename($this->getBucketTitle());
		$definition->setTempDir($normalized_name);
		$definition->setObjectWspIds($this->object_wsp_ids);
		$this->logger->debug('Created copy definition and added the following tempdir: ' . $normalized_name);

		// Collect all files by the definition and prevent duplicates
		$collect_job = $this->task_factory->createTask(ilCollectWorkspaceFilesJob::class, [$definition, $this->initiated_by_folder_action]);
		$this->logger->debug('Collected files based on the following object ids: ');
		$this->logger->dump($this->object_wsp_ids);

		// Check the FileSize
		$file_size_job = $this->task_factory->createTask(ilCheckSumOfWorkspaceFileSizesJob::class, [$collect_job]);

		// Show problems with file-limit
		$file_size_interaction = $this->task_factory->createTask(ilSumOfWorkspaceFileSizesTooLargeInteraction::class, [$file_size_job]);
		$this->logger->debug('Determined the sum of all file sizes');

		// move files from source dir to target directory
		$copy_job = $this->task_factory->createTask(ilCopyWorkspaceFilesToTempDirectoryJob::class, [$file_size_interaction]);

		// Zip it
		$zip_job = $this->task_factory->createTask(ilZipJob::class, [$copy_job]);
		$this->logger->debug('Moved files from source- to target-directory');

		// Download
		$download_name = new StringValue();
		$download_name->setValue($normalized_name . '.zip');
		$download_interaction = $this->task_factory->createTask(ilDownloadZipInteraction::class, [$zip_job, $download_name]);
		$this->logger->debug('Created a download interaction with the following download name: ' . $download_name->getValue());

		// last task to bucket
		$bucket->setTask($download_interaction);
		$bucket->setTitle($this->getBucketTitle());
		$this->logger->debug('Added last task to bucket and set the following title: ' . $this->getBucketTitle());

		$task_manager = $GLOBALS['DIC']->backgroundTasks()->taskManager();
		$task_manager->run($bucket);
		$this->logger->debug('Ran bucket in task manager');

		return true;
	}
}