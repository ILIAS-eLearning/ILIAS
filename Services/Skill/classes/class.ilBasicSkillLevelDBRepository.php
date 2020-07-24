<?php

/**
 * Class ilBasicSkillLevelDBRepository
 */
class ilBasicSkillLevelDBRepository implements ilBasicSkillLevelRepository
{
    /**
     * @var ilDBInterface
     */
    protected $db;

    public function __construct(ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = ($db)
            ? $db
            : $DIC->database();
    }

    /**
     * @inheritDoc
     */
    public function deleteLevelsOfSkill(int $skill_id)
    {
        $ilDB = $this->db;

        $ilDB->manipulate("DELETE FROM skl_level WHERE "
            . " skill_id = " . $ilDB->quote($skill_id, "integer")
        );
    }

    /**
     * @inheritDoc
     */
    public function addLevel(int $skill_id, string $a_title, string $a_description, string $a_import_id = "")
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

    /**
     * Get maximum level nr
     * @return    int        maximum level nr of skill
     */
    protected function getMaxLevelNr(int $skill_id) : int
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT MAX(nr) mnr FROM skl_level WHERE " .
            " skill_id = " . $ilDB->quote($skill_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["mnr"];
    }

    /**
     * @inheritDoc
     */
    public function getLevelData(int $skill_id, int $a_id = 0) : array
    {
        $ilDB = $this->db;

        if ($a_id > 0) {
            $and = " AND id = " . $ilDB->quote($a_id, "integer");
        }

        $set = $ilDB->query("SELECT * FROM skl_level WHERE " .
            " skill_id = " . $ilDB->quote($skill_id, "integer") .
            $and .
            " ORDER BY nr"
        );
        $levels = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if ($a_id > 0) {
                return $rec;
            }
            $levels[] = $rec;
        }
        return $levels;
    }

    /**
     * Lookup level property
     * @param int    $a_id level id
     * @param string $a_prop
     * @return mixed    property value
     */
    protected function lookupLevelProperty(int $a_id, string $a_prop)
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT $a_prop FROM skl_level WHERE " .
            " id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_prop];
    }

    /**
     * @inheritDoc
     */
    public function lookupLevelTitle(int $a_id) : string
    {
        return $this->lookupLevelProperty($a_id, "title");
    }

    /**
     * @inheritDoc
     */
    public function lookupLevelDescription(int $a_id) : string
    {
        return $this->lookupLevelProperty($a_id, "description");
    }

    /**
     * @inheritDoc
     */
    public function lookupLevelSkillId(int $a_id) : int
    {
        return $this->lookupLevelProperty($a_id, "skill_id");
    }

    /**
     * Write level property
     * @param int    $a_id
     * @param string $a_prop
     * @param        $a_value
     * @param string $a_type
     */
    protected function writeLevelProperty(int $a_id, string $a_prop, $a_value, string $a_type)
    {
        $ilDB = $this->db;

        $ilDB->update("skl_level", array(
            $a_prop => array($a_type, $a_value),
        ), array(
            "id" => array("integer", $a_id),
        ));
    }

    /**
     * @inheritDoc
     */
    public function writeLevelTitle(int $a_id, string $a_title)
    {
        $this->writeLevelProperty($a_id, "title", $a_title, "text");
    }

    /**
     * @inheritDoc
     */
    public function writeLevelDescription(int $a_id, string $a_description)
    {
        $this->writeLevelProperty($a_id, "description", $a_description, "clob");
    }

    /**
     * @inheritDoc
     */
    public function updateLevelOrder(array $order)
    {
        $ilDB = $this->db;

        $cnt = 1;
        foreach ($order as $id => $o) {
            $ilDB->manipulate("UPDATE skl_level SET " .
                " nr = " . $ilDB->quote($cnt, "integer") .
                " WHERE id = " . $ilDB->quote($id, "integer")
            );
            $cnt++;
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteLevel(int $a_id)
    {
        $ilDB = $this->db;

        $ilDB->manipulate("DELETE FROM skl_level WHERE "
            . " id = " . $ilDB->quote($a_id, "integer")
        );

    }

    /**
     * @inheritDoc
     */
    public function fixLevelNumbering(int $skill_id)
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT id, nr FROM skl_level WHERE " .
            " skill_id = " . $ilDB->quote($skill_id, "integer") .
            " ORDER BY nr ASC"
        );
        $cnt = 1;
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ilDB->manipulate("UPDATE skl_level SET " .
                " nr = " . $ilDB->quote($cnt, "integer") .
                " WHERE id = " . $ilDB->quote($rec["id"], "integer")
            );
            $cnt++;
        }
    }

    /**
     * @inheritDoc
     */
    public function getSkillForLevelId(int $a_level_id)
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT * FROM skl_level WHERE " .
            " id = " . $ilDB->quote($a_level_id, "integer")
        );
        $skill = null;
        if ($rec = $ilDB->fetchAssoc($set)) {
            if (ilSkillTreeNode::isInTree($rec["skill_id"])) {
                $skill = new ilBasicSkill($rec["skill_id"]);
            }
        }
        return $skill;
    }
}