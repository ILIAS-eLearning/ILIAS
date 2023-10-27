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

namespace ILIAS\Skill\Usage;

class SkillUsageDBRepository
{
    protected \ilDBInterface $db;

    public function __construct(
        \ilDBInterface $db = null
    ) {
        global $DIC;

        $this->db = ($db) ?: $DIC->database();
    }

    public function add(int $obj_id, int $skill_id, int $tref_id): void
    {
        $this->db->replace(
            "skl_usage",
            [
                "obj_id" => ["integer", $obj_id],
                "skill_id" => ["integer", $skill_id],
                "tref_id" => ["integer", $tref_id]
            ],
            []
        );
    }

    public function remove(int $obj_id, int $skill_id, int $tref_id): void
    {
        $this->db->manipulate(
            "DELETE FROM skl_usage WHERE " .
            " obj_id = " . $this->db->quote($obj_id, "integer") .
            " AND skill_id = " . $this->db->quote($skill_id, "integer") .
            " AND tref_id = " . $this->db->quote($tref_id, "integer")
        );
    }

    public function removeFromObject(int $obj_id): void
    {
        $this->db->manipulate(
            "DELETE FROM skl_usage WHERE " .
            " obj_id = " . $this->db->quote($obj_id, "integer")
        );
    }

    public function removeForSkill(int $node_id, bool $is_referenece = false): void
    {
        if (!$is_referenece) {
            $this->db->manipulate(
                "DELETE FROM skl_usage WHERE " .
                " skill_id = " . $this->db->quote($node_id, "integer")
            );
        } else {
            $this->db->manipulate(
                "DELETE FROM skl_usage WHERE " .
                " tref_id = " . $this->db->quote($node_id, "integer")
            );
        }
    }

    /**
     * @return int[]
     */
    public function getUsages(int $skill_id, int $tref_id): array
    {
        $set = $this->db->query(
            "SELECT obj_id FROM skl_usage " .
            " WHERE skill_id = " . $this->db->quote($skill_id, "integer") .
            " AND tref_id = " . $this->db->quote($tref_id, "integer")
        );
        $obj_ids = [];
        while ($rec = $this->db->fetchAssoc($set)) {
            $obj_ids[] = (int) $rec["obj_id"];
        }

        return $obj_ids;
    }

    /**
     * Get standard usage query
     * @param array{skill_id: int, tref_id: int}[] $cskill_ids
     *
     * @return array<string, array<string, array{key: string}[]>>
     */
    public function getUsageInfoGeneric(
        array $cskill_ids,
        string $usage_type,
        string $table,
        string $key_field,
        string $skill_field = "skill_id",
        string $tref_field = "tref_id"
    ): array {
        $usages = [];

        $w = "WHERE";
        $q = "SELECT " . $key_field . ", " . $skill_field . ", " . $tref_field . " FROM " . $table . " ";
        foreach ($cskill_ids as $sk) {
            $q .= $w . " (" . $skill_field . " = " . $this->db->quote($sk["skill_id"], "integer") .
                " AND " . $tref_field . " = " . $this->db->quote($sk["tref_id"], "integer") . ") ";
            $w = "OR";
        }
        $q .= " GROUP BY " . $key_field . ", " . $skill_field . ", " . $tref_field;

        $set = $this->db->query($q);
        while ($rec = $this->db->fetchAssoc($set)) {
            $usages[$rec[$skill_field] . ":" . $rec[$tref_field]][$usage_type][] =
                array("key" => $rec[$key_field]);
        }

        return $usages;
    }
}
