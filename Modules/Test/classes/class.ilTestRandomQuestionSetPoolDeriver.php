<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

reqiure_once 'Modules/Test/classes/class.ilTestRandomQuestionSetStagingPoolQuestionList.php';

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
	 * @var ilTestRandomQuestionSetNonAvailablePool
	 */
	protected $nonAvailablePool;
	
	/**
	 * @var integer
	 */
	protected $targetContainerRef;
	
	public function __construct(ilDBInterface $ilDB, ilPluginAdmin $pluginAdmin, ilObjTest $testOBJ)
	{
		$this->db = $ilDB;
		$this->pluginAdmin = $pluginAdmin;
		$this->testOBJ = $testOBJ;
	}
	
	/**
	 * @return ilTestRandomQuestionSetNonAvailablePool
	 */
	public function getNonAvailablePool()
	{
		return $this->nonAvailablePool;
	}
	
	/**
	 * @param ilTestRandomQuestionSetNonAvailablePool $nonAvailablePool
	 */
	public function setNonAvailablePool($nonAvailablePool)
	{
		$this->nonAvailablePool = $nonAvailablePool;
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
	
	protected function getQuestionsForPool()
	{
		$questionList = new ilTestRandomQuestionSetStagingPoolQuestionList(
			$this->db, $this->pluginAdmin
		);
		
		$questionList->setTestObjId($this->testOBJ->getId());
		$questionList->setTestId($this->testOBJ->getId());
		$questionList->setPoolId($this->getTestStagePoolId());
		
		$questionList->loadQuestions();
		
		return $questionList->getQuestions();
	}
	
	public function letTheDifferentlyThinkedShitRunning()
	{
		$pool = $this->createNewPool();
		$questions = $this->getQuestionsForPool($pool);
		$mapping = $this->copyQuestionsToPool($pool, $questions);
		$this->updateTestQuestionStage($pool, $mapping);
	}
}