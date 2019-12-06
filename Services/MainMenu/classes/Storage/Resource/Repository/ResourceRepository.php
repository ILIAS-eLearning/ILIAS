<?php declare(strict_types=1);

namespace ILIAS\MainMenu\Storage\Resource\Repository;

use Generator;
use ILIAS\MainMenu\Storage\Identification\ResourceIdentification;
use ILIAS\MainMenu\Storage\Resource\ResourceNotFoundException;
use ILIAS\MainMenu\Storage\StorableResource;

/**
 * Interface ResourceRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ResourceRepository
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
