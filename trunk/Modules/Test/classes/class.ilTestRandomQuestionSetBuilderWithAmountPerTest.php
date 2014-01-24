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
	public function checkBuildable()
	{
		$questionStage = $this->getQuestionStageForSourcePoolDefinitionList($this->sourcePoolDefinitionList);

		return $this->isQuestionSetFetchable($questionStage);
	}

	public function performBuild(ilTestSession $testSession)
	{
		$questionStage = $this->getQuestionStageForSourcePoolDefinitionList($this->sourcePoolDefinitionList);

		$questionSet = $this->fetchQuestionsFromStageRandomly(
			$questionStage, $this->questionSetConfig->getQuestionAmountPerTest()
		);

		$this->storeQuestionSet($testSession, $questionSet);
	}

	private function isQuestionSetFetchable($questionStage)
	{
		$requiredAmount = $this->questionSetConfig->getQuestionAmountPerTest();
		$possibleAmount = count($questionStage);

		return ( $possibleAmount >= $requiredAmount );
	}
}