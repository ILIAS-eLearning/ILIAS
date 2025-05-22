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

use ILIAS\TestQuestionPool\QuestionPoolDIC;
use ILIAS\TestQuestionPool\RequestDataCollector;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 */
class ilAssQuestionSkillAssignmentsTableGUI extends ilTable2GUI
{
    private ilAssQuestionSkillAssignmentList $skill_question_assignment_list;
    private RequestDataCollector $request_data_collector;

    private bool $load_skill_points_from_request = false;
    private bool $manipulations_enabled;

    public function setSkillQuestionAssignmentList(ilAssQuestionSkillAssignmentList $assignmentList): void
    {
        $this->skill_question_assignment_list = $assignmentList;
    }

    public function areManipulationsEnabled(): bool
    {
        return $this->manipulations_enabled;
    }

    public function setManipulationsEnabled(bool $manipulationsEnabled): void
    {
        $this->manipulations_enabled = $manipulationsEnabled;
    }

    public function __construct(
        $parent_obj,
        $parent_cmd,
        ilCtrl $ctrl,
        ilLanguage $lng
    ) {
        $this->lng = $lng;
        $this->ctrl = $ctrl;

        $local_dic = QuestionPoolDIC::dic();
        $this->request_data_collector = $local_dic['request_data_collector'];

        $this->setId('assQstSkl');
        $this->setPrefix('assQstSkl');

        parent::__construct($parent_obj, $parent_cmd);

        $this->setStyle('table', 'fullwidth');

        $this->setRowTemplate('tpl.tst_skl_qst_assignment_row.html', 'components/ILIAS/Test');

        $this->enable('header');
        $this->disable('sort');
        $this->disable('select_all');
    }

    public function init(): void
    {
        $this->initColumns();
    }

    public function loadSkillPointsFromRequest(bool $loadSkillPointsFromRequest): void
    {
        $this->load_skill_points_from_request = $loadSkillPointsFromRequest;
    }

    private function initColumns(): void
    {
        $this->addColumn($this->lng->txt('tst_question'), 'question', '25%');
        $this->addColumn($this->lng->txt('tst_competence'), 'competence', '');
        $this->addColumn($this->lng->txt('tst_comp_eval_mode'), 'eval_mode', '13%');
        $this->addColumn($this->lng->txt('tst_comp_points'), 'points', '12%');
        $this->addColumn($this->lng->txt('actions'), 'actions', '20%');
    }

    public function fillRow(array $a_set): void
    {
        $assignments = $this->skill_question_assignment_list->getAssignmentsByQuestionId($a_set['question_id']);

        $this->ctrl->setParameter($this->parent_obj, 'q_id', $a_set['question_id']);

        $this->tpl->setCurrentBlock('question_title');
        $this->tpl->setVariable('ROWSPAN', $this->getRowspan($assignments));
        $this->tpl->setVariable('QUESTION_TITLE', $a_set['title']);
        $this->tpl->setVariable('QUESTION_DESCRIPTION', $a_set['description']);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock('tbl_content');

        $num_assigns = count($assignments);
        /* @var ilAssQuestionSkillAssignment $assignment */
        foreach ($assignments as $key => $assignment) {
            $this->tpl->setCurrentBlock('actions_col');
            $this->tpl->setVariable('ACTION', $this->getCompetenceAssignPropertiesFormLink($assignment));
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('tbl_content');

            $this->tpl->setVariable('COMPETENCE', $assignment->getSkillTitle());
            $this->tpl->setVariable('COMPETENCE_PATH', $assignment->getSkillPath());
            $this->tpl->setVariable('EVAL_MODE', $this->getEvalModeLabel($assignment));
            $this->tpl->setVariable('SKILL_POINTS', $assignment->getMaxSkillPoints());

            if (($key + 1) < $num_assigns || $this->areManipulationsEnabled()) {
                $this->tpl->parseCurrentBlock();

                $this->tpl->setCurrentBlock('tbl_content');
                $this->tpl->setVariable('CSS_ROW', $this->css_row);
            }
        }

        if ($this->areManipulationsEnabled()) {
            $this->tpl->setCurrentBlock('actions_col');
            $this->tpl->setVariable('ACTION', $this->getManageCompetenceAssignsActionLink());
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('tbl_content');
            return;
        }

        if (!$num_assigns) {
            $this->tpl->setCurrentBlock('actions_col');
            $this->tpl->setVariable('ACTION', '&nbsp;');
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('tbl_content');
        }
    }

    private function getRowspan(array $assignments): int
    {
        $cnt = count($assignments);

        if ($cnt === 0) {
            return 1;
        }

        return $this->areManipulationsEnabled() ? $cnt + 1 : $cnt;
    }

    private function getManageCompetenceAssignsActionLink(): string
    {
        $href = $this->ctrl->getLinkTarget(
            $this->parent_obj,
            ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_SELECT
        );

        return $this->buildActionLink($href, $this->lng->txt('tst_manage_competence_assigns'));
    }

    private function getCompetenceAssignPropertiesFormLink(ilAssQuestionSkillAssignment $assignment): string
    {
        $this->ctrl->setParameter($this->parent_obj, 'skill_base_id', $assignment->getSkillBaseId());
        $this->ctrl->setParameter($this->parent_obj, 'skill_tref_id', $assignment->getSkillTrefId());

        $href = $this->ctrl->getLinkTarget(
            $this->parent_obj,
            ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGN_PROPERTIES_FORM
        );

        $this->ctrl->setParameter($this->parent_obj, 'skill_base_id', null);
        $this->ctrl->setParameter($this->parent_obj, 'skill_tref_id', null);

        return $this->buildActionLink($href, $this->lng->txt(
            $this->areManipulationsEnabled()
                ? 'tst_edit_competence_assign'
                : 'tst_view_competence_assign'
        ));
    }

    private function buildActionLink(string $href, string $label): string
    {
        return "<a href=\"{$href}\" title=\"{$label}\">{$label}</a>";
    }

    private function getEvalModeLabel(ilAssQuestionSkillAssignment $assignment): string
    {
        return $this->lng->txt(
            $assignment->hasEvalModeBySolution()
                ? 'qpl_skill_point_eval_mode_solution_compare'
                : 'qpl_skill_point_eval_mode_quest_result'
        );
    }
}
