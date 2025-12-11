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

namespace ILIAS\BookingManager\Schedule;

use ILIAS\BookingManager\Common\Table\Table;
use ILIAS\BookingManager\Common\Table\TableActionExecutorTrait;
use ILIAS\BookingManager\Common\Table\TableActions;
use ILIAS\BookingManager\HttpService;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;

class ScheduleTable implements Table
{
    use TableActionExecutorTrait;

    public const ID = 'bksd';
    public const ROW_ID_PARAMETER = 'schedule_id';
    public const ACTION_PARAMETER = 'action';
    public const ACTION_TYPE_PARAMETER = 'action_type';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly TableActions $table_actions,
        private readonly ScheduleManager $schedule_manager,
        private readonly HttpService $http_service
    ) {
    }
    /**
     * @return array<\ILIAS\UI\Component\Component>
     */
    public function getComponents(URLBuilder $url_builder): array
    {
        return [
            $this->ui_factory->table()->data(
                $this,
                $this->lng->txt('book_schedules'),
                $this->getColumns()
            )
                ->withActions(
                    $this->table_actions->getEnabledActions(...$this->acquireParameters($url_builder))
                )
                ->withRequest($this->http_service->getRequest())
                ->withId(self::ID)
        ];
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        $data = $this->schedule_manager->getScheduleData();
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
    ): \Generator {
        $data = $this->schedule_manager->getScheduleData();

        // Apply sorting if needed
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

        // Apply range (pagination)
        $offset = $range->getStart();
        $length = $range->getLength();
        $data = array_slice($data, $offset, $length);

        foreach ($data as $record) {
            $is_used = (bool) ($record['is_used'] ?? false);

            yield $this->table_actions->onDataRow(
                $row_builder->buildDataRow(
                    (string) $record['booking_schedule_id'],
                    [
                        'title' => $record['title'] ?? '',
                        'is_used' => $is_used,
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
            'title' => $this->ui_factory->table()->column()->text($this->lng->txt('title'))
                ->withIsSortable(true),
            'is_used' => $this->ui_factory->table()->column()->boolean(
                $this->lng->txt('book_is_used'),
                $this->lng->txt('yes'),
                $this->lng->txt('no')
            )
                ->withIsSortable(true),
        ];
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
