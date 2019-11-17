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
	
	/**
	 * @param $objId
	 * @param $usrId
	 * @return ilCmiXapiResult
	 */
	public function getCmixUserResult($objId, $usrId)
	{
		if( !isset($this->cmixUserResult[$objId]) )
		{
			$this->cmixUserResult[$objId] = array();
		}
		
		if( !isset($this->cmixUserResult[$objId][$usrId]) )
		{
			try
			{
				$cmixUserResult = ilCmiXapiResult::getInstanceByObjIdAndUsrId($objId, $usrId);
				$this->cmixUserResult[$objId][$usrId] = $cmixUserResult;
			}
			catch(ilCmiXapiException $e)
			{
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
		if( !($object instanceof ilObjCmiXapi) )
		{
			$object = ilObjectFactory::getInstanceByObjId($objId);
		}
		
		return $object;
	}
	
	public function determineStatus($a_obj_id, $a_usr_id, $a_obj = null)
	{
		$cmixUserResult = $this->getCmixUserResult($a_obj_id, $a_usr_id);
		
		if( $cmixUserResult instanceof ilCmiXapiResult )
		{
			if( $this->resultSatisfyCompleted($cmixUserResult) )
			{
				return self::LP_STATUS_COMPLETED_NUM;
			}
			
			if( $this->resultSatisfyFailed($cmixUserResult) )
			{
				return self::LP_STATUS_FAILED_NUM;
			}
			
			return self::LP_STATUS_IN_PROGRESS_NUM;
		}
		
		if( ilCmiXapiUser::exists($a_obj_id, $a_usr_id) )
		{
			return self::LP_STATUS_IN_PROGRESS_NUM;
		}
		
		return self::LP_STATUS_NOT_ATTEMPTED_NUM;
	}
	
	public function determinePercentage($a_obj_id, $a_usr_id, $a_obj = null)
	{
		$cmixResult = $this->getCmixUserResult($a_obj_id, $a_usr_id);
		
		if( $cmixResult instanceof ilLTIConsumerResult )
		{
			return 100 * (float)$cmixResult->getScore();
		}
		
		return 0;
	}
	
	/**
	 * @param ilCmiXapiResult $result
	 * @return bool
	 */
	abstract protected function resultSatisfyCompleted(ilCmiXapiResult $result);
	
	/**
	 * @param ilCmiXapiResult $result
	 * @return bool
	 */
	abstract protected function resultSatisfyFailed(ilCmiXapiResult $result);
}
