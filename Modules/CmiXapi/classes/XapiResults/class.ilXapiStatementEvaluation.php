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
     * http://adlnet.gov/expapi/verbs/satisfied: should never be sent by AU
     * https://github.com/AICC/CMI-5_Spec_Current/blob/quartz/cmi5_spec.md#939-satisfied
     */
    protected $resultStatusByXapiVerbMap = array(
        ilCmiXapiVerbList::COMPLETED => "completed",
        ilCmiXapiVerbList::PASSED => "passed",
        ilCmiXapiVerbList::FAILED => "failed",
        ilCmiXapiVerbList::SATISFIED => "passed"
    );

    protected $resultProgressByXapiVerbMap = array(
        ilCmiXapiVerbList::PROGRESSED => "progressed",
        ilCmiXapiVerbList::EXPERIENCED => "experienced"
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
            $cmixUser = $this->getCmixUser($xapiStatement);

            $this->evaluateStatement($xapiStatement, $cmixUser->getUsrId());

            $this->log->debug('update lp for object (' . $this->object->getId() . ')');
            ilLPStatusWrapper::_updateStatus($this->object->getId(), $cmixUser->getUsrId());
        }
    }
    
    public function getCmixUser($xapiStatement)
    {
        $cmixUser = null;
        if ($this->object->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5)
        {
            $cmixUser = ilCmiXapiUser::getInstanceByObjectIdAndUsrIdent(
                $this->object->getId(),
                $xapiStatement->actor->account->name
            );
        }
        else
        {
            $cmixUser = ilCmiXapiUser::getInstanceByObjectIdAndUsrIdent(
                $this->object->getId(),
                str_replace('mailto:', '', $xapiStatement->actor->mbox)
            );
        }
        return $cmixUser;
    }

    public function evaluateStatement($xapiStatement, $usrId)
    {
        global $DIC;
        $xapiVerb = $this->getXapiVerb($xapiStatement);      
        
        if ($this->isValidXapiStatement($xapiStatement))
        {
            // result status and if exists scaled score
            if ($this->hasResultStatusRelevantXapiVerb($xapiVerb))
            {
                if (!$this->isValidObject($xapiStatement))
                {
                    return;
                }
                $userResult = $this->getUserResult($usrId);
                
                $oldResultStatus = $userResult->getStatus();
                $newResultStatus = $this->getResultStatusForXapiVerb($xapiVerb);

                // this is for both xapi and cmi5
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

                // only cmi5
                if ($this->object->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5) 
                {
                    if (($xapiVerb == ilCmiXapiVerbList::COMPLETED || $xapiVerb == ilCmiXapiVerbList::PASSED) && $this->isLpModeInterestedInResultStatus($newResultStatus,false)) 
                    {
                        // it is possible to check against authToken usrId!
                        $cmixUser = $this->getCmixUser($xapiStatement);
                        $cmixUser->setSatisfied(true);
                        $cmixUser->save();
                        $this->sendSatisfiedStatement($cmixUser);
                    }
                }
            }
            // result progress (i think only cmi5 relevant)
            if ($this->hasResultProgressRelevantXapiVerb($xapiVerb))
            {
                $userResult = $this->getUserResult($usrId);
                $progressedScore = $this->getProgressedScore($xapiStatement);
                if ($progressedScore !== false && (float) $progressedScore > 0)
                {
                    $userResult->setScore((float) ($progressedScore / 100));
                    $userResult->save();
                }
            }
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

    /**
     * 
     */
    protected function isValidObject($xapiStatement)
    {
        if ($xapiStatement->object->id != $this->object->getActivityId())
        {
            $this->log->debug($xapiStatement->object->id . " != " . $this->object->getActivityId());
            return false;
        }
        return true;
    }

    
    protected function getXapiVerb($xapiStatement)
    {
        return $xapiStatement->verb->id;
    }
    
    protected function getResultStatusForXapiVerb($xapiVerb)
    {
        return $this->resultStatusByXapiVerbMap[$xapiVerb];
    }
    
    protected function hasResultStatusRelevantXapiVerb($xapiVerb)
    {
        return isset($this->resultStatusByXapiVerbMap[$xapiVerb]);
    }
    
    protected function getResultProgressForXapiVerb($xapiVerb)
    {
        return $this->resultProgressByXapiVerbMap[$xapiVerb];
    }

    protected function hasResultProgressRelevantXapiVerb($xapiVerb)
    {
        return isset($this->resultProgressByXapiVerbMap[$xapiVerb]);
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
    
    protected function getProgressedScore($xapiStatement)
    {
        if (!isset($xapiStatement->result)) {
            return false;
        }
        
        if (!isset($xapiStatement->result->extensions)) {
            return false;
        }
        
        if (!isset($xapiStatement->result->extensions->{'https://w3id.org/xapi/cmi5/result/extensions/progress'})) {
            return false;
        }
        return $xapiStatement->result->extensions->{'https://w3id.org/xapi/cmi5/result/extensions/progress'};
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
    
    protected function isLpModeInterestedInResultStatus($resultStatus, $deactivated=true)
    {
        if ($this->lpMode == ilLPObjSettings::LP_MODE_DEACTIVATED) {
            return $deactivated;
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
        if ($oldResultStatus == '' ) {
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

    protected function sendSatisfiedStatement($cmixUser)
    {
        global $DIC;
        
        $lrsType = $this->object->getLrsType();
        $defaultLrs = $lrsType->getLrsEndpoint();
        //$fallbackLrs = $lrsType->getLrsFallbackEndpoint();
        $defaultBasicAuth = $lrsType->getBasicAuth();
        //$fallbackBasicAuth = $lrsType->getFallbackBasicAuth();
        $defaultHeaders = [
            'X-Experience-API-Version' => '1.0.3',
            'Authorization' => $defaultBasicAuth,
            'Content-Type' => 'application/json;charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate'
        ];
        /*
        $fallbackHeaders = [
            'X-Experience-API-Version' => '1.0.3',
            'Authorization' => $fallbackBasicAuth,
            'Content-Type' => 'application/json;charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate'
        ];
        */
        $satisfiedStatement = $this->object->getSatisfiedStatement($cmixUser);
        $satisfiedStatementParams = [];
        $satisfiedStatementParams['statementId'] = $satisfiedStatement['id'];
        $defaultStatementsUrl = $defaultLrs . "/statements";
        $defaultSatisfiedStatementUrl = $defaultStatementsUrl . '?' .  ilCmiXapiAbstractRequest::buildQuery($satisfiedStatementParams);
        
        $client = new GuzzleHttp\Client();
        $req_opts = array(
            GuzzleHttp\RequestOptions::VERIFY => true,
            GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 10,
            GuzzleHttp\RequestOptions::HTTP_ERRORS => false
        );
        
        $defaultSatisfiedStatementRequest = new GuzzleHttp\Psr7\Request(
            'PUT',
            $defaultSatisfiedStatementUrl,
            $defaultHeaders,
            json_encode($satisfiedStatement)
        );
        $promises = array();
        $promises['defaultSatisfiedStatement'] = $client->sendAsync($defaultSatisfiedStatementRequest, $req_opts);
        try
        {
            $responses = GuzzleHttp\Promise\settle($promises)->wait();
            $body = '';
            ilCmiXapiAbstractRequest::checkResponse($responses['defaultSatisfiedStatement'],$body,[204]);
        }
        catch(Exception $e)
        {
            $this->log->error('error:' . $e->getMessage());
        }
    }
}
