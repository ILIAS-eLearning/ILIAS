<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Resource;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Revision\RevisionCollection;
use ILIAS\ResourceStorage\StorableResource;

/**
 * Class StorableFileResource
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StorableFileResource implements StorableResource
{

    /**
     * @var ResourceIdentification
     */
    private $identification;
    /**
     * @var RevisionCollection
     */
    private $revisions = [];
    /**
     * @var ResourceStakeholder[]
     */
    private $stakeholders = [];
    /**
     * @var string
     */
    private $storage_id = '';


    /**
     * StorableFileResource constructor.
     *
     * @param ResourceIdentification $identification
     */
    public function __construct(ResourceIdentification $identification)
    {
        $this->identification = $identification;
        $this->revisions = new RevisionCollection($identification);
    }


    /**
     * @inheritDoc
     */
    public function getIdentification() : ResourceIdentification
    {
        return $this->identification;
    }


    /**
     * @inheritDoc
     */
    public function getCurrentRevision() : Revision
    {
        return $this->revisions->getCurrent();
    }


    /**
     * @inheritDoc
     */
    public function getAllRevisions() : array
    {
        return $this->revisions->getAll();
    }


    /**
     * @inheritDoc
     */
    public function addRevision(Revision $revision) : void
    {
        $this->revisions->add($revision);
    }


    /**
     * @inheritDoc
     */
    public function setRevisions(RevisionCollection $collection) : void
    {
        $this->revisions = $collection;
    }


    /**
     * @return ResourceStakeholder[]
     */
    public function getStakeholders() : array
    {
        return $this->stakeholders;
    }


    /**
     * @param ResourceStakeholder[] $stakeholders
     *
     * @return StorableFileResource
     */
    public function setStakeholders(array $stakeholders) : StorableFileResource
    {
        $this->stakeholders = $stakeholders;

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getStorageId() : string
    {
        return $this->storage_id;
    }


    /**
     * @inheritDoc
     */
    public function setStorageId(string $storage_id) : void
    {
        $this->storage_id = $storage_id;
    }
}
