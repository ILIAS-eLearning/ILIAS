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

namespace ILIAS\Export\ExportHandler\Consumer\ExportWriter;

use ILIAS\Export\ExportHandler\Consumer\ExportWriter\Handler as ilExportHandlerConsumerExportWriter;
use ILIAS\Export\ExportHandler\I\Consumer\ExportWriter\FactoryInterface as ilExportHandlerConsumerExportWriterFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportWriter\HandlerInterface as ilExportHandlerConsumerExportWriterInterface;
use ILIAS\Export\ExportHandler\I\Repository\HandlerInterface as ilExportHandlerRepositoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Key\FactoryInterface as ilExportHandlerRepositoryKeyFactoryInterface;

class Factory implements ilExportHandlerConsumerExportWriterFactoryInterface
{
    protected ilExportHandlerRepositoryInterface $export_repository;
    protected ilExportHandlerRepositoryKeyFactoryInterface $key_factory;

    public function __construct(
        ilExportHandlerRepositoryInterface $export_repository,
        ilExportHandlerRepositoryKeyFactoryInterface $key_factory
    ) {
        $this->export_repository = $export_repository;
        $this->key_factory = $key_factory;
    }
    public function handler(): ilExportHandlerConsumerExportWriterInterface
    {
        return new ilExportHandlerConsumerExportWriter(
            $this->export_repository,
            $this->key_factory
        );
    }
}
