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

namespace ILIAS\AdvancedMetaData\Types\Group\ComplexOptions\Table;

use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use Generator;
use ilAdvancedMDFieldDefinitionGroupBased;
use ILIAS\UI\Component\Table\DataRetrieval as BaseDataRetrieval;

class DataRetrieval implements BaseDataRetrieval
{
    public function __construct(
        protected ilAdvancedMDFieldDefinitionGroupBased $definition
    ) {
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
        $data = [];
        $options = $this->definition->getOptions();
        foreach ($options as $option) {
            $item = ["option" => (string) $option];
            $this->definition->exportOptionToTableGUI($option, $item);
            $data[md5((string) $option)] = $item;
        }

        $order_field = array_keys($order->get())[0] ?? 'option';
        $order_direction = $order->get()[$order_field] ?? Order::ASC;

        uasort($data, function ($a, $b) use ($order_field) {
            return strnatcasecmp((string) ($a[$order_field] ?? ''), (string) ($b[$order_field] ?? ''));
        });

        if ($order_direction === Order::DESC) {
            $data = array_reverse($data, true);
        }

        $data = array_slice($data, $range->getStart(), $range->getLength(), true);

        foreach ($data as $id => $datum) {
            yield $row_builder->buildDataRow($id, $datum);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): int {
        return count($this->definition->getOptions());
    }
}
