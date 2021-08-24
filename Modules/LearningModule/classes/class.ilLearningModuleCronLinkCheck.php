<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * This cron check links in learning modules
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * @package ModulesLearningModule
 */
class ilLearningModuleCronLinkCheck extends ilCronJob
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var ilDB
     */
    protected $db;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->log = $DIC["ilLog"];
        $this->db = $DIC->database();
    }

    public function getId() : string
    {
        return "lm_link_check";
    }
    
    public function getTitle() : string
    {
        $lng = $this->lng;
        
        return $lng->txt("check_link");
    }
    
    public function getDescription() : string
    {
        $lng = $this->lng;
        
        return $lng->txt("check_link_desc");
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
        return false;
    }
    
    public function hasFlexibleSchedule() : bool
    {
        return false;
    }
    
    public function run() : ilCronJobResult
    {
        $ilLog = $this->log;
        $ilDB = $this->db;
        
        $status = ilCronJobResult::STATUS_NO_ACTION;
                
        $link_checker = new ilLinkChecker($ilDB);
        $link_checker->setMailStatus(true);

        $link_checker->checkLinks();
        
        $counter = 0;
        foreach ($link_checker->getLogMessages() as $message) {
            $ilLog->write($message);
            $counter++;
        }
    
        if ($counter) {
            $status = ilCronJobResult::STATUS_OK;
        }
        $result = new ilCronJobResult();
        $result->setStatus($status);
        return $result;
    }
}
