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

namespace ILIAS\Skill\Resource;

use ILIAS\Skill\Service;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillResourceDBRepository
{
    protected \ilDBInterface $db;
    protected Service\SkillInternalFactoryService $factory_service;
    protected \ilTree $tree;

    public function __construct(
        \ilDBInterface $db = null,
        Service\SkillInternalFactoryService $factory_service = null,
        \ilTree $tree = null
    ) {
        global $DIC;

        $this->db = ($db) ?: $DIC->database();
        $this->factory_service = ($factory_service) ?: $DIC->skills()->internal()->factory();
        $this->tree = ($tree) ?: $DIC->repositoryTree();
    }

    /**
     * @return SkillResource[]
     */
    public function getAll(int $skill_id, int $tref_id): array
    {
        $ilDB = $this->db;
        $tree = $this->tree;

        $set = $ilDB->query(
            "SELECT * FROM skl_skill_resource " .
            " WHERE base_skill_id = " . $ilDB->quote($skill_id, "integer") .
            " AND tref_id = " . $ilDB->quote($tref_id, "integer") .
            " ORDER BY level_id"
        );
        $resources = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            if ($tree->isInTree((int) $rec["rep_ref_id"])) {
                $resources[(int) $rec["level_id"]][] = $this->getSkillResourceFromRecord($rec);
            }
        }

        return $resources;
    }

    protected function getSkillResourceFromRecord(array $rec): SkillResource
    {
        $rec["base_skill_id"] = (int) $rec["base_skill_id"];
        $rec["tref_id"] = (int) $rec["tref_id"];
        $rec["level_id"] = (int) $rec["level_id"];
        $rec["rep_ref_id"] = (int) $rec["rep_ref_id"];
        $rec["imparting"] = (bool) $rec["imparting"];
        $rec["ltrigger"] = (bool) $rec["ltrigger"];

        return $this->factory_service->resource()->resource(
            $rec["base_skill_id"],
            $rec["tref_id"],
            $rec["level_id"],
            $rec["rep_ref_id"],
            $rec["imparting"],
            $rec["ltrigger"]
        );
    }

    public function addOrUpdate(
        int $skill_id,
        int $tref_id,
        int $level_id,
        int $rep_ref_id,
        bool $imparting,
        bool $trigger
    ): void {
        $this->db->replace(
            "skl_skill_resource",
            [
                "base_skill_id" => ["integer", $skill_id],
                "tref_id" => ["integer", $tref_id],
                "level_id" => ["integer", $level_id],
                "rep_ref_id" => ["integer", $rep_ref_id]
            ],
            [
                "imparting" => ["integer", (int) $imparting],
                "ltrigger" => ["integer", (int) $trigger]
            ]
        );
    }

    public function remove(int $skill_id, int $tref_id, int $level_id, int $rep_ref_id): void
    {
        $this->db->manipulate(
            "DELETE FROM skl_skill_resource WHERE " .
            " base_skill_id = " . $this->db->quote($skill_id, "integer") .
            " AND tref_id = " . $this->db->quote($tref_id, "integer") .
            " AND level_id = " . $this->db->quote($level_id, "integer") .
            " AND rep_ref_id = " . $this->db->quote($rep_ref_id, "integer")
        );
    }

    /**
     * @return SkillResourceLevel[]
     */
    public function getTriggerLevelsForRefId(int $rep_ref_id): array
    {
        $set = $this->db->query("SELECT * FROM skl_skill_resource " .
            " WHERE rep_ref_id = " . $this->db->quote($rep_ref_id, "integer") .
            " AND ltrigger = " . $this->db->quote(1, "integer"));

        $skill_levels = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $skill_levels[] = $this->getSkillResourceLevelFromRecord($rec);
        }
        return $skill_levels;
    }

    protected function getSkillResourceLevelFromRecord(array $rec): SkillResourceLevel
    {
        $rec["base_skill_id"] = (int) $rec["base_skill_id"];
        $rec["tref_id"] = (int) $rec["tref_id"];
        $rec["level_id"] = (int) $rec["level_id"];

        return $this->factory_service->resource()->resourceLevel(
            $rec["base_skill_id"],
            $rec["tref_id"],
            $rec["level_id"]
        );
    }
}
