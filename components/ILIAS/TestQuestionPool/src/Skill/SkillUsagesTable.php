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

namespace ILIAS\TestQuestionPool\Skill;

use Generator;
use ilAssQuestionSkillAssignmentList;
use ilDBInterface;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\Data;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory;
use ilLanguage;

class SkillUsagesTable implements DataRetrieval
{
    private ?array $records = null;

    public function __construct(
        private readonly Factory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly ilDBInterface $db,
        private readonly int $parent_obj_id
    ) {
    }

    public function getComponent(): Data
    {
        return $this->ui_factory->table()->data(
            $this,
            $this->lng->txt('qpl_skl_sub_tab_usages'),
            $this->getColumns()
        )->withId((string) $this->parent_obj_id);
    }

    public function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();

        return [
            'skill_title' => $column_factory->text($this->lng->txt('qpl_qst_skl_usg_skill_col')),
            'num_assigns' => $column_factory->number($this->lng->txt('qpl_qst_skl_usg_numq_col')),
            'max_points' => $column_factory->number($this->lng->txt('qpl_qst_skl_usg_sklpnt_col'))
        ];
    }

    public function collectRecords(?array $filter_data, ?array $additional_parameters): array
    {
        $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);

        $assignmentList->setParentObjId($this->parent_obj_id);
        $assignmentList->loadFromDb();
        $assignmentList->loadAdditionalSkillData();

        return $assignmentList->getUniqueAssignedSkills();
    }

    private function sortRecords(array $records, Order $order): array
    {
        [$order_field, $order_direction] = $order->join(
            '',
            static fn(string $index, string $key, string $value): array => [$key, $value]
        );

        usort($records, static function (array $a, array $b) use ($order_field): int {
            if (is_numeric($a[$order_field]) || is_bool($a[$order_field]) || is_array($a[$order_field])) {
                return $a[$order_field] <=> $b[$order_field];
            }

            return strcmp($a[$order_field] ?? '', $b[$order_field] ?? '');
        });

        return $order_direction === $order::DESC ? array_reverse($records) : $records;
    }

    private function initRecords(?array $filter_data, ?array $additional_parameters): void
    {
        $this->records ??= $this->collectRecords($filter_data, $additional_parameters);
    }

    private function limitRecords(array $records, Range $range): array
    {
        return array_slice($records, $range->getStart(), $range->getLength());
    }

    protected function getRecords(Order $order, Range $range, ?array $filter_data, ?array $additional_parameters): array
    {
        $this->initRecords($filter_data, $additional_parameters);

        return $this->limitRecords($this->sortRecords($this->records, $order), $range);
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        foreach ($this->getRecords($order, $range, $filter_data, $additional_parameters) as $row_id => $record) {
            yield $row_builder->buildDataRow((string) $row_id, $record);
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        $this->initRecords($filter_data, $additional_parameters);
        return count($this->records);
    }
}
