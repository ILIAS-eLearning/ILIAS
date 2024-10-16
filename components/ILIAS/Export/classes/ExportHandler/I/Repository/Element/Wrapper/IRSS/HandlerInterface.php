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

namespace ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\IRSS;

use ILIAS\Export\ExportHandler\I\Repository\Element\HandlerInterface as ilExportHandlerRepositoryElementInterface;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

interface HandlerInterface
{
    public const TMP_FILE_PATH = "tmp_file_ztopslcaneadw";

    public function withResourceIdSerialized(string $resource_identification_serialized): HandlerInterface;

    public function getResourceIdSerialized(): string;

    public function write(
        FileStream $stream,
        string $path_in_container
    ): bool;

    public function addResourceToContainerByResourceId(
        ResourceIdentification $resource_identification,
        string $path_in_container
    ): void;

    public function addResourceToContainer(
        string $resource_id_serialized,
        string $path_in_container
    ): void;

    public function addResourceCollectionToContainerById(
        string $resource_id_serialized,
        string $dir_path_in_container
    ): void;

    public function addResourceCollectionToContaierByCollection(
        ResourceCollection $collection,
        string $dir_path_in_container
    );

    public function writeZip(
        FileStream $zip_stream,
        string $path_in_container
    ): bool;

    public function writeElement(
        ilExportHandlerRepositoryElementInterface $other,
        string $path_in_container
    ): bool;

    public function isContainerExport(): bool;

    public function download(
        string $zip_file_name = ""
    ): void;
}
