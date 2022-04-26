<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestOutputGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 *
 * @ilCtrlStructureCalls(
 *		children={
 *			"ilAssGenFeedbackPageGUI", "ilAssSpecFeedbackPageGUI", "ilAssQuestionHintRequestGUI", "ilTestSignatureGUI",
 *			"ilAssQuestionPageGUI","ilTestSubmissionReviewGUI", "ilTestPasswordProtectionGUI",
 *			"ilTestAnswerOptionalQuestionsConfirmationGUI","ilConfirmationGUI",
 *		}
 * )
 */
class ilTestPlayerRandomQuestionSetGUI extends ilTestOutputGUI
{
    protected function buildTestPassQuestionList() : ilAssQuestionList
    {
        global $DIC;
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionList.php';
        $questionList = new ilAssQuestionList($this->db, $this->lng, $ilPluginAdmin);
        
        $questionList->setParentObjId($this->object->getId());

        $questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES);

        $questionList->setIncludeQuestionIdsFilter($this->testSequence->getQuestionIds());

        return $questionList;
    }

    protected function populateQuestionOptionalMessage()
    {
        $info = $this->lng->txt('tst_wf_info_optional_question');
        $info .= ' ' . $this->lng->txt('tst_wf_info_answer_not_adopted');
        $this->tpl->setOnScreenMessage('info', $info);
    }
}
