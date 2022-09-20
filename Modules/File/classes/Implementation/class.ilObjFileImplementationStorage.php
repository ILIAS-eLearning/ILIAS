<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Services;
use ILIAS\UI\NotImplementedException;
use ILIAS\DI\Container;

/**
 * Class ilObjFileImplementationStorage
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileImplementationStorage extends ilObjFileImplementationAbstract implements ilObjFileImplementationInterface
{
    protected StorableResource $resource;
    protected Services $storage;
    protected bool $download_with_uploaded_filename;

    /**
     * ilObjFileImplementationStorage constructor.
     */
    public function __construct(StorableResource $resource)
    {
        global $DIC;
        /**
         * @var $DIC Container
         */
        $this->resource = $resource;
        $this->storage = $DIC->resourceStorage();
        $this->download_with_uploaded_filename = (bool) $DIC->clientIni()->readVariable(
            'file_access',
            'download_with_uploaded_filename'
        );
    }

    /**
     * @inheritDoc
     */
    public function getFile(?int $a_hist_entry_id = null): string
    {
        $stream = $this->storage->consume()->stream($this->resource->getIdentification());
        if ($a_hist_entry_id) {
            $stream = $stream->setRevisionNumber($a_hist_entry_id);
        }
        return $stream->getStream()->getMetadata('uri');
    }

    public function getFileSize(): int
    {
        return $this->resource->getCurrentRevision()->getInformation()->getSize() ?: 0;
    }

    /**
     * @inheritDoc
     */
    public function getFileType(): string
    {
        return $this->resource->getCurrentRevision()->getInformation()->getMimeType();
    }

    public function getDirectory(int $a_version = 0): string
    {
        $consumer = $this->storage->consume()->stream($this->resource->getIdentification());
        if ($a_version) {
            $consumer->setRevisionNumber($a_version);
        }
        $stream = $consumer->getStream();

        return dirname($stream->getMetadata('uri'));
    }

    public function sendFile(?int $a_hist_entry_id = null): void
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

    private function isInline(int $a_hist_entry_id = null): bool
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

    public function deleteVersions(?array $a_hist_entry_ids = null): void
    {
        if (is_array($a_hist_entry_ids)) {
            foreach ($a_hist_entry_ids as $id) {
                $this->storage->manage()->removeRevision($this->resource->getIdentification(), $id);
            }
        }
    }

    public function getFileExtension(): string
    {
        return $this->resource->getCurrentRevision()->getInformation()->getSuffix();
    }

    /**
     * @return \ilObjFileVersion[]
     */
    public function getVersions(?array $version_ids = null): array
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

    public function getStorageID(): ?string
    {
        return $this->resource->getStorageID();
    }

    public function getVersion(): int
    {
        return $this->resource->getCurrentRevision()->getVersionNumber();
    }

    public function getMaxVersion(): int
    {
        return $this->resource->getMaxRevision();
    }
}
