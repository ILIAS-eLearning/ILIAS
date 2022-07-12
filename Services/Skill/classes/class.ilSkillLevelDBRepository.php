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
 * Class ilSkillLevelDBRepository
 */
class ilSkillLevelDBRepository implements ilSkillLevelRepository
{
    protected ilDBInterface $db;
    protected ilSkillTreeRepository $tree_repo;

    public function __construct(ilSkillTreeRepository $tree_repo, ilDBInterface $db = null)
    {
        global $DIC;

        $this->tree_repo = $tree_repo;
        $this->db = ($db)
            ?: $DIC->database();
    }

    public function deleteLevelsOfSkill(int $skill_id) : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_level WHERE "
            . " skill_id = " . $ilDB->quote($skill_id, "integer")
        );
    }

    public function addLevel(int $skill_id, string $a_title, string $a_description, string $a_import_id = "") : void
    {
        $ilDB = $this->db;

        $nr = $this->getMaxLevelNr($skill_id);
        $nid = $ilDB->nextId("skl_level");
        $ilDB->insert("skl_level", array(
            "id" => array("integer", $nid),
            "skill_id" => array("integer", $skill_id),
            "nr" => array("integer", $nr + 1),
            "title" => array("text", $a_title),
            "description" => array("clob", $a_description),
            "import_id" => array("text", $a_import_id),
            "creation_date" => array("timestamp", ilUtil::now())
        ));
    }

    protected function getMaxLevelNr(int $skill_id) : int
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT MAX(nr) mnr FROM skl_level WHERE " .
            " skill_id = " . $ilDB->quote($skill_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["mnr"] ?? 0;
    }

    /**
     * Returns multiple rows when $a_id is 0 else one or [].
     */
    public function getLevelData(int $skill_id, int $a_id = 0) : array
    {
        $ilDB = $this->db;

        $and = "";
        if ($a_id > 0) {
            $and = " AND id = " . $ilDB->quote($a_id, "integer");
        }

        $set = $ilDB->query(
            "SELECT * FROM skl_level WHERE " .
            " skill_id = " . $ilDB->quote($skill_id, "integer") .
            $and .
            " ORDER BY nr"
        );
        $levels = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            if ($a_id > 0) {
                return $rec ?? [];
            }
            $levels[] = $rec;
        }
        return $levels;
    }

    protected function lookupLevelProperty(int $a_id, string $a_prop) : ?string
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT $a_prop FROM skl_level WHERE " .
            " id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);

        return isset($rec[$a_prop]) ? (string) $rec[$a_prop] : null;
    }

    public function lookupLevelTitle(int $a_id) : string
    {
        return $this->lookupLevelProperty($a_id, "title") ?? "";
    }

    public function lookupLevelDescription(int $a_id) : string
    {
        return $this->lookupLevelProperty($a_id, "description") ?? "";
    }

    public function lookupLevelSkillId(int $a_id) : int
    {
        return (int) $this->lookupLevelProperty($a_id, "skill_id") ?? 0;
    }

    protected function writeLevelProperty(int $a_id, string $a_prop, ?string $a_value, string $a_type) : void
    {
        $ilDB = $this->db;

        $ilDB->update("skl_level", array(
            $a_prop => array($a_type, $a_value),
        ), array(
            "id" => array("integer", $a_id),
        ));
    }

    public function writeLevelTitle(int $a_id, string $a_title) : void
    {
        $this->writeLevelProperty($a_id, "title", $a_title, "text");
    }

    public function writeLevelDescription(int $a_id, string $a_description) : void
    {
        $this->writeLevelProperty($a_id, "description", $a_description, "clob");
    }

    public function updateLevelOrder(array $order) : void
    {
        $ilDB = $this->db;

        $cnt = 1;
        foreach ($order as $id => $o) {
            $ilDB->manipulate(
                "UPDATE skl_level SET " .
                " nr = " . $ilDB->quote($cnt, "integer") .
                " WHERE id = " . $ilDB->quote($id, "integer")
            );
            $cnt++;
        }
    }

    public function deleteLevel(int $a_id) : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_level WHERE "
            . " id = " . $ilDB->quote($a_id, "integer")
        );
    }

    public function fixLevelNumbering(int $skill_id) : void
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT id, nr FROM skl_level WHERE " .
            " skill_id = " . $ilDB->quote($skill_id, "integer") .
            " ORDER BY nr ASC"
        );
        $cnt = 1;
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ilDB->manipulate(
                "UPDATE skl_level SET " .
                " nr = " . $ilDB->quote($cnt, "integer") .
                " WHERE id = " . $ilDB->quote($rec["id"], "integer")
            );
            $cnt++;
        }
    }

    public function getSkillForLevelId(int $a_level_id) : ?ilBasicSkill
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_level WHERE " .
            " id = " . $ilDB->quote($a_level_id, "integer")
        );
        $skill = null;
        if ($rec = $ilDB->fetchAssoc($set)) {
            if ($this->tree_repo->isInAnyTree((int) $rec["skill_id"])) {
                $skill = new ilBasicSkill((int) $rec["skill_id"]);
            }
        }
        return $skill;
    }
}
