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

use DateTimeImmutable;
use DateTimeZone;
use ilBookingObject;
use ilBookingReservation;
use ilBookingSchedule;
use ilDatePresentation;
use ilDateTime;
use ILIAS\UI\Component\Table\Column\Column;
use Throwable;

class BookableItemWithScheduleTable extends BookableItemTable
{
    public const string ID = 'bkbiws';

    // Hard limit for the number of weeks in the future to enumerate slots for
    public const int MAX_WEEKS_IN_THE_FUTURE = 52;

    private const WEEKDAYS_MAP = [
        'mo' => 'monday',
        'tu' => 'tuesday',
        'we' => 'wednesday',
        'th' => 'thursday',
        'fr' => 'friday',
        'sa' => 'saturday',
        'su' => 'sunday',
    ];

    /**
     * @return array<string, Column>
     */
    protected function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();
        return [
            'availability' => $column_factory->text($this->lng->txt('book_table_col_availability'))->withIsSortable(true),
            'date_time' => $column_factory->text($this->lng->txt('book_table_col_datetime'))->withIsSortable(true),
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
            'period' => $field_factory->duration($this->lng->txt('book_filter_period'))
                ->withUseTime(true)
                ->withFormat($this->user->getDateTimeFormat()),
        ];
    }

    /**
     * @return array<string, mixed>[]
     */
    protected function loadRecords(mixed $filter_data): array
    {
        $filter_data = is_array($filter_data) ? $filter_data : [];

        $period_bounds = $this->resolvePeriod($filter_data);
        $time_slot_filter = $this->stringFilter($filter_data, 'time_slot');

        $booking_objects = $this->loadFilteredBookingObjects(
            $this->stringFilter($filter_data, 'title'),
            $this->stringFilter($filter_data, 'description'),
            $this->arrayFilter($filter_data, 'objects')
        );

        $rows = [];

        foreach ($booking_objects as $item) {
            $schedule_id = (int) $item['schedule_id'];
            if ($schedule_id === 0) {
                continue;
            }

            $object_id = (int) $item['booking_object_id'];
            $schedule = new ilBookingSchedule($schedule_id);
            $is_all_day_schedule = $this->isAllDaySchedule($schedule);
            $slots = $this->enumerateSlots(
                $schedule,
                $period_bounds[0] ?? null,
                $period_bounds[1] ?? null
            );
            foreach ($slots as $slot) {
                if (
                    $time_slot_filter !== null
                    && $this->getTimeSlotLabel($slot['from'], $slot['to'], $is_all_day_schedule) !== $time_slot_filter
                ) {
                    continue;
                }

                $rows[] = $this->composeRow($item, $object_id, $slot['from'], $slot['to'], $is_all_day_schedule);
            }
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
            'date_time' => $this->formatDateTime(
                (int) $record['slot_from'],
                (int) $record['slot_to'],
                (bool) $record['is_all_day']
            ),
            'title' => (string) $record['title'],
            'description' => nl2br((string) $record['description']),
        ];
    }

    protected function getSortCallable(string $key, int $direction): ?callable
    {
        return $key === 'date_time'
            ? static fn(array $a, array $b): int => ((int) $a['slot_from'] <=> (int) $b['slot_from']) * $direction
            : parent::getSortCallable($key, $direction);
    }

    /**
     * @param array{int, int}|null $period_bounds null = no period filter (clip by schedule availability only)
     * @return iterable<array{from: int, to: int}>
     */
    private function enumerateSlots(ilBookingSchedule $schedule, ?int $period_start = null, ?int $period_end = null): array
    {
        $definition = $schedule->getDefinition();
        if ($definition === []) {
            return [];
        }

        $now = time();
        $deadline = $schedule->getDeadline();
        $deadline_timestamp = $now + ($deadline * 3600);

        $availability_from = $schedule->getAvailabilityFrom()?->get(IL_CAL_UNIX);
        $availability_to = $schedule->getAvailabilityTo()?->get(IL_CAL_UNIX);

        $slots = [];

        foreach ($definition as $weekday_key => $day_slots) {
            $next_week_day = new DateTimeImmutable('next ' . self::WEEKDAYS_MAP[$weekday_key]);

            for ($i = 0; $i < self::MAX_WEEKS_IN_THE_FUTURE; $i++) {
                foreach ($day_slots as $time_range) {
                    [$start_string, $end_string] = explode('-', $time_range);
                    [$start_hour, $start_minute] = explode(':', $start_string);
                    [$end_hour, $end_minute] = explode(':', $end_string);

                    $start_timestamp = $next_week_day->setTime((int) $start_hour, (int) $start_minute, 0)->getTimestamp();
                    $end_timestamp = $next_week_day->setTime((int) $end_hour, (int) $end_minute, 0)->getTimestamp();

                    if (
                        (is_int($availability_from) && $start_timestamp < $availability_from)
                        || (is_int($availability_to) && $end_timestamp > $availability_to)
                    ) {
                        continue;
                    }

                    if (
                        ($deadline === 0 && $start_timestamp < $now)
                        || ($deadline === -1 && $end_timestamp < $now)
                        || ($start_timestamp < $deadline_timestamp)
                    ) {
                        continue;
                    }

                    if (
                        (is_int($period_start) && $start_timestamp < $period_start)
                        || (is_int($period_end) && $end_timestamp > $period_end)
                    ) {
                        continue;
                    }

                    $slots[] = [
                        'from' => $start_timestamp,
                        'to' => $end_timestamp,
                    ];
                }

                $next_week_day = $next_week_day->modify('+1 week');
            }
        }

        return $slots;
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function composeRow(
        array $item,
        int $object_id,
        int $slot_from,
        int $slot_to,
        bool $is_all_day = false
    ): array {
        $nr_items = (int) $item['nr_items'];
        $available = max($nr_items - $this->countActiveReservations($object_id, $slot_from, $slot_to), 0);

        return [
            'row_id' => "{$object_id}_{$slot_from}_{$slot_to}",
            'booking_object_id' => $object_id,
            'title' => (string) $item['title'],
            'description' => (string) ($item['description'] ?? ''),
            'nr_items' => $nr_items,
            'available' => $available,
            'is_available' => $available > 0,
            'has_user_booking' => $this->hasReservation($object_id, $slot_from, $slot_to, $this->user->getId()),
            'has_user_active_booking' => $this->hasActiveReservation($object_id, $slot_from, $slot_to, $this->user->getId()),
            'has_reservations' => $this->hasActiveReservation($object_id, $slot_from, $slot_to),
            'slot_from' => $slot_from,
            'slot_to' => $slot_to,
            'is_all_day' => $is_all_day,
            'schedule_id' => (int) $item['schedule_id'],
            'post_text' => (string) ($item['post_text'] ?? ''),
        ];
    }

    private function hasReservation(int $object_id, int $slot_from, int $slot_to, ?int $user_id = null): bool
    {
        return array_any(
            $this->getReservationsForObject($object_id),
            static fn(array $reservation): bool =>
                ($user_id === null || $reservation['user_id'] === $user_id)
                && $reservation['date_from'] === $slot_from
                && $reservation['date_to'] === $slot_to
        );
    }

    private function hasActiveReservation(int $object_id, int $slot_from, int $slot_to, ?int $user_id = null): bool
    {
        return array_any(
            $this->getReservationsForObject($object_id),
            static fn(array $reservation): bool =>
                ($user_id === null || $reservation['user_id'] === $user_id)
                && $reservation['date_from'] === $slot_from
                && $reservation['date_to'] === $slot_to
                && $reservation['status'] !== ilBookingReservation::STATUS_CANCELLED
        );
    }

    private function countReservations(int $object_id, int $slot_from, int $slot_to): int
    {
        return count(
            array_filter(
                $this->getReservationsForObject($object_id),
                static fn(array $reservation): bool =>
                    $reservation['date_from'] === $slot_from
                    && $reservation['date_to'] === $slot_to
            )
        );
    }

    private function countActiveReservations(int $object_id, int $slot_from, int $slot_to): int
    {
        return count(
            array_filter(
                $this->getReservationsForObject($object_id),
                static fn(array $reservation): bool =>
                    $reservation['date_from'] === $slot_from
                    && $reservation['date_to'] === $slot_to
                    && $reservation['status'] !== ilBookingReservation::STATUS_CANCELLED
            )
        );
    }

    public function formatDateTime(int $slot_from, int $slot_to, bool $is_all_day = false): string
    {
        $this->lng->loadLanguageModule('dateplaner');

        if ($is_all_day) {
            return ilDatePresentation::formatDate(new ilDateTime($slot_from, IL_CAL_UNIX, 'UTC'))
                . ", {$this->lng->txt('book_all_day')}";
        }

        return ilDatePresentation::formatPeriod(
            new ilDateTime($slot_from, IL_CAL_UNIX, 'UTC'),
            new ilDateTime($slot_to, IL_CAL_UNIX, 'UTC')
        );
    }

    private function isAllDaySchedule(ilBookingSchedule $schedule): bool
    {
        return $schedule->getScheduleType() === ilBookingSchedule::SCHEDULE_TYPE_ALL_DAY;
    }

    private function getTimeSlotLabel(int $slot_from, int $slot_to, bool $is_all_day): string
    {
        if ($is_all_day) {
            return $this->lng->txt('book_all_day');
        }

        $from = new DateTimeImmutable("@{$slot_from}");
        $to = new DateTimeImmutable("@{$slot_to}");

        return "{$from->format('H:i')} - {$to->format('H:i')}";
    }

    private function stringFilter(array $filter_data, string $key): ?string
    {
        return trim((string) ($filter_data[$key] ?? '')) ?: null;
    }

    /**
     * @return ?int[]
     */
    private function arrayFilter(array $filter_data, string $key): ?array
    {
        if (!isset($filter_data[$key]) || !is_array($filter_data[$key]) || $filter_data[$key] === []) {
            return null;
        }

        return array_values(array_map('intval', $filter_data[$key]));
    }

    /**
     * @return array{int, int}|null null when the period filter is not set — rows are not clipped by a default window
     */
    private function resolvePeriod(array $filter_data): ?array
    {
        $period = $filter_data['period'] ?? null;
        if (!is_array($period) || count($period) < 2) {
            return null;
        }

        $from = $this->parsePeriodEndpoint($period[0] ?? null);
        $to = $this->parsePeriodEndpoint($period[1] ?? null);

        if ($from === null && $to === null) {
            return null;
        }

        $default_start = $this->defaultPeriodStart()->getTimestamp();

        return [
            $from ?? $default_start,
            $to ?? PHP_INT_MAX,
        ];
    }

    private function defaultPeriodStart(): DateTimeImmutable
    {
        return new DateTimeImmutable('today', new DateTimeZone($this->userTimeZoneId()));
    }

    private function userTimeZoneId(): string
    {
        return $this->user->getTimeZone() ?: date_default_timezone_get();
    }

    private function parsePeriodEndpoint(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeImmutable) {
            return $value->getTimestamp();
        }

        try {
            return (new DateTimeImmutable((string) $value))->getTimestamp();
        } catch (Throwable) {
            return null;
        }
    }
}
