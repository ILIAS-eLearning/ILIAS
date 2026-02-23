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

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\Data;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;

class SkillUsagesTable implements DataRetrieval
{
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly \ilLanguage $lng,
        private readonly \ilAssQuestionSkillAssignmentList $assignment_list
    ) {
    }

    public function getComponent(): Data
    {
        return $this->ui_factory->table()->data(
            $this,
            $this->lng->txt('qpl_skl_sub_tab_usages'),
            $this->getColumns()
        );
    }

    public function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();

        return [
            'skill_title' => $column_factory->text($this->lng->txt('qpl_qst_skl_usg_skill_col')),
            'skill_path' => $column_factory->text($this->lng->txt('tst_competence_tree')),
            'num_assigns' => $column_factory->number($this->lng->txt('qpl_qst_skl_usg_numq_col')),
            'max_points' => $column_factory->number($this->lng->txt('qpl_qst_skl_usg_sklpnt_col'))
        ];
    }

    public function collectRecords(?array $filter_data, ?array $additional_parameters): array
    {
        return $this->assignment_list->getUniqueAssignedSkills();
    }

    private function orderRecords(array $records, Order $order): array
    {
        [$order_field, $order_direction] = $order->join(
            '',
            static fn(string $index, string $key, string $value): array => [$key, $value]
        );

        usort(
            $records,
            static fn(array $a, array $b): int => ($a[$order_field] ?? '') <=> ($b[$order_field] ?? '')
        );

        return $order_direction === $order::DESC ? array_reverse($records) : $records;
    }

    private function limitRecords(array $records, Range $range): array
    {
        return array_slice($records, $range->getStart(), $range->getLength());
    }

    private function getRecords(Order $order, Range $range, ?array $filter_data, ?array $additional_parameters): array
    {
        return $this->limitRecords(
            $this->orderRecords(
                $this->collectRecords($filter_data, $additional_parameters),
                $order
            ),
            $range
        );
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): \Generator {
        foreach ($this->getRecords($order, $range, $filter_data, $additional_parameters) as $row_id => $record) {
            yield $row_builder->buildDataRow((string) $row_id, $record);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->collectRecords($filter_data, $additional_parameters));
    }
}
