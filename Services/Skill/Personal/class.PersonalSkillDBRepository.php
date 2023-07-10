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
class PersonalSkillDBRepository
{
    protected \ilSkillTreeRepository $tree_repo;
    protected \ilDBInterface $db;
    protected Service\SkillInternalFactoryService $factory_service;

    public function __construct(
        \ilSkillTreeRepository $tree_repo,
        \ilDBInterface $db = null,
        Service\SkillInternalFactoryService $factory_service = null
    ) {
        global $DIC;

        $this->tree_repo = $tree_repo;
        $this->db = ($db) ?: $DIC->database();
        $this->factory_service = ($factory_service) ?: $DIC->skills()->internal()->factory();
    }

    /**
     * @return array<int, SelectedUserSkill>
     */
    public function get(int $user_id): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_personal_skill " .
            " WHERE user_id = " . $ilDB->quote($user_id, "integer")
        );
        $pskills = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $skill_node_id = (int) $rec["skill_node_id"];
            if ($this->tree_repo->isInAnyTree($skill_node_id)) {
                $pskills[$skill_node_id] = $this->getFromRecord($rec);
            }
        }
        return $pskills;
    }

    protected function getFromRecord(array $rec): SelectedUserSkill
    {
        $skill_node_id = (int) $rec["skill_node_id"];

        return $this->factory_service->personal()->selectedUserSkill(
            $skill_node_id,
            \ilSkillTreeNode::_lookupTitle($skill_node_id)
        );
    }

    public function add(int $user_id, int $skill_node_id): void
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_personal_skill " .
            " WHERE user_id = " . $ilDB->quote($user_id, "integer") .
            " AND skill_node_id = " . $ilDB->quote($skill_node_id, "integer")
        );
        if (!$ilDB->fetchAssoc($set)) {
            $ilDB->manipulate("INSERT INTO skl_personal_skill " .
                "(user_id, skill_node_id) VALUES (" .
                $ilDB->quote($user_id, "integer") . "," .
                $ilDB->quote($skill_node_id, "integer") .
                ")");
        }
    }

    public function remove(int $user_id, int $skill_node_id): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_personal_skill WHERE " .
            " user_id = " . $ilDB->quote($user_id, "integer") .
            " AND skill_node_id = " . $ilDB->quote($skill_node_id, "integer")
        );
    }

    public function removeAll(int $user_id): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_personal_skill WHERE " .
            " user_id = " . $ilDB->quote($user_id, "integer")
        );
    }

    /**
     * @param array<string, array<string, array{key: string}[]>> $usages
     * @param int[] $pskill_ids
     * @param int[] $tref_ids
     *
     * @return array<string, array<string, array{key: string}[]>>
     */
    public function getUsages(array $usages, array $pskill_ids, array $tref_ids): array
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT skill_node_id, user_id FROM skl_personal_skill " .
            " WHERE " . $ilDB->in("skill_node_id", $pskill_ids, false, "integer") .
            " GROUP BY skill_node_id, user_id"
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (isset($tref_ids[(int) $rec["skill_node_id"]])) {
                $usages[$tref_ids[$rec["skill_node_id"]] . ":" . $rec["skill_node_id"]][\ilSkillUsage::PERSONAL_SKILL][] =
                    array("key" => $rec["user_id"]);
            } else {
                $usages[$rec["skill_node_id"] . ":0"][\ilSkillUsage::PERSONAL_SKILL][] =
                    array("key" => $rec["user_id"]);
            }
        }

        return $usages;
    }
}
