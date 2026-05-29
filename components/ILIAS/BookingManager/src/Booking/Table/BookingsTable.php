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
use DateTimeZone;
use Generator;
use Throwable;
use ilBookingObject;
use ilBookingParticipant;
use ilBookingReservation;
use ilBookingReservationsGUI;
use ilCtrlInterface;
use ilGlobalTemplateInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Booking\BookingTableActionsFactory;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Common\Table\Table;
use ILIAS\BookingManager\Common\Table\TableActionExecutorTrait;
use ILIAS\BookingManager\Common\Table\TableActions;
use ILIAS\BookingManager\Reservations\ReservationDBRepository;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Dropdown\Standard as StandardDropdown;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;
use ILIAS\UI\Component\Input\Field\Factory as InputFactory;
use ILIAS\UI\Component\Table\Column\Factory as ColumnFactory;
use ILIAS\UI\Component\Table\Data;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ilObjBookingPool;
use ilObjUser;
use ilTable2GUI;
use ilUIService;
use ilUserUtil;
use ilPublicProfileBaseClassGUI;
use ILIAS\User\Profile\PublicProfileGUI;
use ilUIFilterServiceSessionGateway;
use ILIAS\BookingManager\Settings\Settings;

abstract class BookingsTable implements Table
{
    use TableActionExecutorTrait;

    public const ROW_ID_PARAMETER = 'booking_id';

    public const ACTION_PARAMETER = 'action';

    public const ACTION_TYPE_PARAMETER = 'action_type';

    protected readonly array $booking_items;

    protected readonly array $bookings;

    protected readonly array $participants;

    protected readonly ColumnFactory $column_factory;

    protected readonly InputFactory $input_factory;

    public function __construct(
        protected readonly UIFactory $ui_factory,
        protected readonly UIRenderer $ui_renderer,
        protected readonly AccessManager $access,
        protected readonly ilGlobalTemplateInterface $tpl,
        protected readonly Refinery $refinery,
        protected readonly Language $lng,
        protected readonly HttpService $http,
        protected readonly ilObjUser $user,
        protected readonly ReservationDBRepository $reservation_repository,
        protected readonly ilCtrlInterface $ctrl,
        protected readonly ilUIService $ui_service,
        protected readonly Settings $settings,
        protected readonly ilObjBookingPool $booking_pool
    ) {
        $this->booking_items = array_column(
            ilBookingObject::getList($this->booking_pool->getId()),
            null,
            'booking_object_id'
        );

        $filter = [];
        if (
            !$this->access->canManageAllReservations($this->booking_pool->getRefId())
            && !$this->access->canReadPublicLog($this->booking_pool->getRefId())
        ) {
            $filter['user_id'] = $this->user->getId();
        }
        $this->bookings = array_column(
            ilBookingReservation::getList(array_keys($this->booking_items), 1000, 0, $filter)['data'],
            null,
            'booking_reservation_id'
        );

        $this->participants = array_column(
            ilBookingParticipant::getList($this->booking_pool->getId()),
            null,
            'user_id'
        );

        foreach (['dateplaner', 'tbl'] as $module) {
            $this->lng->loadLanguageModule($module);
        }

        $this->column_factory = $this->ui_factory->table()->column();
        $this->input_factory = $this->ui_factory->input()->field();
    }

    public function getTableId(): string
    {
        return static::ID;
    }

    protected function getUserPresentationName(int $user_id): string
    {
        return str_replace('&', '&amp;', htmlentities(ilUserUtil::getNamePresentation($user_id)));
    }

    abstract public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator;

    abstract protected function getColumns(): array;

    abstract protected function getFilterInputs();

