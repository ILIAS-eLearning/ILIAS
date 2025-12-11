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

namespace ILIAS\BookingManager\Participant;

use Generator;
use ILIAS\BookingManager\Common\Table\Table;
use ILIAS\BookingManager\Common\Table\TableActionExecutorTrait;
use ILIAS\BookingManager\Common\Table\TableActions;
use ILIAS\BookingManager\HttpService;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Input\Container\Filter\Standard as FilterComponent;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;
use ilUIService;
use Psr\Http\Message\ServerRequestInterface;

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
        private readonly ilLanguage $lng,
        private readonly TableActions $table_actions,
        private readonly HttpService $http_service,
        private readonly ilUIService $ui_service,
        private readonly int $pool_id,
        private readonly ServerRequestInterface $request
    ) {
    }

    /**
     * @return array<\ILIAS\UI\Component\Component>
     */
    public function getComponents(URLBuilder $url_builder): array
    {
        $filter = $this->getFilterComponent($url_builder->buildURI()->__toString());

        $table = $this->ui_factory->table()->data(
            $this,
            $this->lng->txt('participants'),
            $this->getColumns()
        )
            ->withActions(
                $this->table_actions->getEnabledActions(...$this->acquireParameters($url_builder))
            )
            ->withRequest($this->request)
            ->withId(self::ID)
            ->withFilter($this->ui_service->filter()->getData($filter));

        return [
            $filter,
            $table
        ];
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        $data = $this->loadRecords($filter_data);
        return count($data);
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
        $data = $this->loadRecords($filter_data);

        $order_data = $order->get();
        if (!empty($order_data)) {
            $order_field = array_keys($order_data)[0];
            $order_direction = $order_data[$order_field];

            usort($data, function ($a, $b) use ($order_field, $order_direction) {
                $a_val = $a[$order_field] ?? '';
                $b_val = $b[$order_field] ?? '';

                $result = $a_val <=> $b_val;
                return $order_direction === Order::ASC ? $result : -$result;
            });
        }

        $offset = $range->getStart();
        $length = $range->getLength();
        $data = array_slice($data, $offset, $length);

        foreach ($data as $record) {
            $bookable_items = implode(', ', $record['object_title'] ?? []);

            yield $this->table_actions->onDataRow(
                $row_builder->buildDataRow(
                    (string) $record['user_id'],
                    [
                        'name' => $record['name'] ?? '',
                        'bookable_item' => $bookable_items,
                    ]
                ),
                $record
            );
        }
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Column\Column>
     */
    private function getColumns(): array
    {
        return [
            'name' => $this->ui_factory->table()->column()->text($this->lng->txt('name'))
                ->withIsSortable(true),
            'bookable_item' => $this->ui_factory->table()->column()->text($this->lng->txt('book_bobj'))
                ->withIsSortable(false),
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
                \ilBookingParticipant::getList($this->pool_id, $filter),
                static fn(array $item): bool => ($item['obj_count'] ?? 0) === 0
            );
        }

        return \ilBookingParticipant::getList($this->pool_id, $filter, $filter_object);
    }

    private function getFilterComponent(string $action): FilterComponent
    {
        $field_factory = $this->ui_factory->input()->field();

        // Bookable Item dropdown
        $bookable_items = [];
        foreach (\ilBookingObject::getList($this->pool_id) as $item) {
            $bookable_items[$item['booking_object_id']] = $item['title'];
        }

        $filter_inputs = [
            'bookable_item_id' => $field_factory->select(
                $this->lng->txt('book_bobj'),
                array_replace(['-1' => $this->lng->txt('book_no_objects')], $bookable_items)
            ),
            'bookable_item_title' => $field_factory->text(
                $this->lng->txt('book_bobj') . ' ' . $this->lng->txt('title') . '/' . $this->lng->txt('description')
            ),
            'participant_id' => $field_factory->select(
                $this->lng->txt('book_participant'),
                \ilBookingParticipant::getUserFilter($this->pool_id)
            ),
        ];

        return $this->ui_service->filter()->standard(
            'participant_filter_' . $this->pool_id,
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
}
