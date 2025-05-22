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

namespace ILIAS\AdvancedMetaData\Record\File\I\Repository\Key;

use ILIAS\Data\ObjectId;

interface HandlerInterface
{
    public function withObjectId(
        ObjectId $object_id
    ): HandlerInterface;

    public function withResourceIdSerialized(
        string $resource_id_serialized
    ): HandlerInterface;

    public function withIsGlobal(
        bool $is_global
    ): HandlerInterface;

    public function getObjectId(): ObjectId;

    public function getResourceIdSerialized(): string;

    public function isGlobal(): bool;

    public function isValid(): bool;

    public function isObjectIdKey(): bool;

    public function isResourceIdKey(): bool;

    public function isGlobalKey(): bool;

    public function isCompositKeyOfObjectIdAndResourceId(): bool;

    public function isCompositKeyOfAll(): bool;
}
