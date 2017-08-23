<?php

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use \ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilDownloadFilesBackgroundTask
{
	/**
	 * @var ilLogger
	 */
	private $logger = null;
	
	/**
	 * @var int
	 */
	protected $user_id;
	
	/**
	 * @var \ILIAS\BackgroundTasks\Task\TaskFactory
	 */
	protected $task_factory = null;
	
	/**
	 * Array of calendar event
	 */
	private $events = [];

	/**
	 * title of the task showed in the main menu.
	 * @var string
	 */
	protected $bucket_title;

	/**
	 *
	 */
	
	/**
	 * Constructor
	 * @param type $a_usr_id
	 */
	public function __construct($a_usr_id)
	{
		$this->logger = $GLOBALS['DIC']->logger()->cal();
		$this->user_id = $a_usr_id;
		$this->task_factory = $GLOBALS['DIC']->backgroundTasks()->taskFactory();
	}
	
	/**
	 * Set events
	 * @param array $a_events
	 */
	public function setEvents(array $a_events)
	{
		$this->events = $a_events;
	}
	
	/**
	 * Get events
	 * @return type
	 */
	public function getEvents()
	{
		return $this->events;
	}

	/**
	 * set bucket title.
	 * @param $a_title
	 */
	public function setBucketTitle($a_title)
	{
		$this->bucket_title = $a_title;
	}

	/**
	 * return bucket title.
	 * @return string
	 */
	public function getBucketTitle()
	{
		return $this->bucket_title;
	}
	
	/**
	 * Run task
	 */
	public function run()
	{
		$bucket = new BasicBucket();
		$bucket->setUserId($this->user_id);

		$definition = new ilCalendarCopyDefinition();

		$this->collectFiles($definition);
		
		
		// move files from source dir to target directory
		$copy_job = $this->task_factory->createTask(ilCalendarCopyFilesToTempDirectoryJob::class, [$definition]);
		$zip_job = $this->task_factory->createTask(ilCalendarZipJob::class, [$copy_job]);
		
		$download_name = new StringValue();
		$download_name->setValue($this->getBucketTitle().'.zip');
		
		
		$download_interaction = $this->task_factory->createTask(
			ilCalendarDownloadZipInteraction::class,
			[
				$zip_job,
				$download_name
			]
		);

		// last task to bucket
		$bucket->setTask($download_interaction);

		$bucket->setTitle($this->getBucketTitle());
		
		$task_manager = $GLOBALS['DIC']->backgroundTasks()->taskManager();
		$task_manager->run($bucket);
	}
	
	/**
	 * Collect files
	 */
	private function collectFiles(ilCalendarCopyDefinition $def)
	{
		foreach($this->getEvents() as $event)
		{
			$folder_date = $event['event']->getStart()->get(IL_CAL_FKT_DATE,'Y-m-d');
			$folder_app = $event['event']->getPresentationTitle();

			$this->logger->debug("collecting files...event title = ".$folder_app);

			$file_handler = ilAppointmentFileHandlerFactory::getInstance($event);
			foreach($file_handler->getFiles() as $file_with_absolut_path)
			{
				$basename = basename($file_with_absolut_path);
				$def->addCopyDefinition(
					$file_with_absolut_path,
					$folder_date.'/'.$folder_app.'/'.$basename
				);
				$this->logger->debug('Added new copy definition: ' . $folder_date.'/'.$folder_app.'/'.$basename. ' -> '. $file_with_absolut_path);
			}
			
		}
	}
}
?>