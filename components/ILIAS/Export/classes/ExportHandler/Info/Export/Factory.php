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

namespace ILIAS\Export\ExportHandler\Info\Export;

use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\CollectionInterface as ilExportHandlerExportInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Component\FactoryInterface as ilExportHandlerExportComponentInfoFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\FactoryInterface as ilExportHandlerContainerExportInfoFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\FactoryInterface as ilExportHandlerExportInfoFactory;
use ILIAS\Export\ExportHandler\I\Info\Export\HandlerInterface as ilExportHandlerExportInfoInterface;
use ILIAS\Export\ExportHandler\Info\Export\Collection as ilExportHandlerExportInfoCollection;
use ILIAS\Export\ExportHandler\Info\Export\Component\Factory as ilExportHandlerExportComponentInfoFactory;
use ILIAS\Export\ExportHandler\Info\Export\Container\Factory as ilExportHandlerContainerExportInfoFactory;
use ILIAS\Export\ExportHandler\Info\Export\Handler as ilExportHandlerExportInfo;

class Factory implements ilExportHandlerExportInfoFactory
{
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->export_handler = $export_handler;
    }

    public function handler(): ilExportHandlerExportInfoInterface
    {
        return new ilExportHandlerExportInfo(
            $this->export_handler,
            $this->export_handler->wrapper()->dataFactory()->handler()
        );
    }

    public function collection(): ilExportHandlerExportInfoCollectionInterface
    {
        return new ilExportHandlerExportInfoCollection();
    }

    public function component(): ilExportHandlerExportComponentInfoFactoryInterface
    {
        return new ilExportHandlerExportComponentInfoFactory($this->export_handler);
    }

    public function container(): ilExportHandlerContainerExportInfoFactoryInterface
    {
        return new ilExportHandlerContainerExportInfoFactory($this->export_handler);
    }
}
