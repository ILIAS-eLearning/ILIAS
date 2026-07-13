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

namespace ILIAS\Export\ExportHandler\Table\DataRetrieval;

use Generator;
use ilExportGUI;
use ILIAS\Data\Order as ilDataOrder;
use ILIAS\Data\Range as ilDataRange;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\CollectionInterface as ilExportHandlerConsumerExportOptionCollectionInterface;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\File\HandlerInterface as ilExportHandlerFileInfoInterface;
use ILIAS\Export\ExportHandler\I\Table\DataRetrieval\HandlerInterface as ilExportHandlerTableDataRetrievalInterface;
use ILIAS\Export\ExportHandler\I\Table\HandlerInterface as ilExportHandlerTableInterface;
use ILIAS\UI\Component\Table\DataRowBuilder as ilTableDataRowBuilderInterface;
use ilLanguage;
use ilObject;

class Handler implements ilExportHandlerTableDataRetrievalInterface
{
    protected const LNG_PUBLIC_ACCESS_STATUS = 'exp_public_access_status';
    protected const LNG_NOT_PUBLIC_ACCESS_STATUS = 'exp_not_public_access_status';

    protected ilLanguage $lng;
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ilExportGUI $export_gui;
    protected ilObject $export_object;
    protected ilExportHandlerConsumerExportOptionCollectionInterface $export_options;

    public function __construct(
        ilLanguage $lng,
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->lng = $lng;
        $this->export_handler = $export_handler;
    }

    public function withExportOptions(
        ilExportHandlerConsumerExportOptionCollectionInterface $export_options
    ): ilExportHandlerTableDataRetrievalInterface {
        $clone = clone $this;
        $clone->export_options = $export_options;
        return $clone;
    }

    public function withExportGUI(ilExportGUI $export_gui): ilExportHandlerTableDataRetrievalInterface
    {
        $clone = clone $this;
        $clone->export_gui = $export_gui;
        return $clone;
    }

    public function withExportObject(ilObject $export_object): ilExportHandlerTableDataRetrievalInterface
    {
        $clone = clone $this;
        $clone->export_object = $export_object;
        return $clone;
    }

    public function getRows(
        ilTableDataRowBuilderInterface $row_builder,
        array $visible_column_ids,
        ilDataRange $range,
        ilDataOrder $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        [$column_name, $direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);
        $public_access_status = [
            $this->lng->txt(self::LNG_PUBLIC_ACCESS_STATUS),
            $this->lng->txt(self::LNG_NOT_PUBLIC_ACCESS_STATUS)
        ];
        $context = $this->export_handler->consumer()->context()->handler($this->export_gui, $this->export_object);
        /** @var array<string, ilExportHandlerFileInfoInterface> $sorted_rows */
        $rows = [];
        foreach ($this->export_options as $export_option) {
            foreach ($export_option->getFiles($context) as $file_info) {
                $row_id = $this->export_handler->table()->rowId()->handler()
                    ->withExportOptionId($export_option->getExportOptionId())
                    ->withFileIdentifier($file_info->getFileIdentifier());
                $rows[$row_id->getCompositId()] = $file_info;
            }
        }
        $comparator = function (ilExportHandlerFileInfoInterface $f1, ilExportHandlerFileInfoInterface $f2) {
            return 0;
        };
        switch ($column_name) {
            case ilExportHandlerTableInterface::TABLE_COL_TYPE:
                $comparator = function (ilExportHandlerFileInfoInterface $f1, ilExportHandlerFileInfoInterface $f2) {
                    return strcmp($f1->getFileType(), $f2->getFileType());
                };
                break;
            case ilExportHandlerTableInterface::TABLE_COL_SIZE:
                $comparator = function (ilExportHandlerFileInfoInterface $f1, ilExportHandlerFileInfoInterface $f2) {
                    if ($f1->getFileSize() === $f2->getFileSize()) {
                        return 0;
                    }
                    return $f1->getFileSize() > $f2->getFileSize() ? 1 : -1;
                };
                break;
            case ilExportHandlerTableInterface::TABLE_COL_FILE:
                $comparator = function (ilExportHandlerFileInfoInterface $f1, ilExportHandlerFileInfoInterface $f2) {
                    return strcmp($f1->getFileName(), $f2->getFileName());
                };
                break;
            case ilExportHandlerTableInterface::TABLE_COL_TIMESTAMP:
                $comparator = function (ilExportHandlerFileInfoInterface $f1, ilExportHandlerFileInfoInterface $f2) {
                    if ($f1->getLastChangedTimestamp() === $f2->getLastChangedTimestamp()) {
                        return 0;
                    }
                    return $f1->getLastChangedTimestamp() > $f2->getLastChangedTimestamp() ? 1 : -1;
                };
                break;
            case ilExportHandlerTableInterface::TABLE_COL_PUBLIC_ACCESS:
                $comparator = function (ilExportHandlerFileInfoInterface $f1, ilExportHandlerFileInfoInterface $f2) {
                    if ($f1->getPublicAccessEnabled() and $f2->getPublicAccessEnabled()) {
                        return 0;
                    };
                    return $f1->getPublicAccessEnabled() ? 1 : -1;
                };
                break;
        }
        uasort($rows, $comparator);
        if ($direction === "DESC") {
            $rows = array_reverse($rows, true);
        }
        $rows = array_slice($rows, $range->getStart(), $range->getLength(), true);
        /** @var ilExportHandlerFileInfoInterface $file_info */
        foreach ($rows as $composit_id => $file_info) {
            yield $row_builder->buildDataRow($composit_id, [
                ilExportHandlerTableInterface::TABLE_COL_TYPE => $file_info->getFileType(),
                ilExportHandlerTableInterface::TABLE_COL_FILE => $file_info->getFileName(),
                ilExportHandlerTableInterface::TABLE_COL_SIZE => ((float) $file_info->getFileSize()) / ((float) pow(1024, 2)),
                ilExportHandlerTableInterface::TABLE_COL_TIMESTAMP => $file_info->getLastChanged(),
                ilExportHandlerTableInterface::TABLE_COL_PUBLIC_ACCESS => $file_info->getPublicAccessEnabled() ? $public_access_status[0] : $public_access_status[1]
            ])->withDisabledAction("enable_pa", !$file_info->getPublicAccessPossible());
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        $context = $this->export_handler->consumer()->context()->handler($this->export_gui, $this->export_object);
        $count = 0;
        foreach ($this->export_options as $export_option) {
            $count += $export_option->getFiles($context)->count();
        }
        return $count;
    }
}