    protected function loadRecords(mixed $filter_data): array
    {
        if (!$filter_data instanceof Filter) {
            return $this->bookings;
        }

        $bookings = $this->bookings;

        foreach ($this->ui_service->filter()->getData($filter_data) ?? [] as $key => $filter_data_value) {
            if ($filter_data_value === null || $filter_data_value === '') {
                continue;
            }

            $bookings = (match ($key) {
                'object' => static fn(array $bookings): array => array_filter(
                    $bookings,
                    static fn(array $booking): bool => (int) $booking['object_id'] === (int) $filter_data_value
                ),
                'object_title_or_description' => static fn(array $bookings): array => array_filter(
                    $bookings,
                    static function (array $booking) use ($filter_data_value): bool {
                        $filter_data_value = strtolower($filter_data_value);
                        return
                            str_contains(strtolower($booking['title']), $filter_data_value)
                            || str_contains(strtolower($booking['message']), $filter_data_value);
                    }
                ),
                'period' => function (array $bookings) use ($filter_data_value): array {
                    $bounds = $this->resolveBookingPeriodBounds($filter_data_value);
                    if ($bounds === null) {
                        return $bookings;
                    }

                    [$period_from, $period_to] = $bounds;
                    return array_filter(
                        $bookings,
                        static fn(array $booking): bool =>
                            $booking['date_from'] >= $period_from && $booking['date_to'] <= $period_to
                    );
                },
                'time_slot' => static fn(array $bookings): array => array_filter(
                    $bookings,
                    static function (array $booking) use ($filter_data_value): bool {
                        $date_from = new DateTimeImmutable("@{$booking['date_from']}");
                        $booking['date_to']++;
                        $date_to = new DateTimeImmutable("@{$booking['date_to']}");
                        return "{$date_from->format('H:i')} - {$date_to->format('H:i')}" === $filter_data_value;
                    }
                ),
                'past_bookings' => static function (array $bookings) use ($filter_data_value): array {
                    $now = new DateTimeImmutable();
                    return array_filter(
                        $bookings,
                        static fn(array $booking): bool => (int) $filter_data_value === 1
                            ? new DateTimeImmutable("@{$booking['date_to']}") < $now
                            : new DateTimeImmutable("@{$booking['date_to']}") >= $now
                    );
                },
                'status' => static fn(array $bookings): array => array_filter(
                    $bookings,
                    static fn(array $booking): bool => match ((int) $filter_data_value) {
                        ilBookingReservation::STATUS_IN_USE =>
                            $booking['status'] === ilBookingReservation::STATUS_IN_USE || $booking['status'] === 0,
                        ilBookingReservation::STATUS_CANCELLED =>
                            $booking['status'] === ilBookingReservation::STATUS_CANCELLED,
                        default => true
                    }
                ),
                'user' => static fn(array $bookings): array => array_filter(
                    $bookings,
                    static fn(array $booking): bool => (int) $booking['user_id'] === (int) $filter_data_value
                ),
                default => static fn(array $bookings): array => $bookings
            })($bookings);
        }

        return $bookings;
    }

