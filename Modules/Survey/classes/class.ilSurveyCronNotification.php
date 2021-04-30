<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Cron for survey notifications
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilSurveyCronNotification extends ilCronJob
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTree
     */
    protected $tree;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        if (isset($DIC["tree"])) {
            $this->tree = $DIC->repositoryTree();
        }
    }

    public function getId()
    {
        return "survey_notification";
    }
    
    public function getTitle()
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule("survey");
        return $lng->txt("survey_reminder_cron");
    }
    
    public function getDescription()
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule("survey");
        return $lng->txt("survey_reminder_cron_info");
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
        $tree = $this->tree;
        global $tree;

        $log = ilLoggerFactory::getLogger("svy");
        $log->debug("start");

        include_once "Modules/Survey/classes/class.ilObjSurvey.php";
        
        $status = ilCronJobResult::STATUS_NO_ACTION;
        $message = array();
                
        $tutor_res = ilObjSurvey::getSurveysWithTutorResults();

        $log->debug(var_export($tutor_res, true));
        
        $root = $tree->getNodeData(ROOT_FOLDER_ID);
        foreach ($tree->getSubTree($root, false, "svy") as $svy_ref_id) {
            $svy = new ilObjSurvey($svy_ref_id);
            $num = $svy->checkReminder();
            if ($num !== false) {
                $message[] = $svy_ref_id . "(" . $num . ")";
                $status = ilCronJobResult::STATUS_OK;
            }
            
            // separate cron-job?
            if (in_array($svy->getId(), $tutor_res)) {
                if ($svy->sendTutorResults()) {
                    $message[] = $svy_ref_id;
                    $status = ilCronJobResult::STATUS_OK;
                }
            }
        }
        
        $result = new ilCronJobResult();
        $result->setStatus($status);
        
        if (sizeof($message)) {
            $result->setMessage("Ref-Ids: " . implode(", ", $message));
            $result->setCode("#" . sizeof($message));
        }
        $log->debug("end");
        return $result;
    }
}
