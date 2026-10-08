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

namespace ILIAS\Search\GUI\UserAssignment\Table;

use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use Generator;
use ILIAS\UI\Component\Table\DataRetrieval as DataRetrievalInterface;
use ilRbacReview;
use ilObject;
use ilParticipants;
use ilUserFilter;

class ObjectDataRetrieval implements DataRetrievalInterface
{
    /**
     * @var int[]
     */
    protected array $filtered_obj_ids;

    /**
     * @param int[] $obj_ids
     */
    public function __construct(
        protected string $search_type,
        protected array $obj_ids,
        protected ilRbacReview $review
    ) {
    }

    protected function getObjIds(): array
    {
        if (isset($this->filtered_obj_ids)) {
            return $this->filtered_obj_ids;
        }

        if ($this->search_type !== 'role') {
            return $this->filtered_obj_ids = $this->obj_ids;
        }

        $filtered = [];
        foreach ($this->obj_ids as $obj_id) {
            if ($this->review->isRoleDeleted($obj_id)) {
                continue;
            }
            $filtered[] = $obj_id;
        }
        return $this->filtered_obj_ids = $filtered;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $data = [];
        foreach ($this->getObjIds() as $obj_id) {
            $member_count = match ($this->search_type) {
                'crs', 'grp' => ilParticipants::hasParticipantListAccess($obj_id) ?
                    count(ilParticipants::getInstanceByObjId($obj_id)->getParticipants()) : 0,
                'role' => count(ilUserFilter::getInstance()->filter($this->review->assignedUsers($obj_id)))
            };
            $data[$obj_id] = [
                ObjectTableBuilder::TITLE_COLUMN => ilObject::_lookupTitle($obj_id),
                ObjectTableBuilder::MEMBER_COUNT_COLUMN => $member_count
            ];
        }

        $order_field = array_keys($order->get())[0];
        $order_direction = $order->get()[$order_field];
        if ($order_field === ObjectTableBuilder::MEMBER_COUNT_COLUMN) {
            uasort($data, fn($a, $b) => $a[$order_field] <=> $b[$order_field]);
        } else {
            uasort($data, fn($a, $b) => strtolower((string) $a[$order_field]) <=> strtolower((string) $b[$order_field]));
        }
        if ($order_direction === Order::DESC) {
            $data = array_reverse($data, true);
        }
        $data = array_slice($data, $range->getStart(), $range->getLength(), true);

        foreach ($data as $id => $datum) {
            yield $row_builder->buildDataRow(
                (string) $id,
                $datum
            );
        }
    }

    /**
     * @return int[]
     */
    public function getAllIDs(): array
    {
        return $this->getObjIds();
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->getObjIds());
    }
}
