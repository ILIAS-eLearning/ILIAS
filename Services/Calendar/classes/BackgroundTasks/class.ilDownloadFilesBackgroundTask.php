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
		//TODO: fix ilUtil zip stuff
		// Error If name starts "-"
		// error massage from ilUtil->execQuoted = ["","zip error: Invalid command arguments (short option 'a' not supported)"]
		if(substr($this->bucket_title, 0, 1) === "-") {
			$this->bucket_title = ltrim($this->bucket_title, "-");
		}

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
		$copy_job = $this->task_factory->createTask(ilCalendarCopyFilesToTempDirectoryJob::class, [$definition, $this->normalizeFileName($this->getBucketTitle())]);
		$zip_job = $this->task_factory->createTask(ilCalendarZipJob::class, [$copy_job]);
		
		$download_name = new StringValue();
		$normalized_name = $this->normalizeFileName($this->getBucketTitle());

		$this->logger->debug("Normalized name = ".$normalized_name);
		$download_name->setValue($normalized_name.'.zip');

		
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
			$folder_app = $this->normalizeFileName($event['event']->getPresentationTitle());   //title formalized

			$this->logger->debug("collecting files...event title = ".$folder_app);

			$file_handler = ilAppointmentFileHandlerFactory::getInstance($event);
			foreach($file_handler->getFiles() as $file_with_absolut_path)
			{
				$basename = $this->normalizeFileName(basename($file_with_absolut_path));
				$def->addCopyDefinition(
					$file_with_absolut_path,
					$folder_date.'/'.$folder_app.'/'.$basename
				);
				$this->logger->debug('Added new copy definition: ' . $folder_date.'/'.$folder_app.'/'.$basename. ' -> '. $file_with_absolut_path);
			}
			
		}
	}

	//Is this method really needed? do we have something centralized for this stuff?
	protected function normalizeFileName($s)
	{
		$org = $s;
		$s = str_replace(
			array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
			array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
			$s
		);
		$s = str_replace(
			array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
			array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
			$s );
		$s = str_replace(
			array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
			array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
			$s );
		$s = str_replace(
			array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
			array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
			$s );
		$s = str_replace(
			array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
			array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
			$s );
		$s = str_replace(
			array('ñ', 'Ñ', 'ç', 'Ç'),
			array('n', 'N', 'c', 'C'),
			$s
		);
		$s = str_replace('ÿ', 'yu', $s);
		$s    = preg_replace( '@\x{00df}@u'    , "ss",    $s );    // maps German ß onto ss
		$s    = preg_replace( '@\x{00c6}@u'    , "AE",    $s );    // Æ => AE
		$s    = preg_replace( '@\x{00e6}@u'    , "ae",    $s );    // æ => ae
		$s    = preg_replace( '@\x{0152}@u'    , "OE",    $s );    // Œ => OE
		$s    = preg_replace( '@\x{0153}@u'    , "oe",    $s );    // œ => oe
		$s    = preg_replace( '@\x{00d0}@u'    , "D",    $s );    // Ð => D
		$s    = preg_replace( '@\x{0110}@u'    , "D",    $s );    // Ð => D
		$s    = preg_replace( '@\x{00f0}@u'    , "d",    $s );    // ð => d
		// remove all non-ASCii characters
		$s    = preg_replace( '@[^\0-\x80]@u'    , "",    $s );
		$s = preg_replace('/\s+/', '_', $s);
		$s = preg_replace("/[^a-zA-Z0-9\_\.\-]/", "", $s);
		// possible errors in UTF8-regular-expressions
		if (empty($s)) {
			$this->logger->debug("Error when normalize filename.");
			return $org;
		}else {
			$this->logger->debug("Filename normalized successfully");
			return $s;
		}
	}
}