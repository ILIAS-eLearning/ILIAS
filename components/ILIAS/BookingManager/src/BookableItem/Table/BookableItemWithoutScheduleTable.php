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

namespace ILIAS\BookingManager\BookableItem\Table;

use ilBookingObject;
use ilBookingReservation;
use ILIAS\UI\Component\Table\Column\Column;

class BookableItemWithoutScheduleTable extends BookableItemTable
{
    public const string ID = 'bkbiwos';

    /**
     * @return array<string, Column>
     */
    protected function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();
        return [
            'availability' => $column_factory->text($this->lng->txt('book_table_col_availability'))->withIsSortable(true),
            'title' => $column_factory->text($this->lng->txt('title'))->withIsSortable(true),
            'description' => $column_factory->text($this->lng->txt('description'))->withIsSortable(true),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFilterInputs(): array
    {
        $bookable_items = [];
        foreach (ilBookingObject::getList($this->pool->getId()) as $item) {
            $bookable_items[(int) $item['booking_object_id']] = (string) $item['title'];
        }

        $field_factory = $this->ui_factory->input()->field();
        return [
            'title' => $field_factory->text($this->lng->txt('title')),
            'description' => $field_factory->text($this->lng->txt('description')),
            'objects' => $field_factory->multiSelect($this->lng->txt('book_filter_objects'), $bookable_items),
        ];
    }

    /**
     * @return array<string, mixed>[]
     */
    protected function loadRecords(mixed $filter_data): array
    {
        $filter_data = is_array($filter_data) ? $filter_data : [];

        $title_filter = trim((string) ($filter_data['title'] ?? '')) ?: null;
        $description_filter = trim((string) ($filter_data['description'] ?? '')) ?: null;
        $object_ids_filter = ($filter_data['objects'] ?? []) !== []
            ? array_map('intval', $filter_data['objects'])
            : null;

        $rows = [];
        foreach ($this->loadFilteredBookingObjects($title_filter, $description_filter, $object_ids_filter) as $item) {
            $object_id = (int) $item['booking_object_id'];
            $available = ilBookingReservation::numAvailableFromObjectNoSchedule($object_id);
            $user_has_booking = false;
            $user_has_active_booking = false;

            foreach ($this->getReservationsForObject($object_id) as $reservation) {
                $user_id = (int) $reservation['user_id'];
                if ($user_id !== $this->user->getId()) {
                    continue;
                }

                $user_has_booking = true;

                if ($reservation['status'] === ilBookingReservation::STATUS_CANCELLED) {
                    continue;
                }

                $user_has_active_booking = true;
                break;
            }

            $rows[] = [
                'row_id' => (string) $object_id,
                'booking_object_id' => $object_id,
                'title' => (string) $item['title'],
                'description' => (string) ($item['description'] ?? ''),
                'nr_items' => (int) $item['nr_items'],
                'available' => $available,
                'is_available' => $available > 0,
                'has_user_booking' => $user_has_booking,
                'has_user_active_booking' => $user_has_active_booking,
                'has_reservations' => $this->hasActiveReservations($object_id),
                'post_text' => (string) ($item['post_text'] ?? ''),
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildRowCells(array $record): array
    {
        return [
            'availability' => $this->buildAvailabilityCell((int) $record['available'], (int) $record['nr_items']),
            'title' => (string) $record['title'],
            'description' => nl2br((string) $record['description']),
        ];
    }

    private function hasActiveReservations(int $object_id): bool
    {
        return array_any(
            $this->getReservationsForObject($object_id),
            static fn(array $reservation): bool => $reservation['status'] !== ilBookingReservation::STATUS_CANCELLED
        );
    }
}
