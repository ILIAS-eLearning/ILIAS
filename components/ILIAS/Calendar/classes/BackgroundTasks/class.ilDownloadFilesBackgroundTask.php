<?php

declare(strict_types=1);

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Task\TaskFactory as TaskFactory;

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilDownloadFilesBackgroundTask
{
    private ilLogger $logger;
    protected ilLanguage $lng;
    protected ?ilObjUser $user;
    protected TaskFactory $task_factory;

    private int $user_id;
    private array $events = [];
    private string $bucket_title;
    private bool $has_files = false;
    private \ilGlobalTemplateInterface $main_tpl;

    /**
     * ilDownloadFilesBackgroundTask constructor.
     * @param $a_usr_id
     * @throws ilDatabaseException | DomainException
     */
    public function __construct($a_usr_id)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->logger = $DIC->logger()->cal();
        $this->user_id = $a_usr_id;
        $this->task_factory = $DIC->backgroundTasks()->taskFactory();
        $this->lng = $DIC->language();

        $user = ilObjectFactory::getInstanceByObjId($a_usr_id, false);
        if (!$user instanceof ilObjUser) {
            throw new DomainException('Invalid or deleted user id given: ' . $this->user_id);
        }
        $this->user = $user;
    }

    public function setEvents(array $a_events): void
    {
        $this->events = $a_events;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function setBucketTitle(string $a_title): void
    {
        $this->bucket_title = $a_title;
    }

    /**
     * @return string
     * @todo see comments
     */
    public function getBucketTitle(): string
    {
        //TODO: fix ilUtil zip stuff
        // Error If name starts "-"
        // error massage from ilUtil->execQuoted = ["","zip error: Invalid command arguments (short option 'a' not supported)"]
        if (substr($this->bucket_title, 0, 1) === "-") {
            $this->bucket_title = ltrim($this->bucket_title, "-");
        }
        return $this->bucket_title;
    }

    public function run(): bool
    {
        $definition = new ilCalendarCopyDefinition();
        $normalized_name = ilFileUtils::getASCIIFilename($this->getBucketTitle());
        $definition->setTempDir($normalized_name);

        $this->collectFiles($definition);

        if (!$this->has_files) {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt("cal_down_no_files"), true);
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

        $download_interaction = $this->task_factory->createTask(
            ilCalendarDownloadZipInteraction::class,
            [$zip_job, $download_name]
        );

        // last task to bucket
        $bucket->setTask($download_interaction);

        $bucket->setTitle($this->getBucketTitle());

        $task_manager = $GLOBALS['DIC']->backgroundTasks()->taskManager();
        $task_manager->run($bucket);
        return true;
    }

    private function collectFiles(ilCalendarCopyDefinition $def): void
    {
        //filter here the objects, don't repeat the object Id
        $object_ids = [];
        foreach ($this->getEvents() as $event) {
            $start = new ilDateTime($event['dstart'], IL_CAL_UNIX);
            $cat = ilCalendarCategory::getInstanceByCategoryId($event['category_id']);
            $obj_id = $cat->getObjId();

            $this->logger->debug('Handling event: ' . $event['event']->getPresentationTitle());
            //22295 If the object type is exc then we need all the assignments.Otherwise we will get only one.
            if (
                $cat->getType() != \ilCalendarCategory::TYPE_OBJ ||
                $cat->getObjType() == 'exc' ||
                !in_array($obj_id, $object_ids)
            ) {
                $this->logger->debug('New obj_id..');
                $object_ids[] = $obj_id;

                $folder_date = $start->get(IL_CAL_FKT_DATE, 'Y-m-d', $this->user->getTimeZone());

                if ($event['fullday']) {
                    $folder_app = ilFileUtils::getASCIIFilename($event['event']->getPresentationTitle(false));   //title formalized
                } else {
                    $start_time = $start->get(IL_CAL_FKT_DATE, 'H.i', $this->user->getTimeZone());

                    $end = new ilDateTime($event['dend'], IL_CAL_UNIX);
                    $end_time = $end->get(IL_CAL_FKT_DATE, 'H.i', $this->user->getTimeZone());

                    if ($start_time != $end_time) {
                        $start_time .= (' - ' . $end_time);
                    }
                    $folder_app = $start_time . ' ' .
                        ilFileUtils::getASCIIFilename($event['event']->getPresentationTitle(false));   //title formalized
                }

                $this->logger->debug("collecting files...event title = " . $folder_app);
                $file_handler = ilAppointmentFileHandlerFactory::getInstance($event);
                $this->logger->debug('Current file handler: ' . get_class($file_handler));

                if ($files = $file_handler->getFiles()) {
                    $this->has_files = true;
                }

                $this->logger->dump($files);
                foreach ($files as $idx => $file_property) {
                    $this->logger->debug('Filename:' . $file_property->getFileName());
                    $this->logger->debug('Absolute path: ' . $file_property->getAbsolutePath());

                    $def->addCopyDefinition(
                        $file_property->getAbsolutePath(),
                        $folder_date . '/' . $folder_app . '/' . $file_property->getFileName()
                    );
                    $this->logger->debug(
                        'Added new copy definition: ' .
                        $folder_date . '/' . $folder_app . '/' . $file_property->getFileName() . ' => ' .
                        $file_property->getAbsolutePath()
                    );
                }
            } else {
                $this->logger->info('Ignoring obj_id: ' . $obj_id . ' already processed.');
            }
        }
    }
}
