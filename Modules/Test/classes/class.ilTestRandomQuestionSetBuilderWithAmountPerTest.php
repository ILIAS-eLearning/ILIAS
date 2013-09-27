<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetBuilder.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestRandomQuestionSetBuilderWithAmountPerTest extends ilTestRandomQuestionSetBuilder
{
	private $questionStage = array();

	private $questionSet = array();

	public function checkBuildable()
	{
		$this->buildQuestionStage();

		return $this->isQuestionSetFetchable();
	}

	public function performBuild(ilTestSession $testSession)
	{
		$this->buildQuestionStage();
		$this->fetchQuestionSet();
		$this->storeQuestionSet($testSession);
	}

	private function buildQuestionStage()
	{
		foreach($this->sourcePoolDefinitionList as $definition)
		{
			/** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */

			$this->stagingPoolQuestionList->resetQuestions();

			$this->stagingPoolQuestionList->setTestId( $this->testOBJ->getTestId() );
			$this->stagingPoolQuestionList->setPoolId( $definition->getPoolId() );

			if( $this->hasTaxonomyFilter($definition) )
			{
				$this->stagingPoolQuestionList->addTaxonomyFilter(
					$definition->getMappedFilterTaxId(), array($definition->getMappedFilterTaxNodeId())
				);
			}

			$this->stagingPoolQuestionList->loadQuestions();

			$this->questionStage = array_merge(
				$this->questionStage, $this->stagingPoolQuestionList->getQuestions()
			);
		}

		array_unique($this->questionStage);
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

	private function isQuestionSetFetchable()
	{
		$requiredAmount = $this->questionSetConfig->getQuestionAmountPerTest();
		$possibleAmount = count($this->questionStage);

		return ( $possibleAmount >= $requiredAmount );
	}

	private function fetchQuestionSet()
	{
		$requiredAmount = $this->questionSetConfig->getQuestionAmountPerTest();

		$randomKeys = array_rand($this->questionStage, $requiredAmount);

		foreach($randomKeys as $randomKey)
		{
			$this->questionSet[] = $this->questionStage[$randomKey];
		}

		if( $this->testOBJ->getShuffleQuestions() )
		{
			shuffle($this->questionSet);
		}
	}

	private function storeQuestionSet(ilTestSession $testSession)
	{
		foreach($this->questionSet as $sequencePosition => $questionId)
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
}