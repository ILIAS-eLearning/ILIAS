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
class SkillTableManager
{
    public function __construct(
    ) {
    }

    public function getSkillUsageTable(
        string $cskill_id,
        array $usage,
        string $mode = ""
    ): SkillUsageTable {
        return new SkillUsageTable($cskill_id, $usage, $mode);
    }

    public function getAssignedObjectsTable(
        array $objects
    ): AssignedObjectsTable {
        return new AssignedObjectsTable($objects);
    }

    public function getSkillProfileLevelAssignmentTable(
        string $cskill_id,
        bool $update = false
    ): SkillProfileLevelAssignmentTable {
        return new SkillProfileLevelAssignmentTable($cskill_id, $update);
    }

    public function getSkillProfileUserAssignmentTable(
        Profile\SkillProfile $profile,
        Access\SkillTreeAccess $tree_access_manager
    ): SkillProfileUserAssignmentTable {
        return new SkillProfileUserAssignmentTable($profile, $tree_access_manager);
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
