<?php declare(strict_types=1);

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
 * Class ilLPStatusLTIConsumerAbstract
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
abstract class ilLPStatusCmiXapiAbstract extends ilLPStatus
{
    protected array $cmixUserResult = array();
    
    private static array $statusInfoCache = array();

    public function getCmixUserResult(int $objId, int $usrId) : \ilCmiXapiResult
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
     * @return bool|ilObjCmiXapi|ilObject|mixed|null
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    protected function ensureObject(int $objId, ?ilObject $object = null)
    {
        if (!($object instanceof ilObjCmiXapi)) {
            $object = ilObjectFactory::getInstanceByObjId($objId);
        }
        
        return $object;
    }

    /**
     * @return array|int[]
     */
    public static function _getNotAttempted(int $a_obj_id) : array
    {
        return self::getUserIdsByLpStatusNum(
            $a_obj_id,
            ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM
        );
    }

    /**
     * @return array|int[]
     */
    public static function _getInProgress(int $a_obj_id) : array
    {
        return self::getUserIdsByLpStatusNum(
            $a_obj_id,
            ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
        );
    }

    public static function _getCompleted(int $a_obj_id) : array
    {
        return self::getUserIdsByLpStatusNum(
            $a_obj_id,
            ilLPStatus::LP_STATUS_COMPLETED_NUM
        );
    }

    /**
     * @return array|int[]
     */
    public static function _getFailed(int $a_obj_id) : array
    {
        return self::getUserIdsByLpStatusNum(
            $a_obj_id,
            ilLPStatus::LP_STATUS_FAILED_NUM
        );
    }
    
    private static function getUserIdsByLpStatusNum(int $objId, int $lpStatusNum) : array
    {
        $statusInfo = self::_getStatusInfo($objId);
        return $statusInfo[$lpStatusNum];
    }

    /**
     * @return array|array[]|int[][]
     */
    public static function _getStatusInfo(int $a_obj_id) : array
    {
        if (self::$statusInfoCache[$a_obj_id] === null) {
            self::$statusInfoCache[$a_obj_id] = self::loadStatusInfo($a_obj_id);
        }
        
        return self::$statusInfoCache[$a_obj_id];
    }

    /**
     * @return array<int, int[]>
     */
    private static function loadStatusInfo(int $a_obj_id) : array
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

    /**
     * @param object|null $a_obj
     */
    public function determineStatus(int $a_obj_id, int $a_usr_id, object $a_obj = null) : int
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

    public function determinePercentage(int $a_obj_id, int $a_usr_id, ?object $a_obj = null) : int
    {
        $cmixResult = $this->getCmixUserResult($a_obj_id, $a_usr_id);
        return (int) round((100 * $cmixResult->getScore()));
    }

    abstract protected function resultSatisfyCompleted(ilCmiXapiResult $result) : bool;

    protected static function _resultSatisfyCompleted(ilCmiXapiResult $result, int $a_obj_id) : bool
    {
        $lpStatusDetermination = new static($a_obj_id);
        return $lpStatusDetermination->resultSatisfyCompleted($result);
    }

    abstract protected function resultSatisfyFailed(ilCmiXapiResult $result) : bool;

    protected static function _resultSatisfyFailed(ilCmiXapiResult $result, int $a_obj_id) : bool
    {
        $lpStatusDetermination = new static($a_obj_id);
        return $lpStatusDetermination->resultSatisfyFailed($result);
    }
}
