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

namespace ILIAS\ResourceStorage\Resource\Repository;

use Generator;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\ResourceNotFoundException;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Lock\LockingRepository;
use ILIAS\ResourceStorage\Preloader\PreloadableRepository;

/**
 * Interface ResourceRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ResourceRepository extends LockingRepository, PreloadableRepository
{
    public function blank(ResourceIdentification $identification): StorableResource;


    /**
     * @throws ResourceNotFoundException
     */
    public function get(ResourceIdentification $identification): StorableResource;


    public function has(ResourceIdentification $identification): bool;


    public function store(StorableResource $resource): void;


    /**
     * @return Generator returning StorableResource instances
     */
    public function getAll(): Generator;


    public function delete(StorableResource $resource): void;
}
