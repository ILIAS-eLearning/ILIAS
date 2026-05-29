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

use Generator;
use ilBookingObject;
use ilBookingReservation;
use ilCtrlInterface;
use ilGlobalTemplateInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\BookableItem\Table\Action\BookableItemTableActionsFactory;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Common\Table\Table;
use ILIAS\BookingManager\Common\Table\TableActionExecutorTrait;
use ILIAS\BookingManager\Common\Table\TableActions;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Container\Filter\Standard as FilterComponent;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ilLanguage;
use ilObjBookingPool;
use ilObjUser;
use ilUIService;
use ilUtil;
use ILIAS\BookingManager\BookingProcess\BookingProcessManager;
use ilBookingObjectGUI;
use ILIAS\BookingManager\Settings\Settings;
use DateTimeImmutable;

abstract class BookableItemTable implements Table
{
    use TableActionExecutorTrait;

    public const string ROW_ID_PARAMETER = 'bookable_item_row';

    public const string ACTION_PARAMETER = 'action';

    public const string ACTION_TYPE_PARAMETER = 'action_type';

    /**
     * @var array<int, array<int, array{user_id: int, status: ?int, date_from: int, date_to: int}>>
     */
    private array $reservation_cache = [];

    private ?TableActions $table_actions = null;

    public function __construct(
        protected readonly UIFactory $ui_factory,
        protected readonly UIRenderer $ui_renderer,
        protected readonly ilLanguage $lng,
        protected readonly HttpService $http,
        protected readonly ilUIService $ui_service,
        protected readonly ilCtrlInterface $ctrl,
        protected readonly ilGlobalTemplateInterface $tpl,
        protected readonly Refinery $refinery,
        protected readonly AccessManager $access,
        protected readonly ilObjBookingPool $pool,
        protected readonly BookingProcessManager $process_manager,
        protected readonly Settings $settings,
        protected readonly ilObjUser $user,
        protected readonly int $ref_id,
        protected readonly bool $active_management,
        protected readonly int $booking_context_obj_id,
    ) {
    }

    public function getTableId(): string
    {
        return static::ID;
    }

    /**
     * @return Component[]
     */
    public function getComponents(URLBuilder $url_builder): array
    {
        $components = [];

        if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_NO_SCHEDULE) {
            $booking_count = ilBookingReservation::isBookingPoolLimitReachedByUser(
                $this->user->getId(),
                $this->pool->getId()
            );

            if ($this->pool->getOverallLimit() <= $booking_count) {
                $components[] = $this->ui_factory->messageBox()->info($this->lng->txt('book_overall_limit_warning'));
            }
        }

        $components[] = $filter = $this->getFilterComponent();
        $filter_data = $this->ui_service->filter()->getData($filter);

        $components[] = $this->ui_factory->table()->data(
            $this,
            $this->lng->txt('book_booking_objects'),
            $this->getColumns()
        )
            ->withActions($this->getTableActions()->getEnabledActions(...$this->acquireParameters($url_builder)))
            ->withRequest($this->http->getRequest())
            ->withId($this->getTableId())
            ->withFilter($filter_data)
            ->withOrder(new Order($this instanceof BookableItemWithScheduleTable ? 'date_time' : 'title', Order::ASC));

