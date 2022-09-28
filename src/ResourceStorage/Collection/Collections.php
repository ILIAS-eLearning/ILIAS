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

namespace ILIAS\ResourceStorage\Collection;

use ILIAS\ResourceStorage\Collection\Sorter\ByCreationDate;
use ILIAS\ResourceStorage\Collection\Sorter\ByTitle;
use ILIAS\ResourceStorage\Collection\Sorter\CollectionSorter;
use ILIAS\ResourceStorage\Collection\Sorter\Sorter;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Lock\LockHandler;
use ILIAS\ResourceStorage\Preloader\RepositoryPreloader;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

/**
 * Class Collections
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class Collections
{
    private \ILIAS\ResourceStorage\Resource\ResourceBuilder $resource_builder;
    private CollectionBuilder $collection_builder;
    private \ILIAS\ResourceStorage\Preloader\RepositoryPreloader $preloader;
    private array $cache = [];

    /**
     * Consumers constructor.
     */
    public function __construct(
        ResourceBuilder $r,
        CollectionBuilder $c,
        RepositoryPreloader $preloader
    ) {
        $this->resource_builder = $r;
        $this->collection_builder = $c;
        $this->preloader = $preloader;
    }

    /**
     * @param string|null $collection_identification an existing collection identification or null for a new
     * @param int|null $owner if this colletion is owned by a users, you must prvide it's owner ID
     */
    public function id(
        ?string $collection_identification = null,
        ?int $owner = null
    ): ResourceCollectionIdentification {
        if ($collection_identification === null
            || $collection_identification === ''
            || !$this->collection_builder->has(new ResourceCollectionIdentification($collection_identification))
        ) {
            $collection = $this->collection_builder->new($owner);
            $identification = $collection->getIdentification();
            $this->cache[$identification->serialize()] = $collection;

            return $identification;
        }

        return new ResourceCollectionIdentification($collection_identification);
    }

    public function exists(string $collection_identification): bool
    {
        return $this->collection_builder->has(new ResourceCollectionIdentification($collection_identification));
    }

    public function idOrNull(
        ?string $collection_identification = null,
        ?int $owner = null
    ): ?ResourceCollectionIdentification {
        if ($this->exists($collection_identification)) {
            return $this->id($collection_identification, $owner);
        }
        return null;
    }

    public function get(
        ResourceCollectionIdentification $identification,
        ?int $owner = null
    ): ResourceCollection {
        $collection = $this->cache[$identification->serialize()]
            ?? $this->collection_builder->get(
                $identification,
                $owner
            );

        $preload = [];
        foreach ($this->collection_builder->getResourceIds($identification) as $resource_identification) {
            if ($this->resource_builder->has($resource_identification)) {
                $collection->add($resource_identification);
                $preload[] = $resource_identification;
            }
        }
        $this->preloader->preload($preload);

        return $this->cache[$identification->serialize()] = $collection;
    }

    public function store(ResourceCollection $collection): bool
    {
        return $this->collection_builder->store($collection);
    }

    public function clone(ResourceCollectionIdentification $source_collection_id): ResourceCollectionIdentification
    {
        $target_collection_id = $this->id();
        $target_collection = $this->get($target_collection_id);
        $source_collection = $this->get($source_collection_id);

        foreach ($source_collection->getResourceIdentifications() as $identification) {
            $resource = $this->resource_builder->get($identification);
            $cloned_resource = $this->resource_builder->clone($resource);
            $target_collection->add($cloned_resource->getIdentification());
        }
        $this->store($target_collection);

        return $target_collection_id;
    }

    public function remove(
        ResourceCollectionIdentification $collection_id,
        ResourceStakeholder $stakeholder,
        bool $delete_resources_as_well = false
    ): bool {
        $collection = $this->get($collection_id);
        if ($delete_resources_as_well) {
            foreach ($collection->getResourceIdentifications() as $resource_identification) {
                $resource = $this->resource_builder->get($resource_identification);
                $this->resource_builder->remove($resource, $stakeholder);
            }
        }
        return $this->collection_builder->delete($collection_id);
    }

    /**
     * @return ResourceIdentification[]
     */
    public function rangeAsArray(ResourceCollection $collection, int $from, int $amout)
    {
        $return = [];
        foreach ($collection->getResourceIdentifications() as $position => $identification) {
            if ($position >= $from && $position < $from + $amout) {
                $return[] = $identification;
            }
        }
        return $return;
    }

    /**
     * @return \Generator|ResourceIdentification[]
     */
    public function rangeAsGenerator(ResourceCollection $collection, int $from, int $to): \Generator
    {
        foreach ($collection->getResourceIdentifications() as $position => $identification) {
            if ($position >= $from && $position <= $to) {
                yield $identification;
            }
        }
    }

    public function sort(
        ResourceCollection $collection,
    ): Sorter {
        return new Sorter(
            $this->resource_builder,
            $this->collection_builder,
            $collection
        );
    }
}
