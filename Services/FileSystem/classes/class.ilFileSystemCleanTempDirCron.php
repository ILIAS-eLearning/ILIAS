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
        $date = "until 1 sec ago";

        // $files = $DIC->filesystem()->temp()->finder()->in([""])->date("until 1 day ago")->files();
        // iterate through matches that are older than one day and delete the corresponding files from the temp directory.
        // also store the matches to be able to return the number of files that were deleted.

        $files = $this->filesystem->finder()->in([""])->date($date)->files();
        $file_matches = [];
        foreach ($files as $match) {
            try {
                if ($match->isFile()) {
                    $file_matches[] = $match;
                    $this->filesystem->delete($match->getPath());
                }
            } catch (Throwable $t) {
                $this->logger->error("Cron Job \"Clean temp directory\" could not delete " . $match->getPath()
                    . "due to the following exception: " . $t->getMessage());
            }
        }
        // $folders = $DIC->filesystem()->temp()->finder()->in([""])->date("until 1 day ago")->directories();
        // iterate through matches that are older than one day and delete the corresponding folders from the temp directory.
        // also store the matches to be able to return the number and folders that were deleted.
        $folders = $this->filesystem->finder()->in([""])->date($date)->directories()->sort(function (
            Metadata $a,
            Metadata $b
        ) {
            return strlen($a->getPath()) - strlen($b->getPath());
        })->reverseSorting();
        $folder_matches = [];
        foreach ($folders as $match) {
            try {
                if ($match->isDir()) {
                    $folder_matches[] = $match;
                    $this->filesystem->deleteDir($match->getPath());
                }
            } catch (Throwable $t) {
                $this->logger->error("Cron Job \"Clean temp directory\" could not delete " . $match->getPath()
                    . "due to the following exception: " . $t->getMessage());
            }
        }

        $num_folders = count($folder_matches);
        $num_files = count($file_matches);

        $result = new ilCronJobResult();
        $result->setMessage($num_folders . " folders and " . $num_files . " files have been deleted.");
        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }
}