    protected function sortRecords(Order $order, array $records): array
    {
        $order_data = $order->get();
        if ($order_data === []) {
            return $records;
        }

        $users = [];
        foreach ($order_data as $key => $value) {
            $order_direction = $value === Order::DESC ? -1 : 1;
            $callable = match ($key) {
                'title' => static fn(array $record_a, array $record_b): int => strcasecmp(
                    $record_a['title'],
                    $record_b['title']
                ) * $order_direction,
                'status' => static fn(array $record_a, array $record_b): int =>
                    ($record_a['status'] <=> $record_b['status']) * $order_direction,
                'date' => static fn(array $record_a, array $record_b): int =>
                    ($record_a['date_from'] <=> $record_b['date_from']) * $order_direction,
                'week' => static function (array $record_a, array $record_b) use ($order_direction): int {
                    $week_a = (int) (new DateTimeImmutable("@{$record_a['date_from']}"))->format('W');
                    $week_b = (int) (new DateTimeImmutable("@{$record_b['date_from']}"))->format('W');
                    return ($week_a <=> $week_b) * $order_direction;
                },
                'weekday' => static function (array $record_a, array $record_b) use ($order_direction): int {
                    $week_a = (int) (new DateTimeImmutable("@{$record_a['date_from']}"))->format('N');
                    $week_b = (int) (new DateTimeImmutable("@{$record_b['date_from']}"))->format('N');
                    return ($week_a <=> $week_b) * $order_direction;
                },
                'time_slot' => static function (array $record_a, array $record_b) use ($order_direction): int {
                    $date_from_a = new DateTimeImmutable("@{$record_a['date_from']}");
                    $record_a['date_to']++;
                    $date_to_a = new DateTimeImmutable("@{$record_a['date_to']}");
                    $time_slot_a = "{$date_from_a->format('H:i')} - {$date_to_a->format('H:i')}";

                    $date_from_b = new DateTimeImmutable("@{$record_b['date_from']}");
                    $record_b['date_to']++;
                    $date_to_b = new DateTimeImmutable("@{$record_b['date_to']}");
                    $time_slot_b = "{$date_from_b->format('H:i')} - {$date_to_b->format('H:i')}";

                    return strcasecmp($time_slot_a, $time_slot_b) * $order_direction;
                },
                'unit_count' => static fn(array $record_a, array $record_b): int => 0 * $order_direction,
                'message' => static fn(array $record_a, array $record_b): int => strcasecmp(
                    (string) ($record_a['message'] ?? ''),
                    (string) ($record_b['message'] ?? '')
                ) * $order_direction,
                'user' => function (array $record_a, array $record_b) use (&$users, $order_direction): int {
                    $user_a = $record_a['user_id'];
                    $user_b = $record_b['user_id'];
                    $user_a = $users[$user_a] ??= $this->getUserPresentationName($user_a);
                    $user_b = $users[$user_b] ??= $this->getUserPresentationName($user_b);
                    return strcasecmp($user_a, $user_b) * $order_direction;
                } ,
                default => null
            };

            if ($callable === null) {
                continue;
            }

            usort($records, $callable);
        }

        return $records;
    }

    protected function limitRecords(Range $range, array $records): array
    {
        return array_slice($records, $range->getStart(), $range->getLength());
    }

    public function getFilter(): ?Filter
    {
        $filter_inputs = $this->getFilterInputs();

        $filter_inputs = $this->presetFilterInputs($filter_inputs);

        if ($filter_inputs === []) {
            return null;
        }

        return $this->ui_service->filter()->standard(
            "{$this->getTableId()}_filter",
            $this->ctrl->getLinkTargetByClass(ilBookingReservationsGUI::class),
            $filter_inputs,
            array_fill(0, count($filter_inputs), true),
            true,
            true
        );
    }

