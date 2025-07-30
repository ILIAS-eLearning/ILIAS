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

namespace ILIAS\Course\Grouping\Table;

use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use Generator;
use ilUtil;
use ilObject;
use ilObjUser;
use ilTree;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\ReferenceId;
use ILIAS\UI\Factory as UIFactory;
use ilLanguage;
use ilObjCourseGrouping;

class AssignmentRetrieval implements DataRetrieval
{
    public function __construct(
        protected int $content_obj_id,
        protected ilObjCourseGrouping $grouping,
        protected ilObjUser $user,
        protected ilTree $tree,
        protected UIFactory $ui_factory,
        protected ilLanguage $lng,
        protected DataFactory $data_factory
    ) {
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        $ref_ids = array_slice($this->getAllEligibleRefIDs(), $range->getStart(), $range->getLength());

        $assigned = $this->grouping->getAssignedItems();
        $assigned_ref_ids = [];
        foreach ($assigned as $item) {
            $assigned_ref_ids[] = $item['target_ref_id'];
        }

        $ok_icon = $this->ui_factory->symbol()->icon()->custom(
            'assets/images/standard/icon_ok.svg',
            $this->lng->txt('assigned')
        );

        $records = [];
        foreach ($ref_ids as $ref_id) {
            $record = [
                AssignmentHandler::COL_TITLE => ilObject::_lookupTitle($ref_id->toObjectId()->toInt()),
                AssignmentHandler::COL_PATH => $this->buildPath($ref_id),
            ];
            if (in_array($ref_id->toInt(), $assigned_ref_ids)) {
                $record[AssignmentHandler::COL_ASSIGNED] = $ok_icon;
            }
            $records[$ref_id->toInt()] = $record;
        }

        $records = $this->sortRecords($records, $order);

        foreach ($records as $ref_id => $record) {
            yield $row_builder->buildDataRow((string) $ref_id, $record);
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return count($this->getAllEligibleRefIDs());
    }

    /**
     * @return ReferenceId[]
     */
    public function getAllEligibleRefIDs(): array
    {
        $type = ilObject::_lookupType($this->content_obj_id);
        $item_ref_ids = ilUtil::_getObjectsByOperations(
            $type,
            'write',
            $this->user->getId(),
            -1
        );

        $filtered_ref_ids = [];
        foreach ($item_ref_ids as $item_ref_id) {
            if ($this->tree->checkForParentType($item_ref_id, 'adm')) {
                continue;
            }
            $filtered_ref_ids[] = $this->data_factory->refId($item_ref_id);
        }
        return $filtered_ref_ids;
    }

    protected function buildPath(ReferenceId $ref_id): string
    {
        $titles = [];
        foreach ($this->tree->getPathFull($ref_id->toInt()) as $step) {
            $titles[] = $step['title'];
        }
        return implode(' > ', $titles);
    }

    protected function sortRecords(array $records, Order $order): array
    {
        $order_field = array_keys($order->get())[0] ?? AssignmentHandler::COL_TITLE;
        $order_direction = $order->get()[$order_field] ?? Order::ASC;

        $ordering_callable_without_direction = match ($order_field) {
            AssignmentHandler::COL_TITLE, AssignmentHandler::COL_PATH =>
                fn($a, $b) => $a[$order_field] ?? '' <=> $b[$order_field] ?? '',
            AssignmentHandler::COL_ASSIGNED =>
                fn($a, $b) => isset($a[$order_field]) <=> isset($b[$order_field])
        };
        $ordering_callable = fn($a, $b) => $order_direction === Order::ASC ?
            $ordering_callable_without_direction($a, $b) :
            $ordering_callable_without_direction($b, $a);

        uasort($records, $ordering_callable);
        return $records;
    }
}
