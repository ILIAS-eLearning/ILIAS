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

        $files_for_deletion = $DIC->filesystem()->temp()->finder()->date("until 1 day ago");
    }
}