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
use ilObjCourseGrouping;
use ILIAS\UI\Factory as UIFactory;
use ilObject;
use ILIAS\StaticURL\Services as StaticURL;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;
use ILIAS\UI\Component\Link\Standard as StandardLink;
use ILIAS\UI\Component\Listing\Unordered as UnorderedListing;

use function ILIAS\UI\examples\Deck\user;

class GroupingRetrieval implements DataRetrieval
{
    public function __construct(
        protected int $content_obj_id,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory,
        protected DataFactory $data_factory,
        protected StaticURL $static_url
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
        $grouping_ids = $this->getAllGroupingIDs();

        $records = [];
        foreach ($grouping_ids as $grouping_id) {
            $grouping = new ilObjCourseGrouping($grouping_id);
            $record = [];

            $record[GroupingHandler::COL_TITLE] = $grouping->getTitle();
            $record[GroupingHandler::COL_DESCRIPTION] = $grouping->getDescription();

            $link = $this->buildLinkToObject($grouping->getContainerRefId());
            if ($link !== null) {
                $record[GroupingHandler::COL_SOURCE] = $link;
            }

            $record[GroupingHandler::COL_UNIQUE_FIELD] = $this->lng->txt($grouping->getUniqueField());

            $condition_ref_ids = [];
            foreach ($grouping->getAssignedItems() as $condition) {
                $condition_ref_ids[] = $condition['target_ref_id'];
            }
            $record[GroupingHandler::COL_ASSIGNED_OBJS] = $this->buildLinkListing(...$condition_ref_ids);

            $records[$grouping->getId()] = $record;
        }

        $records = $this->sortRecords($records, $order);
        $records = array_slice($records, $range->getStart(), $range->getLength(), true);

        foreach ($records as $id => $record) {
            yield $row_builder->buildDataRow((string) $id, $record);
        }
    }

    /**
     * @return int[]
     */
    public function getAllGroupingIDs(): array
    {
        return ilObjCourseGrouping::_getVisibleGroupings($this->content_obj_id);
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return count($this->getAllGroupingIDs());
    }

    protected function buildLinkToObject(int $ref_id): ?StandardLink
    {
        $ref_id = $this->data_factory->refId($ref_id);
        if (ilObject::_exists($ref_id->toInt(), true)) {
            $type = ilObject::_lookupType($ref_id->toInt(), true);
            $title = ilObject::_lookupTitle($ref_id->toObjectId()->toInt());
            $link = $this->static_url->builder()->build($type, $ref_id);
            return $this->ui_factory->link()->standard($title, (string) $link);
        }
        return null;
    }

    protected function buildLinkListing(int ...$ref_ids): UnorderedListing
    {
        $links = [];
        foreach ($ref_ids as $ref_id) {
            $link = $this->buildLinkToObject($ref_id);
            if ($link !== null) {
                $links[] = $link;
            }
        }
        return $this->ui_factory->listing()->unordered($links);
    }

    protected function sortRecords(array $records, Order $order): array
    {
        $order_field = array_keys($order->get())[0] ?? GroupingHandler::COL_TITLE;
        $order_direction = $order->get()[$order_field] ?? Order::ASC;

        $ordering_callable_without_direction = match ($order_field) {
            GroupingHandler::COL_TITLE, GroupingHandler::COL_DESCRIPTION, GroupingHandler::COL_UNIQUE_FIELD =>
                fn($a, $b) => $a[$order_field] ?? '' <=> $b[$order_field] ?? '',
            GroupingHandler::COL_SOURCE =>
                fn($a, $b) => $a[GroupingHandler::COL_SOURCE]?->getLabel() ?? '' <=> $b[GroupingHandler::COL_SOURCE]?->getLabel() ?? '',
            GroupingHandler::COL_ASSIGNED_OBJS =>
                function ($a, $b) {
                    $a_items = ($a[GroupingHandler::COL_ASSIGNED_OBJS] ?? null)?->getItems() ?? [];
                    $b_items = ($b[GroupingHandler::COL_ASSIGNED_OBJS] ?? null)?->getItems() ?? [];
                    $a_first_item_label = ($a_items[0] ?? null)?->getLabel() ?? '';
                    $b_first_item_label = ($b_items[0] ?? null)?->getLabel() ?? '';
                    return $a_first_item_label <=> $b_first_item_label;
                }
        };
        $ordering_callable = fn($a, $b) => $order_direction === Order::ASC ?
            $ordering_callable_without_direction($a, $b) :
            $ordering_callable_without_direction($b, $a);

        uasort($records, $ordering_callable);
        return $records;
    }
}