    private function presetFilterInputs(array $filter_inputs): array
    {
        $query_params = $this->http->getRequest()->getQueryParams();

        if (
            $this->access->canManageAllReservations($this->booking_pool->getRefId())
            || $this->access->canReadPublicLog($this->booking_pool->getRefId())
        ) {
            $user_id = $query_params['user_id'] ?? null;
            if (is_numeric($user_id)) {
                $filter_inputs['user'] = $filter_inputs['user']->withValue((int) $user_id);
            }
        } elseif ($this->access->canManageOwnReservations($this->booking_pool->getRefId())) {
            $filter_inputs['user'] = $filter_inputs['user']->withValue($this->user->getId());
        }

        $object_id = $query_params['object_id'] ?? null;
        if (is_numeric($object_id)) {
            $filter_inputs['object'] = $filter_inputs['object']->withValue((int) $object_id);
        }

        if ($this->settings->getReservationPeriod() > 0) {
            $filter_inputs['period'] = $filter_inputs['period']->withValue(
                [
                    new DateTimeImmutable('today 00:00:00'),
                    new DateTimeImmutable("today +{$this->settings->getReservationPeriod()} days 23:59:59")
                ]
            );
        }

        $period_from = $query_params['period_from'] ?? null;
        $period_to = $query_params['period_to'] ?? null;
        if (is_numeric($period_from) && is_numeric($period_to)) {
            $filter_inputs['period'] = $filter_inputs['period']->withValue(
                [
                    new DateTimeImmutable("@{$period_from}"),
                    new DateTimeImmutable("@{$period_to}")
                ]
            );

        }

        (new ilUIFilterServiceSessionGateway())->reset(static::ID . '_filter');

        return $filter_inputs;
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->bookings);
    }

    public function getComponents(URLBuilder $url_builder): array
    {
        $filter = $this->getFilter();
        $table = $this->getTable($url_builder)->withFilter($filter);
        return array_filter([$filter, $table, $this->ui_factory->divider()->horizontal(), $this->getExportDropdown()]);
    }

    protected function acquireParameters(URLBuilder $url_builder): array
    {
        return $url_builder->acquireParameters(
            [$this->getTableId()],
            self::ROW_ID_PARAMETER,
            self::ACTION_PARAMETER,
            self::ACTION_TYPE_PARAMETER
        );
    }

    private function getTable(URLBuilder $url_builder): Data
    {
        return
            $this->ui_factory->table()->data(
                $this,
                $this->lng->txt('bookings'),
                $this->getColumns()
            )
            ->withActions($this->getTableActions()->getEnabledActions(...$this->acquireParameters($url_builder)))
            ->withRequest($this->http->getRequest())
            ->withId($this->getTableId());
    }

    private function getExportDropdown(): StandardDropdown
    {
        $parameter = "bkrsv{$this->booking_pool->getRefId()}_xpt";
        $export_formats = [ilTable2GUI::EXPORT_EXCEL => 'tbl_export_excel' , ilTable2GUI::EXPORT_CSV => 'tbl_export_csv'];
        $actions = [];

        foreach ($export_formats as $format => $caption_lng_id) {
            $this->ctrl->setParameterByClass(ilBookingReservationsGUI::class, $parameter, $format);
            $actions[] = $this->ui_factory->link()->standard(
                $this->lng->txt($caption_lng_id),
                $this->ctrl->getLinkTargetByClass(
                    ilBookingReservationsGUI::class,
                    ilBookingReservationsGUI::DEFAULT_CMD
                )
            );
            $this->ctrl->setParameterByClass(ilBookingReservationsGUI::class, $parameter, null);
        }

        return $this->ui_factory->dropdown()->standard($actions)->withLabel($this->lng->txt('export'));
    }

    /**
     * @return string[]
     */
    protected function getUsers(?int $user_id = null): array
    {
        if (is_int($user_id)) {
            return [$user_id => $this->getUserPresentationName($user_id)];
        }

        return array_map(
            fn(array $participant): string => $this->getUserPresentationName($participant['user_id']),
            $this->participants
        );
    }

    /**
     * @return array<string, string>
     */
    protected function getStatuses(): array
    {
        return [
            ilBookingReservation::STATUS_IN_USE => $this->lng->txt('book_not_cancelled'),
            ilBookingReservation::STATUS_CANCELLED => $this->lng->txt('book_reservation_status_5')
        ];
    }

    protected function getTableActions(): TableActions
    {
        return (new BookingTableActionsFactory(
            $this->ctrl,
            $this->lng,
            $this->tpl,
            $this->ui_factory,
            $this->ui_renderer,
            $this->refinery,
            $this->http,
            $this->access,
            $this->reservation_repository,
            $this->booking_pool,
            $this->bookings
        ))->getTableActions();
    }

    protected function getUserComponent(int $user_id)
    {
        $user_name = $this->getUserPresentationName($user_id);
        if (!ilUserUtil::hasPublicProfile($user_id)) {
            return $this->ui_factory->link()->standard($user_name, '')->withDisabled(true);
        }

        $this->ctrl->setParameterByClass(PublicProfileGUI::class, 'user_id', $user_id);

        return  $this->ui_factory->link()->standard(
            $user_name,
            $this->ctrl->getLinkTargetByClass(
                [ilPublicProfileBaseClassGUI::class, PublicProfileGUI::class],
                'getHTML'
            )
        );
    }

    private function resolveBookingPeriodBounds(mixed $period): ?array
    {
        if (!$period) {
            return null;
        }

        $from = $this->parseBookingPeriodValue($period[0] ?? null);
        $to = $this->parseBookingPeriodValue($period[1] ?? null);

        if ($from === null && $to === null) {
            return null;
        }

        return [
            $from ?? PHP_INT_MIN,
            $to ?? PHP_INT_MAX,
        ];
    }

    private function parseBookingPeriodValue(mixed $value): ?int
    {
        if (!$value) {
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
