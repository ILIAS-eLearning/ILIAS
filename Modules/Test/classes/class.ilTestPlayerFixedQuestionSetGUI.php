<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Services\AssessmentQuestion\PublicApi\Common\ilCtrlCallBackCmd;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;

require_once 'Modules/Test/classes/class.ilTestOutputGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 *
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilAssGenFeedbackPageGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilAssQuestionHintRequestGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilTestSignatureGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilAssQuestionPageGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilTestSubmissionReviewGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilTestPasswordProtectionGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilTestAnswerOptionalQuestionsConfirmationGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilConfirmationGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilAsqQuestionProcessingGUI
 */
class ilTestPlayerFixedQuestionSetGUI extends ilTestOutputGUI
{
	protected function buildTestPassQuestionList()
	{
		global $DIC;
		$ilPluginAdmin = $DIC['ilPluginAdmin'];
		
		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionList.php';
		$questionList = new ilAssQuestionList($this->db, $this->lng, $ilPluginAdmin);
		
		$questionList->setParentObjId($this->object->getId());

		$questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES);

		return $questionList;
	}

	protected function populateQuestionOptionalMessage()
	{
		$info = $this->lng->txt('tst_wf_info_optional_question');
		$info .= ' '.$this->lng->txt('tst_wf_info_answer_adopted_from_prev_pass');
		ilUtil::sendInfo($info);
	}

	public function getQuestionConfig(): QuestionConfig
    {
        global $DIC;

        $question_config = new QuestionConfig();
        $question_config->setFeedbackForAnswerOption(true);
        $question_config->setFeedbackOnDemand(true);
        $question_config->setFeedbackOnSubmit(true);
        $question_config->setFeedbackShowCorrectSolution(true);
        $question_config->setFeedbackShowScore(true);
        $question_config->setHintsActivated(true);
        $question_config->setShowTotalPointsOfQuestion(true);

        //Previous
        $nextSequenceElement = $this->testSequence->getNextSequence($this->getCurrentSequenceElement());
        if($this->isValidSequenceElement($nextSequenceElement)) {
            //NEXT
            $question_config ->setShowNextQuestionAction(new ilCtrlCallBackCmd(['ilRepositoryGUI','ilObjTestGUI','ilTestPlayerFixedQuestionSetGUI'],self::CMD_REDIRECT_TO_NEXT_QUESTION));
        }

        //Previous
        $nextSequenceElement = $this->testSequence->getPreviousSequence($this->getCurrentSequenceElement());
        if($this->isValidSequenceElement($nextSequenceElement)) {
            $question_config ->setShowPreviousQuestionAction(new ilCtrlCallBackCmd(['ilRepositoryGUI','ilObjTestGUI','ilTestPlayerFixedQuestionSetGUI'],self::CMD_REDIRECT_TO_PREVIOUS_QUESTION));
        }

        //Finish
        $question_config ->setShowFinishTestSessionAction(new ilCtrlCallBackCmd(['ilRepositoryGUI','ilObjTestGUI','ilTestPlayerFixedQuestionSetGUI'],self::CMD_FINISH_TEST_SESSION));


        return $question_config;
    }
}
