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
 * Manages resources for skills. This is not about user assigned materials,
 * it is about resources that are assigned to skill levels in the
 * competence management administration of ILIAS.
 *
 * This can be either triggers (e.g. a course that triggers a competence level)
 * or resources that impart the knowledge of a competence level. Imparting
 * does not necessarily mean that it triggers a competence level.
 *
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillResources implements ilSkillUsageInfo
{
    protected ilDBInterface $db;
    protected ilTree $tree;
    protected int $base_skill_id = 0;
    protected int $tref_id = 0;

    // The resources array has the following keys (int)
    // first dimension is "level_id" (int): the skill level id
    // second dimension is "rep_ref_id" (int): the ref id of the repository resource
    //
    // The values of the array are associatives arrays with the following key value pairs:
    // level_id (int): the skill level id
    // rep_ref_id (int): the ref id of the repository resource
    // trigger: 1, if the resource triggers the skill level (0 otherwise)
    // imparting: 1, if the resource imparts knowledge of the skill level (0 otherwise)
    /**
     * @var array<int, array<int, array{level_id: int, rep_ref_id: int, trigger: int, imparting: int}>>
     */
    protected array $resources = [];

    public function __construct(int $a_skill_id = 0, int $a_tref_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->setBaseSkillId($a_skill_id);
        $this->setTemplateRefId($a_tref_id);

        if ($a_skill_id > 0) {
            $this->readResources();
        }
    }

    public function setBaseSkillId(int $a_val): void
    {
        $this->base_skill_id = $a_val;
    }

    public function getBaseSkillId(): int
    {
        return $this->base_skill_id;
    }

    public function setTemplateRefId(int $a_val): void
    {
        $this->tref_id = $a_val;
    }

    public function getTemplateRefId(): int
    {
        return $this->tref_id;
    }

    public function readResources(): void
    {
        $ilDB = $this->db;
        $tree = $this->tree;

        $set = $ilDB->query(
            "SELECT * FROM skl_skill_resource " .
            " WHERE base_skill_id = " . $ilDB->quote($this->getBaseSkillId(), "integer") .
            " AND tref_id = " . $ilDB->quote($this->getTemplateRefId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            if ($tree->isInTree($rec["rep_ref_id"])) {
                $this->resources[(int) $rec["level_id"]][(int) $rec["rep_ref_id"]] = array(
                    "level_id" => (int) $rec["level_id"],
                    "rep_ref_id" => (int) $rec["rep_ref_id"],
                    "trigger" => (int) $rec["ltrigger"],
                    "imparting" => (int) $rec["imparting"]
                );
            }
        }
    }

    public function save(): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_skill_resource WHERE " .
            " base_skill_id = " . $ilDB->quote($this->getBaseSkillId(), "integer") .
            " AND tref_id = " . $ilDB->quote($this->getTemplateRefId(), "integer")
        );
        foreach ($this->getResources() as $level_id => $l) {
            foreach ($l as $ref_id => $r) {
                if ($r["imparting"] || $r["trigger"]) {
                    $ilDB->manipulate("INSERT INTO skl_skill_resource " .
                        "(base_skill_id, tref_id, level_id, rep_ref_id, imparting, ltrigger) VALUES (" .
                        $ilDB->quote($this->getBaseSkillId(), "integer") . "," .
                        $ilDB->quote($this->getTemplateRefId(), "integer") . "," .
                        $ilDB->quote($level_id, "integer") . "," .
                        $ilDB->quote($ref_id, "integer") . "," .
                        $ilDB->quote((int) $r["imparting"], "integer") . "," .
                        $ilDB->quote((int) $r["trigger"], "integer") .
                        ")");
                }
            }
        }
    }

    /**
     * @return array<int, array<int, array{level_id: int, rep_ref_id: int, trigger: int, imparting: int}>>
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * @return array<int, array{level_id: int, rep_ref_id: int, trigger: int, imparting: int}>
     */
    public function getResourcesOfLevel(int $a_level_id): array
    {
        $ret = (isset($this->resources[$a_level_id]) && is_array($this->resources[$a_level_id]))
            ? $this->resources[$a_level_id]
            : [];

        return $ret;
    }

    public function setResourceAsTrigger(int $a_level_id, int $a_rep_ref_id, bool $a_trigger = true): void
    {
        if (!isset($this->resources[$a_level_id]) || !is_array($this->resources[$a_level_id])) {
            $this->resources[$a_level_id] = [];
        }
        if (!isset($this->resources[$a_level_id][$a_rep_ref_id]) || !is_array($this->resources[$a_level_id][$a_rep_ref_id])) {
            $this->resources[$a_level_id][$a_rep_ref_id] = [];
        }

        $this->resources[$a_level_id][$a_rep_ref_id]["trigger"] = $a_trigger;
    }

    public function setResourceAsImparting(int $a_level_id, int $a_rep_ref_id, bool $a_imparting = true): void
    {
        if (!isset($this->resources[$a_level_id]) || !is_array($this->resources[$a_level_id])) {
            $this->resources[$a_level_id] = [];
        }
        if (!isset($this->resources[$a_level_id][$a_rep_ref_id]) || !is_array($this->resources[$a_level_id][$a_rep_ref_id])) {
            $this->resources[$a_level_id][$a_rep_ref_id] = [];
        }

        $this->resources[$a_level_id][$a_rep_ref_id]["imparting"] = $a_imparting;
    }

    /**
     * @param array{skill_id: int, tref_id: int}[] $a_cskill_ids array of common skill ids
     *
     * @return array<string, array<string, array{key: string}[]>>
     */
    public static function getUsageInfo(array $a_cskill_ids): array
    {
        return ilSkillUsage::getUsageInfoGeneric(
            $a_cskill_ids,
            ilSkillUsage::RESOURCE,
            "skl_skill_resource",
            "rep_ref_id",
            "base_skill_id"
        );
    }

    /**
     * @return array{base_skill_id: int, tref_id: int, level_id: int}[]
     */
    public static function getTriggerLevelsForRefId(int $a_ref_id): array
    {
        global $DIC;

        $db = $DIC->database();

        $set = $db->query("SELECT * FROM skl_skill_resource " .
            " WHERE rep_ref_id = " . $db->quote($a_ref_id, "integer") .
            " AND ltrigger = " . $db->quote(1, "integer"));

        $skill_levels = [];
        while ($rec = $db->fetchAssoc($set)) {
            $skill_levels[] = array(
                "base_skill_id" => (int) $rec["base_skill_id"],
                "tref_id" => (int) $rec["tref_id"],
                "level_id" => (int) $rec["level_id"]
            );
        }
        return $skill_levels;
    }
}
