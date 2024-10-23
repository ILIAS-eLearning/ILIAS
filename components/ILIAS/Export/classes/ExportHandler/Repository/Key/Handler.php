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

use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Repository\Key\HandlerInterface as ilExportHandlerRepositoryKeyInterface;
use ILIAS\Export\ExportHandler\I\Wrapper\DataFactory\HandlerInterface as ilExportHandlerDataFactoryWrapperInterface;

class Handler implements ilExportHandlerRepositoryKeyInterface
{
    protected ilExportHandlerDataFactoryWrapperInterface $data_factory_wrapper;
    protected ObjectId $object_id;
    protected string $resource_identification_serialized;

    public function __construct(
        ilExportHandlerDataFactoryWrapperInterface $data_factory_wrapper
    ) {
        $this->data_factory_wrapper = $data_factory_wrapper;
        $this->object_id = $this->data_factory_wrapper->objId(self::EMPTY_OBJECT_ID);
        $this->resource_identification_serialized = self::EMPTY_RESOURCE_IDENTIFICATION;
    }

    public function withObjectId(
        ObjectId $object_id
    ): ilExportHandlerRepositoryKeyInterface {
        $clone = clone $this;
        $clone->object_id = $object_id;
        return $clone;
    }

    public function withResourceIdSerialized(
        string $resource_identification_serialized
    ): ilExportHandlerRepositoryKeyInterface {
        $clone = clone $this;
        $clone->resource_identification_serialized = $resource_identification_serialized;
        return $clone;
    }

    public function getObjectId(): ObjectId
    {
        return $this->object_id;
    }

    public function getResourceIdSerialized(): string
    {
        return $this->resource_identification_serialized;
    }

    public function isCompleteKey(): bool
    {
        return (
            !$this->isObjectIdKey() and
            !$this->isResourceIdKey() and
            $this->object_id->toInt() !== self::EMPTY_OBJECT_ID and
            $this->resource_identification_serialized !== self::EMPTY_RESOURCE_IDENTIFICATION
        );
    }

    public function isObjectIdKey(): bool
    {
        return (
            $this->object_id->toInt() !== self::EMPTY_OBJECT_ID and
            $this->resource_identification_serialized === self::EMPTY_RESOURCE_IDENTIFICATION
        );
    }

    public function isResourceIdKey(): bool
    {
        return (
            $this->object_id->toInt() === self::EMPTY_OBJECT_ID and
            $this->resource_identification_serialized !== self::EMPTY_RESOURCE_IDENTIFICATION
        );
    }

    public function equals(
        ilExportHandlerRepositoryKeyInterface $other_repository_key
    ): bool {
        $object_id_equals =
            (
                isset($this->object_id) and
                isset($other_repository_key->object_id) and
                $this->object_id->toInt() === $other_repository_key->object_id->toInt()
            ) || (
                !isset($this->object_id) and
                !isset($other_repository_key->object_id)
            );
        $resource_id_equals =
            (
                isset($this->resource_identification_serialized) and
                isset($other_repository_key->resource_identification_serialized) and
                $this->resource_identification_serialized === $other_repository_key->resource_identification_serialized
            ) || (
                !isset($this->resource_identification_serialized) and
                !isset($other_repository_key->resource_identification_serialized)
            );
        return $resource_id_equals and $object_id_equals;
    }
}
