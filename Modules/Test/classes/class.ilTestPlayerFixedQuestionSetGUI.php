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
 * @package     Modules/Test
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilAssGenFeedbackPageGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilAssQuestionHintRequestGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilAssQuestionPageGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilTestSubmissionReviewGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilTestPasswordProtectionGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilTestAnswerOptionalQuestionsConfirmationGUI
 * @ilCtrl_Calls ilTestPlayerFixedQuestionSetGUI: ilConfirmationGUI
 */
class ilTestPlayerFixedQuestionSetGUI extends ilTestOutputGUI
{
    protected function buildTestPassQuestionList(): ilAssQuestionList
    {
        $question_list = new ilAssQuestionList($this->db, $this->lng, $this->component_repository);
        $question_list->setParentObjId($this->object->getId());
        $question_list->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES);

        return $question_list;
    }

    protected function populateQuestionOptionalMessage()
    {
        $info = $this->lng->txt('tst_wf_info_optional_question');
        $info .= ' ' . $this->lng->txt('tst_wf_info_answer_adopted_from_prev_pass');
        $this->tpl->setOnScreenMessage('info', $info);
    }
}
