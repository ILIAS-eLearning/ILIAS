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

require_once 'Services/Form/classes/class.ilNumberInputGUI.php';
require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI
 * @author     Michael Jansen <mjansen@datababay.de>
 * @version    $Id $
 * @ingroup    ModulesTest
 */
class ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI extends ilTable2GUI
{
    const PARENT_DEFAULT_CMD = 'showManScoringByQuestionParticipantsTable';
    const PARENT_APPLY_FILTER_CMD = 'applyManScoringByQuestionFilter';
    const PARENT_RESET_FILTER_CMD = 'resetManScoringByQuestionFilter';
    const PARENT_SAVE_SCORING_CMD = 'saveManScoringByQuestion';
    
    private ?float $curQuestionMaxPoints = null;

    protected bool $first_row_rendered = false;

    protected bool $first_row = true;

    public function __construct($parentObj)
    {
        $this->setFilterCommand(self::PARENT_APPLY_FILTER_CMD);
        $this->setResetCommand(self::PARENT_RESET_FILTER_CMD);
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $tpl = $DIC->ui()->mainTemplate();
        $tpl->addJavaScript('./node_modules/tinymce/tinymce.js');
        $this->setId('man_scor_by_qst_' . $parentObj->getObject()->getId());

        parent::__construct($parentObj, self::PARENT_DEFAULT_CMD);

        $this->setFormAction($ilCtrl->getFormAction($parentObj, self::PARENT_DEFAULT_CMD));
        $this->setRowTemplate("tpl.il_as_tst_man_scoring_by_question_tblrow.html", "Modules/Test");
        $this->setShowRowsSelector(true);

        $this->initOrdering();
        $this->initColumns();
        $this->initFilter();
    }

    private function initColumns() : void
    {
        $this->addColumn($this->lng->txt('name'), 'name');
        $this->addColumn($this->lng->txt('tst_reached_points'), 'reached_points');
        $this->addColumn($this->lng->txt('tst_maximum_points'), 'maximum_points');
        $this->addColumn($this->lng->txt('tst_feedback'), 'feedback', '30%');
        $this->addColumn($this->lng->txt('finalized_evaluation'), 'finalized_evaluation');
        $this->addColumn($this->lng->txt('finalized_by'), 'finalized_by_uid');
        $this->addColumn($this->lng->txt('finalized_on'), 'finalized_tstamp');
        $this->addColumn('', '');
    }

    private function initOrdering() : void
    {
        $this->enable('sort');

        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");
    }

    public function initFilter() : void
    {
        $this->setDisableFilterHiding(true);

        include_once 'Services/Form/classes/class.ilSelectInputGUI.php';
        $available_questions = new ilSelectInputGUI($this->lng->txt('question'), 'question');
        $select_questions = array();
        if (!$this->getParentObject()->getObject()->isRandomTest()) {
            $questions = $this->getParentObject()->getObject()->getTestQuestions();
        } else {
            $questions = $this->getParentObject()->getObject()->getPotentialRandomTestQuestions();
        }
        $scoring = ilObjAssessmentFolder::_getManualScoring();
        foreach ($questions as $data) {
            include_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
            $info = assQuestion::_getQuestionInfo($data['question_id']);
            $type = $info["question_type_fi"];
            if (in_array($type, $scoring)) {
                $maxpoints = assQuestion::_getMaximumPoints($data["question_id"]);
                if ($maxpoints == 1) {
                    $maxpoints = ' (' . $maxpoints . ' ' . $this->lng->txt('point') . ')';
                } else {
                    $maxpoints = ' (' . $maxpoints . ' ' . $this->lng->txt('points') . ')';
                }
                
                $select_questions[$data["question_id"]] = $data['title'] . $maxpoints . ' [' . $this->lng->txt('question_id_short') . ': ' . $data["question_id"] . ']';
            }
        }
        if (!$select_questions) {
            $select_questions[0] = $this->lng->txt('tst_no_scorable_qst_available');
        }
        $available_questions->setOptions(array('' => $this->lng->txt('please_choose')) + $select_questions);
        $this->addFilterItem($available_questions);
        $available_questions->readFromSession();
        $this->filter['question'] = $available_questions->getValue();

        $pass = new ilSelectInputGUI($this->lng->txt('pass'), 'pass');
        $passes = array();
        $max_pass = $this->getParentObject()->getObject()->getMaxPassOfTest();
        for ($i = 1; $i <= $max_pass; $i++) {
            $passes[$i] = $i;
        }
        $pass->setOptions($passes);
        $this->addFilterItem($pass);
        $pass->readFromSession();
        $this->filter['pass'] = $pass->getValue();

        $only_answered = new ilCheckboxInputGUI($this->lng->txt('tst_man_scoring_only_answered'), 'only_answered');
        $this->addFilterItem($only_answered);
        $only_answered->readFromSession();
        ;
        $this->filter['only_answered'] = $only_answered->getChecked();

        $correction = new ilSelectInputGUI(
            $this->lng->txt('finalized_evaluation'),
            'finalize_evaluation'
        );
        $evaluated = array(
            $this->lng->txt('all_users'),
            $this->lng->txt('evaluated_users'),
            $this->lng->txt('not_evaluated_users')
        );
        $correction->setOptions($evaluated);
        $this->addFilterItem($correction);
        $correction->readFromSession();
        $this->filter['finalize_evaluation'] = $correction->getValue();
    }

