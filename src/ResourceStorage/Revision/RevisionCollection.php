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

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class RevisionCollection
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 */
class RevisionCollection
{
    private ResourceIdentification $identification;
    /**
     * @var FileRevision[]
     */
    private array $revisions = [];

    /**
     * RevisionCollection constructor.
     * @param FileRevision[] $revisions
     */
    public function __construct(ResourceIdentification $identification, array $revisions = [])
    {
        $this->identification = $identification;
        $this->revisions = $revisions;
    }

    public function add(Revision $revision): void
    {
        if ($this->identification->serialize() !== $revision->getIdentification()->serialize()) {
            throw new NonMatchingIdentificationException(
                "Can't add Revision since it's not the same ResourceIdentification"
            );
        }
        foreach ($this->revisions as $r) {
            if ($r->getVersionNumber() === $revision->getVersionNumber()) {
                throw new RevisionExistsException(
                    sprintf(
                        "Can't add already existing version number: %s",
                        $revision->getVersionNumber()
                    )
                );
            }
        }
        $this->revisions[$revision->getVersionNumber()] = $revision;
        asort($this->revisions);
    }

    public function remove(Revision $revision): void
    {
        foreach ($this->revisions as $k => $revision_e) {
            if ($revision->getVersionNumber() === $revision_e->getVersionNumber()) {
                unset($this->revisions[$k]);

                return;
            }
        }
    }

    public function replaceSingleRevision(Revision $revision): void
    {
        foreach ($this->revisions as $k => $revision_e) {
            if ($revision_e->getVersionNumber() === $revision->getVersionNumber()) {
                $this->revisions[$k] = $revision;
            }
        }
    }

    public function replaceAllRevisions(Revision $revision): void
    {
        $this->revisions = [];
        $this->add($revision);
    }

    public function getCurrent(bool $including_drafts): Revision
    {
        $v = $this->revisions;

        if (!$including_drafts) {
            $v = array_filter($v, static function (Revision $revision): bool {
                return $revision->getStatus() === RevisionStatus::PUBLISHED;
            });
        }
        usort($v, static function (Revision $a, Revision $b): int {
            return $a->getVersionNumber() <=> $b->getVersionNumber();
        });

        $current = end($v);
        if (!$current instanceof Revision) {
            $current = new NullRevision($this->identification);
        }

        return $current;
    }

    /**
     * @return Revision[]
     */
    public function getAll(bool $including_drafts): array
    {
        if($including_drafts) {
            return $this->revisions;
        }
        return array_filter($this->revisions, static function (Revision $revision): bool {
            return $revision->getStatus() === RevisionStatus::PUBLISHED;
        });
    }

    public function getMax(bool $including_drafts): int
    {
        if ($this->revisions === []) {
            return 0;
        }
        return $this->getCurrent($including_drafts)->getVersionNumber();
    }

    public function getFullSize(): int
    {
        $size = 0;
        foreach ($this->revisions as $revision) {
            $size += $revision->getInformation()->getSize();
        }

        return $size;
    }
}
