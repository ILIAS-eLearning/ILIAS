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

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Data\ReferenceId;
use ILIAS\StaticURL\Services;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory;

/**
 * Class ilLTIConsumerProviderUsageTableGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package components\ILIAS/LTIConsumer
 */
class ilLTIConsumerProviderUsageTableGUI implements DataRetrieval
{
    protected ilLanguage $lng;
    protected Factory $ui_factory;
    protected \ILIAS\UI\Renderer $ui_renderer;
    protected $request;
    protected array $records;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
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
        global $DIC;

        /** @var Services $static_url */
        $static_url = $DIC["static_url"];

        $records = $this->applyOrdering($this->records, $order, $range);
        foreach ($records as $record) {
            $record['icon'] = $record['icon'] ?? "lti";
            $record['icon'] = $this->ui_factory->symbol()->icon()->standard($record['icon'], $record['icon'], Icon::SMALL);

            $link = (string) $static_url->builder()->build(
                ilObject::_lookupType($record['usedByObjId']),
                new ReferenceId($record['usedByRefId'])
            );

            $record['used_by'] = $this->ui_factory->link()->standard(
                $record['usedByTitle'],
                $link
            );

            yield $row_builder->buildDataRow((string) $record['id'], $record);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->records);
    }

    public function setData(array $data): void
    {
        $this->records = $data;
    }

    protected function applyOrdering(array $records, Order $order, ?Range $range = null): array
    {
        [$order_field, $order_direction] = $order->join(
            [],
            fn($ret, $key, $value) => [$key, $value]
        );

        usort($records, static function (array $left, array $right) use ($order_field): int {
            $left_val = $left[$order_field] ?? '';
            $right_val = $right[$order_field] ?? '';
            return ilStr::strCmp($left_val, $right_val);
        });

        if ($order_direction === Order::DESC) {
            $records = array_reverse($records);
        }

        if ($range !== null) {
            $records = array_slice($records, $range->getStart(), $range->getLength());
        }

        return $records;
    }

    public function getHTML(): string
    {
        $table = $this->ui_factory->table()
            ->data($this, $this->lng->txt('tbl_provider_usage_header'), $this->getColumns())
            ->withOrder(new Order('title', Order::ASC))
            ->withRange(new Range(0, 20))
            ->withRequest($this->request);

        return $this->ui_renderer->render($table);
    }

    private function getColumns(): array
    {
        return [
            "icon" => $this->ui_factory->table()->column()->statusIcon($this->lng->txt('tbl_lti_prov_icon')),
            "title" => $this->ui_factory->table()->column()->text($this->lng->txt('tbl_lti_prov_title')),
            "usedByIsTrashed" => $this->ui_factory->table()->column()->boolean(
                $this->lng->txt('tbl_lti_prov_usages_trashed'),
                $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_ok.svg', $this->lng->txt('icon_ok'), Icon::SMALL),
                $this->ui_factory->symbol()->icon()->custom('assets/images/standard/icon_not_ok.svg', $this->lng->txt('icon_not_ok'), Icon::SMALL)
            ),
            "used_by" => $this->ui_factory->table()->column()->link($this->lng->txt('tbl_lti_prov_used_by'))
        ];
    }
}
