<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
        $info .= ' ' . $this->lng->txt('tst_wf_info_answer_adopted_from_prev_pass');
        ilUtil::sendInfo($info);
    }
}