    public function fillRow(array $a_set) : void
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $ilAccess = $DIC->access();

        if (
            $this->getParentObject()->object->anonymity == 1 ||
            (
                $this->getParentObject()->object->getAnonymity() == 2 &&
                false == $ilAccess->checkAccess('write', '', $this->getParentObject()->object->getRefId())
            )
        ) {
            $this->tpl->setVariable('VAL_NAME', $this->lng->txt("anonymous"));
        } else {
            $this->tpl->setVariable('VAL_NAME', $a_set['name']);
        }

        if (!$this->first_row_rendered) {
            $this->first_row_rendered = true;
            $this->tpl->touchBlock('row_js');
        }

        $this->tpl->setVariable('VAL_REACHED_POINTS', $a_set['reached_points']);
        $this->tpl->setVariable('VAL_MAX_POINTS', $a_set['maximum_points']);
        $finalized = (isset($row['finalized_evaluation']) && $a_set['finalized_evaluation'] == 1);
        $this->tpl->setVariable(
            'VAL_EVALUATED',
            ($finalized) ? $this->lng->txt('yes') : $this->lng->txt('no')
        );
        $fin_usr_id = $a_set['finalized_by_usr_id'] ?? null;

        $this->tpl->setVariable('VAL_MODAL_CORRECTION', $a_set['feedback'] ?? '');
        if (is_numeric($fin_usr_id) && $fin_usr_id > 0) {
            $this->tpl->setVariable('VAL_FINALIZED_BY', ilObjUser::_lookupFullname($fin_usr_id));
        }
        $fin_timestamp = $a_set['finalized_tstamp'];
        if ($fin_timestamp > 0) {
            $time = new ilDateTime($fin_timestamp, 3);
            $this->tpl->setVariable('VAL_FINALIZED_ON', \ilDatePresentation::formatDate($time));
        }

        $this->tpl->setVariable('VAL_PASS', $a_set['pass_id']);
        $this->tpl->setVariable('VAL_ACTIVE_ID', $a_set['active_id']);
        $this->tpl->setVariable('VAL_QUESTION_ID', $a_set['qst_id']);

        if ($this->first_row) {
            $this->tpl->touchBlock('scoring_by_question_refresher');
            $this->tpl->parseCurrentBlock();
            $this->first_row = false;
        }

        $ilCtrl->setParameter($this->getParentObject(), 'qst_id', $a_set['qst_id']);
        $ilCtrl->setParameter($this->getParentObject(), 'active_id', $a_set['active_id']);
        $ilCtrl->setParameter($this->getParentObject(), 'pass_id', $a_set['pass_id']);
        $this->tpl->setVariable('VAL_LINK_ANSWER', $ilCtrl->getLinkTarget($this->getParentObject(), 'getAnswerDetail', '', true, false));
        $ilCtrl->setParameter($this->getParentObject(), 'qst_id', '');
        $ilCtrl->setParameter($this->getParentObject(), 'active_id', '');
        $ilCtrl->setParameter($this->getParentObject(), 'pass_id', '');
        $this->tpl->setVariable('VAL_TXT_ANSWER', $this->lng->txt('tst_eval_show_answer'));
        $this->tpl->setVariable('ANSWER_TITLE', $this->lng->txt('answer_of') . ': ' . $a_set['name']);
    }
    
    public function getCurQuestionMaxPoints() : ?float
    {
        return $this->curQuestionMaxPoints;
    }

    public function setCurQuestionMaxPoints(float $curQuestionMaxPoints) : void
    {
        $this->curQuestionMaxPoints = $curQuestionMaxPoints;
    }
}
