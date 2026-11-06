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

namespace ILIAS\Membership\Export\Table;

use ILIAS\UI\Component\Table\DataRetrieval as DataRetrievalInterface;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use Generator;
use ilFileSystemAbstractionStorage;
use ilLanguage;
use ilUtil;
use DateTimeImmutable;

class DataRetrieval implements DataRetrievalInterface
{
    protected ilFileSystemAbstractionStorage $fss_export;
    protected ilLanguage $lng;

    protected ?array $raw_data = null;

    public function __construct(
        ilFileSystemAbstractionStorage $fss_export,
        ilLanguage $lng
    ) {
        $this->fss_export = $fss_export;
        $this->lng = $lng;
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
        $data = $this->getRawData();

        $order_field = array_keys($order->get())[0] ?? TableBuilder::COL_DATE;
        $order_direction = $order->get()[$order_field] ?? Order::DESC;

        uasort($data, function ($a, $b) use ($order_field) {
            if ($order_field === TableBuilder::COL_SIZE || $order_field === TableBuilder::COL_DATE) {
                return $a[$order_field] <=> $b[$order_field];
            }
            return strnatcasecmp((string) $a[$order_field], (string) $b[$order_field]);
        });

        if ($order_direction === Order::DESC) {
            $data = array_reverse($data, true);
        }

        $data = array_slice($data, $range->getStart(), $range->getLength() ?? 50, true);

        foreach ($data as $id => $row) {
            $row_copy = $row;
            $row_copy[TableBuilder::COL_SIZE] = ilUtil::formatSize((int) $row[TableBuilder::COL_SIZE]);
            $row_copy[TableBuilder::COL_DATE] = new DateTimeImmutable('@' . $row[TableBuilder::COL_DATE]);

            yield $row_builder->buildDataRow((string) $id, $row_copy);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->getRawData());
    }

    public function getAllIDs(): array
    {
        return array_keys($this->getRawData());
    }

    protected function getRawData(): array
    {
        if ($this->raw_data !== null) {
            return $this->raw_data;
        }
        $data = [];
        foreach ($this->fss_export->getMemberExportFiles() as $exp_file) {
            $id = md5($exp_file['name']);
            $data[$id] = [
                TableBuilder::COL_ID => $id,
                TableBuilder::COL_NAME => $exp_file['name'],
                TableBuilder::COL_TYPE => strtoupper((string) $exp_file["type"]),
                TableBuilder::COL_DATE => $exp_file['timest'],
                TableBuilder::COL_SIZE => $exp_file['size']
            ];
        }
        return $this->raw_data = $data;
    }
}
