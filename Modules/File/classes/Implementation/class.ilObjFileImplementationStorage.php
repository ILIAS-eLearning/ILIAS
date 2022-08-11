<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Services;
use ILIAS\UI\NotImplementedException;
use ILIAS\DI\Container;
use ILIAS\Filesystem\Util\LegacyPathHelper;

/**
 * Class ilObjFileImplementationStorage
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileImplementationStorage extends ilObjFileImplementationAbstract implements ilObjFileImplementationInterface
{
    /**
     * @var StorableResource
     */
    protected $resource;
    /**
     * @var Services
     */
    protected $storage;
    /**
     * @var bool
     */
    protected $download_with_uploaded_filename;
    /**
     * @var int
     */
    protected $obj_id;
    /**
     * ilObjFileImplementationStorage constructor.
     * @param StorableResource $resource
     */
    public function __construct(StorableResource $resource, int $obj_id)
    {
        global $DIC;
        /**
         * @var $DIC Container
         */
        $this->resource = $resource;
        $this->obj_id = $obj_id;
        $this->storage = $DIC->resourceStorage();
        $this->download_with_uploaded_filename = (bool) $DIC->clientIni()->readVariable(
            'file_access',
            'download_with_uploaded_filename'
        );
    }

    private function debug() : void
    {
        // debug
        $stream = $this->storage->consume()->stream($this->resource->getIdentification())->getStream();
        $container = dirname($stream->getMetadata('uri'), 2);

        $dir_reader = function (string $path) {
            $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

            $files = array();
            foreach ($rii as $file) {
                if (!$file->isDir()) {
                    $files[] = $file->getPathname();
                }
            }

            return $files;
        };

        ilUtil::sendInfo('<pre>' . print_r($dir_reader($container), true) . '</pre>');
    }

    /**
     * @inheritDoc
     */
    public function getFile($a_hist_entry_id = null)
    {
        $stream = $this->storage->consume()->stream($this->resource->getIdentification());
        if ($a_hist_entry_id) {
            $stream = $stream->setRevisionNumber($a_hist_entry_id);
        }
        return $stream->getStream()->getMetadata('uri');
    }

    /**
     * @inheritDoc
     */
    public function getFileType()
    {
        return $this->resource->getCurrentRevision()->getInformation()->getMimeType();
    }

    /**
     * @inheritDoc
     */
    public function getDirectory($a_version = 0)
    {
        $consumer = $this->storage->consume()->stream($this->resource->getIdentification());
        if ($a_version) {
            $consumer->setRevisionNumber($a_version);
        }
        $stream = $consumer->getStream();

        return dirname($stream->getMetadata('uri'));
    }

    /**
     * @inheritDoc
     */
    public function sendFile($a_hist_entry_id = null)
    {
        if ($this->isInline($a_hist_entry_id)) {
            $consumer = $this->storage->consume()->inline($this->resource->getIdentification());
        } else {
            $consumer = $this->storage->consume()->download($this->resource->getIdentification());
        }


        if ($a_hist_entry_id) {
            $revision = $this->resource->getSpecificRevision($a_hist_entry_id);
            $consumer->setRevisionNumber($a_hist_entry_id);
        } else {
            $revision = $this->resource->getCurrentRevision();
        }

        if (!$this->download_with_uploaded_filename) {
            $consumer->overrideFileName($revision->getTitle());
        }

        $consumer->run();
    }

    /**
     * @param null $a_hist_entry_id
     * @return bool
     */
    private function isInline($a_hist_entry_id = null)
    {
        try {
            $revision = $a_hist_entry_id ?
                $this->resource->getSpecificRevision($a_hist_entry_id) :
                $this->resource->getCurrentRevision();
            return \ilObjFileAccess::_isFileInline($revision->getTitle());
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteVersions($a_hist_entry_ids = null)
    {
        if (is_array($a_hist_entry_ids)) {
            foreach ($a_hist_entry_ids as $id) {
                $this->storage->manage()->removeRevision($this->resource->getIdentification(), $id);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getFileExtension()
    {
        return $this->resource->getCurrentRevision()->getInformation()->getSuffix();
    }

    /**
     * @inheritDoc
     */
    public function getVersions($version_ids = null) : array
    {
        $versions = [];
        foreach ($this->resource->getAllRevisions() as $revision) {
            if (is_array($version_ids) && !in_array($revision->getVersionNumber(), $version_ids)) {
                continue;
            }
            $information = $revision->getInformation();
            $v = new ilObjFileVersion();
            $v->setVersion($revision->getVersionNumber());
            $v->setHistEntryId($revision->getVersionNumber());
            $v->setFilename($information->getTitle());
            $v->setAction($revision->getVersionNumber() === 1 ? 'create' : 'new_version');
            $v->setTitle($revision->getTitle());
            $v->setDate($information->getCreationDate()->format(DATE_ATOM));
            $v->setUserId($revision->getOwnerId() !== 0 ? $revision->getOwnerId() : 6);
            $v->setSize($information->getSize());

            $versions[] = $v;
        }

        return $versions;
    }

    public function export(string $target_dir) : void
    {
        global $DIC;
        $relative_dir = LegacyPathHelper::createRelativePath($target_dir);
        $filesystem = LegacyPathHelper::deriveFilesystemFrom($target_dir);
    
        if ($filesystem->has($target_dir)) {
            $directory = $relative_dir . '/objects/il_' . IL_INST_ID . "_file_" . $this->obj_id;
            $filesystem->createDir($directory);
            $stream = $DIC->resourceStorage()->consume()->stream($this->resource->getIdentification())->getStream();
            $filesystem->writeStream(
                $directory . '/' . $this->resource->getCurrentRevision()->getInformation()->getTitle(),
                $stream
            );
        }
    }

    public function getStorageID() : ?string
    {
        return $this->resource->getStorageID();
    }
}