        return array_filter($components);
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->loadRecords($filter_data));
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
        $records = $this->limitRecords($range, $this->sortRecords($order, $this->loadRecords($filter_data)));

        foreach ($records as $record) {
            yield $this->getTableActions()->onDataRow(
                $row_builder->buildDataRow((string) $record['row_id'], $this->buildRowCells($record)),
                $record
            );
        }
    }

    /**
     * @return array<string, Column>
     */
    abstract protected function getColumns(): array;

    /**
     * @return array<string, mixed>
     */
    abstract protected function getFilterInputs(): array;

    /**
     * @return array<string, mixed>[]
     */
    abstract protected function loadRecords(mixed $filter_data): array;

    /**
     * @return array<string, mixed>
     */
    abstract protected function buildRowCells(array $record): array;

    /**
     * @param array<string, mixed>[] $records
     * @return array<string, mixed>[]
     */
    protected function sortRecords(Order $order, array $records): array
    {
        $order_data = $order->get();
        if ($order_data === []) {
            return $records;
        }

        foreach ($order_data as $key => $direction) {
            $order_direction = $direction === Order::DESC ? -1 : 1;
            $callable = $this->getSortCallable($key, $order_direction);

            if ($callable === null) {
                continue;
            }

            usort($records, $callable);
        }

        return $records;
    }

    protected function getSortCallable(string $key, int $direction): ?callable
    {
        return match ($key) {
            'availability' => static fn(array $a, array $b): int
                => ((int) $a['available'] <=> (int) $b['available']) * $direction,
            'title' => static fn(array $a, array $b): int
                => strcasecmp((string) $a['title'], (string) $b['title']) * $direction,
            'description' => static fn(array $a, array $b): int
                => strcasecmp((string) $a['description'], (string) $b['description']) * $direction,
            default => null,
        };
    }

    /**
     * @param array<string, mixed>[] $records
     * @return array<string, mixed>[]
     */
    protected function limitRecords(Range $range, array $records): array
    {
        return array_slice($records, $range->getStart(), $range->getLength());
    }

    protected function getFilterComponent(): FilterComponent
    {
        $filter_inputs = $this->getFilterInputs();

        $filter_inputs = $this->presetFilterInputs($filter_inputs);

        return $this->ui_service->filter()->standard(
            "bookable_item_filter_{$this->pool->getId()}",
            $this->ctrl->getLinkTargetByClass(ilBookingObjectGUI::class),
            $filter_inputs,
            array_fill(0, count($filter_inputs), true),
            true,
            true
        );
    }

    private function presetFilterInputs(array $filter_inputs): array
    {
        if ($this->settings->getReservationPeriod() > 0) {
            $filter_inputs['period'] = $filter_inputs['period']->withValue(
                [
                    new DateTimeImmutable('today 00:00:00'),
                    new DateTimeImmutable("today +{$this->settings->getReservationPeriod()} days 23:59:59")
                ]
            );
        }

        return $filter_inputs;
    }

    /**
     * @return array{URLBuilder, URLBuilderToken, URLBuilderToken, URLBuilderToken}
     */
    protected function acquireParameters(URLBuilder $url_builder): array
    {
        return $url_builder->acquireParameters(
            [$this->getTableId()],
            self::ROW_ID_PARAMETER,
            self::ACTION_PARAMETER,
            self::ACTION_TYPE_PARAMETER
        );
    }

    protected function getTableActions(): TableActions
    {
        return $this->table_actions ??= (new BookableItemTableActionsFactory(
            $this->ctrl,
            $this->lng,
            $this->tpl,
            $this->ui_factory,
            $this->ui_renderer,
            $this->refinery,
            $this->http,
            $this->access,
            $this->pool,
            $this->process_manager,
            $this->user,
            $this->ref_id,
            $this->active_management,
            $this->booking_context_obj_id,
            array_column($this->loadRecords([]), null, 'row_id'),
        ))->getTableActions();
    }

    /**
     * @return array<string, mixed>[]
     */
    protected function loadFilteredBookingObjects(
        ?string $title_filter,
        ?string $description_filter,
        ?array $object_ids_filter
    ): array {
        $items = ilBookingObject::getList($this->pool->getId());
        $title_filter = mb_strtolower($title_filter ?? '') ?: null;
        $description_filter = mb_strtolower($description_filter ?? '') ?: null;
        $object_ids_filter = $object_ids_filter ?: null;

        $filtered = [];
        foreach ($items as $item) {
            $object_id = (int) $item['booking_object_id'];

            if ($object_ids_filter !== null && !in_array($object_id, $object_ids_filter, true)) {
                continue;
            }

            if ($title_filter !== null && !str_contains(mb_strtolower((string) $item['title']), $title_filter)) {
                continue;
            }

            if ($description_filter !== null && !str_contains(mb_strtolower((string) ($item['description'] ?? '')), $description_filter)) {
                continue;
            }

            $filtered[] = $item;
        }

        return $filtered;
    }

    /**
     * @return array{user_id: int, status: ?int, date_from: int, date_to: int}[]
     */
    protected function getReservationsForObject(int $booking_object_id): array
    {
        if (isset($this->reservation_cache[$booking_object_id])) {
            return $this->reservation_cache[$booking_object_id];
        }

        $list = ilBookingReservation::getList([$booking_object_id], 1000, 0, []);
        $this->reservation_cache[$booking_object_id] = array_values(array_map(
            static fn(array $row): array => [
                'user_id' => (int) $row['user_id'],
                'status' => isset($row['status']) ? (int) $row['status'] : null,
                'date_from' => (int) $row['date_from'],
                'date_to' => (int) $row['date_to'],
            ],
            $list['data'] ?? []
        ));

        return $this->reservation_cache[$booking_object_id];
    }

    protected function buildAvailabilityCell(int $available, int $total): string
    {
        $icon = $this->ui_factory->symbol()->icon()->custom(
            ilUtil::getImagePath($available > 0 ? 'standard/icon_ok.svg' : 'standard/icon_not_ok.svg'),
            $this->lng->txt($available > 0 ? 'book_book' : 'book_no_objects')
        );
        return "{$this->ui_renderer->render($icon)} ({$available} / {$total})";
    }
}
