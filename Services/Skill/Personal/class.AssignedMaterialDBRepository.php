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

namespace ILIAS\Skill\Personal;

use ILIAS\Skill\Service;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class AssignedMaterialDBRepository
{
    protected \ilDBInterface $db;
    protected Service\SkillInternalFactoryService $factory_service;

    public function __construct(
        \ilDBInterface $db = null,
        Service\SkillInternalFactoryService $factory_service = null
    ) {
        global $DIC;

        $this->db = ($db) ?: $DIC->database();
        $this->factory_service = ($factory_service) ?: $DIC->skills()->internal()->factory();
    }

    /**
     * Assign material to skill level
     *
     * @param int $user_id user id
     * @param int $top_skill_id the "selectable" top skill
     * @param int $tref_id template reference id
     * @param int $basic_skill_id the basic skill the level belongs to
     * @param int $level_id level id
     * @param int $wsp_id workspace object
     */
    public function assign(
        int $user_id,
        int $top_skill_id,
        int $tref_id,
        int $basic_skill_id,
        int $level_id,
        int $wsp_id
    ): void {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_assigned_material " .
            " WHERE user_id = " . $ilDB->quote($user_id, "integer") .
            " AND top_skill_id = " . $ilDB->quote($top_skill_id, "integer") .
            " AND tref_id = " . $ilDB->quote($tref_id, "integer") .
            " AND skill_id = " . $ilDB->quote($basic_skill_id, "integer") .
            " AND level_id = " . $ilDB->quote($level_id, "integer") .
            " AND wsp_id = " . $ilDB->quote($wsp_id, "integer")
        );
        if (!$ilDB->fetchAssoc($set)) {
            $ilDB->manipulate("INSERT INTO skl_assigned_material " .
                "(user_id, top_skill_id, tref_id, skill_id, level_id, wsp_id) VALUES (" .
                $ilDB->quote($user_id, "integer") . "," .
                $ilDB->quote($top_skill_id, "integer") . "," .
                $ilDB->quote($tref_id, "integer") . "," .
                $ilDB->quote($basic_skill_id, "integer") . "," .
                $ilDB->quote($level_id, "integer") . "," .
                $ilDB->quote($wsp_id, "integer") .
                ")");
        }
    }

    /**
     * Get assigned materials (for a skill level and user)
     * @return AssignedMaterial[]
     */
    public function get(int $user_id, int $tref_id, int $level_id): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_assigned_material " .
            " WHERE level_id = " . $ilDB->quote($level_id, "integer") .
            " AND tref_id = " . $ilDB->quote($tref_id, "integer") .
            " AND user_id = " . $ilDB->quote($user_id, "integer")
        );
        $mat = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $mat[] = $this->getFromRecord($rec);
        }
        return $mat;
    }

    protected function getFromRecord(array $rec): AssignedMaterial
    {
        $rec['user_id'] = (int) $rec['user_id'];
        $rec['top_skill_id'] = (int) $rec['top_skill_id'];
        $rec['skill_id'] = (int) $rec['skill_id'];
        $rec['level_id'] = (int) $rec['level_id'];
        $rec['wsp_id'] = (int) $rec['wsp_id'];
        $rec['tref_id'] = (int) $rec['tref_id'];

        return $this->factory_service->personal()->assignedMaterial(
            $rec['user_id'],
            $rec['top_skill_id'],
            $rec['skill_id'],
            $rec['level_id'],
            $rec['wsp_id'],
            $rec['tref_id']
        );
    }

    /**
     * Count assigned materials (for a skill level and user)
     */
    public function count(int $user_id, int $tref_id, int $level_id): int
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT count(*) as cnt FROM skl_assigned_material " .
            " WHERE level_id = " . $ilDB->quote($level_id, "integer") .
            " AND tref_id = " . $ilDB->quote($tref_id, "integer") .
            " AND user_id = " . $ilDB->quote($user_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["cnt"];
    }

    public function remove(int $user_id, int $tref_id, int $level_id, int $wsp_id): void
    {
        $ilDB = $this->db;

        $t = "DELETE FROM skl_assigned_material WHERE " .
            " user_id = " . $ilDB->quote($user_id, "integer") .
            " AND tref_id = " . $ilDB->quote($tref_id, "integer") .
            " AND level_id = " . $ilDB->quote($level_id, "integer") .
            " AND wsp_id = " . $ilDB->quote($wsp_id, "integer");

        $ilDB->manipulate($t);
    }

    public function removeAll(int $user_id): void
    {
        $ilDB = $this->db;

        $t = "DELETE FROM skl_assigned_material WHERE " .
            " user_id = " . $ilDB->quote($user_id, "integer");
        $ilDB->manipulate($t);
    }
}
