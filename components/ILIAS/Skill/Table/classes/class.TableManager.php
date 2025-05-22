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

namespace ILIAS\Skill\Table;

use ILIAS\Skill\Access;
use ILIAS\Skill\Profile;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class TableManager
{
    public function __construct(
    ) {
    }

    public function getTreeTable(
        int $ref_id
    ): TreeTable {
        return new TreeTable($ref_id);
    }

    public function getLevelResourcesTable(
        int $ref_id,
        int $base_skill_id,
        int $tref_id,
        int $requested_level_id
    ): LevelResourcesTable {
        return new LevelResourcesTable($ref_id, $base_skill_id, $tref_id, $requested_level_id);
    }

    public function getAssignedObjectsTable(
        object $parent_obj,
        array $objects,
        int $skill_id = 0,
        int $tref_id = 0,
        int $profile_id = 0
    ): AssignedObjectsTable {
        return new AssignedObjectsTable($parent_obj, $objects, $skill_id, $tref_id, $profile_id);
    }

    public function getProfileTable(
        int $ref_id,
        int $skill_tree_id
    ): ProfileTable {
        return new ProfileTable($ref_id, $skill_tree_id);
    }

    public function getProfileUserAssignmentTable(
        Profile\SkillProfile $profile,
        Access\SkillTreeAccess $tree_access_manager
    ): ProfileUserAssignmentTable {
        return new ProfileUserAssignmentTable($profile, $tree_access_manager);
    }

    public function getAssignMaterialsTable(
        int $top_skill_id,
        int $tref_id,
        int $basic_skill_id
    ): AssignMaterialsTable {
        return new AssignMaterialsTable($top_skill_id, $tref_id, $basic_skill_id);
    }
}
