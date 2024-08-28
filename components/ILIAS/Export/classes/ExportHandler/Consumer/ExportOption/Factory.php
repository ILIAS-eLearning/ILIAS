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

namespace ILIAS\Export\ExportHandler\Consumer\ExportOption;

use ILIAS\Export\ExportHandler\Consumer\ExportOption\Collection as ilExportHandlerConsumerExportOptionCollection;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\CollectionInterface as ilExportHandlerConsumerExportOptionCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\FactoryInterface as ilExportHandlerConsumerExportOptionFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\HandlerInterface as ilExportHandlerConsumerExportOptionInterface;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\Setup\BuildExportOptionsMapObjective as ilExportBuildExportOptionsMapObjective;

class Factory implements ilExportHandlerConsumerExportOptionFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->export_handler = $export_handler;
    }

    public function collection(): ilExportHandlerConsumerExportOptionCollectionInterface
    {
        return new ilExportHandlerConsumerExportOptionCollection($this->export_handler);
    }

    public function allExportOptions(): ilExportHandlerConsumerExportOptionCollectionInterface
    {
        $collection = $this->collection();
        $class_names = include ilExportBuildExportOptionsMapObjective::PATH();
        foreach ($class_names as $class_name) {
            /** @var ilExportHandlerConsumerExportOptionInterface $export_option */
            $export_option = new ($class_name)();
            $collection = $collection->withElement($export_option);
        }
        return $collection;
    }

    public function exportOptionWithId(
        string $export_option_id
    ): ?ilExportHandlerConsumerExportOptionInterface {
        foreach ($this->allExportOptions() as $export_option) {
            if ($export_option->getExportOptionId() === $export_option_id) {
                return $export_option;
            }
        }
        return null;
    }
}
