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
 ********************************************************************
 */

/**
 * Class ilBasicSkillObjectAdapter
 */
class ilSkillObjectAdapter implements ilSkillObjectAdapterInterface
{
    public function __construct()
    {
    }

    public function getObjIdForRefId(int $a_ref_id): int
    {
        $trigger_obj_id = ($a_ref_id > 0)
            ? ilObject::_lookupObjId($a_ref_id)
            : 0;

        return $trigger_obj_id;
    }

    public function getTypeForObjId(int $a_obj_id): ?string
    {
        return ilObject::_lookupType($a_obj_id);
    }

    public function getTitleForObjId(int $a_obj_id): string
    {
        return ilObject::_lookupTitle($a_obj_id);
    }
}
