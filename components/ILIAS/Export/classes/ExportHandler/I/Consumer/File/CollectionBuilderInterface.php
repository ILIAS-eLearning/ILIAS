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

namespace ILIAS\Export\ExportHandler\I\Consumer\File;

use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\HandlerInterface as ilExportHandlerConsumerExportOptionInterface;
use ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface as ilExportHandlerFileInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\File\HandlerInterface as ilExportHandlerFileInfoInterface;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use SplFileInfo;

interface CollectionBuilderInterface
{
    public function withSPLFileInfo(
        SplFileInfo $spl_file_info,
        ObjectId $object_id,
        ilExportHandlerConsumerExportOptionInterface $export_option
    ): CollectionBuilderInterface;

    public function withResourceIdentifier(
        ResourceIdentification $resource_id,
        ObjectId $object_id,
        ilExportHandlerConsumerExportOptionInterface $export_option
    ): CollectionBuilderInterface;

    public function withFileInfo(
        ilExportHandlerFileInfoInterface $file_info
    ): CollectionBuilderInterface;

    public function collection(): ilExportHandlerFileInfoCollectionInterface;
}
