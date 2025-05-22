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

namespace ILIAS\AdvancedMetaData\Record\File\Repository\Key;

use ILIAS\AdvancedMetaData\Record\File\I\Repository\Key\HandlerInterface as FileRepositoryKeyInterface;
use ILIAS\Data\ObjectId;

class Handler implements FileRepositoryKeyInterface
{
    protected ObjectId $object_id;
    protected string $resource_id_serialized;
    protected bool $is_global;

    public function withObjectId(
        ObjectId $object_id
    ): FileRepositoryKeyInterface {
        $clone = clone $this;
        $clone->object_id = $object_id;
        return $clone;
    }

    public function withResourceIdSerialized(
        string $resource_id_serialized
    ): FileRepositoryKeyInterface {
        $clone = clone $this;
        $clone->resource_id_serialized = $resource_id_serialized;
        return $clone;
    }

    public function withIsGlobal(
        bool $is_global
    ): FileRepositoryKeyInterface {
        $clone = clone $this;
        $clone->is_global = $is_global;
        return $clone;
    }

    public function getObjectId(): ObjectId
    {
        return $this->object_id;
    }

    public function getResourceIdSerialized(): string
    {
        return $this->resource_id_serialized;
    }

    public function isGlobal(): bool
    {
        return $this->is_global;
    }

    public function isValid(): bool
    {
        return (
            $this->isObjectIdKey() or
            $this->isResourceIdKey() or
            $this->isCompositKeyOfObjectIdAndResourceId() or
            $this->isCompositKeyOfAll() or
            $this->isGlobalKey()
        );
    }

    public function isObjectIdKey(): bool
    {
        return (
            isset($this->object_id) &&
            !isset($this->resource_id_serialized) &&
            !isset($this->is_global)
        );
    }

    public function isResourceIdKey(): bool
    {
        return (
            !isset($this->object_id) &&
            isset($this->resource_id_serialized) &&
            !isset($this->is_global)
        );
    }

    public function isGlobalKey(): bool
    {
        return (
            !isset($this->object_id) &&
            !isset($this->resource_id_serialized) &&
            isset($this->is_global)
        );
    }

    public function isCompositKeyOfObjectIdAndResourceId(): bool
    {
        return (
            isset($this->object_id) &&
            isset($this->resource_id_serialized) &&
            !isset($this->is_global)
        );
    }

    public function isCompositKeyOfAll(): bool
    {
        return (
            isset($this->object_id) &&
            isset($this->resource_id_serialized) &&
            isset($this->is_global)
        );
    }
}
