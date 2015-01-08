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

		if( $questionStage->isSmallerThan($this->sourcePoolDefinitionList->getQuestionAmount()) )
		{
			return false;
		}

		return true;
	}

	public function performBuild(ilTestSession $testSession)
	{
		$questionSet = new ilTestRandomQuestionSetQuestionCollection();

		foreach($this->sourcePoolDefinitionList as $definition)
		{
			/** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */

			$requiredQuestionAmount = $definition->getQuestionAmount();

			$potentialQuestionStage = $this->getQuestionStageForSourcePoolDefinition($definition);

			$actualQuestionStage = $potentialQuestionStage->getRelativeComplementCollection($questionSet);

			if( $actualQuestionStage->isGreaterThan($requiredQuestionAmount) )
			{
				$questions = $this->fetchQuestionsFromStageRandomly($actualQuestionStage, $requiredQuestionAmount);
			}
			else
			{
				$questions = $actualQuestionStage;
			}

			$questionSet->mergeQuestionCollection($questions);
		}

		$requiredQuestionAmount = $this->sourcePoolDefinitionList->getQuestionAmount();

		if( $questionSet->isSmallerThan($requiredQuestionAmount) )
		{
			$missingQuestionCount = $questionSet->getMissingCount($requiredQuestionAmount);
			$questionStage = $this->getQuestionStageForSourcePoolDefinitionList($this->sourcePoolDefinitionList);
			$questions = $this->fetchQuestionsFromStageRandomly($questionStage, $missingQuestionCount);

			$questionSet->mergeQuestionCollection($questions);
		}

		$this->handleQuestionOrdering($questionSet);

		$this->storeQuestionSet($testSession, $questionSet);
	}
}