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

namespace ILIAS\Test\Table;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\DataRowBuilder;

trait TableRecordsTrait
{
    private ?array $records = null;

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        $records = $this->getRecords($order, $range, $filter_data, $additional_parameters);
        foreach($records as $record) {
            $row_id = $this->getRowID($record);
            yield $row_builder->buildDataRow(
                $row_id,
                $this->transformRecord($row_id, $record)
            );
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return $this->countRecords($filter_data, $additional_parameters);
    }

    protected function getRecords(Order $order, Range $range, ?array $filter_data, ?array $additional_parameters): array
    {
        $this->initRecords($filter_data, $additional_parameters);

        return $this->limitRecords(
            $this->sortRecords($this->records, $order),
            $range
        );
    }

    protected function countRecords(?array $filter_data, ?array $additional_parameters): int
    {
        $this->initRecords($filter_data, $additional_parameters);

        return count($this->records);
    }

    protected function transformRecord(string $row_id, array $record): array
    {
        return $record;
    }

    private function initRecords(?array $filter_data, ?array $additional_parameters): void
    {
        if ($this->records !== null) {
            return;
        }

        $this->records = $this->collectRecords($filter_data, $additional_parameters);
    }

    private function sortRecords(array $records, Order $order): array
    {
        [$order_field, $order_direction] = $order->join(
            '',
            fn(string $index, string $key, string $value): array => [$key, $value]
        );

        usort($records, static function (array $a, array $b) use ($order_field): int {
            if (is_numeric($a[$order_field]) || is_bool($a[$order_field]) || is_array($a[$order_field])) {
                return $a[$order_field] <=> $b[$order_field];
            }

            return strcmp($a[$order_field] ?? '', $b[$order_field] ?? '');
        });

        return $order_direction === $order::DESC ? array_reverse($records) : $records;
    }

    private function limitRecords(array $records, Range $range): array
    {
        return \array_slice($records, $range->getStart(), $range->getLength());
    }

    abstract protected function getRowID(array $record): string;

    abstract protected function collectRecords(?array $filter_data, ?array $additional_parameters): array;
}
