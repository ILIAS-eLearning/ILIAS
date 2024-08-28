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

namespace ILIAS\Export\ExportHandler\Info\Export\Container;

use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\FactoryInterface as ilExportHandlerContainerExportInfoFactroyInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\HandlerInterface as ilExportHandlerContainerExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\FactoryInterface as ilExportHandlerContainerExportInfoObjectIdFactoryInterface;
use ILIAS\Export\ExportHandler\Info\Export\Container\Handler as ilExportHandlerContainerExportInfo;
use ILIAS\Export\ExportHandler\Info\Export\Container\ObjectId\Factory as ilExportHandlerContainerExportInfoObjectIdFactory;

class Factory implements ilExportHandlerContainerExportInfoFactroyInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->export_handler = $export_handler;
    }

    public function handler(): ilExportHandlerContainerExportInfoInterface
    {
        return new ilExportHandlerContainerExportInfo($this->export_handler);
    }

    public function objectId(): ilExportHandlerContainerExportInfoObjectIdFactoryInterface
    {
        return new ilExportHandlerContainerExportInfoObjectIdFactory($this->export_handler);
    }
}
