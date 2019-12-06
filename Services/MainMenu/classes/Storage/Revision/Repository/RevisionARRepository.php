<?php declare(strict_types=1);

namespace ILIAS\MainMenu\Storage\Revision\Repository;

use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\MainMenu\Storage\Identification\ResourceIdentification;
use ILIAS\MainMenu\Storage\Revision\FileRevision;
use ILIAS\MainMenu\Storage\Revision\Revision;
use ILIAS\MainMenu\Storage\Revision\RevisionCollection;
use ILIAS\MainMenu\Storage\Revision\UploadedFileRevision;
use ILIAS\MainMenu\Storage\StorableResource;

/**
 * Class RevisionARRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class RevisionARRepository implements RevisionRepository
{

    /**
     * @param StorableResource $resource
     * @param UploadResult     $result
     *
     * @return UploadedFileRevision
     */
    public function blank(StorableResource $resource, UploadResult $result) : UploadedFileRevision
    {
        $new_version_number = $resource->getCurrentRevision()->getVersionNumber() + 1;
        $revision = new UploadedFileRevision($resource->getIdentification(), $result);
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
     *
     * @return string
     */
    private function getInternalID(Revision $revision) : string
    {
        return $revision->getIdentification()->serialize() . '_' . (string) $revision->getVersionNumber();
    }


    /**
     * @param Revision $revision
     *
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
            $ar->create();
        }

        return $ar;
    }


    private function getRevisionFromAR(ARRevision $AR_revision) : Revision
    {
        $r = new FileRevision(new ResourceIdentification($AR_revision->getIdentification()));
        $r->setVersionNumber($AR_revision->getVersionNumber());

        return $r;
    }
}
