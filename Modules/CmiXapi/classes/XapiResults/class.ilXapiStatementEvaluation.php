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
	protected $resultStatusByXapiVerbMap = array (
		"http://adlnet.gov/expapi/verbs/completed" => "completed",
		"http://adlnet.gov/expapi/verbs/passed" => "passed",
		"http://adlnet.gov/expapi/verbs/failed" => "failed",
		"http://adlnet.gov/expapi/verbs/satisfied" => "passed"
	);
	
	/**
	 * @var int
	 */
	protected $objId;
	
	/**
	 * @var ilLogger
	 */
	protected $log;
	
	/**
	 * ilXapiStatementEvaluation constructor.
	 * @param ilLogger $log
	 * @param int $objId
	 * @param int $usrId
	 */
	public function __construct(ilLogger $log, int $objId)
	{
		$this->log = $log;
		$this->objId = $objId;
	}
	
	public function evaluateReport(ilCmiXapiStatementsReport $report)
	{
		foreach($report->getStatements() as $xapiStatement)
		{
			#$this->log->debug(
			#	"handle statement:\n".json_encode($xapiStatement, JSON_PRETTY_PRINT)
			#);
			
			// ensure json decoded non assoc
			$xapiStatement = json_decode(json_encode($xapiStatement));
			
			$cmixUser = ilCmiXapiUser::getInstanceByObjectIdAndUsrIdent(
				$this->objId, str_replace('mailto:', '', $xapiStatement->actor->mbox)
			);
			
			$this->evaluateStatement($xapiStatement, $cmixUser->getUsrId());
		}
	}
	
	public function evaluateStatement($xapiStatement, $usrId)
	{
		if( $this->isValidXapiStatement($xapiStatement) && $this->hasResultStatusRelevantXapiVerb($xapiStatement) )
		{
			$xapiVerb = $this->getXapiVerb($xapiStatement);
			$this->log->debug("sniffing verb: " . $xapiVerb);
			
			if( $this->hasContextActivitiesParentNotEqualToObject($xapiStatement) )
			{
				$this->log->debug(
					"no root context: " . $xapiStatement->object->id . " ...ignored verb " . $xapiVerb
				);
				
				return;
			}
			
			$userResult = $this->getUserResult($usrId);

			$userResult->setStatus(
				$this->getResultStatusForXapiVerb($xapiVerb)
			);
			
			if( $this->hasXapiScore($xapiStatement) )
			{
				$xapiScore = $this->getXapiScore($xapiStatement);
				$this->log->info("Score: " . $xapiScore);
				
				$userResult->setScore((float)$xapiScore);
			}
			
			$userResult->save();
		}
	}
	
	protected function isValidXapiStatement($xapiStatement)
	{
		if( !isset($xapiStatement->actor) )
		{
			return false;
		}
		
		if( !isset($xapiStatement->verb) || !isset($xapiStatement->verb->id) )
		{
			return false;
		}
		
		if( !isset($xapiStatement->object) || !isset($xapiStatement->object->id) )
		{
			return false;
		}
		
		return true;
	}

	protected function hasContextActivitiesParentNotEqualToObject($xapiStatement)
	{
		if( !isset($xapiStatement->context) )
		{
			return false;
		}
		
		if( !isset($xapiStatement->context->contextActivities) )
		{
			return false;
		}
		
		if( !isset($xapiStatement->context->contextActivities->parent) )
		{
			return false;
		}
		
		if( $xapiStatement->object->id != $xapiStatement->context->contextActivities->parent )
		{
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
		if( !isset($xapiStatement->result) )
		{
			return false;
		}
		
		if( !isset($xapiStatement->result->score) )
		{
			return false;
		}
		
		if( !isset($xapiStatement->result->score->scaled) )
		{
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
		try
		{
			$result = ilCmiXapiResult::getInstanceByObjIdAndUsrId($this->objId, $usrId);
		}
		catch(ilCmiXapiException $e)
		{
			$result = ilCmiXapiResult::getEmptyInstance();
			$result->setObjId($this->objId);
			$result->setUsrId($usrId);
		}
		
		return $result;
	}
}
