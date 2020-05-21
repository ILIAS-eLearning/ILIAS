<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilXapiStatementEvaluation
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilXapiStatementEvaluation
{
    /**
     * @var array
     */
    protected $resultStatusByXapiVerbMap = array(
        "http://adlnet.gov/expapi/verbs/completed" => "completed",
        "http://adlnet.gov/expapi/verbs/passed" => "passed",
        "http://adlnet.gov/expapi/verbs/failed" => "failed",
        "http://adlnet.gov/expapi/verbs/satisfied" => "passed"
    );
    
    /**
     * @var ilObjCmiXapi
     */
    protected $object;
    
    /**
     * @var ilLogger
     */
    protected $log;
    
    /**
     * ilXapiStatementEvaluation constructor.
     * @param ilLogger $log
     * @param ilObjCmiXapi $object
     */
    public function __construct(ilLogger $log, ilObjCmiXapi $object)
    {
        $this->log = $log;
        $this->object = $object;
        
        $objLP = ilObjectLP::getInstance($this->object->getId());
        $this->lpMode = $objLP->getCurrentMode();
    }
    
    public function evaluateReport(ilCmiXapiStatementsReport $report)
    {
        foreach ($report->getStatements() as $xapiStatement) {
            #$this->log->debug(
            #	"handle statement:\n".json_encode($xapiStatement, JSON_PRETTY_PRINT)
            #);
            
            // ensure json decoded non assoc
            $xapiStatement = json_decode(json_encode($xapiStatement));
            
            $cmixUser = ilCmiXapiUser::getInstanceByObjectIdAndUsrIdent(
                $this->object->getId(),
                str_replace('mailto:', '', $xapiStatement->actor->mbox)
            );
            
            $this->evaluateStatement($xapiStatement, $cmixUser->getUsrId());

            $this->log->debug('update lp for object (' . $this->object->getId() . ')');
            ilLPStatusWrapper::_updateStatus($this->object->getId(), $cmixUser->getUsrId());
        }
    }
    
    public function evaluateStatement($xapiStatement, $usrId)
    {
        if ($this->isValidXapiStatement($xapiStatement) && $this->hasResultStatusRelevantXapiVerb($xapiStatement)) {
            $xapiVerb = $this->getXapiVerb($xapiStatement);
            $this->log->debug("sniffing verb: " . $xapiVerb);
            
            if ($this->hasContextActivitiesParentNotEqualToObject($xapiStatement)) {
                $this->log->debug(
                    "no root context: " . $xapiStatement->object->id . " ...ignored verb " . $xapiVerb
                );
                
                return;
            }
            
            $userResult = $this->getUserResult($usrId);
            
            $oldResultStatus = $userResult->getStatus();
            $newResultStatus = $this->getResultStatusForXapiVerb($xapiVerb);
            
            if ($this->isResultStatusToBeReplaced($oldResultStatus, $newResultStatus)) {
                $this->log->debug("isResultStatusToBeReplaced: true");
                $userResult->setStatus($newResultStatus);
            }
            
            if ($this->hasXapiScore($xapiStatement)) {
                $xapiScore = $this->getXapiScore($xapiStatement);
                $this->log->debug("Score: " . $xapiScore);
                
                $userResult->setScore((float) $xapiScore);
            }
            
            $userResult->save();
        }
    }
    
    protected function isValidXapiStatement($xapiStatement)
    {
        if (!isset($xapiStatement->actor)) {
            return false;
        }
        
        if (!isset($xapiStatement->verb) || !isset($xapiStatement->verb->id)) {
            return false;
        }
        
        if (!isset($xapiStatement->object) || !isset($xapiStatement->object->id)) {
            return false;
        }
        
        return true;
    }

    protected function hasContextActivitiesParentNotEqualToObject($xapiStatement)
    {
        if (!isset($xapiStatement->context)) {
            return false;
        }
        
        if (!isset($xapiStatement->context->contextActivities)) {
            return false;
        }
        
        if (!isset($xapiStatement->context->contextActivities->parent)) {
            return false;
        }
        
        if ($xapiStatement->object->id != $xapiStatement->context->contextActivities->parent) {
            return true;
        }
        
        return false;
    }
    
    protected function getXapiVerb($xapiStatement)
    {
        return $xapiStatement->verb->id;
    }
    
    protected function getResultStatusForXapiVerb($xapiVerb)
    {
        return $this->resultStatusByXapiVerbMap[$xapiVerb];
    }
    
    protected function hasResultStatusRelevantXapiVerb($xapiStatement)
    {
        $xapiVerb = $this->getXapiVerb($xapiStatement);
        return isset($this->resultStatusByXapiVerbMap[$xapiVerb]);
    }
    
    protected function hasXapiScore($xapiStatement)
    {
        if (!isset($xapiStatement->result)) {
            return false;
        }
        
        if (!isset($xapiStatement->result->score)) {
            return false;
        }
        
        if (!isset($xapiStatement->result->score->scaled)) {
            return false;
        }
        
        return true;
    }
    
    protected function getXapiScore($xapiStatement)
    {
        return $xapiStatement->result->score->scaled;
    }
    
    protected function getUserResult($usrId)
    {
        try {
            $result = ilCmiXapiResult::getInstanceByObjIdAndUsrId($this->object->getId(), $usrId);
        } catch (ilCmiXapiException $e) {
            $result = ilCmiXapiResult::getEmptyInstance();
            $result->setObjId($this->object->getId());
            $result->setUsrId($usrId);
        }
        
        return $result;
    }
    
    protected function isResultStatusToBeReplaced($oldResultStatus, $newResultStatus)
    {
        if (!$this->isLpModeInterestedInResultStatus($newResultStatus)) {
            $this->log->debug("isLpModeInterestedInResultStatus: false");
            return false;
        }
        
        if (!$this->doesNewResultStatusDominateOldOne($oldResultStatus, $newResultStatus)) {
            $this->log->debug("doesNewResultStatusDominateOldOne: false");
            return false;
        }
        
        if ($this->needsAvoidFailedEvaluation($oldResultStatus, $newResultStatus)) {
            $this->log->debug("needsAvoidFailedEvaluation: false");
            return false;
        }
        
        return true;
    }
    
    protected function isLpModeInterestedInResultStatus($resultStatus)
    {
        if ($this->lpMode == ilLPObjSettings::LP_MODE_DEACTIVATED) {
            return true;
        }
        
        switch ($resultStatus) {
            case 'failed':
                
                return in_array($this->lpMode, [
                    ilLPObjSettings::LP_MODE_CMIX_COMPL_WITH_FAILED,
                    ilLPObjSettings::LP_MODE_CMIX_PASSED_WITH_FAILED,
                    ilLPObjSettings::LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED
                ]);
            
            case 'passed':
                
                return in_array($this->lpMode, [
                    ilLPObjSettings::LP_MODE_CMIX_PASSED,
                    ilLPObjSettings::LP_MODE_CMIX_PASSED_WITH_FAILED,
                    ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED,
                    ilLPObjSettings::LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED
                ]);
            
            case 'completed':
                
                return in_array($this->lpMode, [
                    ilLPObjSettings::LP_MODE_CMIX_COMPLETED,
                    ilLPObjSettings::LP_MODE_CMIX_COMPL_WITH_FAILED,
                    ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED,
                    ilLPObjSettings::LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED
                ]);
        }
        
        return false;
    }
    
    protected function doesNewResultStatusDominateOldOne($oldResultStatus, $newResultStatus)
    {
        if ($oldResultStatus == '') {
            return true;
        }
        
        if (in_array($newResultStatus, ['passed', 'failed'])) {
            return true;
        }
        
        if (!in_array($oldResultStatus, ['passed', 'failed'])) {
            return true;
        }
        
        return false;
    }
    
    protected function needsAvoidFailedEvaluation($oldResultStatus, $newResultStatus)
    {
        if (!$this->object->isKeepLpStatusEnabled()) {
            return false;
        }
        
        if ($newResultStatus != 'failed') {
            return false;
        }
        
        return $oldResultStatus == 'completed' || $oldResultStatus == 'passed';
    }
}
