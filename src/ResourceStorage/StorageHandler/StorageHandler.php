<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\StorageHandler;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Identification\IdentificationGenerator;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Revision\CloneRevision;
use ILIAS\ResourceStorage\Revision\FileStreamRevision;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Revision\UploadedFileRevision;

/**
 * Class FileResourceHandler
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
interface StorageHandler
{

    /**
     * @return string not longer than 8 characters
     */
    public function getID() : string;

    public function isPrimary() : bool;

    /**
     * @return IdentificationGenerator
     */
    public function getIdentificationGenerator() : IdentificationGenerator;

    /**
     * @param ResourceIdentification $identification
     * @return bool
     */
    public function has(ResourceIdentification $identification) : bool;

    /**
     * @param Revision $revision
     * @return FileStream
     */
    public function getStream(Revision $revision) : FileStream;

    /**
     * @param UploadedFileRevision $revision
     * @return bool
     */
    public function storeUpload(UploadedFileRevision $revision) : bool;

    /**
     * @param FileStreamRevision $revision
     * @return bool
     */
    public function storeStream(FileStreamRevision $revision) : bool;

    /**
     * @param CloneRevision $revision
     * @return bool
     */
    public function cloneRevision(CloneRevision $revision) : bool;

    /**
     * This only delets a revision of a Resource
     * @param Revision $revision
     */
    public function deleteRevision(Revision $revision) : void;

    /**
     * This deleted the whole container of a resource
     * @param StorableResource $resource
     */
    public function deleteResource(StorableResource $resource) : void;

    /**
     * This checks if there are empty directories in the filesystem which can be deleted. Currently only on first level.
     * @param StorableResource $resource
     */
    public function cleanUpContainer(StorableResource $resource) : void;

    /**
     * This is the place in the filesystem where the containers (nested) get created
     * @return string
     */
    public function getStorageLocationBasePath() : string;

    /**
     * This is the full path to the container of a ResourceIdentification (incl. StorageLocation base path).
     * @param ResourceIdentification $identification
     * @return string
     */
    public function getFullContainerPath(ResourceIdentification $identification) : string;

    /**
     * This is only the path of a ResourceIdentification inside the StorageLocation base path
     * @param ResourceIdentification $identification
     * @return string
     */
    public function getContainerPathWithoutBase(ResourceIdentification $identification) : string;

    /**
     * This is the full path to a revision of a Resource, incl. the StorageLocation base path. This can be used
     * to access the file itself. But getStream is musch easier for this.
     * @param Revision $revision
     * @return string
     * @see getStream instead.
     */
    public function getRevisionPath(Revision $revision) : string;

    /**
     * @return string "link" or "rename"
     */
    public function movementImplementation() : string;
}
