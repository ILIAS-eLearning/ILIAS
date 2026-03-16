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

use DateTimeImmutable;
use Generator;
use ilBookingReservation;
use ilBookingSchedule;
use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ilDatePresentation;
use ilDateTime;

class BookingsWithScheduleTable extends BookingsTable
{
    public const string ID = 'bkbws';

    /**
     * @var array<int, ilBookingSchedule>
     */
    private array $schedule_cache = [];

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $records = $this->limitRecords($range, $this->sortRecords($order, $this->loadRecords($filter_data)));

        foreach ($records as $record) {
            $user_id = $record['user_id'];

            $record['date_to']++;

            $date_from = new DateTimeImmutable("@{$record['date_from']}");
            $time_slot = $this->isAllDayBooking($record)
                ? $this->lng->txt('book_all_day')
                : ilDatePresentation::formatPeriod(
                    new ilDateTime($record['date_from'], IL_CAL_UNIX),
                    new ilDateTime($record['date_to'], IL_CAL_UNIX),
                    true
                );

            yield $this->getTableActions()->onDataRow(
                $row_builder->buildDataRow(
                    (string) $record['booking_reservation_id'],
                    [
                        'title' => (string) $record['title'],
                        'status' => $record['status'] === ilBookingReservation::STATUS_CANCELLED
                            ? $this->lng->txt('book_reservation_status_5')
                            : '',
                        'date' => $date_from,
                        'week' => $date_from,
                        'weekday' => $date_from,
                        'time_slot' => $time_slot,
                        'unit_count' => 1,
                        'message' => (string) ($record['message'] ?? ''),
                        'user' => $this->getUserComponent($user_id)
                    ]
                ),
                $record
            );
        }
    }

    protected function getColumns(): array
    {
        $columns = [
            'title' => $this->column_factory->text($this->lng->txt('title')),
            'status' => $this->column_factory->text($this->lng->txt('status')),
            'date' => $this->column_factory->date(
                $this->lng->txt('date'),
                new DateFormat([
                    DateFormat::DAY,
                    DateFormat::DOT,
                    DateFormat::SPACE,
                    DateFormat::MONTH_SPELLED_SHORT,
                    DateFormat::SPACE,
                    DateFormat::YEAR
                ])
            ),
            'week' => $this->column_factory->date($this->lng->txt('week'), new DateFormat([DateFormat::WEEK]))
                ->withIsOptional(true, true),
            'weekday' => $this->column_factory->date($this->lng->txt('cal_weekday'), new DateFormat([DateFormat::WEEKDAY_SHORT]))
                ->withIsOptional(true, true),
            'time_slot' => $this->column_factory->text($this->lng->txt('book_schedule_slot')),
            'unit_count' => $this->column_factory->number($this->lng->txt('book_no_of_objects')),
            'message' => $this->column_factory->text($this->lng->txt('book_message'))->withIsSortable(false),
            'user' => $this->column_factory->link($this->lng->txt('user'))
        ];

        if (!$this->booking_pool->usesMessages()) {
            unset($columns['message']);
        }

        return $columns;
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
            'period' => $this->input_factory->duration($this->lng->txt('book_filter_period'))
                ->withUseTime(true)
                ->withFormat($this->user->getDateTimeFormat()),
            'time_slot' => $this->input_factory->select($this->lng->txt('book_schedule_slot'), $this->getTimeSlots()),
            'past_bookings' => $this->input_factory->select(
                $this->lng->txt('book_show_past_bookings'),
                [1 => $this->lng->txt('book_past_bookings'), 2 => $this->lng->txt('book_present_bookings')]
            ),
            'status' => $this->input_factory->select($this->lng->txt('status'), $this->getStatuses()),
            'user' => $user_input
        ];
    }

    /**
     * @return string[]
     */
    protected function getTimeSlots(): array
    {
        $time_slots = [];

        foreach ($this->bookings as $booking) {
            $time_slot = $this->getBookingTimeSlotFilterLabel($booking);
            $time_slots[$time_slot] ??= $time_slot;
        }

        return $time_slots;
    }

    protected function getBookingTimeSlotFilterLabel(array $booking): string
    {
        return $this->isAllDayBooking($booking)
            ? $this->lng->txt('book_all_day')
            : parent::getBookingTimeSlotFilterLabel($booking);
    }

    private function isAllDayBooking(array $booking): bool
    {
        $object_id = (int) $booking['object_id'];
        $schedule_id = (int) ($this->booking_items[$object_id]['schedule_id'] ?? 0);

        return
            $schedule_id !== 0
            && $this->getSchedule($schedule_id)->getScheduleType() === ilBookingSchedule::SCHEDULE_TYPE_ALL_DAY;
    }

    private function getSchedule(int $schedule_id): ilBookingSchedule
    {
        return $this->schedule_cache[$schedule_id] ??= new ilBookingSchedule($schedule_id);
    }
}
