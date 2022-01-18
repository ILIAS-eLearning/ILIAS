<?php

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
    
    protected function initThisRunTS(): void
    {
        $this->thisRunTS = time();
    }
    
    protected function readLastRunTS(): void
    {
        $settings = new ilSetting('cmix');
        $this->lastRunTS = $settings->get(self::LAST_RUN_TS_SETTING_NAME, 0);
    }
    
    protected function writeThisAsLastRunTS(): void
    {
        $settings = new ilSetting('cmix');
        $settings->set(self::LAST_RUN_TS_SETTING_NAME, $this->thisRunTS);
    }
    
    public function getThisRunTS(): int
    {
        return $this->thisRunTS;
    }
    
    public function getLastRunTS(): int
    {
        return $this->lastRunTS;
    }
    
    public function getId() : string
    {
        return 'xapi_results_evaluation';
    }
    
    public function getTitle() : string
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        return $DIC->language()->txt("cron_xapi_results_evaluation");
    }
    
    public function getDescription() : string
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        return $DIC->language()->txt("cron_xapi_results_evaluation_desc");
    }
    
    public function hasAutoActivation() : bool
    {
        return false;
    }
    
    public function hasFlexibleSchedule() : bool
    {
        return true;
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
    
    protected function getXapiStatementsReport(ilObject $object, ilCmiXapiStatementsReportFilter $filter): \ilCmiXapiStatementsReport
    {
        $filter->setActivityId($object->getActivityId());
        
        $linkBuilder = new ilCmiXapiStatementsReportLinkBuilder(
            $object->getId(),
            $object->getLrsType()->getLrsEndpointStatementsAggregationLink(),
            $filter
        );
        
        $request = new ilCmiXapiStatementsReportRequest(
            $object->getLrsType()->getBasicAuth(),
            $linkBuilder
        );
        
        return $request->queryReport($object->getId());
    }
    
    protected function buildReportFilter(): \ilCmiXapiStatementsReportFilter
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
