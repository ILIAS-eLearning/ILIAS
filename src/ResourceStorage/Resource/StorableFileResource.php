<?php

declare(strict_types=1);

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
 *********************************************************************/

namespace ILIAS\ResourceStorage\Resource;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Revision\RevisionCollection;

/**
 * Class StorableFileResource
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StorableFileResource implements StorableResource
{
    private \ILIAS\ResourceStorage\Identification\ResourceIdentification $identification;
    private \ILIAS\ResourceStorage\Revision\RevisionCollection $revisions;
    /**
     * @var ResourceStakeholder[]
     */
    private array $stakeholders = [];
    private string $storage_id = '';

    /**
     * StorableFileResource constructor.
     */
    public function __construct(ResourceIdentification $identification)
    {
        $this->identification = $identification;
        $this->revisions = new RevisionCollection($identification);
    }

    /**
     * @inheritDoc
     */
    public function getIdentification(): ResourceIdentification
    {
        return $this->identification;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentRevision(): Revision
    {
        return $this->revisions->getCurrent();
    }

    /**
     * @inheritDoc
     */
    public function getSpecificRevision(int $number): ?Revision
    {
        foreach ($this->getAllRevisions() as $revision) {
            if ($revision->getVersionNumber() === $number) {
                return $revision;
            }
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function hasSpecificRevision(int $number): bool
    {
        foreach ($this->getAllRevisions() as $revision) {
            if ($revision->getVersionNumber() === $number) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getAllRevisions(): array
    {
        return $this->revisions->getAll();
    }

    /**
     * @inheritDoc
     */
    public function addRevision(Revision $revision): void
    {
        $this->revisions->add($revision);
    }

    public function removeRevision(Revision $revision): void
    {
        $this->revisions->remove($revision);
    }

    /**
     * @inheritDoc
     */
    public function replaceRevision(Revision $revision): void
    {
        $this->revisions->replaceSingleRevision($revision);
    }

    /**
     * @inheritDoc
     */
    public function setRevisions(RevisionCollection $collection): void
    {
        $this->revisions = $collection;
    }

    /**
     * @return ResourceStakeholder[]
     */
    public function getStakeholders(): array
    {
        return $this->stakeholders;
    }

    /**
     * @inheritDoc
     */
    public function addStakeholder(ResourceStakeholder $s): void
    {
        $this->stakeholders[] = $s;
    }

    /**
     * @inheritDoc
     */
    public function removeStakeholder(ResourceStakeholder $s): void
    {
        foreach ($this->stakeholders as $k => $stakeholder) {
            if ($stakeholder->getId() === $s->getId()) {
                unset($this->stakeholders[$k]);
            }
        }
    }

    /**
     * @param ResourceStakeholder[] $stakeholders
     */
    public function setStakeholders(array $stakeholders): self
    {
        $this->stakeholders = $stakeholders;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStorageId(): string
    {
        return $this->storage_id;
    }

    /**
     * @inheritDoc
     */
    public function setStorageId(string $storage_id): void
    {
        $this->storage_id = $storage_id;
    }

    /**
     * @inheritDoc
     */
    public function getMaxRevision(): int
    {
        return $this->revisions->getMax();
    }
}
