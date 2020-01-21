<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLPStatusLTIConsumerAbstract
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
abstract class ilLPStatusCmiXapiAbstract extends ilLPStatus
{
    protected $cmixUserResult = array();
    
    private static $statusInfoCache = array();
    
    /**
     * @param $objId
     * @param $usrId
     * @return ilCmiXapiResult
     */
    public function getCmixUserResult($objId, $usrId)
    {
        if (!isset($this->cmixUserResult[$objId])) {
            $this->cmixUserResult[$objId] = array();
        }
        
        if (!isset($this->cmixUserResult[$objId][$usrId])) {
            try {
                $cmixUserResult = ilCmiXapiResult::getInstanceByObjIdAndUsrId($objId, $usrId);
                $this->cmixUserResult[$objId][$usrId] = $cmixUserResult;
            } catch (ilCmiXapiException $e) {
                $this->cmixUserResult[$objId][$usrId] = null;
            }
        }
        
        return $this->cmixUserResult[$objId][$usrId];
    }
    
    /**
     * @param $objId
     * @param $object
     * @return ilObjCmiXapi
     */
    protected function ensureObject($objId, $object = null)
    {
        if (!($object instanceof ilObjCmiXapi)) {
            $object = ilObjectFactory::getInstanceByObjId($objId);
        }
        
        return $object;
    }
    
    public static function _getNotAttempted($a_obj_id)
    {
        return self::getUserIdsByLpStatusNum(
            $a_obj_id,
            ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM
        );
    }
    
    public static function _getInProgress($a_obj_id)
    {
        return self::getUserIdsByLpStatusNum(
            $a_obj_id,
            ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
        );
    }
    
    public static function _getCompleted($a_obj_id)
    {
        return self::getUserIdsByLpStatusNum(
            $a_obj_id,
            ilLPStatus::LP_STATUS_COMPLETED_NUM
        );
    }
    
    public static function _getFailed($a_obj_id)
    {
        return self::getUserIdsByLpStatusNum(
            $a_obj_id,
            ilLPStatus::LP_STATUS_FAILED_NUM
        );
    }
    
    private static function getUserIdsByLpStatusNum($objId, $lpStatusNum)
    {
        $statusInfo = self::_getStatusInfo($objId);
        return $statusInfo[$lpStatusNum];
    }
    
    public static function _getStatusInfo($a_obj_id)
    {
        if (self::$statusInfoCache[$a_obj_id] === null) {
            self::$statusInfoCache[$a_obj_id] = self::loadStatusInfo($a_obj_id);
        }
        
        return self::$statusInfoCache[$a_obj_id];
    }
    
    private static function loadStatusInfo($a_obj_id)
    {
        $statusInfo = [
            ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM => [],
            ilLPStatus::LP_STATUS_IN_PROGRESS_NUM => [],
            ilLPStatus::LP_STATUS_COMPLETED_NUM => [],
            ilLPStatus::LP_STATUS_FAILED_NUM => []
        ];
        
        $cmixUsers = ilCmiXapiUser::getUsersForObject($a_obj_id);
        $userResults = ilCmiXapiResult::getResultsForObject($a_obj_id);
        
        foreach ($cmixUsers as $cmixUser) {
            $status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
            
            if (isset($userResults[$cmixUser->getUsrId()])) {
                $userResult = $userResults[$cmixUser->getUsrId()];
                
                if (self::_resultSatisfyCompleted($userResult, $a_obj_id)) {
                    $status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
                }
                
                if (self::_resultSatisfyFailed($userResult, $a_obj_id)) {
                    $status = ilLPStatus::LP_STATUS_FAILED_NUM;
                }
            }
            
            $statusInfo[$status][] = $cmixUser->getUsrId();
        }
        
        return $statusInfo;
    }
    
    public function determineStatus($a_obj_id, $a_usr_id, $a_obj = null)
    {
        $cmixUserResult = $this->getCmixUserResult($a_obj_id, $a_usr_id);
        
        if ($cmixUserResult instanceof ilCmiXapiResult) {
            if ($this->resultSatisfyCompleted($cmixUserResult)) {
                return self::LP_STATUS_COMPLETED_NUM;
            }
            
            if ($this->resultSatisfyFailed($cmixUserResult)) {
                return self::LP_STATUS_FAILED_NUM;
            }
            
            return self::LP_STATUS_IN_PROGRESS_NUM;
        }
        
        if (ilCmiXapiUser::exists($a_obj_id, $a_usr_id)) {
            return self::LP_STATUS_IN_PROGRESS_NUM;
        }
        
        return self::LP_STATUS_NOT_ATTEMPTED_NUM;
    }
    
    public function determinePercentage($a_obj_id, $a_usr_id, $a_obj = null)
    {
        $cmixResult = $this->getCmixUserResult($a_obj_id, $a_usr_id);
        
        if ($cmixResult instanceof ilCmiXapiResult) {
            return 100 * (float) $cmixResult->getScore();
        }
        
        return 0;
    }
    
    /**
     * @param ilCmiXapiResult $result
     * @return bool
     */
    abstract protected function resultSatisfyCompleted(ilCmiXapiResult $result);
    
    /**
     * @param ilObjCmiXapi $object
     * @param ilCmiXapiResult $result
     * @return bool
     */
    protected static function _resultSatisfyCompleted(ilCmiXapiResult $result, $a_obj_id)
    {
        $lpStatusDetermination = new static($a_obj_id);
        return $lpStatusDetermination->resultSatisfyCompleted($result);
    }
    
    /**
     * @param ilCmiXapiResult $result
     * @return bool
     */
    abstract protected function resultSatisfyFailed(ilCmiXapiResult $result);
    
    /**
     * @param ilObjCmiXapi $object
     * @param ilCmiXapiResult $result
     * @return bool
     */
    protected static function _resultSatisfyFailed(ilCmiXapiResult $result, $a_obj_id)
    {
        $lpStatusDetermination = new static($a_obj_id);
        return $lpStatusDetermination->resultSatisfyFailed($result);
    }
}
