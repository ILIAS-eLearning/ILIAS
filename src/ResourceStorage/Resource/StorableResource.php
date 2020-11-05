<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Revision\RevisionCollection;

/**
 * Interface StorageResource
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface StorableResource
{

    /**
     * @return ResourceIdentification
     */
    public function getIdentification() : ResourceIdentification;

    /**
     * @return Revision
     */
    public function getCurrentRevision() : Revision;

    /**
     * @param int $number
     * @return Revision|null
     */
    public function getSpecificRevision(int $number) : ?Revision;

    /**
     * @param int $number
     * @return bool
     */
    public function hasSpecificRevision(int $number) : bool;

    /**
     * @return Revision[]
     */
    public function getAllRevisions() : array;

    /**
     * @return ResourceStakeholder[]
     */
    public function getStakeholders() : array;

    /**
     * @param ResourceStakeholder $s
     */
    public function addStakeholder(ResourceStakeholder $s) : void;

    /**
     * @param Revision $revision
     */
    public function addRevision(Revision $revision) : void;

    /**
     * @param RevisionCollection $collection
     */
    public function setRevisions(RevisionCollection $collection) : void;

    /**
     * @return string
     */
    public function getStorageID() : string;

    /**
     * @param string $storage_id
     */
    public function setStorageID(string $storage_id) : void;
}
