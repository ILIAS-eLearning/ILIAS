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
class ilTestRandomQuestionSetBuilderWithAmountPerTest extends ilTestRandomQuestionSetBuilder
{
    /**
     * @return bool
     */
    public function checkBuildable(): bool
    {
        $question_stage = $this->getSrcPoolDefListRelatedQuestUniqueCollection($this->source_pool_definition_list);

        if ($question_stage->isSmallerThan($this->question_set_config->getQuestionAmountPerTest())) {
            return false;
        }

        return true;
    }

    /**
     * @param ilTestSession $test_session
     */
    public function performBuild(ilTestSession $test_session)
    {
        $question_stage = $this->getSrcPoolDefListRelatedQuestUniqueCollection($this->source_pool_definition_list);

        $question_set = $this->fetchQuestionsFromStageRandomly(
            $question_stage,
            $this->question_set_config->getQuestionAmountPerTest()
        );

        $this->handleQuestionOrdering($question_set);

        $this->storeQuestionSet($test_session, $question_set);
    }
}
