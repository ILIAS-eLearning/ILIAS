<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    
    private $manPointsPostData = array();
    
    private $curQuestionMaxPoints = null;
    
    /**
     * @var bool
     */
    protected $first_row_rendered = false;

    /**
     * @var bool
     */
    protected $first_row = true;

    public function __construct($parentObj)
    {
        $this->setFilterCommand(self::PARENT_APPLY_FILTER_CMD);
        $this->setResetCommand(self::PARENT_RESET_FILTER_CMD);
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $tpl = $DIC->ui()->mainTemplate();
        $tpl->addJavaScript('./node_modules/tinymce/tinymce.js');
        $this->setId('man_scor_by_qst_' . $parentObj->object->getId());

        parent::__construct($parentObj, self::PARENT_DEFAULT_CMD);

        $this->setFormAction($ilCtrl->getFormAction($parentObj, self::PARENT_DEFAULT_CMD));
        $this->setRowTemplate("tpl.il_as_tst_man_scoring_by_question_tblrow.html", "Modules/Test");
        $this->setShowRowsSelector(true);

	$this->initOrdering();
        $this->initColumns();
        $this->initFilter();
    }

    private function initColumns()
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

    private function initOrdering()
    {
        $this->enable('sort');

        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");
    }

    public function initFilter()
    {
        $this->setDisableFilterHiding(true);

        include_once 'Services/Form/classes/class.ilSelectInputGUI.php';
        $available_questions = new ilSelectInputGUI($this->lng->txt('question'), 'question');
        $select_questions = array();
        if (!$this->getParentObject()->object->isRandomTest()) {
            $questions = $this->getParentObject()->object->getTestQuestions();
        } else {
            $questions = $this->getParentObject()->object->getPotentialRandomTestQuestions();
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
        $max_pass = $this->getParentObject()->object->getMaxPassOfTest();
        for ($i = 1; $i <= $max_pass; $i++) {
            $passes[$i] = $i;
        }
        $pass->setOptions($passes);
        $this->addFilterItem($pass);
        $pass->readFromSession();
        $this->filter['pass'] = $pass->getValue();
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

    /**
     * @param array $row
     */
    public function fillRow($row)
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
            $this->tpl->setVariable('VAL_NAME', $row['name']);
	}

        if (!$this->first_row_rendered) {
            $this->first_row_rendered = true;
            $this->tpl->touchBlock('row_js');
        }

        $this->tpl->setVariable('VAL_REACHED_POINTS', $row['reached_points']);
        $this->tpl->setVariable('VAL_MAX_POINTS', $row['maximum_points']);
        $finalized = (isset($row['finalized_evaluation']) && $row['finalized_evaluation'] == 1);
        $this->tpl->setVariable(
            'VAL_EVALUATED',
            ($finalized) ? $this->lng->txt('yes') : $this->lng->txt('no')
        );
        $fin_usr_id = $row['finalized_by_usr_id'];

        $this->tpl->setVariable('VAL_MODAL_CORRECTION', $row['feedback']);
        if ($fin_usr_id > 0) {
            $this->tpl->setVariable('VAL_FINALIZED_BY', ilObjUser::_lookupFullname($fin_usr_id));
        }
        $fin_timestamp = $row['finalized_tstamp'];
        if ($fin_timestamp > 0) {
            $time = new ilDateTime($fin_timestamp, 3);
            $this->tpl->setVariable('VAL_FINALIZED_ON', \ilDatePresentation::formatDate($time));
        }

        $this->tpl->setVariable('VAL_PASS', $row['pass_id']);
        $this->tpl->setVariable('VAL_ACTIVE_ID', $row['active_id']);
        $this->tpl->setVariable('VAL_QUESTION_ID', $row['qst_id']);

        if ($this->first_row) {
            $this->tpl->touchBlock('scoring_by_question_refresher');
            $this->tpl->parseCurrentBlock();
            $this->first_row = false;
        }

        $ilCtrl->setParameter($this->getParentObject(), 'qst_id', $row['qst_id']);
        $ilCtrl->setParameter($this->getParentObject(), 'active_id', $row['active_id']);
        $ilCtrl->setParameter($this->getParentObject(), 'pass_id', $row['pass_id']);
        $this->tpl->setVariable('VAL_LINK_ANSWER', $ilCtrl->getLinkTarget($this->getParentObject(), 'getAnswerDetail', '', true, false));
        $ilCtrl->setParameter($this->getParentObject(), 'qst_id', '');
        $ilCtrl->setParameter($this->getParentObject(), 'active_id', '');
        $ilCtrl->setParameter($this->getParentObject(), 'pass_id', '');
        $this->tpl->setVariable('VAL_TXT_ANSWER', $this->lng->txt('tst_eval_show_answer'));
        $this->tpl->setVariable('ANSWER_TITLE', $this->lng->txt('answer_of') . ': ' . $row['name']);
    }
    
    public function setManualScoringPointsPostData($manPointsPostData)
    {
        $this->manPointsPostData = $manPointsPostData;
    }

    public function getCurQuestionMaxPoints()
    {
        return $this->curQuestionMaxPoints;
    }

    public function setCurQuestionMaxPoints($curQuestionMaxPoints)
    {
        $this->curQuestionMaxPoints = $curQuestionMaxPoints;
    }
}
