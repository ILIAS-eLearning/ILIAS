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

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestRandomQuestionSetBuilderWithAmountPerTest extends ilTestRandomQuestionSetBuilder
{
    /**
     * @return bool
     */
    public function checkBuildable(): bool
    {
        $questionStage = $this->getSrcPoolDefListRelatedQuestUniqueCollection($this->sourcePoolDefinitionList);

        if ($questionStage->isSmallerThan($this->questionSetConfig->getQuestionAmountPerTest())) {
            return false;
        }

        return true;
    }

    /**
     * @param ilTestSession $testSession
     */
    public function performBuild(ilTestSession $testSession)
    {
        $questionStage = $this->getSrcPoolDefListRelatedQuestUniqueCollection($this->sourcePoolDefinitionList);

        $questionSet = $this->fetchQuestionsFromStageRandomly(
            $questionStage,
            $this->questionSetConfig->getQuestionAmountPerTest()
        );

        $this->handleQuestionOrdering($questionSet);

        $this->storeQuestionSet($testSession, $questionSet);
    }
}
