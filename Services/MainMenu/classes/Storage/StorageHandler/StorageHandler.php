<?php declare(strict_types=1);

namespace ILIAS\MainMenu\Storage\StorageHandler;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\MainMenu\Storage\Identification\IdentificationGenerator;
use ILIAS\MainMenu\Storage\Identification\ResourceIdentification;
use ILIAS\MainMenu\Storage\Revision\Revision;
use ILIAS\MainMenu\Storage\Revision\UploadedFileRevision;
use ILIAS\MainMenu\Storage\StorableResource;

/**
 * Class FileResourceHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface StorageHandler
{

    /**
     * @return string
     */
    public function getID() : string;


    /**
     * @return IdentificationGenerator
     */
    public function getIdentificationGenerator() : IdentificationGenerator;


    /**
     * @param ResourceIdentification $identification
     *
     * @return bool
     */
    public function has(ResourceIdentification $identification) : bool;


    /**
     * @param Revision $revision
     *
     * @return FileStream
     */
    public function getStream(Revision $revision) : FileStream;


    /**
     * @param UploadedFileRevision $revision
     *
     * @return bool
     */
    public function storeUpload(UploadedFileRevision $revision) : bool;


    /**
     * @param Revision $revision
     */
    public function deleteRevision(Revision $revision) : void;


    /**
     * @param StorableResource $resource
     */
    public function deleteResource(StorableResource $resource) : void;
}
