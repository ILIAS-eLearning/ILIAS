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

namespace ILIAS\AdvancedMetaData\Record\File\I;

use ILIAS\Data\ObjectId;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\CollectionInterface as FileRepositoryElementCollectionInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\HandlerInterface as FileRepositoryElementInterface;
use ILIAS\Filesystem\Stream\FileStream;

interface HandlerInterface
{
    public function getFilesByObjectId(
        ObjectId $object_id
    ): FileRepositoryElementCollectionInterface;

    public function getFileByObjectIdAndResourceId(
        ObjectId $object_id,
        string $resource_id_serialized
    ): FileRepositoryElementInterface|null;

    public function getGlobalFiles(): FileRepositoryElementCollectionInterface;

    public function addFile(
        ObjectId $object_id,
        int $user_id,
        string $file_name,
        FileStream $content
    ): void;

    public function addGlobalFile(
        int $user_id,
        string $file_name,
        FileStream $content
    ): void;

    public function download(
        ObjectId $object_id,
        string $resource_id_serialized,
        string|null $filename_overwrite
    ): void;

    public function downloadGlobal(
        string $resource_id_serialized,
        string|null $filename_overwrite
    ): void;

    public function delete(
        ObjectId $object_id,
        int $user_id,
        string $resource_id_serialized
    ): void;

    public function deleteGlobal(
        int $user_id,
        string $resource_id_serialized
    ): void;
}
