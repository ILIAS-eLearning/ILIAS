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

namespace ILIAS\AdvancedMetaData\Record\Export;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use Generator;
use ILIAS\UI\Component\Table\DataRetrieval as BaseDataRetrieval;
use ilAdvancedMDRecordExportFiles;
use DateTimeImmutable;
use ILIAS\Data\Factory as DataFactory;
use DateTimeZone;
use ilObjUser;

class DataRetrieval implements BaseDataRetrieval
{
    protected ?array $file_info_cache = null;

    public function __construct(
        protected ilAdvancedMDRecordExportFiles $export_files,
        protected DataFactory $data_factory,
        protected UIFactory $ui_factory,
        protected ilObjUser $user
    ) {
    }

    protected function getFileInfo(): array
    {
        if ($this->file_info_cache === null) {
            $this->file_info_cache = $this->export_files->readFilesInfo();
        }
        return $this->file_info_cache;
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
        $file_info = $this->getFileInfo();

        foreach ($file_info as $id => $info) {
            $records = (array) ($info['name'] ?? []);
            $date = new DateTimeImmutable()
                ->setTimestamp((int) $info['date'])
                ->setTimezone(new DateTimeZone($this->user->getTimezone()));
            $data[$id] = [
                TableBuilder::COLUMN_RECORDS => $this->ui_factory->listing()->unordered($records),
                TableBuilder::COLUMN_DATE => $date,
                TableBuilder::COLUMN_FILE_SIZE => (string) $this->data_factory->dataSize((int) $info['size'], 'B'),
                'file_size_raw' => (int) $info['size'],
                'records_raw' => $records
            ];
        }

        $order_field = array_keys($order->get())[0] ?? TableBuilder::COLUMN_DATE;
        $order_direction = $order->get()[$order_field] ?? Order::DESC;

        uasort($data, function ($a, $b) use ($order_field) {
            if ($order_field === TableBuilder::COLUMN_DATE) {
                return $a[$order_field] <=> $b[$order_field];
            }
            if ($order_field === TableBuilder::COLUMN_FILE_SIZE) {
                return $a['file_size_raw'] <=> $b['file_size_raw'];
            }
            if ($order_field === TableBuilder::COLUMN_RECORDS) {
                return strnatcasecmp(implode(', ', $a['records_raw']), implode(', ', $b['records_raw']));
            }
            return strnatcasecmp((string) $a[$order_field], (string) $b[$order_field]);
        });

        if ($order_direction === Order::DESC) {
            $data = array_reverse($data, true);
        }

        $data = array_slice($data, $range->getStart(), $range->getLength(), true);

        foreach ($data as $id => $datum) {
            yield $row_builder->buildDataRow($id, $datum);
        }
    }

    public function getAllIds(): array
    {
        return array_keys($this->getFileInfo());
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): int {
        return count($this->getFileInfo());
    }
}
