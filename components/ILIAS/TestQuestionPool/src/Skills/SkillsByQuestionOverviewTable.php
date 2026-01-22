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

namespace ILIAS\TestQuestionPool\Skills;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Table\PresentationRow;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Implementation\Component\Input\ViewControl\Mode;

class SkillsByQuestionOverviewTable
{
    public const string VIEW_CONTROL_QUERY_PARAM = 'mode';

    private const string ROW_ID_PARAMETER = 'q_id';

    public function __construct(
        private readonly \ilAssQuestionList $question_list,
        private readonly \ilAssQuestionSkillAssignmentList $assignment_list,
        private readonly UIFactory $ui_factory,
        private readonly \ilLanguage $lng,
        private readonly \ilCtrl $ctrl
    ) {
    }

    public function getComponents(URI $edit_uri, SkillAssignmentViewControlMode $skill_assignment_view_control_mode = SkillAssignmentViewControlMode::ALL): array
    {
        return [
            $this->ui_factory->table()->presentation(
                $this->lng->txt('qpl_skl_sub_tab_quest_assign'),
                $this->getViewControls($skill_assignment_view_control_mode),
                fn(PresentationRow $row, SkillAssignments $record): PresentationRow => $this->mapRow($row, $record, $edit_uri)
            )
            ->withData($this->retrieveRecords($skill_assignment_view_control_mode))
        ];
    }

    private function mapRow(PresentationRow $row, SkillAssignments $record, URI $edit_uri): PresentationRow
    {
        $assignment_details = [];
        foreach ($record->getSkillAssignments() as $skill_assignment) {
            $assignment_details[$skill_assignment->getSkillTitle()] = $this->ui_factory
                ->listing()
                ->property()
                ->withProperty($this->lng->txt('tree'), $skill_assignment->getSkillPath())
                ->withProperty(
                    $this->lng->txt('tst_comp_eval_mode'),
                    $this->lng->txt(
                        $skill_assignment->hasEvalModeBySolution()
                            ? 'qpl_skill_point_eval_mode_solution_compare'
                            : 'qpl_skill_point_eval_mode_quest_result'
                    )
                )
                ->withProperty($this->lng->txt('tst_comp_points'), (string) $skill_assignment->getMaxSkillPoints());
        }

        $row = $row
            ->withHeadline($record->getQuestion()['title'])
            ->withSubheadline($record->getQuestion()['description'])
            ->withLeadingSymbol(
                $this->ui_factory->symbol()->icon()->standard('ques', '')
            )
            ->withContent(
                $assignment_details !== []
                    ? $this->ui_factory->listing()->descriptive($assignment_details)
                    : $this->ui_factory->legacy()->content($this->lng->txt('ui_table_no_records'))
            )
            ->withAction(
                $this->ui_factory->button()->standard(
                    $this->lng->txt('tst_manage_competence_assigns'),
                    (string) $edit_uri->withParameter(self::ROW_ID_PARAMETER, $record->getQuestion()['question_id'])
                )
            );

        $assignments = $this->assignment_list->getAssignmentsByQuestionId($record->getQuestion()['question_id']);
        if ($assignments === []) {
            return $row;
        }

        return $row->withImportantFields([
            $this->lng->txt('tst_competence') => implode(
                ', ',
                array_map(static fn(\ilAssQuestionSkillAssignment $a) => $a->getSkillTitle(), $assignments)
            ),
        ]);
    }

    /**
     * @return array<SkillAssignments>
     */
    public function retrieveRecords(SkillAssignmentViewControlMode $skill_assignment_view_control_mode = SkillAssignmentViewControlMode::ALL): array
    {
        $records = [];
        foreach ($this->question_list->getQuestionDataArray() as $question_id => $question_data) {
            $assignments_by_question_id = $this->assignment_list->getAssignmentsByQuestionId($question_id);
            if (
                $skill_assignment_view_control_mode === SkillAssignmentViewControlMode::ASSIGNED
                && $assignments_by_question_id === []
            ) {
                continue;
            }

            if (
                $skill_assignment_view_control_mode === SkillAssignmentViewControlMode::UNASSIGNED
                && $assignments_by_question_id !== []
            ) {
                continue;
            }

            $records[] = new SkillAssignments($question_data, $assignments_by_question_id);
        }

        return $records;
    }

    /**
     * @return Mode[]
     */
    private function getViewControls(SkillAssignmentViewControlMode $skill_assignment_view_control_mode = SkillAssignmentViewControlMode::ALL): array
    {
        $labeled_actions = [];
        foreach (SkillAssignmentViewControlMode::cases() as $value) {
            $this->ctrl->setParameterByClass(
                \ilAssQuestionSkillAssignmentsGUI::class,
                self::VIEW_CONTROL_QUERY_PARAM,
                $value->value
            );
            $labeled_actions[$this->lng->txt($value->getLabel())] = $this->ctrl->getLinkTargetByClass(
                \ilAssQuestionSkillAssignmentsGUI::class,
                \ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS
            );
        }

        return [
            $this->ui_factory->viewControl()->mode($labeled_actions, $this->lng->txt('qpl_skl_view_control_mode_aria'))
                ->withActive($this->lng->txt($skill_assignment_view_control_mode->getLabel())),
        ];
    }
}
