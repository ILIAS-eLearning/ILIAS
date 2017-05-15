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
	// hey: fixRandomTestBuildable - improvment of improved pass build check
	public function checkBuildableNewer()
	{
		$lng = $GLOBALS['DIC'] ? $GLOBALS['DIC']['lng'] : $GLOBALS['lng'];
			
		$isBuildable = true;
		
		require_once 'Modules/Test/classes/class.ilTestRandomQuestionsQuantitiesDistribution.php';
		$quantitiesDistribution = new ilTestRandomQuestionsQuantitiesDistribution($this);
		$quantitiesDistribution->setSourcePoolDefinitionList($this->sourcePoolDefinitionList);
		$quantitiesDistribution->initialise();
		
		foreach($this->sourcePoolDefinitionList as $definition)
		{
			/** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
			
			$quantityCalculation = $quantitiesDistribution->calculateQuantities($definition);
			
			if( $quantityCalculation->isRequiredAmountGuaranteedAvailable() )
			{
				continue;
			}
			
			$isBuildable = false;
			
			$this->checkMessages[] = $quantityCalculation->getDistributionReport($lng);
		}
		
		return $isBuildable;
	}
	// hey.
	
	// fau: fixRandomTestBuildable - improved the check for buildable test
	public function checkBuildableNew()
	{
		global $lng;
		
		$this->checkMessages = array();
		$questionsPerDefinition = array();
		$questionsMatchingCount = array();
		$buildable = true;
		
		// first round: collect all used questions and count their matching
		/** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */
		foreach($this->sourcePoolDefinitionList as $definition)
		{
			$questionsPerDefinition[$definition->getId()] = array();
			$stage = $this->getSrcPoolDefRelatedQuestCollection($definition);
			foreach ($stage->getInvolvedQuestionIds() as $id)
			{
				$questionsPerDefinition[$definition->getId()][$id]++;
				$questionsMatchingCount[$id]++;
			}
		}
		
		// second round: count the exclusive questions of each definition
		foreach($this->sourcePoolDefinitionList as $definition)
		{
			$exclusive = 0;
			foreach ($questionsPerDefinition[$definition->getId()] as $id => $used)
			{
				// all matchings are from this definition
				if ($questionsMatchingCount[$id] == $used)
				{
					// increase the number of exclusive questions
					$exclusive++;
				}
			}
			if ($exclusive < $definition->getQuestionAmount())
			{
				$buildable = false;
				$this->checkMessages[] = sprintf($lng->txt('tst_msg_rand_quest_set_pass_not_buildable_detail'),
					$definition->getSequencePosition());
			}
		}
		
		// return $buildable;
		
		// keep old check for a while but messages will be created for the new check
		$questionStage = $this->getSrcPoolDefListRelatedQuestUniqueCollection($this->sourcePoolDefinitionList);
		if( $questionStage->isSmallerThan($this->sourcePoolDefinitionList->getQuestionAmount()) )
		{
			return false;
		}
		
		return true;
	}
	// fau.
	
	public function checkBuildable()
	{
		// hey: fixRandomTestBuildable - improved the buildable check improvement
		return $this->checkBuildableNewer();
		// hey.
		
		// fau: fixRandomTestBuildable - improved the check for buildable test
		return $this->checkBuildableNew();
		// fau.
		
		$questionStage = $this->getSrcPoolDefListRelatedQuestUniqueCollection($this->sourcePoolDefinitionList);

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

			$potentialQuestionStage = $this->getSrcPoolDefRelatedQuestCollection($definition);

			$actualQuestionStage = $potentialQuestionStage->getRelativeComplementCollection($questionSet);

			if( $actualQuestionStage->isGreaterThan($requiredQuestionAmount) )
			{
				$questions = $this->fetchQuestionsFromStageRandomly($actualQuestionStage, $requiredQuestionAmount);
			}
			else
			{
				// fau: fixRandomTestBuildable - log missing questions for a random test rule
				if( $actualQuestionStage->isSmallerThan($requiredQuestionAmount) )
				{
					global $ilDB, $ilLog;
					if (!isset($translator))
					{
						require_once("./Modules/Test/classes/class.ilTestTaxonomyFilterLabelTranslater.php");
						$translator = new ilTestTaxonomyFilterLabelTranslater($ilDB);
						$translator->loadLabels($this->sourcePoolDefinitionList);
					}
					$ilLog->write("RANDOM TEST: missing questions for: "
						. implode(" - ",array($definition->getPoolTitle(), $translator->getTaxonomyFilterLabel($definition->getMappedTaxonomyFilter()))));
				}
				// fau.
				$questions = $actualQuestionStage;
			}

			$questionSet->mergeQuestionCollection($questions);
		}

		$requiredQuestionAmount = $this->sourcePoolDefinitionList->getQuestionAmount();

		if( $questionSet->isSmallerThan($requiredQuestionAmount) )
		{
			$missingQuestionCount = $questionSet->getMissingCount($requiredQuestionAmount);
			// fau: fixRandomTestBuildable - avoid already chosen questions being used as fillers
			$potentialQuestionStage = $this->getSrcPoolDefListRelatedQuestUniqueCollection($this->sourcePoolDefinitionList);
			$actualQuestionStage = $potentialQuestionStage->getRelativeComplementCollection($questionSet);
			$questions = $this->fetchQuestionsFromStageRandomly($actualQuestionStage, $missingQuestionCount);
			// fau.
			$questionSet->mergeQuestionCollection($questions);
		}

		$this->handleQuestionOrdering($questionSet);

		$this->storeQuestionSet($testSession, $questionSet);
	}
}