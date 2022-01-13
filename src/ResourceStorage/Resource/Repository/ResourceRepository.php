<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Resource\Repository;

use Generator;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\ResourceNotFoundException;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Lock\LockingRepository;
use ILIAS\ResourceStorage\Preloader\PreloadableRepository;

/**
 * Interface ResourceRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ResourceRepository extends LockingRepository, PreloadableRepository
{

    /**
     * @param ResourceIdentification $identification
     *
     * @return StorableResource
     */
    public function blank(ResourceIdentification $identification) : StorableResource;


    /**
     * @param ResourceIdentification $identification
     *
     * @return StorableResource
     * @throws ResourceNotFoundException
     */
    public function get(ResourceIdentification $identification) : StorableResource;


    /**
     * @param ResourceIdentification $identification
     *
     * @return bool
     */
    public function has(ResourceIdentification $identification) : bool;


    /**
     * @param StorableResource $resource
     */
    public function store(StorableResource $resource) : void;


    /**
     * @return Generator returning StorableResource instances
     */
    public function getAll() : Generator;


    /**
     * @param StorableResource $resource
     */
    public function delete(StorableResource $resource) : void;
}
