<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilXapiResultsCronjob
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilXapiResultsCronjob extends ilCronJob
{
    const LAST_RUN_TS_SETTING_NAME = 'cron_xapi_res_eval_last_run';
    
    /**
     * @var int
     */
    protected $thisRunTS;
    
    /**
     * @var int
     */
    protected $lastRunTS;
    
    /**
     * @var ilLogger
     */
    protected $log;
    
    public function __construct()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->language()->loadLanguageModule('cmix');
        
        $this->log = ilLoggerFactory::getLogger('cmix');
        
        $this->initThisRunTS();
        $this->readLastRunTS();
    }
    
    protected function initThisRunTS()
    {
        $this->thisRunTS = time();
    }
    
    protected function readLastRunTS()
    {
        $settings = new ilSetting('cmix');
        $this->lastRunTS = $settings->get(self::LAST_RUN_TS_SETTING_NAME, 0);
    }
    
    protected function writeThisAsLastRunTS()
    {
        $settings = new ilSetting('cmix');
        $settings->set(self::LAST_RUN_TS_SETTING_NAME, $this->thisRunTS);
    }
    
    public function getThisRunTS()
    {
        return $this->thisRunTS;
    }
    
    public function getLastRunTS()
    {
        return $this->lastRunTS;
    }
    
    public function getId()
    {
        return 'xapi_results_evaluation';
    }
    
    public function getTitle()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        return $DIC->language()->txt("cron_xapi_results_evaluation");
    }
    
    public function getDescription()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        return $DIC->language()->txt("cron_xapi_results_evaluation_desc");
    }
    
    public function hasAutoActivation()
    {
        return false;
    }
    
    public function hasFlexibleSchedule()
    {
        return true;
    }
    
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_DAILY;
    }
    
    public function getDefaultScheduleValue()
    {
        return;
    }
    
    public function hasCustomSettings()
    {
        return false;
    }
    
    public function run()
    {
        $objects = $this->getObjectsToBeReported();
        
        foreach ($objects as $objId) {
            $this->log->debug('handle object (' . $objId . ')');
            
            $filter = $this->buildReportFilter();
            
            $object = ilObjectFactory::getInstanceByObjId($objId, false);
            
            $evaluation = new ilXapiStatementEvaluation($this->log, $object);
            
            if ($object->getLaunchMode() != ilObjCmiXapi::LAUNCH_MODE_NORMAL) {
                $this->log->debug('skipped object due to launch mode (' . $objId . ')');
                continue;
            }
            
            $report = $this->getXapiStatementsReport($object, $filter);
            
            $evaluation->evaluateReport($report);
            
            //$this->log->debug('update lp for object (' . $objId . ')');
            //ilLPStatusWrapper::_refreshStatus($objId);
            
            $objectIds[] = $objId;
        }
        
        ilCmiXapiUser::updateFetchedUntilForObjects(
            new ilCmiXapiDateTime($this->getThisRunTS(), IL_CAL_UNIX),
            $objectIds
        );
        
        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_OK);
        
        $this->writeThisAsLastRunTS();
        return $result;
    }
    
    protected function getXapiStatementsReport(ilObjCmiXapi $object, ilCmiXapiStatementsReportFilter $filter)
    {
        $filter->setActivityId($object->getActivityId());
        
        $linkBuilder = new ilCmiXapiStatementsReportLinkBuilder(
            $object,
            $object->getLrsType()->getLrsEndpointStatementsAggregationLink(),
            $filter
        );
        
        $request = new ilCmiXapiStatementsReportRequest(
            $object->getLrsType()->getBasicAuth(),
            $linkBuilder
        );
        
        return $request->queryReport($object);
    }
    
    protected function buildReportFilter()
    {
        $filter = new ilCmiXapiStatementsReportFilter();
        
        $start = $end = null;
        
        if ($this->getLastRunTS()) {
            $filter->setStartDate(new ilCmiXapiDateTime($this->getLastRunTS(), IL_CAL_UNIX));
            $start = $filter->getStartDate()->get(IL_CAL_DATETIME);
        }

        $filter->setEndDate(new ilCmiXapiDateTime($this->getThisRunTS(), IL_CAL_UNIX));
        $end = $filter->getEndDate()->get(IL_CAL_DATETIME);
        
        $this->log->debug("use filter from ($start) until ($end)");
        
        return $filter;
    }
    
    /**
     * @return array
     */
    protected function getObjectsToBeReported() : array
    {
        $objects = array_unique(array_merge(
            ilCmiXapiUser::getCmixObjectsHavingUsersMissingProxySuccess(),
            ilObjCmiXapi::getObjectsHavingBypassProxyEnabledAndRegisteredUsers()
        ));
        
        return $objects;
    }
}
