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

use ILIAS\ResourceStorage\Identification\CollectionIdentificationGenerator;
use ILIAS\ResourceStorage\Identification\IdentificationGenerator;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Identification\UniqueIDCollectionIdentificationGenerator;
use ILIAS\ResourceStorage\Lock\LockHandler;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Preloader\SecureString;
use ILIAS\ResourceStorage\Identification\UniqueIDIdentificationGenerator;

/**
 * Class CollectionBuilder
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal This class is not part of the public API and may be changed without notice. Do not use this class in your code.
 */
class CollectionBuilder
{
    use SecureString;

    private const NO_SPECIFIC_OWNER = -1;

    private \ILIAS\ResourceStorage\Collection\Repository\CollectionRepository $collection_repository;
    private CollectionIdentificationGenerator $id_generator;
    private ?LockHandler $lock_handler = null;

    /**
     * @param Repository\CollectionRepository $collection_repository
     */
    public function __construct(
        Repository\CollectionRepository $collection_repository,
        ?CollectionIdentificationGenerator $id_generator = null,
        ?LockHandler $lock_handler = null
    ) {
        $this->collection_repository = $collection_repository;
        $this->id_generator = $id_generator ?? new UniqueIDCollectionIdentificationGenerator();
        $this->lock_handler = $lock_handler;
    }

    public function has(ResourceCollectionIdentification $identification): bool
    {
        return $this->collection_repository->has($identification);
    }

    /**
     * @return \Generator|string[]
     */
    public function getResourceIdStrings(ResourceCollectionIdentification $identification): \Generator
    {
        yield from $this->collection_repository->getResourceIdStrings($identification);
    }

    /**
     * @return \Generator|ResourceIdentification[]
     */
    public function getResourceIds(ResourceCollectionIdentification $identification): \Generator
    {
        foreach ($this->getResourceIdStrings($identification) as $string) {
            yield new ResourceIdentification($string);
        }
    }

    private function validate(ResourceCollectionIdentification $identification): void
    {
        if ($this->id_generator->validateScheme($identification->serialize()) === false) {
            throw new \InvalidArgumentException('Invalid identification scheme');
        }
    }

    public function new(?int $owner = null): ResourceCollection
    {
        return $this->collection_repository->blank(
            $this->id_generator->getUniqueResourceCollectionIdentification(),
            $owner ?? self::NO_SPECIFIC_OWNER
        );
    }

    public function get(ResourceCollectionIdentification $identification, ?int $owner = null): ResourceCollection
    {
        $this->validate($identification);
        $existing = $this->collection_repository->existing($identification);
        if ($existing->hasSpecificOwner()
            && $existing->getOwner() !== $owner
        ) {
            throw new \InvalidArgumentException('Invalid owner of collection');
        }
        return $existing;
    }

    public function store(ResourceCollection $collection): bool
    {
        if ($this->lock_handler !== null) {
            $result = $this->lock_handler->lockTables(
                $this->collection_repository->getNamesForLocking(),
                function () use ($collection) {
                    $this->collection_repository->update($collection);
                }
            );
            $result->runAndUnlock();
        } else {
            $this->collection_repository->update($collection);
        }

        return true;
    }

    public function delete(ResourceCollectionIdentification $identification): bool
    {
        $this->collection_repository->delete($identification);
        return true;
    }

    public function notififyResourceDeletion(ResourceIdentification $identification): void
    {
        $this->collection_repository->removeResourceFromAllCollections($identification);
    }
}
