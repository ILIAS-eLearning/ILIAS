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
use ILIAS\Modules\File\Settings\General;
use ILIAS\ResourceStorage\Revision\RevisionStatus;

/**
 * Class ilObjFileImplementationStorage
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileImplementationStorage extends ilObjFileImplementationAbstract implements ilObjFileImplementationInterface
{
    protected Services $storage;

    /**
     * ilObjFileImplementationStorage constructor.
     */
    public function __construct(protected StorableResource $resource)
    {
        global $DIC;
        $settings = new General();
        $this->storage = $DIC->resourceStorage();
    }

    public function handleChangedObjectTitle(string $new_title): void
    {
        $current_revision = $this->resource->getCurrentRevision();
        $current_revision->setTitle($new_title);
        $this->storage->manage()->updateRevision($current_revision);
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

    public function getFileName(): string
    {
        return $this->resource->getCurrentRevision()->getInformation()->getTitle();
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
        if ($a_version !== 0) {
            $consumer->setRevisionNumber($a_version);
        }
        $stream = $consumer->getStream();

        return dirname($stream->getMetadata('uri'));
    }

    public function sendFile(?int $a_hist_entry_id = null, bool $inline = true): void
    {
        if ($inline) {
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
        $consumer->overrideFileName($revision->getTitle());

        $consumer->run();
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
        $current_revision = $this->resource->getCurrentRevisionIncludingDraft();
        foreach ($this->resource->getAllRevisionsIncludingDraft() as $revision) {
            if (is_array($version_ids) && !in_array($revision->getVersionNumber(), $version_ids)) {
                continue;
            }
            $information = $revision->getInformation();
            $v = new ilObjFileVersion();
            $v->setVersion($revision->getVersionNumber());
            $v->setHistEntryId($revision->getVersionNumber());
            $v->setFilename($information->getTitle());
            if ($revision->getStatus() === RevisionStatus::DRAFT) {
                $v->setAction('draft');
            } else {
                $version_number = $revision->getVersionNumber();
                switch ($version_number) {
                    case 1:
                        $v->setAction('create');
                        break;
                    case $current_revision->getVersionNumber():
                        $v->setAction('published_version');
                        break;
                    default:
                        $v->setAction('intermediate_version');
                        break;
                }
            }
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

    public function getVersion(bool $inclduing_drafts = false): int
    {
        if ($inclduing_drafts) {
            return $this->resource->getCurrentRevisionIncludingDraft()->getVersionNumber();
        }
        return $this->resource->getCurrentRevision()->getVersionNumber();
    }

    public function getMaxVersion(): int
    {
        return $this->resource->getMaxRevision(false);
    }
}
