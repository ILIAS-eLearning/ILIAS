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

use ILIAS\Skill\Service\SkillTreeService;

/**
 * Skills of a container
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilContainerSkills
{
    protected ilDBInterface $db;
    protected SkillTreeService $tree_service;
    protected array $skills = [];
    protected int $id = 0;

    public function __construct(int $a_obj_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->tree_service = $DIC->skills()->tree();

        $this->setId($a_obj_id);
        if ($a_obj_id > 0) {
            $this->read();
        }
    }

    public function setId(int $a_val): void
    {
        $this->id = $a_val;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function resetSkills(): void
    {
        $this->skills = [];
    }

    public function addSkill(int $a_skill_id, int $a_tref_id): void
    {
        $this->skills[$a_skill_id . "-" . $a_tref_id] = [
            "skill_id" => $a_skill_id,
            "tref_id" => $a_tref_id
        ];
    }

    public function removeSkill(int $a_skill_id, int $a_tref_id): void
    {
        unset($this->skills[$a_skill_id . "-" . $a_tref_id]);
    }

    public function getSkills(): array
    {
        return $this->skills;
    }

    /**
     * @return array[]|string[]
     */
    public function getOrderedSkills(): array
    {
        $vtree = $this->tree_service->getGlobalVirtualSkillTree();
        return $vtree->getOrderedNodeset($this->getSkills(), "skill_id", "tref_id");
    }

    public function read(): void
    {
        $db = $this->db;

        $this->skills = [];
        $set = $db->query("SELECT * FROM cont_skills " .
            " WHERE id  = " . $db->quote($this->getId(), "integer"));
        while ($rec = $db->fetchAssoc($set)) {
            $this->skills[$rec["skill_id"] . "-" . $rec["tref_id"]] = $rec;
        }
    }

    public function delete(): void
    {
        $db = $this->db;

        $db->manipulate("DELETE FROM cont_skills WHERE " .
            " id = " . $db->quote($this->getId(), "integer"));
    }

    public function save(): void
    {
        $db = $this->db;

        $this->delete();
        foreach ($this->skills as $s) {
            $db->manipulate("INSERT INTO cont_skills " .
                "(id, skill_id, tref_id) VALUES (" .
                $db->quote($this->getId(), "integer") . "," .
                $db->quote($s["skill_id"], "integer") . "," .
                $db->quote($s["tref_id"], "integer") . ")");
        }
    }
}
