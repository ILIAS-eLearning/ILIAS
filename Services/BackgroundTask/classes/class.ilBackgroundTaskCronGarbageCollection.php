<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Background task GC
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBackgroundTaskCronGarbageCollection extends ilCronJob
{
    public function getId()
    {
        return "bgtsk_gc";
    }
    
    public function getTitle()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $lng->loadLanguageModule("bgtask");
        return $lng->txt("bgtask_cron_gc_title");
    }
    
    public function getDescription()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $lng->loadLanguageModule("bgtask");
        return $lng->txt("bgtask_cron_gc_desc");
    }
    
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_DAILY;
    }
    
    public function getDefaultScheduleValue()
    {
        return;
    }
    
    public function hasAutoActivation()
    {
        return false;
    }
    
    public function hasFlexibleSchedule()
    {
        return true;
    }

    public function run()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $status = ilCronJobResult::STATUS_NO_ACTION;
        
        $cut = new ilDateTime(strtotime("-1day"), IL_CAL_UNIX);
        
        include_once "Services/BackgroundTask/classes/class.ilBackgroundTask.php";
    
        $set = $ilDB->query("SELECT id FROM " . ilBackgroundTask::DB_NAME .
            " WHERE start_date <= " . $ilDB->quote($cut->get(IL_CAL_DATETIME), "text"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $status = ilCronJobResult::STATUS_OK;
            
            $task = new ilBackgroundTask($row["id"]);
            $handler = $task->getHandlerInstance();
            $handler->deleteTaskAndFiles();
        }
    
        $result = new ilCronJobResult();
        $result->setStatus($status);
        
        return $result;
    }
}
