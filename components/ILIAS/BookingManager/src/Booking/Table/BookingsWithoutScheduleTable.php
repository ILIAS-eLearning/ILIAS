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

namespace ILIAS\BookingManager\Bookings\Table;

use Generator;
use ilBookingReservation;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\DataRowBuilder;

class BookingsWithoutScheduleTable extends BookingsTable
{
    public const string ID = 'bkbwos';

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $bookings = $this->limitRecords($range, $this->sortRecords($order, $this->loadRecords($filter_data)));

        foreach ($bookings as $record) {
            $user_id = $record['user_id'];

            yield $this->getTableActions()->onDataRow(
                $row_builder->buildDataRow(
                    (string) $record['booking_reservation_id'],
                    [
                        'title' => (string) $record['title'],
                        'status' => $record['status'] === ilBookingReservation::STATUS_CANCELLED
                            ? $this->lng->txt('book_reservation_status_5')
                            : '',
                        'user' => $this->getUserComponent($user_id)
                    ]
                ),
                $record
            );
        }
    }

    protected function getColumns(): array
    {
        return [
            'title' => $this->column_factory->text($this->lng->txt('title')),
            'status' => $this->column_factory->text($this->lng->txt('status')),
            'user' => $this->column_factory->link($this->lng->txt('user')),
        ];
    }

    protected function getFilterInputs(): array
    {
        $txt_user = $this->lng->txt('user');
        if (
            $this->access->canManageAllReservations($this->booking_pool->getRefId())
            || $this->access->canReadPublicLog($this->booking_pool->getRefId())
        ) {
            $user_input = $this->input_factory->select($txt_user, $this->getUsers());
        } else {
            $user_input = $this->input_factory
                ->select($txt_user, $this->getUsers($this->user->getId()))
                ->withValue($this->user->getId())
                ->withDisabled(true);
        }

        return [
            'object' => $this->input_factory->select(
                $this->lng->txt('object'),
                array_column($this->booking_items, 'title', 'booking_object_id')
            ),
            'object_title_or_description' => $this->input_factory->text($this->lng->txt('book_object_title_or_description')),
            'status' => $this->input_factory->select($this->lng->txt('status'), $this->getStatuses()),
            'user' => $user_input
        ];
    }
}
