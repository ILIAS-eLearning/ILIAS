<?php

use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\DI\Container;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilFileSystemCleanTempDirCron
 *
 * @author Lukas Zehnder <lz@studer-raimann.ch>
 */
class ilFileSystemCleanTempDirCron extends ilCronJob
{
    protected \ILIAS\Filesystem\Filesystem $filesystem;

    protected ilLanguage $language;

    protected ilLogger $logger;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        /**
         * @var $DIC Container
         */
        global $DIC;
        if ($DIC->offsetExists('lng')) {
            $this->language = $DIC['lng'];
        }
        if ($DIC->offsetExists('filesystem')) {
            $this->filesystem = $DIC->filesystem()->temp();
        }
        if ($DIC->offsetExists('ilLoggerFactory')) {
            $this->logger = $DIC->logger()->root();
        }
    }

    private function initDependencies() : void
    {
    }

    public function getId() : string
    {
        return "file_system_clean_temp_dir";
    }

    public function getTitle() : string
    {
        return $this->language->txt('file_system_clean_temp_dir_cron');
    }

    public function getDescription() : string
    {
        return $this->language->txt("file_system_clean_temp_dir_cron_info");
    }

    public function hasAutoActivation() : bool
    {
        return true;
    }

    public function hasFlexibleSchedule() : bool
    {
        return false;
    }

    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue() : ?int
    {
        return null;
    }

    public function run() : ilCronJobResult
    {
        $this->initDependencies();
        // only delete files and folders older than ten days to prevent issues with ongoing processes (e.g. zipping a folder)
        $date = "until 10 day ago";

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
        $folders = $this->filesystem->finder()->in([""])->date($date)->directories()->sort(fn (
            Metadata $a,
            Metadata $b
        ) : int => strlen($a->getPath()) - strlen($b->getPath()))->reverseSorting();
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
