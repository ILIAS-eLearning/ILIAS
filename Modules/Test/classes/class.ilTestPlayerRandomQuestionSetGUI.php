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
 * @package     Modules/Test
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilAssGenFeedbackPageGUI
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilAssQuestionHintRequestGUI
 * @ilCtrl_Calls ilTestPlayerRandomQuestionSetGUI: ilTestSignatureGUI
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
        global $DIC;
        $component_repository = $DIC['component.repository'];

        $questionList = new ilAssQuestionList($this->db, $this->lng, $component_repository);

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
