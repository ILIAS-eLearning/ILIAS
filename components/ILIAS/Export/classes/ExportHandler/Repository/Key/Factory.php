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

namespace ILIAS\Export\ExportHandler\Repository\Key;

use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Key\CollectionInterface as ilExportHandlerRepositoryKeyCollectionInterface;
use ILIAS\Export\ExportHandler\I\Repository\Key\FactoryInterface as ilExportHandlerRepositoryKeyFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Key\HandlerInterface as ilExportHandlerRepositoryKeyInterface;
use ILIAS\Export\ExportHandler\Repository\Key\Collection as ilExportHandlerRepositoryKeyCollection;
use ILIAS\Export\ExportHandler\Repository\Key\Handler as ilExportHandlerRepositoryKey;

class Factory implements ilExportHandlerRepositoryKeyFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->export_handler = $export_handler;
    }

    public function handler(): ilExportHandlerRepositoryKeyInterface
    {
        return new ilExportHandlerRepositoryKey(
            $this->export_handler->wrapper()->dataFactory()->handler()
        );
    }

    public function collection(): ilExportHandlerRepositoryKeyCollectionInterface
    {
        return new ilExportHandlerRepositoryKeyCollection();
    }
}
