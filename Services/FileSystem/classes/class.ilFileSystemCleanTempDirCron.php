<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Class ilFileSystemCleanTempDirCron
 * @author Lukas Zehnder <lz@studer-raimann.ch>
 */
class ilFileSystemCleanTempDirCron extends ilCronJob
{

    public function getId()
    {
        return "file_system_clean_temp_dir";
    }


    public function getTitle()
    {
        global $DIC;
        $lng = $DIC['lng'];

        return $lng->txt('file_system_clean_temp_dir_cron');
    }

    public function getDescription()
    {
        global $DIC;
        $lng = $DIC['lng'];

        return $lng->txt("file_system_clean_temp_dir_cron_info");
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
        global $DIC;

        $matches = $DIC->filesystem()->temp()->finder()->in([""]);
        // $matches = $DIC->filesystem()->temp()->finder()->in([""])->date("until 1 day ago");
        // iterate through matches that are older than one day and delete the corresponding folders and files from the temp directory.
        // also store the matches to be able to return the number of files and folders that were deleted.
        $folder_matches = [];
        $file_matches = [];
        foreach ($matches as $match) {
            try {
                if($match->isFile()) {
                    $file_matches[] = $match;
                    $DIC->filesystem()->temp()->delete($match->getPath());
                } elseif ($match->isDir()) {
                    $path = $match->getPath();
                    $folder_matches[] = $match;
                    if (!is_null($path) && $DIC->filesystem()->temp()->has($path)) {
                        $DIC->filesystem()->temp()->deleteDir(dirname($path));
                    }
                }
            } catch (Exception $exception) {
                $DIC->logger()->root()->error("Cron Job \"Clean temp directory\" could not delete " . $match->getPath()
                    . "due to the following exception: " . $exception->getMessage());
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