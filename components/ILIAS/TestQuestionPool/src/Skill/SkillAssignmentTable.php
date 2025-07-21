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

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\TestQuestionPool\RequestDataCollector;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;

class SkillAssignmentTable implements DataRetrieval
{
    public const string ID = 'ska';

    private ?iterable $records = null;

    public function __construct(
        private readonly RequestDataCollector $pool_request,
        private readonly ilAssQuestionSkillAssignmentList $assignment_list,
        private readonly UIFactory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly SkillAssignmentTableActions $table_actions
    ) {
    }

    /**
     * @return array<Component>
     */
    public function getComponents(URLBuilder $url_builder): array
    {
        $question = assQuestionGUI::_getQuestionGUI('', $this->pool_request->getQuestionId())->getObject();
        $column = $this->ui_factory->table()->column();
        return [
            $this->ui_factory->table()->data(
                $this,
                sprintf($this->lng->txt('qpl_skl_assignment_for_question'), $question->getTitle()),
                [
                    'competence' => $column->text($this->lng->txt('tst_competence'))->withIsSortable(true),
                    'competence_tree' => $column->text($this->lng->txt('tst_competence_tree'))->withIsSortable(true),
                    'eval_mode' => $column->text($this->lng->txt('tst_comp_eval_mode'))->withIsSortable(true),
                    'points' => $column->number($this->lng->txt('tst_comp_points'))->withIsSortable(true)
                ]
            )
                ->withActions($this->table_actions->getEnabledActions(...$this->acquireParameters($url_builder)))
                ->withRequest($this->pool_request->getRequest())
        ];
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        /** @var ilAssQuestionSkillAssignment $record */
        foreach ($this->loadRecords() as $record) {
            yield $this->table_actions->onDataRow(
                $row_builder->buildDataRow(
                    "{$record->getQuestionId()}_{$record->getSkillBaseId()}_{$record->getSkillTrefId()}",
                    [
                        'competence' => htmlspecialchars($record->getSkillTitle(), ENT_QUOTES, 'UTF-8', false),
                        'competence_tree' => $record->getSkillPath(),
                        'eval_mode' => $this->lng->txt($record->hasEvalModeBySolution()
                            ? 'qpl_skill_point_eval_mode_solution_compare'
                            : 'qpl_skill_point_eval_mode_quest_result'),
                        'points' => $record->getSkillPoints(),
                    ]
                ),
                $record
            );
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return count($this->assignment_list->getAssignmentsByQuestionId($this->pool_request->getQuestionId()));
    }

    public function acquireParameters(URLBuilder $url_builder): array
    {
        return $url_builder->acquireParameters(
            [self::ID],
            SkillAssignmentTableActions::ROW_ID_PARAMETER,
            SkillAssignmentTableActions::ACTION_PARAMETER,
            SkillAssignmentTableActions::ACTION_TYPE_PARAMETER
        );
    }

    private function loadRecords(): iterable
    {
        $this->records ??= $this->assignment_list->getAssignmentsByQuestionId($this->pool_request->getQuestionId());

        return $this->records;
    }
}
