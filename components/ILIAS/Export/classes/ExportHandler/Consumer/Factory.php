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

namespace ILIAS\Export\ExportHandler\Consumer;

use ILIAS\Export\ExportHandler\Consumer\Context\Factory as ilExportHandlerConsumerContextFactory;
use ILIAS\Export\ExportHandler\Consumer\ExportOption\Factory as ilExportHandlerConsumerExportOptionFactory;
use ILIAS\Export\ExportHandler\Consumer\ExportWriter\Factory as ilExportHandlerConsumerExportWriterFactory;
use ILIAS\Export\ExportHandler\Consumer\File\Factory as ilExportHandlerConsumerFileFactory;
use ILIAS\Export\ExportHandler\Consumer\Handler as ilExportHandlerConsumer;
use ILIAS\Export\ExportHandler\I\Consumer\Context\FactoryInterface as ilExportHandlerConsumerContextFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\FactoryInterface as ilExportHandlerConsumerExportOptionFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportWriter\FactoryInterface as ilExportHandlerConsumerExportWriterFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\FactoryInterface as ilExportHandlerConsumerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\FactoryInterface as ilExportHandlerConsumerFileFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\HandlerInterface as ilExportHandlerConsumerInterface;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;

class Factory implements ilExportHandlerConsumerFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler,
    ) {
        $this->export_handler = $export_handler;
    }

    public function handler(): ilExportHandlerConsumerInterface
    {
        return new ilExportHandlerConsumer(
            $this->export_handler
        );
    }

    public function exportOption(): ilExportHandlerConsumerExportOptionFactoryInterface
    {
        return new ilExportHandlerConsumerExportOptionFactory(
            $this->export_handler
        );
    }

    public function context(): ilExportHandlerConsumerContextFactoryInterface
    {
        return new ilExportHandlerConsumerContextFactory(
            $this->export_handler
        );
    }

    public function exportWriter(): ilExportHandlerConsumerExportWriterFactoryInterface
    {
        return new ilExportHandlerConsumerExportWriterFactory(
            $this->export_handler->repository()->handler(),
            $this->export_handler->repository()->key()
        );
    }

    public function file(): ilExportHandlerConsumerFileFactoryInterface
    {
        return new ilExportHandlerConsumerFileFactory(
            $this->export_handler
        );
    }
}
