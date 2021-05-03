<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\DTO\Metadata;

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Class ilFileSystemCleanTempDirCron
 * @author Lukas Zehnder <lz@studer-raimann.ch>
 */
class ilFileSystemCleanTempDirCron extends ilCronJob
{
    /**
     * @var \ILIAS\Filesystem\Filesystem
     */
    protected $filesystem;
    /**
     * @var ilLanguage
     */
    protected $language;
    /**
     * @var ilLogger
     */
    protected $logger;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        global $DIC;
        $this->language = $DIC['lng'];
        $this->filesystem = $DIC->filesystem()->temp();
        $this->logger = $DIC->logger()->root();
    }

    public function getId()
    {
        return "file_system_clean_temp_dir";
    }

    public function getTitle()
    {
        return $this->language->txt('file_system_clean_temp_dir_cron');
    }

    public function getDescription()
    {
        return $this->language->txt("file_system_clean_temp_dir_cron_info");
    }

    public function hasAutoActivation()
    {
        return true;
    }

    public function hasFlexibleSchedule()
    {
        return false;
    }

    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue()
    {
        return;
    }

    public function run()
    {
        // only delete files and folders older than one day to prevent issues with ongoing processes (e.g. zipping a folder)
        $date = "until 1 day ago";

        // files are deleted before folders to prevent issues that would arise when trying to delete a (no longer existing) file in a deleted folder.
        $files = $this->filesystem->finder()->in([""])->date($date)->files();
        $deleted_files = [];
        foreach ($files as $file_match) {
            try {
                if ($file_match->isFile()) {
                    $this->filesystem->delete($file_match->getPath());
                    $deleted_files[] = $file_match;
                }
            } catch (Throwable $t) {
                $this->logger->error("Cron Job \"Clean temp directory\" could not delete " . $file_match->getPath()
                    . "due to the following exception: " . $t->getMessage());
            }
        }

        // the folders are sorted based on their path length to ensure that nested folders are deleted first
        // thereby preventing any issues due to deletion attempts on no longer existing folders.
        $folders = $this->filesystem->finder()->in([""])->date($date)->directories()->sort(function (
            Metadata $a,
            Metadata $b
        ) {
            return strlen($a->getPath()) - strlen($b->getPath());
        })->reverseSorting();
        $deleted_folders = [];
        foreach ($folders as $folder_match) {
            try {
                if ($folder_match->isDir()) {
                    $this->filesystem->deleteDir($folder_match->getPath());
                    $deleted_folders[] = $folder_match;
                }
            } catch (Throwable $t) {
                $this->logger->error("Cron Job \"Clean temp directory\" could not delete " . $folder_match->getPath()
                    . "due to the following exception: " . $t->getMessage());
            }
        }

        $num_folders = count($deleted_folders);
        $num_files = count($deleted_files);

        $result = new ilCronJobResult();
        $result->setMessage($num_folders . " folders and " . $num_files . " files have been deleted.");
        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }
}
