<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetQuestionCollection.php';

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
		$questionStage = new ilTestRandomQuestionSetQuestionCollection();

		foreach($sourcePoolDefinitionList as $definition)
		{
			/** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */

			$questions = $this->getQuestionStageForSourcePoolDefinition($definition);
			$questionStage->mergeQuestionCollection($questions);
		}

		return $questionStage->getUniqueQuestionCollection();
	}

	protected function getQuestionStageForSourcePoolDefinition(ilTestRandomQuestionSetSourcePoolDefinition $definition)
	{
		$questionIds = $this->getQuestionIdsForSourcePoolDefinitionIds($definition);
		$questionStage = $this->buildSetQuestionCollection($definition, $questionIds);

		return $questionStage;
	}

	private function getQuestionIdsForSourcePoolDefinitionIds(ilTestRandomQuestionSetSourcePoolDefinition $definition)
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

	private function buildSetQuestionCollection(ilTestRandomQuestionSetSourcePoolDefinition $definition, $questionIds)
	{
		$setQuestionCollection = new ilTestRandomQuestionSetQuestionCollection();

		foreach($questionIds as $questionId)
		{
			$setQuestion = new ilTestRandomQuestionSetQuestion();

			$setQuestion->setQuestionId($questionId);
			$setQuestion->setSourcePoolDefinitionId($definition->getId());

			$setQuestionCollection->addQuestion($setQuestion);
		}

		return $setQuestionCollection;
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
		$position = 0;

		foreach($questionSet->getQuestions() as $setQuestion)
		{
			/* @var ilTestRandomQuestionSetQuestion $setQuestion */

			$setQuestion->setSequencePosition($position++);

			$this->storeQuestion($testSession, $setQuestion);
		}
	}

	private function storeQuestion(ilTestSession $testSession, ilTestRandomQuestionSetQuestion $setQuestion)
	{
		$nextId = $this->db->nextId('tst_test_rnd_qst');

		$this->db->insert('tst_test_rnd_qst', array(
			'test_random_question_id' => array('integer', $nextId),
			'active_fi' => array('integer', $testSession->getActiveId()),
			'question_fi' => array('integer', $setQuestion->getQuestionId()),
			'sequence' => array('integer', $setQuestion->getSequencePosition()),
			'pass' => array('integer', $testSession->getPass()),
			'tstamp' => array('integer', time()),
			'src_pool_def_fi' => array('integer', $setQuestion->getSourcePoolDefinitionId())
		));
	}

	protected function fetchQuestionsFromStageRandomly(ilTestRandomQuestionSetQuestionCollection $questionStage, $requiredQuestionAmount)
	{
		$questionSet = $questionStage->getRandomQuestionCollection($requiredQuestionAmount);

		return $questionSet;
	}

	protected function handleQuestionOrdering(ilTestRandomQuestionSetQuestionCollection $questionSet)
	{
		if( $this->testOBJ->getShuffleQuestions() )
		{
			$questionSet->shuffleQuestions();
		}
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