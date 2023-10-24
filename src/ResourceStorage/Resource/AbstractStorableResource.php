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

declare(strict_types=1);

namespace ILIAS\ResourceStorage\Resource;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Revision\RevisionCollection;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

/**
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 */
abstract class AbstractStorableResource implements StorableResource
{
    protected \ILIAS\ResourceStorage\Revision\RevisionCollection $revisions;
    /**
     * @var ResourceStakeholder[]
     */
    protected array $stakeholders = [];
    protected string $storage_id = '';
    protected ResourceIdentification $identification;

    /**
     * StorableFileResource constructor.
     */
    public function __construct(ResourceIdentification $identification)
    {
        $this->identification = $identification;
        $this->revisions = new RevisionCollection($identification);
    }

    public function getIdentification(): ResourceIdentification
    {
        return $this->identification;
    }

    public function getCurrentRevision(): Revision
    {
        return $this->revisions->getCurrent(false);
    }

    public function getCurrentRevisionIncludingDraft(): Revision
    {
        return $this->revisions->getCurrent(true);
    }

    public function getSpecificRevision(int $number): ?Revision
    {
        foreach ($this->getAllRevisionsIncludingDraft() as $revision) {
            if ($revision->getVersionNumber() === $number) {
                return $revision;
            }
        }
        return null;
    }

    public function hasSpecificRevision(int $number): bool
    {
        foreach ($this->getAllRevisionsIncludingDraft() as $revision) {
            if ($revision->getVersionNumber() === $number) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Revision[]
     */
    public function getAllRevisions(): array
    {
        return $this->revisions->getAll(false);
    }

    /**
     * @return Revision[]
     */
    public function getAllRevisionsIncludingDraft(): array
    {
        return $this->revisions->getAll(true);
    }

    public function addRevision(Revision $revision): void
    {
        $this->revisions->add($revision);
    }

    public function removeRevision(Revision $revision): void
    {
        $this->revisions->remove($revision);
    }

    public function replaceRevision(Revision $revision): void
    {
        $this->revisions->replaceSingleRevision($revision);
    }

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

    public function addStakeholder(ResourceStakeholder $s): void
    {
        $this->stakeholders[] = $s;
    }

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

    public function getStorageId(): string
    {
        return $this->storage_id;
    }

    public function setStorageId(string $storage_id): void
    {
        $this->storage_id = $storage_id;
    }

    /**
     * @param bool $including_drafts
     * @inheritDoc
     */
    public function getMaxRevision(bool $including_drafts = false): int
    {
        return $this->revisions->getMax($including_drafts);
    }

    public function getFullSize(): int
    {
        return $this->revisions->getFullSize();
    }

    abstract public function getType(): ResourceType;
}
