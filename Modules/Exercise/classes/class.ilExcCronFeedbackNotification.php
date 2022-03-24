<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
/**
 * Cron for exercise feedback notification
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcCronFeedbackNotification extends ilCronJob
{
    protected ilLanguage $lng;


    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    public function getId() : string
    {
        return "exc_feedback_notification";
    }
    
    public function getTitle() : string
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule("exc");
        return $lng->txt("exc_global_feedback_file_cron");
    }
    
    public function getDescription() : string
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule("exc");
        return $lng->txt("exc_global_feedback_file_cron_info");
    }
    
    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }
    
    public function getDefaultScheduleValue() : ?int
    {
        return null;
    }
    
    public function hasAutoActivation() : bool
    {
        return true;
    }
    
    public function hasFlexibleSchedule() : bool
    {
        return false;
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function run() : ilCronJobResult
    {
        $status = ilCronJobResult::STATUS_NO_ACTION;

        $count = 0;
        
        foreach (ilExAssignment::getPendingFeedbackNotifications() as $ass_id) {
            if (ilExAssignment::sendFeedbackNotifications($ass_id)) {
                $count++;
            }
        }
        
        if ($count !== 0) {
            $status = ilCronJobResult::STATUS_OK;
        }
        
        $result = new ilCronJobResult();
        $result->setStatus($status);
        
        return $result;
    }
}
