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

use Generator;
use ilBookingObject;
use ilBookingObjectGUI;
use ilCtrl;
use ilLanguage;
use ilObjBookingPool;
use ilUIService;
use ILIAS\BookingManager\Common\Table\Table;
use ILIAS\BookingManager\Common\Table\TableActionExecutorTrait;
use ILIAS\BookingManager\Common\Table\TableActions;
use ILIAS\BookingManager\HttpService;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Input\Container\Filter\Standard as FilterStandard;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Component;
use DateTime;
use ilObjUser;
use ilCalendarUtil;
use ilDate;
use ilCalendarUserSettings;
use ilBookingSchedule;
use ilBookingReservation;
use ILIAS\Data\Factory;

class BookableItemTable implements Table
{
    use TableActionExecutorTrait;

    public const string ID = 'bookbktbl';
    public const string FILTER_ID = 'bookbkf';
    public const string ROW_ID_ALL_OBJECTS = 'ALL_OBJECTS';
    public const string ROW_ID_PARAMETER = 'row_ids';
    public const string ACTION_PARAMETER = 'action';
    public const string ACTION_TYPE_PARAMETER = 'action_type';
    public const string FILTER_TITLE = 'title';
    public const string FILTER_DESC = 'desc';
    public const string FILTER_OBJECTS = 'objs';
    public const string FILTER_PERIOD = 'period';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly TableActions $table_actions,
        private readonly HttpService $http_service,
        private readonly ilUIService $ui_service,
        private readonly ServerRequestInterface $request,
        private readonly ilBookingObjectGUI $parent,
        private readonly ilCtrl $ctrl,
        private readonly ilObjUser $user,
        private readonly ilObjBookingPool $pool,
        private readonly int $ref_id,
        private readonly int $pool_id,
        private readonly bool $has_schedule,
        private readonly bool $pool_uses_preferences,
        private readonly BookableItemTableData $data
    ) {
    }

    /**
     * @return array<Component>
     */
    public function getComponents(URLBuilder $url_builder): array
    {
        $filter = $this->getFilterComponent($this->ctrl->getLinkTarget($this->parent, 'render'));
        $columns = $this->getColumns();
        if (!$this->has_schedule) {
            unset($columns[BookableItemTableData::COL_TIME]);
        }

        $table = $this->ui_factory->table()->data($this, $this->lng->txt('book_booking_objects'), $columns)
            ->withActions($this->table_actions->getEnabledActions(...$this->acquireParameters($url_builder)))
            ->withRequest($this->request)
            ->withId(self::ID)
            ->withFilter($this->ui_service->filter()->getData($filter));

        return [$filter, $table];
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->loadRecords(is_array($filter_data) ? $filter_data : null));
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
        $rows = $this->loadRecords(is_array($filter_data) ? $filter_data : null);

        $order_data = $order->get();
        if ($order_data !== []) {
            $order_field = array_keys($order_data)[0];
            $order_direction = $order_data[$order_field];

            usort($rows, function (array $a, array $b) use ($order_field, $order_direction) {
                $a_val = $a[$order_field] ?? '';
                $b_val = $b[$order_field] ?? '';

                $result = $a_val <=> $b_val;
                return $order_direction === Order::ASC ? $result : -$result;
            });
        }

        $offset = $range->getStart();
        $length = $range->getLength();
        $rows = array_slice($rows, $offset, $length);

        [$may_edit, $may_assign, $current_user_bookings] = $this->data->getActionContextForRows();

        foreach ($rows as $row) {
            $row_id = $this->data->formatRowId($row);
            $cells = $this->data->buildRowCells($row, $current_user_bookings);

            yield $this->table_actions->onDataRow(
                $row_builder->buildDataRow($row_id, $cells),
                [
                    'row' => $row,
                    'may_edit' => $may_edit,
                    'may_assign' => $may_assign,
                    'current_user_bookings' => $current_user_bookings,
                ]
            );
        }
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Column\Column>
     */
    private function getColumns(): array
    {
        return [
            BookableItemTableData::COL_AVAIL => $this->ui_factory->table()->column()->text(
                $this->lng->txt('book_table_col_availability')
            )->withIsSortable(false),
            BookableItemTableData::COL_TIME => $this->ui_factory->table()->column()->text(
                $this->lng->txt('book_table_col_datetime')
            )->withIsSortable($this->has_schedule),
            BookableItemTableData::COL_TITLE => $this->ui_factory->table()->column()->text($this->lng->txt('title')),
            BookableItemTableData::COL_DESC => $this->ui_factory->table()->column()->text($this->lng->txt('description')),
        ];
    }

    /**
     * @return array<ParticipantRecord>
     */
    private function loadRecords(?array $filter_data): array
    {
        if (!$this->has_schedule) {
            return $this->loadNoScheduleRows($filter_data);
        }

        [$start, $end] = $this->parsePeriod($filter_data);

        return $this->getSlotRows(
            $this->pool_id,
            $start,
            $end,
            isset($filter_data[self::FILTER_TITLE]) && $filter_data[self::FILTER_TITLE] !== '' ? $filter_data[self::FILTER_TITLE] : null,
            isset($filter_data[self::FILTER_DESC]) && $filter_data[self::FILTER_DESC] !== '' ? $filter_data[self::FILTER_DESC] : null,
            isset($filter_data[self::FILTER_OBJECTS]) && !empty($filter_data[self::FILTER_OBJECTS])
                ? array_map('intval', $filter_data[self::FILTER_OBJECTS])
                : null
        );
    }


    /**
     * @return list<array<string, mixed>>
     */
    public function getSlotRows(
        int $pool_id,
        ilDate $period_start,
        ilDate $period_end,
        ?string $title_filter,
        ?string $description_filter,
        ?array $bookable_item_ids_filter
    ): array {
        $rows = [];
        $map = ['mo', 'tu', 'we', 'th', 'fr', 'sa', 'su'];

        foreach (ilBookingObject::getList($pool_id) as $obj_row) {
            $booking_object_id = (int) $obj_row['booking_object_id'];
            if ($bookable_item_ids_filter !== null && $bookable_item_ids_filter !== [] && !in_array($booking_object_id, $bookable_item_ids_filter, true)) {
                continue;
            }

            if (
                $title_filter !== null
                && $title_filter !== ''
                && !str_contains(strtolower((string) $obj_row['title']), strtolower($title_filter))
            ) {
                continue;
            }

            if ($description_filter !== null && $description_filter !== '') {
                $desc = (string) ($obj_row['description'] ?? '');
                if (!str_contains(strtolower($desc), strtolower($description_filter))) {
                    continue;
                }
            }

            $schedule = new ilBookingSchedule((new ilBookingObject($booking_object_id))->getScheduleId());
            $availability_from = $schedule->getAvailabilityFrom() && !$schedule->getAvailabilityFrom()->isNull()
                ? $schedule->getAvailabilityFrom()->get(IL_CAL_DATE)
                : null;
            $availability_to = $schedule->getAvailabilityTo() && !$schedule->getAvailabilityTo()->isNull()
                ? $schedule->getAvailabilityTo()->get(IL_CAL_DATE)
                : null;

            $end_date_str = $period_end->get(IL_CAL_DATE);
            $day = clone $period_start;
            while (true) {
                if ($day->get(IL_CAL_DATE) > $end_date_str) {
                    break;
                }
                $date_info = $day->get(IL_CAL_FKT_GETDATE, '', 'UTC');

                if ($availability_from || $availability_to) {
                    $today = $day->get(IL_CAL_DATE);
                    if ($availability_from && $availability_from > $today) {
                        $day->increment(IL_CAL_DAY, 1);
                        continue;
                    }
                    if ($availability_to && $availability_to < $today) {
                        $day->increment(IL_CAL_DAY, 1);
                        continue;
                    }
                }

                $slots = [];
                if (isset($schedule->getDefinition()[$map[$date_info['isoday'] - 1]])) {
                    foreach ($schedule->getDefinition()[$map[$date_info['isoday'] - 1]] as $slot) {
                        $slot = explode('-', $slot);
                        $slots[] = [
                            'from' => str_replace(':', '', $slot[0]),
                            'to' => str_replace(':', '', $slot[1]),
                        ];
                    }
                }

                foreach ($slots as $slot) {
                    $slot_from = mktime(
                        (int) substr($slot['from'], 0, 2),
                        (int) substr($slot['from'], 2, 2),
                        0,
                        (int) $date_info['mon'],
                        (int) $date_info['mday'],
                        (int) $date_info['year']
                    );
                    $slot_to = mktime(
                        (int) substr($slot['to'], 0, 2),
                        (int) substr($slot['to'], 2, 2),
                        0,
                        (int) $date_info['mon'],
                        (int) $date_info['mday'],
                        (int) $date_info['year']
                    );

                    $nr_available = ilBookingReservation::getAvailableObject(
                        [$booking_object_id],
                        $slot_from,
                        $slot_to - 1,
                        false,
                        true
                    );

                    if ($schedule->getDeadline() >= 0) {
                        if ($slot_from < (time() + $schedule->getDeadline() * 60 * 60)) {
                            continue;
                        }
                    } elseif ($slot_to < time()) {
                        continue;
                    }

                    $rows[] = [
                        'booking_object_id' => $booking_object_id,
                        'title' => $obj_row['title'],
                        'description' => $obj_row['description'] ?? '',
                        'slot_from' => $slot_from,
                        'slot_to' => $slot_to,
                        'nr_available' => array_sum($nr_available),
                        'nr_items' => (int) $obj_row['nr_items'],
                        'post_text' => $obj_row['post_text'] ?? '',
                        'post_file' => $obj_row['post_file'] ?? null,
                        'obj_info_rid' => $obj_row['obj_info_rid'] ?? null,
                    ];
                }

                $day->increment(IL_CAL_DAY, 1);
            }
        }

        usort($rows, static fn(array $a, array $b): int =>
            $a['slot_from'] === $b['slot_from']
                ? strcmp((string) $a['title'], (string) $b['title'])
                : $a['slot_from'] <=> $b['slot_from']);

        return $rows;
    }

    private function getFilterComponent(string $action): FilterStandard
    {
        $field_factory = $this->ui_factory->input()->field();

        $filter_inputs = [
            self::FILTER_TITLE => $field_factory->text($this->lng->txt('title')),
            self::FILTER_DESC => $field_factory->text($this->lng->txt('description')),
            self::FILTER_OBJECTS => $field_factory->multiSelect(
                $this->lng->txt('book_filter_objects'),
                array_map(
                    static fn(array $item): string => $item['title'],
                    ilBookingObject::getList($this->pool_id)
                )
            ),
        ];

        if ($this->has_schedule) {
            $filter_inputs[self::FILTER_PERIOD] = $field_factory->duration($this->lng->txt('book_filter_period'))
                ->withFormat($this->data->getUser()->getDateTimeFormat())
                ->withUseTime(true)
                ->withLabels($this->lng->txt('book_filter_start_date'), $this->lng->txt('book_filter_end_date'));
        }

        return $this->ui_service->filter()->standard(
            self::FILTER_ID,
            $action,
            $filter_inputs,
            array_fill(0, count($filter_inputs), true),
            false,
            true
        );
    }

    /**
     * Filter data in the same shape as passed to {@see getRows()}.
     */
    public function getFilterDataForActions(): ?array
    {
        return $this->ui_service->filter()->getData(
            $this->getFilterComponent($this->ctrl->getLinkTarget($this->parent, 'render'))
        );
    }

    /**
     * @return array{URLBuilder, URLBuilderToken, URLBuilderToken, URLBuilderToken}
     */
    protected function acquireParameters(URLBuilder $url_builder): array
    {
        return $url_builder->acquireParameters(
            [self::ID],
            self::ROW_ID_PARAMETER,
            self::ACTION_PARAMETER,
            self::ACTION_TYPE_PARAMETER
        );
    }

    /**
     * @return array{0: ilDate, 1: ilDate}
     */
    private function parsePeriod(?array $filter_data): array
    {
        [$default_start, $default_end] = $this->defaultPeriodFromUserWeek();
        if (
            $filter_data === null
            || !isset($filter_data[self::FILTER_PERIOD][0])
            || !isset($filter_data[self::FILTER_PERIOD][1])
        ) {
            return [$default_start, $default_end];
        }

        [$start, $end] = $filter_data[self::FILTER_PERIOD];
        $start = new ilDate((new DateTime($start))->getTimestamp(), IL_CAL_UNIX);
        $end = new ilDate((new DateTime($end))->getTimestamp(), IL_CAL_UNIX);

        return $start > $end ? [$default_start, $default_end] : [$start, $end];
    }

    /**
     * @return array{0: ilDate, 1: ilDate}
     */
    private function defaultPeriodFromUserWeek(): array
    {
        $weekday_list = ilCalendarUtil::_buildWeekDayList(
            new ilDate(time(), IL_CAL_UNIX),
            ilCalendarUserSettings::_getInstanceByUserId($this->user->getId())->getWeekStart()
        )->get();

        return [
            new ilDate(current($weekday_list)->get(IL_CAL_UNIX), IL_CAL_UNIX),
            new ilDate(end($weekday_list)->get(IL_CAL_UNIX), IL_CAL_UNIX),
        ];
    }

    public function getActionUrlBuilderForExecuteTableAction(): URLBuilder
    {
        $data_factory = new Factory();
        return new URLBuilder($data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTarget(
                $this->parent,
                'executeTableAction'
            )
        ));
    }

    public static function forObjectList(ilBookingObjectGUI $gui): self
    {
        global $DIC;
        $refinery = $DIC->refinery();
        $http_service = new HttpService($DIC->http(), $refinery);
        $internal_gui = $gui->getBookingGuiService();
        $ref_id = $gui->getPoolRefId();
        $has_schedule = $gui->hasPoolSchedule();
        $data = new BookableItemTableData(
            $gui,
            $gui->getPool(),
            $ref_id,
            $gui->getPoolObjId(),
            $has_schedule,
            $gui->getPoolOverallLimit(),
            $gui->isManagementActivated(),
            $internal_gui->process()->getProcessClass($has_schedule),
            $DIC->bookingManager()->internal()->domain()->access(),
            $DIC->user(),
            $DIC->ui()->factory(),
            $DIC->ui()->renderer(),
            $DIC->language(),
            $DIC->ctrl(),
            $gui->getPoolUsesPreferences()
        );
        $bookable_table = null;
        $process_with = $internal_gui->process()->getProcessClass(true);
        $process_without = $internal_gui->process()->getProcessClass(false);
        $table = new self(
            $DIC->ui()->factory(),
            $DIC->language(),
            new TableActions(
                $DIC->ctrl(),
                $DIC->language(),
                $DIC['tpl'],
                $DIC->ui()->factory(),
                $DIC->ui()->renderer(),
                $refinery,
                $http_service,
                [
                    BookableItemTableBookAction::ACTION_ID => new BookableItemTableBookAction(
                        $DIC->ui()->factory(),
                        $DIC->language(),
                        $DIC->bookingManager()->internal()->domain()->access(),
                        $gui,
                        $DIC->http(),
                        $refinery,
                        $ref_id,
                        static function () use (&$bookable_table): ?array {
                            return $bookable_table?->getFilterDataForActions();
                        },
                        $data
                    ),
                    BookableItemTableAssignParticipantAction::ACTION_ID => new BookableItemTableAssignParticipantAction(
                        $DIC->ui()->factory(),
                        $DIC->language(),
                        $DIC->bookingManager()->internal()->domain()->access(),
                        $DIC->ctrl(),
                        $http_service,
                        $gui,
                        $ref_id,
                        $process_with,
                        $process_without,
                        $data
                    ),
                    BookableItemTableEditAction::ACTION_ID => new BookableItemTableEditAction(
                        $DIC->ui()->factory(),
                        $DIC->language(),
                        $DIC->bookingManager()->internal()->domain()->access(),
                        $DIC->ctrl(),
                        $http_service,
                        $gui,
                        $ref_id
                    ),
                    BookableItemTableDeleteAction::ACTION_ID => new BookableItemTableDeleteAction(
                        $DIC->ui()->factory(),
                        $DIC->language(),
                        $DIC->bookingManager()->internal()->domain()->access(),
                        $DIC['tpl'],
                        $DIC->http(),
                        $refinery,
                        $gui,
                        $ref_id,
                        static function () use (&$bookable_table): ?array {
                            return $bookable_table?->getFilterDataForActions();
                        },
                        $data
                    ),
                    BookableItemTableLogAction::ACTION_ID => new BookableItemTableLogAction(
                        $DIC->ui()->factory(),
                        $DIC->language(),
                        $DIC->bookingManager()->internal()->domain()->access(),
                        $DIC->ctrl(),
                        $http_service,
                        $gui,
                        $ref_id,
                        $data
                    ),
                    BookableItemTableCancelBookingAction::ACTION_ID => new BookableItemTableCancelBookingAction(
                        $DIC->ui()->factory(),
                        $DIC->language(),
                        $DIC->bookingManager()->internal()->domain()->access(),
                        $DIC->ctrl(),
                        $http_service,
                        $gui,
                        $ref_id,
                        $has_schedule,
                        $data
                    ),
                ]
            ),
            $http_service,
            $DIC->uiService(),
            $http_service->getRequest(),
            $gui,
            $DIC->ctrl(),
            $DIC->user(),
            $gui->getPool(),
            $ref_id,
            $gui->getPoolObjId(),
            $has_schedule,
            $gui->getPoolUsesPreferences(),
            $data
        );
        $bookable_table = $table;
        return $table;
    }
}
