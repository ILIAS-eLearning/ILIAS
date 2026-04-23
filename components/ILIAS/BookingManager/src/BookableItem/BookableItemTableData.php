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

namespace ILIAS\BookingManager\BookableItem;

use ilBookingObject;
use ilBookingObjectGUI;
use ilBookingReservation;
use ilCalendarSettings;
use ilCalendarUtil;
use ilCtrl;
use ilDatePresentation;
use ilDateTime;
use ilObjBookingPool;
use ilObjUser;
use ilUtil;
use ilLanguage;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * Row data and cell building for the bookable items Kitchen Sink data table
 */
class BookableItemTableData
{
    public const COL_AVAIL = 'avail';
    public const COL_TIME = 'time';
    public const COL_TITLE = 'title';
    public const COL_DESC = 'description';

    /**
     * @var list<array<string,mixed>>|null
     */
    protected ?array $cache_rows = null;
    /**
     * @var array<int, true>
     */
    protected array $cache_keys = [];
    /**
     * @var array<int, list<array<string,mixed>>>
     */
    protected array $reservation_cache = [];

    public function __construct(
        protected ilBookingObjectGUI $parent,
        protected ilObjBookingPool $pool,
        protected int $ref_id,
        protected int $pool_id,
        protected bool $has_schedule,
        protected ?int $overall_limit,
        protected bool $active_management,
        protected string $process_class,
        protected AccessManager $access,
        protected ilObjUser $user,
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected ilLanguage $lng,
        protected ilCtrl $ctrl,
        protected bool $pool_uses_preferences = false
    ) {
    }

    public function hasDateTimeColumn(): bool
    {
        return $this->has_schedule;
    }

    public function getCtrl(): ilCtrl
    {
        return $this->ctrl;
    }

    public function getUser(): ilObjUser
    {
        return $this->user;
    }

    /**
     * @return array{0: bool, 1: bool, 2: int} may_edit, may_assign, current_user_bookings
     */
    public function getActionContextForRows(): array
    {
        $may_edit = $this->active_management && $this->access->canManageObjects($this->ref_id);
        $may_assign = $this->active_management && $this->access->canManageAllReservations($this->ref_id);
        $current_user_bookings = 0;
        if (!$this->has_schedule && $this->overall_limit) {
            foreach ($this->reservation_cache as $obj_rsv) {
                foreach ($obj_rsv as $rsv) {
                    if ((int) $rsv['status'] !== ilBookingReservation::STATUS_CANCELLED &&
                        (int) $rsv['user_id'] === $this->user->getId()) {
                        $current_user_bookings++;
                    }
                }
            }
        }
        return [$may_edit, $may_assign, $current_user_bookings];
    }

    public function withReservationCache(
        int $object_id,
        array $reservations
    ): self {
        $c = clone $this;
        $c->reservation_cache[$object_id] = $reservations;
        return $c;
    }

