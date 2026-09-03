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

namespace ILIAS\BookingManager\Participant\Table;

use Generator;
use ilBookingObject;
use ilBookingParticipant;
use ilCtrlInterface;
use ilGlobalTemplateInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Common\Table\Table;
use ILIAS\BookingManager\Common\Table\TableActionExecutorTrait;
use ILIAS\BookingManager\Common\Table\TableActions;
use ILIAS\BookingManager\Participant\ParticipantRepository;
use ILIAS\BookingManager\Participant\Table\Action\ParticipantTableActionsFactory;
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
use ILIAS\UI\URLBuilderToken;
use ilLanguage;
use ilUIService;

class ParticipantTable implements Table
{
    use TableActionExecutorTrait;

    public const ID = 'bksp';

    public const ROW_ID_PARAMETER = 'participant_id';

    public const ACTION_PARAMETER = 'action';

    public const ACTION_TYPE_PARAMETER = 'action_type';

    /**
     * @phpstan-type ParticipantRecord array{user_id: int, name: string, object_title: array<string>, obj_count: int, object_ids: array<int>}
     */
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly ilLanguage $lng,
        private readonly HttpService $http,
        private readonly ilUIService $ui_service,
        private readonly ilCtrlInterface $ctrl,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly Refinery $refinery,
        private readonly AccessManager $access,
        private readonly ParticipantRepository $participant_repository,
        private readonly int $ref_id,
        private readonly int $pool_id,
    ) {
    }

    public function getTableId(): string
    {
        return self::ID;
    }

    /**
     * @return array<Component>
     */
    public function getComponents(URLBuilder $url_builder): array
    {
        $filter = $this->getFilterComponent($url_builder->buildURI()->__toString());

        $table = $this->ui_factory->table()->data($this, $this->lng->txt('participants'), $this->getColumns())
            ->withActions($this->getTableActions()->getEnabledActions(...$this->acquireParameters($url_builder)))
            ->withRequest($this->http->getRequest())
            ->withId($this->getTableId())
            ->withFilter($this->ui_service->filter()->getData($filter));

        return [$filter, $table];
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
                $row_builder->buildDataRow(
                    (string) $record['user_id'],
                    [
                        'name' => $record['name'] ?? '',
                        'bookable_item' => $this->ui_renderer->render(
                            $this->ui_factory->listing()->unordered($record['object_title'] ?? [])
                        ),
                    ]
                ),
                $record
            );
        }
    }

    /**
     * @return array<string, Column>
     */
    private function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();
        return [
            'name' => $column_factory->text($this->lng->txt('name'))->withIsSortable(true),
            'bookable_item' => $column_factory->text($this->lng->txt('book_bobj'))->withIsSortable(false),
        ];
    }

    private function loadRecords(?array $filter_data): array
    {
        $filter = [];

        if (isset($filter_data['bookable_item_id']) && $filter_data['bookable_item_id'] !== '') {
            $filter['object'] = (int) $filter_data['bookable_item_id'];
        }

        if (isset($filter_data['bookable_item_title']) && $filter_data['bookable_item_title'] !== '') {
            $filter['title'] = (string) $filter_data['bookable_item_title'];
        }

        if (isset($filter_data['participant_id']) && $filter_data['participant_id'] !== '') {
            $filter['user_id'] = (int) $filter_data['participant_id'];
        }

        $filter_object = isset($filter['object']) ? (int) $filter['object'] : null;
        if ($filter_object === -1) {
            return array_filter(
                ilBookingParticipant::getList($this->pool_id, $filter),
                static fn(array $item): bool => ($item['obj_count'] ?? 0) === 0
            );
        }

        return ilBookingParticipant::getList($this->pool_id, $filter, $filter_object);
    }

    protected function sortRecords(Order $order, array $records): array
    {
        $order_data = $order->get();
        if ($order_data === []) {
            return $records;
        }

        foreach ($order_data as $key => $value) {
            $order_direction = $value === Order::DESC ? -1 : 1;
            $callable = match ($key) {
                'name' => static fn(array $a, array $b): int => strcmp($a['name'], $b['name']) * $order_direction,
                'bookable_item' => static fn(array $a, array $b): int => strcmp($a['bookable_item'], $b['bookable_item']) * $order_direction,
                default => null,
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

    private function getFilterComponent(string $action): FilterComponent
    {
        $bookable_items = [];
        foreach (ilBookingObject::getList($this->pool_id) as $item) {
            $bookable_items[$item['booking_object_id']] = $item['title'];
        }

        $field_factory = $this->ui_factory->input()->field();
        $filter_inputs = [
            'bookable_item_id' => $field_factory->select(
                $this->lng->txt('book_bobj'),
                array_replace(['-1' => $this->lng->txt('book_no_objects')], $bookable_items)
            ),
            'bookable_item_title' => $field_factory->text(
                "{$this->lng->txt('book_bobj')} {$this->lng->txt('title')}/{$this->lng->txt('description')}"
            ),
            'participant_id' => $field_factory->select(
                $this->lng->txt('book_participant'),
                ilBookingParticipant::getUserFilter($this->pool_id)
            ),
        ];

        return $this->ui_service->filter()->standard(
            "participant_filter_{$this->pool_id}",
            $action,
            $filter_inputs,
            array_fill(0, count($filter_inputs), true),
            true,
            true
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

    protected function getTableActions(): TableActions
    {
        return (new ParticipantTableActionsFactory(
            $this->ctrl,
            $this->lng,
            $this->tpl,
            $this->ui_factory,
            $this->ui_renderer,
            $this->refinery,
            $this->http,
            $this->access,
            $this->participant_repository,
            $this->ref_id,
            $this->pool_id,
        ))->getTableActions();
    }
}
