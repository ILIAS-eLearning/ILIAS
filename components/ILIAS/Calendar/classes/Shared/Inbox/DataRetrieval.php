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

namespace ILIAS\Calendar\Shared\Inbox;

use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use Generator;
use ilObjUser;
use ILIAS\Calendar\Shared\DataRetrievalInterface;
use ilCalendarSharedStatus;
use DateTimeImmutable;

class DataRetrieval implements DataRetrievalInterface
{
    protected array $open_invitations;

    public function __construct(
        protected int $user_id
    ) {
    }

    protected function getOpenInvitations(): array
    {
        return $this->open_invitations ??= new ilCalendarSharedStatus($this->user_id)->getOpenInvitations();
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
        foreach ($this->getOpenInvitations() as $item) {
            $datum = [];
            $datum[TableBuilder::NAME_COLUMN] = $item['name'];
            $name = ilObjUser::_lookupName((int) $item['owner']);
            $datum[TableBuilder::OWNER_COLUMN] = $name['lastname'] . ', ' . $name['firstname'];
            $datum[TableBuilder::APPOINTMENTS_COUNT_COLUMN] = (int) $item['apps'];
            $datum[TableBuilder::CREATED_ON_COLUMN] = new DateTimeImmutable($item['create_date']);

            $data[(int) $item['cal_id']] = $datum;
        }

        $order_field = array_keys($order->get())[0];
        $order_direction = $order->get()[$order_field];
        if ($order_field === TableBuilder::CREATED_ON_COLUMN || $order_field === TableBuilder::APPOINTMENTS_COUNT_COLUMN) {
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
        $ids = [];
        foreach ($this->getOpenInvitations() as $item) {
            $ids[] = (int) $item['cal_id'];
        }
        return $ids;
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->getOpenInvitations());
    }
}
