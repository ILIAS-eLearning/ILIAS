<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssQuestionSkillAssignmentsTableGUI extends ilTable2GUI
{
    /**
     * @var ilAssQuestionSkillAssignmentList
     */
    private $skillQuestionAssignmentList;

    /**
     * @var bool
     */
    private $loadSkillPointsFromRequest = false;

    /**
     * @var bool
     */
    private $manipulationsEnabled;

    public function setSkillQuestionAssignmentList(ilAssQuestionSkillAssignmentList $assignmentList)
    {
        $this->skillQuestionAssignmentList = $assignmentList;
    }

    /**
     * @return boolean
     */
    public function areManipulationsEnabled()
    {
        return $this->manipulationsEnabled;
    }

    /**
     * @param boolean $manipulationsEnabled
     */
    public function setManipulationsEnabled($manipulationsEnabled)
    {
        $this->manipulationsEnabled = $manipulationsEnabled;
    }

    public function __construct($parentOBJ, $parentCmd, ilCtrl $ctrl, ilLanguage $lng)
    {
        parent::__construct($parentOBJ, $parentCmd);

        $this->lng = $lng;
        $this->ctrl = $ctrl;
        
        $this->setId('assQstSkl');
        $this->getPrefix('assQstSkl');

        $this->setStyle('table', 'fullwidth');

        $this->setRowTemplate("tpl.tst_skl_qst_assignment_row.html", "Modules/Test");

        $this->enable('header');
        $this->disable('sort');
        $this->disable('select_all');
    }
    
    public function init()
    {
        $this->initColumns();
        
        if ($this->areManipulationsEnabled()) {
            $this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
            
            $this->addCommandButton(
                ilAssQuestionSkillAssignmentsGUI::CMD_SAVE_SKILL_POINTS,
                $this->lng->txt('tst_save_comp_points')
            );
        }
    }

    /**
     * @param bool $loadSkillPointsFromRequest
     */
    public function loadSkillPointsFromRequest($loadSkillPointsFromRequest)
    {
        $this->loadSkillPointsFromRequest = $loadSkillPointsFromRequest;
    }

    private function initColumns()
    {
        $this->addColumn($this->lng->txt('tst_question'), 'question', '25%');
        $this->addColumn($this->lng->txt('tst_competence'), 'competence', '');
        $this->addColumn($this->lng->txt('tst_comp_eval_mode'), 'eval_mode', '13%');
        $this->addColumn($this->lng->txt('tst_comp_points'), 'points', '12%');
        $this->addColumn($this->lng->txt('actions'), 'actions', '20%');
    }

    public function fillRow($question)
    {
        $assignments = $this->skillQuestionAssignmentList->getAssignmentsByQuestionId($question['question_id']);

        $this->ctrl->setParameter($this->parent_obj, 'question_id', $question['question_id']);

        $this->tpl->setCurrentBlock('question_title');
        $this->tpl->setVariable('ROWSPAN', $this->getRowspan($assignments));
        $this->tpl->setVariable('QUESTION_TITLE', $question['title']);
        $this->tpl->setVariable('QUESTION_DESCRIPTION', $question['description']);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock('tbl_content');
        
        for ($i = 0, $numAssigns = count($assignments); $i < $numAssigns; $i++) {
            /* @var ilAssQuestionSkillAssignment $assignment */
            $assignment = $assignments[$i];
            
            $this->tpl->setCurrentBlock('actions_col');
            $this->tpl->setVariable('ACTION', $this->getCompetenceAssignPropertiesFormLink($assignment));
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('tbl_content');
            
            $this->tpl->setVariable('COMPETENCE', $assignment->getSkillTitle());
            $this->tpl->setVariable('COMPETENCE_PATH', $assignment->getSkillPath());
            $this->tpl->setVariable('EVAL_MODE', $this->getEvalModeLabel($assignment));

            if ($this->isSkillPointInputRequired($assignment)) {
                $this->tpl->setVariable('SKILL_POINTS', $this->buildSkillPointsInput($assignment));
            } else {
                $this->tpl->setVariable('SKILL_POINTS', $assignment->getMaxSkillPoints());
            }

            if ($this->areManipulationsEnabled() || ($i + 1) < $numAssigns) {
                $this->tpl->parseCurrentBlock();

                $this->tpl->setCurrentBlock('tbl_content');
                $this->tpl->setVariable("CSS_ROW", $this->css_row);
            }
        }

        if ($this->areManipulationsEnabled()) {
            $this->tpl->setCurrentBlock('actions_col');
            $this->tpl->setVariable('ACTION', $this->getManageCompetenceAssignsActionLink());
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('tbl_content');
        } elseif (!$numAssigns) {
            $this->tpl->setCurrentBlock('actions_col');
            $this->tpl->setVariable('ACTION', '&nbsp;');
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('tbl_content');
        }
    }

    private function getRowspan($assignments)
    {
        $cnt = count($assignments);

        if ($cnt == 0) {
            return 1;
        }
        
        if ($this->areManipulationsEnabled()) {
            $cnt++;
        }

        return $cnt;
    }

    private function getManageCompetenceAssignsActionLink()
    {
        $href = $this->ctrl->getLinkTarget(
            $this->parent_obj,
            ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_SELECT
        );

        $label = $this->lng->txt('tst_manage_competence_assigns');

        return $this->buildActionLink($href, $label);
    }

    private function getCompetenceAssignPropertiesFormLink(ilAssQuestionSkillAssignment $assignment)
    {
        $this->ctrl->setParameter($this->parent_obj, 'skill_base_id', $assignment->getSkillBaseId());
        $this->ctrl->setParameter($this->parent_obj, 'skill_tref_id', $assignment->getSkillTrefId());

        $href = $this->ctrl->getLinkTarget(
            $this->parent_obj,
            ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGN_PROPERTIES_FORM
        );

        if ($this->areManipulationsEnabled()) {
            $label = $this->lng->txt('tst_edit_competence_assign');
        } else {
            $label = $this->lng->txt('tst_view_competence_assign');
        }

        $this->ctrl->setParameter($this->parent_obj, 'skill_base_id', null);
        $this->ctrl->setParameter($this->parent_obj, 'skill_tref_id', null);

        return $this->buildActionLink($href, $label);
    }

    private function buildActionLink($href, $label)
    {
        return "<a href=\"{$href}\" title=\"{$label}\">{$label}</a>";
    }

    private function buildActionColumnHTML($assignments)
    {
        $actions = array();

        foreach ($assignments as $assignment) {
            $this->ctrl->setParameter($this->parent_obj, 'skill_base_id', $assignment->getSkillBaseId());
            $this->ctrl->setParameter($this->parent_obj, 'skill_tref_id', $assignment->getSkillTrefId());

            $href = $this->ctrl->getLinkTarget(
                $this->parent_obj,
                ilAssQuestionSkillAssignmentsGUI::CMD_REMOVE_SKILL_QUEST_ASSIGN
            );

            $label = $this->lng->txt('tst_remove_competence');

            $actions[] = $this->buildActionLink($href, $label);
        }

        $href = $this->ctrl->getLinkTarget(
            $this->parent_obj,
            ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_SELECT
        );

        $label = $this->lng->txt('tst_assign_competence');
        $actions[] = $this->buildActionLink($href, $label);

        return implode('<br />', $actions);
    }
    
    private function getEvalModeLabel(ilAssQuestionSkillAssignment $assignment)
    {
        if ($assignment->hasEvalModeBySolution()) {
            return $this->lng->txt('qpl_skill_point_eval_mode_solution_compare');
        }

        return $this->lng->txt('qpl_skill_point_eval_mode_quest_result');
    }

    private function buildSkillPointsInput(ilAssQuestionSkillAssignment $assignment)
    {
        $assignmentKey = implode(':', array(
            $assignment->getSkillBaseId(), $assignment->getSkillTrefId(), $assignment->getQuestionId()
        ));

        if ($this->loadSkillPointsFromRequest) {
            $points = isset($_POST['skill_points'][$assignmentKey]) ? ilUtil::stripSlashes($_POST['skill_points'][$assignmentKey]) : '';
        } else {
            $points = $assignment->getSkillPoints();
        }

        return "<input type\"text\" size=\"2\" name=\"skill_points[{$assignmentKey}]\" value=\"{$points}\" />";
    }
    
    private function isSkillPointInputRequired(ilAssQuestionSkillAssignment $assignment)
    {
        if (!$this->areManipulationsEnabled()) {
            return false;
        }

        if ($assignment->hasEvalModeBySolution()) {
            return false;
        }
        
        return true;
    }
}
