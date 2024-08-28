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

namespace ILIAS\Export\ExportHandler\I\Consumer\ExportWriter;

use ILIAS\Data\ObjectId;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Collection\ResourceCollection;

interface HandlerInterface
{
    public function withObjectId(
        objectId $objectId
    ): HandlerInterface;

    public function withResourceIdSerialized(
        string $resource_id_serialized
    ): HandlerInterface;

    public function getObjectId(): ObjectId;

    public function getResourceIdSerialized(): string;

    public function writeFileByStream(
        FileStream $stream,
        string $path_in_container
    ): void;

    public function writeFileByFilePath(
        string $path_to_file,
        string $path_in_container
    ): void;

    public function writeDirectory(
        string $dir_path,
        string $dir_path_in_container
    ): void;

    public function writeDirectoryRecursive(
        string $dir_path,
        string $dir_path_in_container
    ): void;

    public function writeFilesByResourceCollectionId(
        string $resource_id_serialized,
        string $path_in_container
    ): void;

    public function writeFilesByResourceCollection(
        ResourceCollection $collection,
        string $path_in_container
    ): void;

    public function writeFilesByResourceId(
        string $resource_id_serialized,
        string $path_in_container
    ): void;
}
