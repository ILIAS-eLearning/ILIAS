<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 */
class ilTestRandomQuestionSetBuilderWithAmountPerPool extends ilTestRandomQuestionSetBuilder
{
    public function checkBuildable(): bool
    {
        $quantities_distribution = new ilTestRandomQuestionsQuantitiesDistribution(
            $this->db,
            $this,
            $this->source_pool_definition_list
        );
        $quantities_distribution->initialise();

        $is_buildable = true;
        foreach ($this->source_pool_definition_list as $definition) {
            $quantity_calculation = $quantities_distribution->calculateQuantities($definition);
            if ($quantity_calculation->isRequiredAmountGuaranteedAvailable()) {
                continue;
            }
            $is_buildable = false;
            $this->check_messages[] = $quantity_calculation->getDistributionReport($this->lng);
        }

        return $is_buildable;
    }

    public function performBuild(ilTestSession $test_session)
    {
        $question_set = new ilTestRandomQuestionSetQuestionCollection();

        foreach ($this->source_pool_definition_list as $definition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */

            $required_question_amount = $definition->getQuestionAmount();

            $potential_question_stage = $this->getSrcPoolDefRelatedQuestCollection($definition);

            $actual_question_stage = $potential_question_stage->getRelativeComplementCollection($question_set);

            if ($actual_question_stage->isGreaterThan($required_question_amount)) {
                $questions = $this->fetchQuestionsFromStageRandomly($actual_question_stage, $required_question_amount);
            } else {
                // fau: fixRandomTestBuildable - log missing questions for a random test rule
                if ($actual_question_stage->isSmallerThan($required_question_amount)) {
                    if (!isset($translator)) {
                        $translator = new ilTestQuestionFilterLabelTranslator($this->db, $this->lng);
                        $translator->loadLabels($this->source_pool_definition_list);
                    }
                    $this->logger->info("RANDOM TEST: missing questions for: "
                        . implode(" - ", [$definition->getPoolTitle(), $translator->getTaxonomyFilterLabel($definition->getMappedTaxonomyFilter())]));
                }
                // fau.
                $questions = $actual_question_stage;
            }

            $question_set->mergeQuestionCollection($questions);
        }

        $required_question_amount = $this->source_pool_definition_list->getQuestionAmount();

        if ($question_set->isSmallerThan($required_question_amount)) {
            $missing_question_count = $question_set->getMissingCount($required_question_amount);
            // fau: fixRandomTestBuildable - avoid already chosen questions being used as fillers
            $potential_question_stage = $this->getSrcPoolDefListRelatedQuestUniqueCollection($this->source_pool_definition_list);
            $actual_question_stage = $potential_question_stage->getRelativeComplementCollection($question_set);
            $questions = $this->fetchQuestionsFromStageRandomly($actual_question_stage, $missing_question_count);
            // fau.
            $question_set->mergeQuestionCollection($questions);
        }

        $this->handleQuestionOrdering($question_set);

        $this->storeQuestionSet($test_session, $question_set);
    }
}
