<?php

namespace ILIAS\ResourceStorage\Revision\Repository;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Revision\FileStreamRevision;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Revision\RevisionCollection;
use ILIAS\ResourceStorage\Revision\UploadedFileRevision;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Lock\LockingRepository;
use ILIAS\ResourceStorage\Revision\CloneRevision;
use ILIAS\ResourceStorage\Revision\FileRevision;

/**
 * Class RevisionARRepository
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface RevisionRepository extends LockingRepository
{

    /**
     * @param StorableResource $resource
     * @param UploadResult     $result
     * @return UploadedFileRevision
     */
    public function blankFromUpload(StorableResource $resource, UploadResult $result) : UploadedFileRevision;

    public function blankFromStream(
        StorableResource $resource,
        FileStream $stream,
        bool $keep_original = false
    ) : FileStreamRevision;

    /**
     * @param StorableResource $resource
     * @param FileRevision     $revision_to_clone
     * @return CloneRevision
     */
    public function blankFromClone(StorableResource $resource, FileRevision $revision_to_clone) : CloneRevision;

    /**
     * @param Revision $revision
     */
    public function store(Revision $revision) : void;

    /**
     * @param StorableResource $resource
     * @return RevisionCollection
     */
    public function get(StorableResource $resource) : RevisionCollection;

    /**
     * @param Revision $revision
     */
    public function delete(Revision $revision) : void;
}