    public function isSchedulePool(): bool
    {
        return $this->has_schedule;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function reservationsForObject(int $objectId): array
    {
        return $this->reservation_cache[$objectId] ?? [];
    }

    public function userOverallLimit(): ?int
    {
        return $this->overall_limit;
    }

    /**
     * Same rule as the former per-row bulk-book flag: self-service may add a booking on this row.
     */
    public function rowStillAllowsUserBulkBook(array $r, int $poolUserBookings): bool
    {
        if (!$this->access->canManageOwnReservations($this->ref_id)) {
            return false;
        }
        $not_yet = isset($r['full_up']) ? (string) $r['full_up'] : (isset($r['not_yet']) ? (string) $r['not_yet'] : '');
        if ($not_yet !== '') {
            return false;
        }
        $oid = (int) $r['booking_object_id'];
        if ($this->has_schedule) {
            foreach ($this->reservation_cache[$oid] ?? [] as $i) {
                if ((int) $i['status'] === ilBookingReservation::STATUS_CANCELLED
                    || (int) $i['user_id'] !== (int) $this->user->getId()) {
                    continue;
                }
                if ((int) $i['date_from'] === (int) $r['slot_from'] && (int) $i['date_to'] === (int) $r['slot_to']) {
                    return false;
                }
            }
            return (int) ($r['nr_available'] ?? 0) > 0;
        }
        $n = 0;
        foreach ($this->reservation_cache[$oid] ?? [] as $i) {
            if ((int) $i['status'] !== ilBookingReservation::STATUS_CANCELLED) {
                $n++;
            }
        }
        if ((int) $r['nr_items'] <= $n) {
            return false;
        }
        if ($this->overall_limit !== null && $poolUserBookings >= (int) $this->overall_limit) {
            return false;
        }
        foreach ($this->reservation_cache[$oid] ?? [] as $i) {
            if ((int) $i['status'] !== ilBookingReservation::STATUS_CANCELLED
                && (int) $i['user_id'] === (int) $this->user->getId()) {
                return false;
            }
        }
        return true;
    }

    public function currentUserHasActiveBookingOnRow(array $r): bool
    {
        $oid = (int) $r['booking_object_id'];
        if ($this->has_schedule) {
            foreach ($this->reservation_cache[$oid] ?? [] as $i) {
                if ((int) $i['status'] === ilBookingReservation::STATUS_CANCELLED
                    || (int) $i['user_id'] !== (int) $this->user->getId()) {
                    continue;
                }
                if ((int) $i['date_from'] === (int) $r['slot_from'] && (int) $i['date_to'] === (int) $r['slot_to']) {
                    return true;
                }
            }
            return false;
        }
        foreach ($this->reservation_cache[$oid] ?? [] as $i) {
            if ((int) $i['status'] !== ilBookingReservation::STATUS_CANCELLED
                && (int) $i['user_id'] === (int) $this->user->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function loadAllRows(
        ?array $filter_data
    ): array {
        if ($this->cache_rows !== null) {
            return $this->cache_rows;
        }
        if ($this->has_schedule) {
            [$p_start, $p_end] = $this->parsePeriod($filter_data);
            $title_f = $this->parseTextFilter($filter_data, BookableItemTable::FILTER_TITLE);
            $desc_f = $this->parseTextFilter($filter_data, BookableItemTable::FILTER_DESC);
            $obj_f = $this->parseObjectFilter($filter_data);
            $this->cache_rows = ilBookableItemsSlotQuery::getSlotRows(
                $this->pool_id,
                $p_start,
                $p_end,
                $title_f,
                $desc_f,
                $obj_f
            );
        } else {
            $this->cache_rows = $this->loadNoScheduleRows($filter_data);
        }
        foreach ($this->cache_rows as $r) {
            $oid = (int) $r['booking_object_id'];
            if (!isset($this->reservation_cache[$oid])) {
                $item_rsv = ilBookingReservation::getList([$oid], 1000, 0, []);
                $this->reservation_cache[$oid] = $item_rsv['data'];
            }
        }
        return $this->cache_rows;
    }

    /**
     * @return list<array<string,mixed>>
     */
    protected function loadNoScheduleRows(
        ?array $filter_data
    ): array {
        $data = ilBookingObject::getList($this->pool_id, null);
        $title_f = $this->parseTextFilter($filter_data, BookableItemTable::FILTER_TITLE);
        if ($title_f !== null && $title_f !== '') {
            $data = array_values(array_filter($data, static function (array $row) use ($title_f) {
                return str_contains(strtolower((string) $row['title']), strtolower($title_f));
            }));
        }
        $desc_f = $this->parseTextFilter($filter_data, BookableItemTable::FILTER_DESC);
        $obj_f = $this->parseObjectFilter($filter_data);
        $rows = [];
        foreach ($data as $item) {
            $oid = (int) $item['booking_object_id'];
            if ($obj_f !== null && $obj_f !== [] && !in_array($oid, $obj_f, true)) {
                continue;
            }
            if ($desc_f !== null && $desc_f !== '' &&
                !str_contains(strtolower((string) ($item['description'] ?? '')), strtolower($desc_f))) {
                continue;
            }
            $item['is_slot'] = false;
            $item['slot_from'] = null;
            $item['slot_to'] = null;
            if (isset($item['full_up']) || isset($item['not_yet'])) {
                // keep flags from getList? getList for no schedule may not set these
            }
            $rows[] = $item;
        }
        return $rows;
    }

    /**
     * Date/time column: weekday (long), calendar date, time range (same day as {@see ilDatePresentation::formatPeriod} end).
     */
    protected function formatScheduleSlotForTable(int $slot_from, int $slot_to): string
    {
        $this->lng->loadLanguageModule('dateplaner');
        $tz = $this->user->getTimeZone();
        $start = new ilDateTime($slot_from, IL_CAL_UNIX, $tz);
        $end = new ilDateTime($slot_to - 1, IL_CAL_UNIX, $tz);
        if (!ilDateTime::_equals($start, $end, IL_CAL_DAY, $tz)) {
            return ilDatePresentation::formatPeriod($start, $end);
        }
        $wd_keys = [
            0 => 'Su_long',
            1 => 'Mo_long',
            2 => 'Tu_long',
            3 => 'We_long',
            4 => 'Th_long',
            5 => 'Fr_long',
            6 => 'Sa_long',
        ];
        $date_info = $start->get(IL_CAL_FKT_GETDATE, '', $tz);
        $wday = (int) $date_info['wday'];
        $weekday = $this->lng->txt($wd_keys[$wday]);
        $date_part = (int) $date_info['mday'] . '. ' .
            ilCalendarUtil::_numericMonthToString((int) $date_info['mon'], false, $this->lng) . ' ' .
            (int) $date_info['year'];
        if ($this->user->getTimeFormat() === ilCalendarSettings::TIME_FORMAT_12) {
            $t0 = $start->get(IL_CAL_FKT_DATE, 'g:ia', $tz);
            $t1 = $end->get(IL_CAL_FKT_DATE, 'g:ia', $tz);
        } else {
            $t0 = $start->get(IL_CAL_FKT_DATE, 'H:i', $tz);
            $t1 = $end->get(IL_CAL_FKT_DATE, 'H:i', $tz);
        }

        return $weekday . ', ' . $date_part . ', ' . $t0 . ' - ' . $t1;
    }

    /**
     * @param array<string,mixed> $row
     * @return array<string, mixed>
     */
    public function buildRowCells(array $row, int $current_user_bookings): array
    {
        $oid = (int) $row['booking_object_id'];
        $a_set = $row;
        $booking_possible = $this->access->canManageOwnReservations($this->ref_id);
        $has_booking = false;
        if ($this->has_schedule) {
            $res = $this->reservation_cache[$oid] ?? [];
            foreach ($res as $item) {
                if ((int) $item['status'] === ilBookingReservation::STATUS_CANCELLED) {
                    continue;
                }
                if ((int) $item['user_id'] !== (int) $this->user->getId()) {
                    continue;
                }
                if ((int) $item['date_from'] === (int) $row['slot_from'] &&
                    (int) $item['date_to'] === (int) $row['slot_to']) {
                    $has_booking = true;
                    break;
                }
            }
        } else {
            $res = $this->reservation_cache[$oid] ?? [];
            $cnt = 0;
            foreach ($res as $item) {
                if ((int) $item['status'] !== ilBookingReservation::STATUS_CANCELLED) {
                    $cnt++;
                    if ((int) $item['user_id'] === (int) $this->user->getId()) {
                        $has_booking = true;
                    }
                }
            }
            $nr = (int) $a_set['nr_items'];
            if ($nr <= $cnt || ($this->overall_limit && $current_user_bookings >= (int) $this->overall_limit)) {
                $booking_possible = false;
            }
            if ($has_booking) {
                $booking_possible = false;
            }
        }
        $not_yet = '';
        if (isset($a_set['full_up'])) {
            $booking_possible = false;
            $not_yet = (string) $a_set['full_up'];
        } elseif (isset($a_set['not_yet'])) {
            $not_yet = (string) $a_set['not_yet'];
        }
        $avail = '';
        if ($this->has_schedule) {
            $ok = $booking_possible && (int) $row['nr_available'] > 0 && $not_yet === '';
            $g = $ok
                ? $this->ui_factory->symbol()->icon()->custom(
                    ilUtil::getImagePath('standard/icon_ok.svg'),
                    $this->lng->txt('book_book'),
                    'small'
                )
                : $this->ui_factory->symbol()->icon()->custom(
                    ilUtil::getImagePath('standard/icon_not_ok.svg'),
                    $this->lng->txt('book_no_objects'),
                    'small'
                );
            $avail = $this->ui_renderer->render($g) . ' ' . (int) $row['nr_available'] . ' / ' . (int) $row['nr_items'];
            if ($not_yet !== '') {
                $avail .= ' ' . $not_yet;
            }
            if (!$ok) {
                $booking_possible = false;
            }
        } else {
            $cnt = 0;
            foreach ($this->reservation_cache[$oid] ?? [] as $item) {
                if ((int) $item['status'] !== ilBookingReservation::STATUS_CANCELLED) {
                    $cnt++;
                }
            }
            $nr = (int) $a_set['nr_items'];
            $avail_n = $nr - $cnt;
            $ok = $avail_n > 0 && $booking_possible && $not_yet === '';
            $g = $ok
                ? $this->ui_factory->symbol()->icon()->custom(
                    ilUtil::getImagePath('standard/icon_ok.svg'),
                    (string) $avail_n,
                    'small'
                )
                : $this->ui_factory->symbol()->icon()->custom(
                    ilUtil::getImagePath('standard/icon_not_ok.svg'),
                    (string) $avail_n,
                    'small'
                );
            $avail = $this->ui_renderer->render($g) . ' ' . $avail_n . ' / ' . (int) $a_set['nr_items'];
        }
        $time_str = '—';
        if ($this->has_schedule) {
            $time_str = $this->formatScheduleSlotForTable(
                (int) $row['slot_from'],
                (int) $row['slot_to']
            );
        }
        $cells = [
            self::COL_AVAIL => $avail,
        ];
        if ($this->has_schedule) {
            $cells[self::COL_TIME] = $time_str;
        }
        $cells[self::COL_TITLE] = (string) $a_set['title'];
        $cells[self::COL_DESC] = nl2br((string) ($a_set['description'] ?? ''));
        return $cells;
    }

    /**
     * @param array<string,mixed> $row
     */
    public function formatRowId(array $row): string
    {
        $oid = (int) $row['booking_object_id'];
        if ($this->has_schedule) {
            return 'bobj-' . $oid . '-' . (int) $row['slot_from'] . '-' . (int) $row['slot_to'];
        }
        return 'bobj-' . $oid;
    }

    /**
     * All row id strings for the current filter (same rows as the table, ignoring pagination).
     * Used when the data table multi-action "all objects" sends the placeholder {@see BookableItemTable::ROW_ID_ALL_OBJECTS}.
     *
     * @param array<string, mixed>|null $filter_data same shape as passed to {@see getRows()}
     * @return list<string>
     */
    public function getAllRowIdStringsForFilter(?array $filter_data): array
    {
        $this->cache_rows = null;
        $this->reservation_cache = [];
        $rows = $this->loadAllRows($filter_data);
        $ids = [];
        foreach ($rows as $row) {
            $ids[] = $this->formatRowId($row);
        }
        return $ids;
    }

    /**
     * @return array{object_id: int, from: ?int, to: ?int, is_slot: bool}|null
     */
    public static function parseRowIdForBulk(string $row_id): ?array
    {
        $p = explode('-', $row_id);
        if (count($p) === 2 && $p[0] === 'bobj') {
            return ['object_id' => (int) $p[1], 'from' => null, 'to' => null, 'is_slot' => false];
        }
        if (count($p) === 4 && $p[0] === 'bobj') {
            return [
                'object_id' => (int) $p[1],
                'from' => (int) $p[2],
                'to' => (int) $p[3],
                'is_slot' => true,
            ];
        }
        return null;
    }
}
