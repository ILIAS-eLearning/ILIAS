<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
abstract class ilTestRandomQuestionSetBuilder
{
	/**
	 * @var ilDB
	 */
	protected $db = null;

	/**
	 * @var ilObjTest
	 */
	protected $testOBJ = null;

	/**
	 * @var ilTestRandomQuestionSetConfig
	 */
	protected $questionSetConfig = null;

	/**
	 * @var ilTestRandomQuestionSetSourcePoolDefinitionList
	 */
	protected $sourcePoolDefinitionList = null;

	/**
	 * @var ilTestRandomQuestionSetStagingPoolQuestionList
	 */
	protected $stagingPoolQuestionList = null;

	/**
	 * @param ilDB $db
	 * @param ilObjTest $testOBJ
	 * @param ilTestRandomQuestionSetConfig $questionSetConfig
	 * @param ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList
	 * @param ilTestRandomQuestionSetStagingPoolQuestionList $stagingPoolQuestionList
	 */
	protected function __construct(
		ilDB $db,
		ilObjTest $testOBJ,
		ilTestRandomQuestionSetConfig $questionSetConfig,
		ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList,
		ilTestRandomQuestionSetStagingPoolQuestionList $stagingPoolQuestionList
	)
	{
		$this->db = $db;
		$this->testOBJ = $testOBJ;
		$this->questionSetConfig = $questionSetConfig;
		$this->sourcePoolDefinitionList = $sourcePoolDefinitionList;
		$this->stagingPoolQuestionList = $stagingPoolQuestionList;
	}

	abstract public function checkBuildable();

	abstract public function performBuild(ilTestSession $testSession);


	protected function getQuestionStageForSourcePoolDefinitionList(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
	{
		$questionStage = array();

		foreach($sourcePoolDefinitionList as $definition)
		{
			/** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */

			$questions = $this->getQuestionStageForSourcePoolDefinition($definition);
			$questionStage = array_merge($questionStage, $questions);
		}

		return array_unique($questionStage);
	}

	protected function getQuestionStageForSourcePoolDefinition(ilTestRandomQuestionSetSourcePoolDefinition $definition)
	{
		$this->stagingPoolQuestionList->resetQuestionList();

		$this->stagingPoolQuestionList->setTestObjId( $this->testOBJ->getId() );
		$this->stagingPoolQuestionList->setTestId( $this->testOBJ->getTestId() );
		$this->stagingPoolQuestionList->setPoolId( $definition->getPoolId() );

		if( $this->hasTaxonomyFilter($definition) )
		{
			$this->stagingPoolQuestionList->addTaxonomyFilter(
				$definition->getMappedFilterTaxId(), array($definition->getMappedFilterTaxNodeId())
			);
		}

		$this->stagingPoolQuestionList->loadQuestions();

		return $this->stagingPoolQuestionList->getQuestions();
	}

	private function hasTaxonomyFilter(ilTestRandomQuestionSetSourcePoolDefinition $definition)
	{
		if( !(int)$definition->getMappedFilterTaxId() )
		{
			return false;
		}

		if( !(int)$definition->getMappedFilterTaxNodeId() )
		{
			return false;
		}

		return true;
	}

	protected function storeQuestionSet(ilTestSession $testSession, $questionSet)
	{
		foreach($questionSet as $sequencePosition => $questionId)
		{
			$this->storeQuestion($testSession, $questionId, $sequencePosition);
		}
	}

	private function storeQuestion(ilTestSession $testSession, $questionId, $sequencePosition)
	{
		$nextId = $this->db->nextId('tst_test_rnd_qst');

		$this->db->insert('tst_test_rnd_qst', array(
			'test_random_question_id' => array('integer', $nextId),
			'active_fi' => array('integer', $testSession->getActiveId()),
			'question_fi' => array('integer', $questionId),
			'sequence' => array('integer', $sequencePosition),
			'pass' => array('integer', $testSession->getPass()),
			'tstamp' => array('integer', time())
		));
	}

	protected function fetchQuestionsFromStageRandomly($questionStage, $requiredQuestionAmount)
	{
		$randomKeys = $this->getRandomArrayKeys($questionStage, $requiredQuestionAmount);

		$questionSet = array();

		foreach($randomKeys as $randomKey)
		{
			$questionSet[] = $questionStage[$randomKey];
		}

		if( $this->testOBJ->getShuffleQuestions() )
		{
			shuffle($questionSet);
		}

		return $questionSet;
	}

	private function getRandomArrayKeys($array, $numKeys)
	{
		if( $numKeys < 1 )
		{
			return array();
		}

		if( $numKeys > 1 )
		{
			return array_rand($array, $numKeys);
		}

		return array( array_rand($array, $numKeys) );
	}

	// =================================================================================================================

	final static public function getInstance(
		ilDB $db, ilObjTest $testOBJ, ilTestRandomQuestionSetConfig $questionSetConfig,
		ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList,
		ilTestRandomQuestionSetStagingPoolQuestionList $stagingPoolQuestionList
	)
	{
		if( $questionSetConfig->isQuestionAmountConfigurationModePerPool() )
		{
			require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetBuilderWithAmountPerPool.php';

			return new ilTestRandomQuestionSetBuilderWithAmountPerPool(
				$db, $testOBJ, $questionSetConfig, $sourcePoolDefinitionList, $stagingPoolQuestionList
			);
		}

		require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetBuilderWithAmountPerTest.php';

		return new ilTestRandomQuestionSetBuilderWithAmountPerTest(
			$db, $testOBJ, $questionSetConfig, $sourcePoolDefinitionList, $stagingPoolQuestionList
		);
	}
}