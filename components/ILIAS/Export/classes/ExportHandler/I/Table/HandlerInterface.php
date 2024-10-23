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

namespace ILIAS\Export\ExportHandler\I\Table;

use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\CollectionInterface as ilExportHandlerConsumerExportOptionCollectionInterface;

interface HandlerInterface
{
    public const TABLE_COL_TYPE = 'type';
    public const TABLE_COL_FILE = 'file';
    public const TABLE_COL_SIZE = 'size';
    public const TABLE_COL_TIMESTAMP = 'timestamp';
    public const TABLE_COL_PUBLIC_ACCESS = 'public_access';
    public const TABLE_COL_PUBLIC_ACCESS_POSSIBLE = 'public_access_possible';

    public function withExportOptions(
        ilExportHandlerConsumerExportOptionCollectionInterface $export_options
    ): HandlerInterface;

    public function handleCommands(): void;

    public function getHTML(): string;

    public function withContext(
        ilExportHandlerConsumerContextInterface $context
    ): HandlerInterface;
}
