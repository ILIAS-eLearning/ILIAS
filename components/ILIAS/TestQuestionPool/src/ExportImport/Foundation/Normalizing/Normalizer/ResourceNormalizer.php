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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer;

use ILIAS\DI\Container;
use ILIAS\ResourceStorage\Resource\ResourceType;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\Repository\ResourceRepository;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Attributes\Normalizes;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;
use InitResourceStorage;

/**
 * @implements Normalizer<ResourceIdentification|StorableResource, string>
 */
#[Normalizes(ResourceIdentification::class, StorableResource::class)]
class ResourceNormalizer implements Normalizer
{
    private const string KEY_TYPE = 'type';
    private const string TYPE_RID = 'rid';
    private const string TYPE_RESOURCE = 'resource';

    private readonly ResourceRepository $resource_repository;

    public function __construct(
        private readonly Transformations $tt,
        Container $dic
    ) {
        $this->resource_repository = $dic[InitResourceStorage::D_REPOSITORIES]->getResourceRepository();
    }

    /**
     * @inheritDoc
     */
    public function normalize($value): array|float|bool|int|string|null
    {
        if ($value instanceof ResourceIdentification) {
            return $this->normalizeIdentification($value);
        }

        if ($value instanceof StorableResource) {
            return $this->normalizeResource($value);
        }

        throw new NormalizingException('Invalid value', $value);
    }

    private function normalizeIdentification(ResourceIdentification $rid): array
    {
        return [
            self::KEY_TYPE => self::TYPE_RID,
            'id' => $rid->serialize(),
        ];
    }

    private function normalizeResource(StorableResource $resource): array
    {
        return [
            self::KEY_TYPE => self::TYPE_RESOURCE,
            'resource_type' => $resource->getType()->value,
            'id' => $resource->getIdentification()->serialize(),
            'revision' => $resource->getCurrentRevision()->getVersionNumber(),
            'title' => $resource->getCurrentRevision()->getTitle(),
            'mime_type' => $resource->getCurrentRevision()->getInformation()->getMimeType(),
            'suffix' => $resource->getCurrentRevision()->getInformation()->getSuffix(),
            'creation_date' => $this->tt->normalize($resource->getCurrentRevision()->getInformation()->getCreationDate()),
        ];
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $value, string $type): ResourceIdentification|StorableResource
    {
        if ($type === ResourceIdentification::class) {
            return $this->denormalizeIdentification($value);
        }

        if ($type === StorableResource::class) {
            $this->denormalizeResource($value);
        }

        throw new NormalizingException('Invalid type', $type);
    }

    private function denormalizeIdentification(array $value): ResourceIdentification
    {
        if (!self::isResourceIdentification($value)) {
            throw new NormalizingException('Invalid resource identification', $value);
        }

        return new ResourceIdentification($value['id']);
    }

    private function denormalizeResource(array $value): StorableResource
    {
        if (!self::isStorableResource($value)) {
            throw new NormalizingException('Invalid storable resource', $value);
        }

        $id = new ResourceIdentification($value['id']);
        $type = ResourceType::from($value['resource_type']);

        return $this->resource_repository->blank($id, $type);
    }

    /**
     * Returns true if the value is a normalized resource identification.
     */
    public static function isResourceIdentification(mixed $value): bool
    {
        return is_array($value)
            && isset($value[self::KEY_TYPE])
            && $value[self::KEY_TYPE] === self::TYPE_RID;
    }

    /**
     * Returns true if the value is a normalized storable resource.
     */
    public static function isStorableResource(mixed $value): bool
    {
        return is_array($value)
            && isset($value[self::KEY_TYPE])
            && $value[self::KEY_TYPE] === self::TYPE_RESOURCE;
    }
}
