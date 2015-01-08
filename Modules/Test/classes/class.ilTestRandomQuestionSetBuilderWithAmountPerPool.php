<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetBuilder.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestRandomQuestionSetBuilderWithAmountPerPool extends ilTestRandomQuestionSetBuilder
{
	public function checkBuildable()
	{
		$questionStage = $this->getQuestionStageForSourcePoolDefinitionList($this->sourcePoolDefinitionList);
		return $this->isQuestionSetFetchable($this->sourcePoolDefinitionList, $questionStage);
	}

	private function isQuestionSetFetchable(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList, $questionStage)
	{
		$requiredAmount = $this->getRequiredQuestionAmountForDefinitionList($sourcePoolDefinitionList);
		$possibleAmount = count($questionStage);

		return ( $possibleAmount >= $requiredAmount );
	}

	public function performBuild(ilTestSession $testSession)
	{
		$questionSet = array();

		foreach($this->sourcePoolDefinitionList as $definition)
		{
			/** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */

			$requiredQuestionAmount = $definition->getQuestionAmount();

			$potentialQuestionStage = $this->getQuestionStageForSourcePoolDefinition($definition);

			$actualQuestionStage = $this->getUniqueQuestionCollectionFromPotentialQuestionCollection(
				$potentialQuestionStage, $questionSet
			);

			if( $this->questionCollectionGreaterThanRequiredAmount($actualQuestionStage, $requiredQuestionAmount) )
			{
				$questions = $this->fetchQuestionsFromStageRandomly($actualQuestionStage, $requiredQuestionAmount);
			}
			else
			{
				$questions = $actualQuestionStage;
			}

			$questionSet = $this->mergeQuestionCollections($questionSet, $questions);
		}

		$requiredQuestionAmount = self::getRequiredQuestionAmountForDefinitionList($this->sourcePoolDefinitionList);

		if( $this->questionCollectionSmallerThanRequiredAmount($questionSet, $requiredQuestionAmount) )
		{
			$missingQuestionCount = $this->getMissingQuestionCount($questionSet, $requiredQuestionAmount);
			$questionStage = $this->getQuestionStageForSourcePoolDefinitionList($this->sourcePoolDefinitionList);
			$questions = $this->fetchQuestionsFromStageRandomly($questionStage, $missingQuestionCount);

			$questionSet = $this->mergeQuestionCollections($questionSet, $questions);
		}

		$questionSet = $this->handleQuestionOrdering($questionSet);

		$this->storeQuestionSet($testSession, $questionSet);
	}

	private function getUniqueQuestionCollectionFromPotentialQuestionCollection($potentialQuestionCollection, $otherQuestionCollection)
	{
		$uniqueQuestionCollection = array_diff($potentialQuestionCollection, $otherQuestionCollection);
		return $uniqueQuestionCollection;
	}

	private function questionCollectionGreaterThanRequiredAmount($questionCollection, $requiredAmount)
	{
		return count($questionCollection) > $requiredAmount;
	}

	private function questionCollectionSmallerThanRequiredAmount($questionCollection, $requiredAmount)
	{
		return count($questionCollection) < $requiredAmount;
	}

	private function mergeQuestionCollections($questionSet, $questions)
	{
		return array_merge($questionSet, $questions);
	}

	private function getMissingQuestionCount($questionSet, $requiredQuestionAmount)
	{
		return ( $requiredQuestionAmount - count($questionSet) );
	}

	public static function getRequiredQuestionAmountForDefinitionList(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
	{
		$requiredQuestionAmountPerTest = 0;

		foreach($sourcePoolDefinitionList as $definition)
		{
			/** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
			$requiredQuestionAmountPerTest += $definition->getQuestionAmount();
		}

		return $requiredQuestionAmountPerTest;
	}
}