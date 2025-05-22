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

namespace ILIAS\Export\ExportHandler\Consumer\File\Identifier;

use ILIAS\Export\ExportHandler\Consumer\File\Identifier\Collection as ilExportHandlerConsumerFileIdentifierCollection;
use ILIAS\Export\ExportHandler\Consumer\File\Identifier\Handler as ilExportHandlerConsumerFileIdentifier;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\CollectionInterface as ilExportHandlerConsumerFileIdentifierCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\FactoryInterface as ilExportHandlerConsumerFileIdentifierFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\HandlerInterface as ilExportHandlerConsumerFileIdentifierInterface;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;

class Factory implements ilExportHandlerConsumerFileIdentifierFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->export_handler = $export_handler;
    }

    public function handler(): ilExportHandlerConsumerFileIdentifierInterface
    {
        return new ilExportHandlerConsumerFileIdentifier();
    }

    public function collection(): ilExportHandlerConsumerFileIdentifierCollectionInterface
    {
        return new ilExportHandlerConsumerFileIdentifierCollection();
    }
}
