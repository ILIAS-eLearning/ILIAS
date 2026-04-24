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

namespace ILIAS\AccessControl\Object;

/**
 * @internal
 */
class ObjectDataAccessProxy
{
    public function lookupObjId(int $ref_id): int
    {
        return $this->cache()->lookupObjId($ref_id);
    }

    public function lookupOwner(int $obj_id): int
    {
        return $this->cache()->lookupOwner($obj_id);
    }

    private function cache(): \ilObjectDataCache
    {
        global $DIC;
        return $DIC['ilObjDataCache'];
    }
}
