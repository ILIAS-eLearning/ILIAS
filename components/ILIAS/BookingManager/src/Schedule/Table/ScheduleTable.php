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

namespace ILIAS\BookingManager\Schedule\Table;

use Generator;
use ilCtrlInterface;
use ilGlobalTemplateInterface;
use ILIAS\BookingManager\Access\AccessManager;
use ILIAS\BookingManager\Common\HttpService;
use ILIAS\BookingManager\Common\Table\Table;
use ILIAS\BookingManager\Common\Table\TableActionExecutorTrait;
use ILIAS\BookingManager\Common\Table\TableActions;
use ILIAS\BookingManager\Schedule\ScheduleManager;
use ILIAS\BookingManager\Schedule\Table\Action\ScheduleTableActionsFactory;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;
use ilUtil;

class ScheduleTable implements Table
{
    use TableActionExecutorTrait;

    public const ID = 'bksd';

    public const ROW_ID_PARAMETER = 'schedule_id';

    public const ACTION_PARAMETER = 'action';

    public const ACTION_TYPE_PARAMETER = 'action_type';

    public function __construct(
        private readonly ilCtrlInterface $ctrl,
        private readonly ilLanguage $lng,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly Refinery $refinery,
        private readonly AccessManager $access,
        private readonly HttpService $http,
        private readonly ScheduleManager $schedule_manager,
        private readonly int $ref_id,
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
        return [
            $this->ui_factory->table()->data($this, $this->lng->txt('book_schedules'), $this->getColumns())
                ->withActions($this->getTableActions()->getEnabledActions(...$this->acquireParameters($url_builder)))
                ->withRequest($this->http->getRequest())
                ->withId($this->getTableId())
        ];
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
                    (string) $record['booking_schedule_id'],
                    [
                        'title' => $record['title'] ?? '',
                        'is_used' => (bool) ($record['is_used'] ?? false),
                    ]
                ),
                $record
            );
        }
    }

    protected function loadRecords(mixed $filter_data): array
    {
        return $this->schedule_manager->getScheduleData();
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
                'title' => static fn(array $a, array $b): int => strcmp($a['title'], $b['title']) * $order_direction,
                'is_used' => static fn(array $a, array $b): int => (($a['is_used'] ?? false) <=> ($b['is_used'] ?? false)) * -$order_direction,
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

    /**
     * @return array<string, Column>
     */
    private function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();
        $icon_factory = $this->ui_factory->symbol()->icon();
        return [
            'title' => $column_factory->text($this->lng->txt('title'))->withIsSortable(true),
            'is_used' => $column_factory->boolean(
                $this->lng->txt('book_is_used'),
                $icon_factory->custom(
                    ilUtil::getImagePath('standard/icon_ok.svg'),
                    $this->lng->txt('yes')
                ),
                $icon_factory->custom(
                    ilUtil::getImagePath('standard/icon_not_ok.svg'),
                    $this->lng->txt('no')
                )
            )->withIsSortable(true),
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

    protected function getTableActions(): TableActions
    {
        return (new ScheduleTableActionsFactory(
            $this->ctrl,
            $this->lng,
            $this->tpl,
            $this->ui_factory,
            $this->ui_renderer,
            $this->refinery,
            $this->access,
            $this->http,
            $this->schedule_manager,
            $this->ref_id,
        ))->getTableActions();
    }
}
