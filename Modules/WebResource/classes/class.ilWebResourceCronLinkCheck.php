<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * This cron check links in web resources
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * @package ModulesWebResource
 */
class ilWebResourceCronLinkCheck extends ilCronJob
{
    public function getId()
    {
        return "webr_link_check";
    }
    
    public function getTitle()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        return $lng->txt("check_web_resources");
    }
    
    public function getDescription()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        return $lng->txt("check_web_resources_desc");
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

        $ilLog = $DIC->logger()->webr();
        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];
        
        $status = ilCronJobResult::STATUS_NO_ACTION;
    
        include_once'./Services/LinkChecker/classes/class.ilLinkChecker.php';

        $counter = 0;
        foreach (ilUtil::_getObjectsByOperations('webr', 'write', $ilUser->getId(), -1) as $node) {
            if (!is_object($tmp_webr = ilObjectFactory::getInstanceByRefId($node, false))) {
                continue;
            }

            $tmp_webr->initLinkResourceItemsObject();
            
            // Set all link to valid. After check invalid links will be set to invalid

            $link_checker = new ilLinkChecker($ilDB);
            $link_checker->setMailStatus(true);
            $link_checker->setCheckPeriod($this->__getCheckPeriod());
            $link_checker->setObjId($tmp_webr->getId());


            $tmp_webr->items_obj->updateValidByCheck($this->__getCheckPeriod());
            foreach ($link_checker->checkWebResourceLinks() as $invalid) {
                $tmp_webr->items_obj->readItem($invalid['page_id']);
                $tmp_webr->items_obj->setActiveStatus(false);
                $tmp_webr->items_obj->setValidStatus(false);
                $tmp_webr->items_obj->setDisableCheckStatus(true);
                $tmp_webr->items_obj->setLastCheckDate(time());
                $tmp_webr->items_obj->update(false);
            }
            
            $tmp_webr->items_obj->updateLastCheck($this->__getCheckPeriod());

            foreach ($link_checker->getLogMessages() as $message) {
                $ilLog->debug($message);
                $counter++;
            }
        }
    
        if ($counter) {
            $status = ilCronJobResult::STATUS_OK;
        }
        $result = new ilCronJobResult();
        $result->setStatus($status);
        return $result;
    }
    
    public function __getCheckPeriod()
    {
        switch ($this->getScheduleType()) {
            case self::SCHEDULE_TYPE_DAILY:
                $period = 24 * 60 * 60;
                break;

            case self::SCHEDULE_TYPE_WEEKLY:
                $period = 7 * 24 * 60 * 60;
                break;

            case self::SCHEDULE_TYPE_MONTHLY:
                $period = 30 * 7 * 24 * 60 * 60;
                break;

            case self::SCHEDULE_TYPE_QUARTERLY:
                $period = 3 * 30 * 7 * 24 * 60 * 60;
                break;

            default:
                $period = 0;
        }
        return $period;
    }
    
    public function activationWasToggled($a_currently_active)
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
                
        // propagate cron-job setting to object setting
        $ilSetting->set("cron_web_resource_check", (bool) $a_currently_active);
    }
}
