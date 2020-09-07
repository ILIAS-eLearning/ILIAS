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
    /**
     * @return bool
     */
    public function checkBuildable()
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
