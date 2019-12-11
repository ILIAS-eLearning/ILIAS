<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Cron for exercise feedback notification
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExcCronFeedbackNotification extends ilCronJob
{
    /**
     * @var ilLanguage
     */
    protected $lng;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    public function getId()
    {
        return "exc_feedback_notification";
    }
    
    public function getTitle()
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule("exc");
        return $lng->txt("exc_global_feedback_file_cron");
    }
    
    public function getDescription()
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule("exc");
        return $lng->txt("exc_global_feedback_file_cron_info");
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
        return true;
    }
    
    public function hasFlexibleSchedule()
    {
        return false;
    }
    
    public function run()
    {
        $status = ilCronJobResult::STATUS_NO_ACTION;
        $message = array();
        
        $count = 0;
        
        include_once "Modules/Exercise/classes/class.ilExAssignment.php";
        foreach (ilExAssignment::getPendingFeedbackNotifications() as $ass_id) {
            if (ilExAssignment::sendFeedbackNotifications($ass_id)) {
                $count++;
            }
        }
        
        if ($count) {
            $status = ilCronJobResult::STATUS_OK;
        }
        
        $result = new ilCronJobResult();
        $result->setStatus($status);
        
        return $result;
    }
}
