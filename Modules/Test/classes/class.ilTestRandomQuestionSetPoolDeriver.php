<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetStagingPoolQuestionList.php';
require_once 'Modules/TestQuestionPool/classes/class.ilQuestionPoolFactory.php';
require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
		
/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilTestRandomQuestionSetPoolDeriver
{
	/**
	 * @var ilDBInterface
	 */
	protected $db;
	
	/**
	 * @var ilPluginAdmin
	 */
	protected $pluginAdmin;
	
	/**
	 * @var ilObjTest
	 */
	protected $testOBJ;
	
	/**
	 * @var integer
	 */
	protected $targetContainerRef;
	
	/**
	 * @var integer
	 */
	protected $ownerId;
	
	/**
	 * @var ilQuestionPoolFactory
	 */
	protected $poolFactory;
	
	public function __construct(ilDBInterface $ilDB, ilPluginAdmin $pluginAdmin, ilObjTest $testOBJ)
	{
		$this->db = $ilDB;
		$this->pluginAdmin = $pluginAdmin;
		$this->testOBJ = $testOBJ;
		$this->poolFactory = new ilQuestionPoolFactory();
	}
		
	/**
	 * @return int
	 */
	public function getTargetContainerRef()
	{
		return $this->targetContainerRef;
	}
	
	/**
	 * @param int $targetContainerRef
	 */
	public function setTargetContainerRef($targetContainerRef)
	{
		$this->targetContainerRef = $targetContainerRef;
	}
	
	/**
	 * @return int
	 */
	public function getOwnerId()
	{
		return $this->ownerId;
	}
	
	/**
	 * @param int $ownerId
	 */
	public function setOwnerId($ownerId)
	{
		$this->ownerId = $ownerId;
	}
	
	protected function getQuestionsForPool(ilTestRandomQuestionSetNonAvailablePool $nonAvailablePool)
	{
		$questionList = new ilTestRandomQuestionSetStagingPoolQuestionList(
			$this->db, $this->pluginAdmin
		);
		
		$questionList->setTestObjId($this->testOBJ->getId());
		$questionList->setTestId($this->testOBJ->getTestId());
		$questionList->setPoolId($nonAvailablePool->getId());
		
		$questionList->loadQuestions();
		
		$questions = array();
		
		foreach($questionList as $questionId)
		{
			$questions[] = assQuestion::_instantiateQuestion($questionId);
		}
		
		return $questions;
	}
	
	protected function createNewPool(ilTestRandomQuestionSetNonAvailablePool $nonAvailablePool)
	{
		$pool = $this->poolFactory->createNewInstance($this->getTargetContainerRef());
		
		if( strlen($nonAvailablePool->getTitle()) )
		{
			$pool->setTitle($nonAvailablePool->getTitle());
			$pool->update();
		}
		
		return $pool;
	}
	
	protected function copyQuestionsToPool(ilObjQuestionPool $pool, $questions)
	{
		$poolQidByTestQidMap = array();
		
		foreach($questions as $questionOBJ)
		{
			/* @var assQuestion $questionOBJ */

			$testQuestionId = $questionOBJ->getId();
			$poolQuestionId = $questionOBJ->duplicate(false, '', '', $this->getOwnerId(), $pool->getId());
			
			$poolQidByTestQidMap[$testQuestionId] = $poolQuestionId;
		}
		
		return $poolQidByTestQidMap;
	}
	
	protected function updateTestQuestionStage($poolQidByTestQidMap)
	{
		foreach($poolQidByTestQidMap as $testQid => $poolQid)
		{
			assQuestion::resetOriginalId($poolQid);
			assQuestion::saveOriginalId($testQid, $poolQid);
		}
	}
	
	public function letTheDifferentlyThinkedShitRunning(ilTestRandomQuestionSetNonAvailablePool $nonAvailablePool)
	{
		$pool = $this->createNewPool($nonAvailablePool);
		$questions = $this->getQuestionsForPool($nonAvailablePool);
		
		$poolQidByTestQidMap = $this->copyQuestionsToPool($pool, $questions);
		
		$this->updateTestQuestionStage($poolQidByTestQidMap);
		
		return $pool->getId();
	}
}