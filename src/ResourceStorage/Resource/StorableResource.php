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
 * Interface StorageResource
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface StorableResource
{
    public function getIdentification(): ResourceIdentification;

    public function getCurrentRevision(): Revision;

    public function getSpecificRevision(int $number): ?Revision;

    public function hasSpecificRevision(int $number): bool;

    /**
     * @return Revision[]
     */
    public function getAllRevisions(): array;

    /**
     * @return ResourceStakeholder[]
     */
    public function getStakeholders(): array;

    public function addStakeholder(ResourceStakeholder $s): void;

    public function removeStakeholder(ResourceStakeholder $s): void;

    public function addRevision(Revision $revision): void;

    public function removeRevision(Revision $revision): void;

    public function replaceRevision(Revision $revision): void;

    public function setRevisions(RevisionCollection $collection): void;

    public function getStorageID(): string;

    public function setStorageID(string $storage_id): void;

    public function getMaxRevision(): int;
}
