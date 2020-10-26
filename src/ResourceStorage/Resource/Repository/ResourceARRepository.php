<?php

namespace ILIAS\ResourceStorage\Resource\Repository;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\ResourceNotFoundException;
use ILIAS\ResourceStorage\Resource\StorableFileResource;
use ILIAS\ResourceStorage\StorableResource;

/**
 * Class ResourceARRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ResourceARRepository implements ResourceRepository
{

    /**
     * @inheritDoc
     */
    public function __construct()
    {
//        ARResource::installDB();
    }


    /**
     * @inheritDoc
     */
    public function blank(ResourceIdentification $identification) : StorableResource
    {
        return new StorableFileResource($identification);
    }


    /**
     * @inheritDoc
     */
    public function get(ResourceIdentification $identification) : StorableResource
    {
        $ar = $this->getAR($identification);
        $r = $this->blank($identification);
        $r->setStorageId($ar->getStorageId());

        return $r;
    }


    /**
     * @inheritDoc
     */
    public function has(ResourceIdentification $identification) : bool
    {
        return ARResource::find($identification->serialize()) instanceof ARResource;
    }


    /**
     * @inheritDoc
     */
    public function store(StorableResource $resource) : void
    {
        $ar = $this->getAR($resource->getIdentification(), true);
        $ar->setStorageId($resource->getStorageID());
        $ar->update();
    }


    /**
     * @param ResourceIdentification $identification
     * @param bool                   $create_if_not_existing
     *
     * @return ARResource
     * @throws ResourceNotFoundException
     */
    public function getAR(ResourceIdentification $identification, bool $create_if_not_existing = false) : ARResource
    {
        $ar = ARResource::find($identification->serialize());
        if ($ar === null) {
            if (!$create_if_not_existing) {
                throw new ResourceNotFoundException("Resource not found: " . $identification->serialize());
            }
            $ar = new ARResource();
            $ar->setIdentification($identification->serialize());
            $ar->create();
        }

        return $ar;
    }


    /**
     * @inheritDoc
     */
    public function delete(StorableResource $resource) : void
    {
        $ar = ARResource::find($resource->getIdentification()->serialize());
        if ($ar instanceof ARResource) {
            $ar->delete();
        }
    }


    /**
     * @inheritDoc
     */
    public function getAll() : \Generator
    {
        /**
         * @var $item ARResource
         */
        foreach (ARResource::get() as $item) {
            yield $this->getResourceFromAR($item);
        }
    }


    public function getResourceFromAR(ARResource $AR_resource) : StorableResource
    {
        $id = new ResourceIdentification($AR_resource->getIdentification());
        $r = new StorableFileResource($id);
        $r->setStorageId($AR_resource->getStorageId());

        return $r;
    }
}
