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
 * @package components\ILIAS/Test
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilAssGenFeedbackPageGUI
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilAssQuestionHintRequestGUI
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilAssQuestionPageGUI
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilTestSubmissionReviewGUI
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilTestPasswordProtectionGUI
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilTestAnswerOptionalQuestionsConfirmationGUI
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilConfirmationGUI
 */
class ilTestPlayerRandomQuestionSetGUI extends ilTestOutputGUI
{
    protected function buildTestPassQuestionList(): ilAssQuestionList
    {
        $question_list = new ilAssQuestionList($this->db, $this->lng, $this->component_repository);
        $question_list->setParentObjId($this->object->getId());
        $question_list->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES);
        $question_list->setIncludeQuestionIdsFilter($this->testSequence->getQuestionIds());
        return $question_list;
    }

    protected function populateQuestionOptionalMessage()
    {
        $info = $this->lng->txt('tst_wf_info_optional_question');
        $info .= ' ' . $this->lng->txt('tst_wf_info_answer_not_adopted');
        $this->tpl->setOnScreenMessage('info', $info);
    }
}
