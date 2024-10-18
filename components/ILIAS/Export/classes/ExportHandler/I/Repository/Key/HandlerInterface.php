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

namespace ILIAS\Export\ExportHandler\I\Repository\Key;

use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Repository\Key\HandlerInterface as ilExportHandlerRepositoryKeyInterface;

interface HandlerInterface
{
    public const EMPTY_RESOURCE_IDENTIFICATION = "";
    public const EMPTY_OBJECT_ID = -1;

    public function withObjectId(
        ObjectId $object_id
    ): HandlerInterface;

    public function withResourceIdSerialized(
        string $resource_identification_serialized
    ): HandlerInterface;

    public function getObjectId(): ObjectId;

    public function getResourceIdSerialized(): string;

    public function isCompleteKey(): bool;

    public function isObjectIdKey(): bool;

    public function isResourceIdKey(): bool;

    public function equals(
        ilExportHandlerRepositoryKeyInterface $other_repository_key
    ): bool;
}
