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
     * @var \ilObjUser|null
     */
    protected $user = null;
    
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
     * if the task has collected files to create the ZIP file.
     * @var bool
     */
    protected $has_files = false;
    
    /**
     * Constructor
     * @param type $a_usr_id
     */
    public function __construct($a_usr_id)
    {
        global $DIC;
        $this->logger = $DIC->logger()->cal();
        $this->user_id = $a_usr_id;
        $this->task_factory = $DIC->backgroundTasks()->taskFactory();
        $this->lng = $DIC->language();

        $this->user = \ilObjectFactory::getInstanceByObjId($a_usr_id, false);
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
        if (substr($this->bucket_title, 0, 1) === "-") {
            $this->bucket_title = ltrim($this->bucket_title, "-");
        }

        return $this->bucket_title;
    }
    
    /**
     * Run task
     * @return bool
     */
    public function run()
    {
        $definition = new ilCalendarCopyDefinition();
        $normalized_name = ilUtil::getASCIIFilename($this->getBucketTitle());
        $definition->setTempDir($normalized_name);

        $this->collectFiles($definition);

        if (!$this->has_files) {
            ilUtil::sendInfo($this->lng->txt("cal_down_no_files"), true);
            return false;
        }

        $bucket = new BasicBucket();
        $bucket->setUserId($this->user_id);
        
        // move files from source dir to target directory
        $copy_job = $this->task_factory->createTask(ilCalendarCopyFilesToTempDirectoryJob::class, [$definition]);
        $zip_job = $this->task_factory->createTask(ilCalendarZipJob::class, [$copy_job]);
        
        $download_name = new StringValue();

        $this->logger->debug("Normalized name = " . $normalized_name);
        $download_name->setValue($normalized_name . '.zip');

        $download_interaction = $this->task_factory->createTask(ilCalendarDownloadZipInteraction::class, [$zip_job, $download_name]);

        // last task to bucket
        $bucket->setTask($download_interaction);

        $bucket->setTitle($this->getBucketTitle());
        
        $task_manager = $GLOBALS['DIC']->backgroundTasks()->taskManager();
        $task_manager->run($bucket);
        return true;
    }
    
    /**
     * Collect files
     */
    private function collectFiles(ilCalendarCopyDefinition $def)
    {
        //filter here the objects, don't repeat the object Id
        $object_ids = array();
        foreach ($this->getEvents() as $event) {
            $start = new ilDateTime($event['dstart'], IL_CAL_UNIX);
            $cat = ilCalendarCategory::getInstanceByCategoryId($event['category_id']);
            $obj_id = $cat->getObjId();

            //22295 If the object type is exc then we need all the assignments.Otherwise we will get only one.
            if (!in_array($obj_id, $object_ids) || $cat->getObjType() == "exc") {
                $object_ids[] = $obj_id;

                $folder_date = $start->get(IL_CAL_FKT_DATE, 'Y-m-d', $this->user->getTimeZone());


                if ($event['fullday']) {
                    $folder_app = ilUtil::getASCIIFilename($event['event']->getPresentationTitle(false));   //title formalized
                } else {
                    $start_time = $start->get(IL_CAL_FKT_DATE, 'H.i', $this->user->getTimeZone());

                    $end = new ilDateTime($event['dend'], IL_CAL_UNIX);
                    $end_time = $end->get(IL_CAL_FKT_DATE, 'H.i', $this->user->getTimeZone());

                    if ($start_time != $end_time) {
                        $start_time .= (' - ' . $end_time);
                    }
                    $folder_app = $start_time . ' ' .
                        ilUtil::getASCIIFilename($event['event']->getPresentationTitle(false));   //title formalized
                }

                $this->logger->debug("collecting files...event title = " . $folder_app);

                $file_handler = ilAppointmentFileHandlerFactory::getInstance($event);

                if ($files = $file_handler->getFiles()) {
                    $this->has_files = true;
                }
                //if file_system_path is set, it is the real path of the file (courses use ids as names file->getId())
                //otherwise $file_with_absolut_path is the path. ($file->getName())
                foreach ($files as $file_system_path => $file_with_absolut_path) {
                    #22198 check if the key is a string defined by ILIAS or a number set by PHP as a sequential key
                    //[/Sites/data/client/ilCourse/2/crs_xx/info/1] => /Sites/data/client/ilCourse/2/crs_xxx/info/image.png
                    //[0] =>  /Sites/data/client/ilFile/3/file_3xx/001/image.png
                    if (is_string($file_system_path)) {
                        $file_with_absolut_path = $file_system_path;
                        $file_id = (int) basename($file_system_path);
                        $basename = $this->getEventFileNameFromId($event['event'], $file_id);
                    } else {
                        $basename = ilUtil::getASCIIFilename(basename($file_with_absolut_path));
                    }
                    $def->addCopyDefinition(
                        $file_with_absolut_path,
                        $folder_date . '/' . $folder_app . '/' . $basename
                    );

                    $this->logger->debug('Added new copy definition: ' . $folder_date . '/' . $folder_app . '/' . $basename . ' -> ' . $file_with_absolut_path);
                }
            }
        }
    }

    /**
     * Only courses store the files using the id for naming.
     * @param ilCalendarEntry
     * @return string
     */
    private function getEventFileNameFromId(ilCalendarEntry $a_event, $a_file_id)
    {
        $filename = "";
        $cat_id = ilCalendarCategoryAssignments::_lookupCategory($a_event->getEntryId());
        $cat = ilCalendarCategory::getInstanceByCategoryId($cat_id);
        $cat_type = $cat->getType();
        $obj_id = $cat->getObjId();
        $obj_type = ilObject::_lookupType($obj_id);

        if ($cat_type == ilCalendarCategory::TYPE_OBJ && $obj_type == "crs") {
            $course_file = new ilCourseFile((int) $a_file_id);
            $filename = $course_file->getFileName();
        }
        return ilUtil::getASCIIFilename($filename);
    }
}
