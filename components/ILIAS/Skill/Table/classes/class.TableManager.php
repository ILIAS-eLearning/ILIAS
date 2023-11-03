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
 ********************************************************************
 */

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

    public function getUsageTable(
        string $cskill_id,
        array $usage,
        string $mode = ""
    ): UsageTable {
        return new UsageTable($cskill_id, $usage, $mode);
    }

    public function getAssignedObjectsTable(
        array $objects
    ): AssignedObjectsTable {
        return new AssignedObjectsTable($objects);
    }

    public function getProfileTable(
        int $ref_id,
        int $skill_tree_id
    ): ProfileTable {
        return new ProfileTable($ref_id, $skill_tree_id);
    }

    public function getProfileLevelAssignmentTable(
        string $cskill_id,
        bool $update = false
    ): ProfileLevelAssignmentTable {
        return new ProfileLevelAssignmentTable($cskill_id, $update);
    }

    public function getProfileUserAssignmentTable(
        Profile\SkillProfile $profile,
        Access\SkillTreeAccess $tree_access_manager
    ): ProfileUserAssignmentTable {
        return new ProfileUserAssignmentTable($profile, $tree_access_manager);
    }

    public function getSelfEvaluationTable(
        int $top_skill_id,
        int $tref_id,
        int $basic_skill_id
    ): SelfEvaluationTable {
        return new SelfEvaluationTable($top_skill_id, $tref_id, $basic_skill_id);
    }

    public function getAssignMaterialsTable(
        int $top_skill_id,
        int $tref_id,
        int $basic_skill_id
    ): AssignMaterialsTable {
        return new AssignMaterialsTable($top_skill_id, $tref_id, $basic_skill_id);
    }
}
