<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\StorageHandler;

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\Location;
use ILIAS\ResourceStorage\Identification\IdentificationGenerator;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Identification\UniqueIDIdentificationGenerator;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Revision\UploadedFileRevision;
use ILIAS\ResourceStorage\StorableResource;

/**
 * Class FileSystemStorage
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 *
 * @package ILIAS\ResourceStorage\Storage
 */
class FileSystemStorageHandler implements StorageHandler
{
    const BASE = "storage";
    const DATA = 'data';
    /**
     * @var Filesystem
     */
    private $fs;
    /**
     * @var UniqueIDIdentificationGenerator
     */
    private $id;


    /**
     * FileSystemStorageHandler constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->fs = $DIC->filesystem()->customizing();
        $this->id = new UniqueIDIdentificationGenerator();
    }


    /**
     * @inheritDoc
     */
    public function getID() : string
    {
        return 'fsv1';
    }


    /**
     * @inheritDoc
     */
    public function getIdentificationGenerator() : IdentificationGenerator
    {
        return $this->id;
    }


    public function has(ResourceIdentification $identification) : bool
    {
        return $this->fs->has($this->getBasePath($identification));
    }


    /**
     * @inheritDoc
     */
    public function getStream(Revision $revision) : FileStream
    {
        return $this->fs->readStream($this->getRevisionPath($revision) . '/' . self::DATA);
    }


    public function storeUpload(UploadedFileRevision $revision) : bool
    {
        global $DIC;

        $DIC->upload()->moveOneFileTo($revision->getUpload(), $this->getRevisionPath($revision), Location::CUSTOMIZING, self::DATA);

        return true;
    }


    /**
     * @inheritDoc
     */
    public function deleteRevision(Revision $revision) : void
    {
        $this->fs->deleteDir($this->getRevisionPath($revision));
    }


    /**
     * @inheritDoc
     */
    public function deleteResource(StorableResource $resource) : void
    {
        $this->fs->deleteDir($this->getBasePath($resource->getIdentification()));
    }


    private function getBasePath(ResourceIdentification $identification) : string
    {
        return self::BASE . '/' . str_replace("-", "/", $identification->serialize());
    }


    private function getRevisionPath(Revision $revision) : string
    {
        return $this->getBasePath($revision->getIdentification()) . '/' . $revision->getVersionNumber();
    }
}
