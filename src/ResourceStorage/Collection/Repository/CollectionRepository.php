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
 *
 *********************************************************************/

namespace ILIAS\ResourceStorage\Collection\Repository;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Lock\LockingRepository;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Collection\ResourceCollection;

/**
 * Interface CollectionRepository
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface CollectionRepository extends LockingRepository
{
    public function has(ResourceCollectionIdentification $identification): bool;

    public function blank(ResourceCollectionIdentification $identification, ?int $owner = null): ResourceCollection;

    public function existing(ResourceCollectionIdentification $identification): ResourceCollection;

    public function clear(ResourceCollectionIdentification $identification): void;

    /**
     * @return \Generator|string[]
     */
    public function getResourceIdStrings(ResourceCollectionIdentification $identification): \Generator;

    public function update(ResourceCollection $collection): void;

    public function delete(ResourceCollectionIdentification $identification): void;

    public function removeResourceFromAllCollections(ResourceIdentification $resource_identification): void;
}
