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

namespace ILIAS\MetaData\OERHarvester\RepositoryObjects;

class NullHandler implements HandlerInterface
{
    public function referenceObjectInTargetContainer(int $obj_id, int $container_ref_id): int
    {
        return 0;
    }

    public function getObjectReferenceIDInContainer(int $obj_id, int $container_ref_id): ?int
    {
        return null;
    }

    public function isObjectDeleted(int $obj_id): bool
    {
        return false;
    }

    public function deleteReference(int $ref_id): void
    {
    }

    public function getTypeOfReferencedObject(int $ref_id): string
    {
        return '';
    }
}
