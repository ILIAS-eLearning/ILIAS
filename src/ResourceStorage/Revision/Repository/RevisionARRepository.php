<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Revision\Repository;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Revision\FileRevision;
use ILIAS\ResourceStorage\Revision\FileStreamRevision;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Revision\RevisionCollection;
use ILIAS\ResourceStorage\Revision\UploadedFileRevision;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Revision\CloneRevision;

/**
 * Class RevisionARRepository
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class RevisionARRepository implements RevisionRepository
{
    public function getNameForLocking() : string
    {
        return (new ARRevision())->getConnectorContainerName();
    }

    /**
     * @param StorableResource $resource
     * @param UploadResult     $result
     * @return UploadedFileRevision
     */
    public function blankFromUpload(StorableResource $resource, UploadResult $result) : UploadedFileRevision
    {
        $new_version_number = $resource->getMaxRevision() + 1;
        $revision = new UploadedFileRevision($resource->getIdentification(), $result);
        $revision->setVersionNumber($new_version_number);

        return $revision;
    }

    public function blankFromStream(
        StorableResource $resource,
        FileStream $stream,
        bool $keep_original = false
    ) : FileStreamRevision {
        $new_version_number = $resource->getMaxRevision() + 1;
        $revision = new FileStreamRevision($resource->getIdentification(), $stream, $keep_original);
        $revision->setVersionNumber($new_version_number);

        return $revision;
    }

    public function blankFromClone(StorableResource $resource, int $revision_number_to_clone) : CloneRevision
    {
        $new_version_number = $resource->getMaxRevision() + 1;
        $revision = new CloneRevision($resource->getIdentification(), $revision_number_to_clone);
        $revision->setVersionNumber($new_version_number);

        return $revision;
    }

    /**
     * @param Revision $revision
     */
    public function store(Revision $revision) : void
    {
        $ar = $this->getAR($revision);
        $ar->setVersionNumber($revision->getVersionNumber());
        $ar->setAvailable($revision->isAvailable());
        $ar->setOwnerId($revision->getOwnerId());
        $ar->setTitle($revision->getTitle());
        $ar->update();
    }

    /**
     * @inheritDoc
     */
    public function get(StorableResource $resource) : RevisionCollection
    {
        $collection = new RevisionCollection($resource->getIdentification());

        foreach (ARRevision::where(['identification' => $resource->getIdentification()->serialize()])->get() as $ar_revision) {
            $collection->add($this->getRevisionFromAR($ar_revision));
        }

        return $collection;
    }

    /**
     * @inheritDoc
     */
    public function delete(Revision $revision) : void
    {
        $primary = $this->getInternalID($revision);
        $ar = ARRevision::find($primary);
        if ($ar instanceof ARRevision) {
            $ar->delete();
        }
    }

    /**
     * @param Revision $revision
     * @return string
     */
    private function getInternalID(Revision $revision) : string
    {
        return $revision->getIdentification()->serialize() . '_' . (string) $revision->getVersionNumber();
    }

    /**
     * @param Revision $revision
     * @return ARRevision
     */
    private function getAR(Revision $revision) : ARRevision
    {
        $primary = $this->getInternalID($revision);
        $ar = ARRevision::find($primary);
        if ($ar === null) {
            $ar = new ARRevision();
            $ar->setInternal($primary);
            $ar->setIdentification($revision->getIdentification()->serialize());
            $ar->setOwnerId($revision->getOwnerId());
            $ar->setTitle($revision->getTitle());
            $ar->setAvailable(true);
            $ar->create();
        }

        return $ar;
    }

    private function getRevisionFromAR(ARRevision $AR_revision) : Revision
    {
        $r = new FileRevision(new ResourceIdentification($AR_revision->getIdentification()));
        $r->setVersionNumber($AR_revision->getVersionNumber());
        $r->setOwnerId($AR_revision->getOwnerId());
        $r->setTitle($AR_revision->getTitle());
        if (!$AR_revision->isAvailable()) {
            $r->setUnavailable();
        }

        return $r;
    }
}
