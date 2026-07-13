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

use ilObject;
use ilObjectFactory;
use ilTree;

class Handler implements HandlerInterface
{
    protected ilTree $tree;

    public function __construct(ilTree $tree)
    {
        $this->tree = $tree;
    }

    public function referenceObjectInTargetContainer(int $obj_id, int $container_ref_id): int
    {
        $object = ilObjectFactory::getInstanceByObjId($obj_id);
        $new_ref_id = $object->createReference();

        $object->putInTree($container_ref_id);
        $object->setPermissions($container_ref_id);

        return $new_ref_id;
    }

    public function isObjectDeleted(int $obj_id): bool
    {
        $exists = false;
        foreach (ilObject::_getAllReferences($obj_id) as $ref_id => $tmp) {
            if (!$this->tree->isDeleted($ref_id)) {
                $exists = true;
            }
        }
        return !$exists;
    }

    public function deleteReference(int $ref_id): void
    {
        if (!ilObject::_exists($ref_id, true)) {
            return;
        }
        $object = ilObjectFactory::getInstanceByRefId($ref_id);
        $object->delete();
    }

    public function getTypeOfObject(int $obj_id): string
    {
        return ilObject::_lookupType($obj_id);
    }

    public function isReferenceInContainer(int $ref_id, int $container_ref_id): bool
    {
        return $this->tree->isGrandChild($container_ref_id, $ref_id);
    }

    public function doesReferenceExist(int $ref_id): bool
    {
        return ilObject::_exists($ref_id, true);
    }

    public function isOnlyReference(int $ref_id): bool
    {
        $other_references_count = 0;
        foreach (ilObject::_getAllReferences(ilObject::_lookupObjId($ref_id)) as $other_ref_id) {
            if ($other_ref_id === $ref_id) {
                continue;
            }
            if (ilObject::_isInTrash($other_ref_id)) {
                continue;
            }
            $other_references_count++;
        }
        return $other_references_count === 0;
    }
}
