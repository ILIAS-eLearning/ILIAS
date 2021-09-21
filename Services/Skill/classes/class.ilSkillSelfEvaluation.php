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
 * Self evaluation application class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillSelfEvaluation
{
    protected ilDBInterface $db;
    protected int $id;
    protected int $user_id;
    protected int $top_skill_id;
    protected string $created;
    protected string $last_update;
    protected array $levels;

    public function __construct(int $a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        if ($a_id > 0) {
            $this->setId($a_id);
            $this->read();
        }
    }

    public function setId(int $a_val) : void
    {
        $this->id = $a_val;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setUserId(int $a_val) : void
    {
        $this->user_id = $a_val;
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function setTopSkillId(int $a_val) : void
    {
        $this->top_skill_id = $a_val;
    }

    public function getTopSkillId() : int
    {
        return $this->top_skill_id;
    }

    public function setCreated(string $a_val) : void
    {
        $this->created = $a_val;
    }

    public function getCreated() : string
    {
        return $this->created;
    }

    public function setLastUpdate(string $a_val) : void
    {
        $this->last_update = $a_val;
    }

    public function getLastUpdate() : string
    {
        return $this->last_update;
    }

    /**
     * @param array|null $a_val level; index: skill id, value: level id (or 0 for no skills)
     * @param bool       $a_keep_existing
     */
    public function setLevels(?array $a_val, bool $a_keep_existing = false) : void
    {
        if (!$a_keep_existing) {
            $this->levels = $a_val;
        } elseif (is_array($a_val)) {
            foreach ($a_val as $k => $v) {
                $this->levels[$k] = $v;
            }
        }
    }

    public function getLevels() : array
    {
        return $this->levels;
    }

    public function read() : void
    {
        $ilDB = $this->db;

        $set = $ilDB->query(
            "SELECT * FROM skl_self_eval WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            $this->setUserId($rec["user_id"]);
            $this->setTopSkillId($rec["top_skill_id"]);
            $this->setCreated($rec["created"]);
            $this->setLastUpdate($rec["last_update"]);
        }

        // levels
        $set = $ilDB->query(
            "SELECT * FROM skl_self_eval_level WHERE " .
            " self_eval_id = " . $ilDB->quote($this->getId(), "integer")
        );
        $levels = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $levels[$rec["skill_id"]] = $rec["level_id"];
        }
        $this->setLevels($levels);
    }

    public function create() : void
    {
        $ilDB = $this->db;

        $this->setId($ilDB->nextId("skl_self_eval"));

        $ilDB->manipulate("INSERT INTO skl_self_eval " .
            "(id, user_id, top_skill_id, created, last_update) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($this->getUserId(), "integer") . "," .
            $ilDB->quote($this->getTopSkillId(), "integer") . "," .
            $ilDB->now() . "," .
            $ilDB->now() .
            ")");

        $levels = $this->getLevels();
        if (is_array($levels)) {
            foreach ($levels as $skill_id => $level_id) {
                $ilDB->manipulate("INSERT INTO skl_self_eval_level " .
                    "(self_eval_id, skill_id, level_id) VALUES (" .
                    $ilDB->quote($this->getId(), "integer") . "," .
                    $ilDB->quote($skill_id, "integer") . "," .
                    $ilDB->quote($level_id, "integer") .
                    ")");
            }
        }
    }

    public function update() : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "UPDATE skl_self_eval SET " .
            " user_id = " . $ilDB->quote($this->getUserId(), "integer") .
            ", top_skill_id = " . $ilDB->quote($this->getTopSkillId(), "integer") .
            ", last_update = " . $ilDB->now() .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );

        $ilDB->manipulate(
            "DELETE FROM skl_self_eval_level WHERE "
            . " self_eval_id = " . $ilDB->quote($this->getId(), "integer")
        );

        $levels = $this->getLevels();
        if (is_array($levels)) {
            foreach ($levels as $skill_id => $level_id) {
                $ilDB->manipulate("INSERT INTO skl_self_eval_level " .
                    "(self_eval_id, skill_id, level_id) VALUES (" .
                    $ilDB->quote($this->getId(), "integer") . "," .
                    $ilDB->quote($skill_id, "integer") . "," .
                    $ilDB->quote($level_id, "integer") .
                    ")");
            }
        }
    }

    public function delete() : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM skl_self_eval WHERE "
            . " id = " . $ilDB->quote($this->getId(), "integer")
        );

        $ilDB->manipulate(
            "DELETE FROM skl_self_eval_level WHERE "
            . " self_eval_id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    public static function getAllSelfEvaluationsOfUser(int $a_user, bool $a_one_per_top_skill = false) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM skl_self_eval WHERE user_id = " .
            $ilDB->quote($a_user, "integer") . " " .
            "ORDER BY last_update DESC"
        );

        $self_evaluation = [];

        $top_skills = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (!$a_one_per_top_skill || !in_array($rec["top_skill_id"], $top_skills)) {
                $self_evaluation[] = $rec;
                $top_skills[] = $rec["top_skill_id"];
            }
        }

        return $self_evaluation;
    }

    /**
     * @return mixed property value
     */
    protected static function lookupProperty(int $a_id, string $a_prop)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT $a_prop FROM skl_self_eval WHERE " .
            " id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_prop];
    }

    /**
     * Get average level of user self evaluation
     *
     * Note: this method does not make much sense in general, since it
     * assumes that all basic skills have the same level
     */
    public static function getAverageLevel(int $a_se_id, int $a_user_id, int $a_top_skill_id) : ?array
    {
        global $DIC;

        $lng = $DIC->language();

        $lng->loadLanguageModule("skmg");

        $stree = new ilSkillTree();
        $cnode = [];
        $ls = [];
        $cnt = 0;
        $sum = 0;
        if ($stree->isInTree($a_top_skill_id)) {
            $se = new ilSkillSelfEvaluation($a_se_id);
            $levels = $se->getLevels();

            $cnode = $stree->getNodeData($a_top_skill_id);
            $childs = $stree->getSubTree($cnode);

            foreach ($childs as $child) {
                if ($child["type"] == "skll") {
                    $sk = new ilBasicSkill($child["child"]);
                    $ls = $sk->getLevelData();
                    $ord = [];
                    foreach ($ls as $k => $l) {
                        $ord[$l["id"]] = $k + 1;
                    }
                    reset($ls);
                    foreach ($ls as $ld) {
                        if ($ld["id"] == $levels[$child["child"]]) {
                            $sum += $ord[$ld["id"]];
                        }
                    }
                    $cnt += 1;
                }
            }
        }
        if ($cnt > 0) {
            $avg = round($sum / $cnt);
            if ($avg > 0) {
                return (array("skill_title" => $cnode["title"],
                    "ord" => $avg, "avg_title" => $ls[$avg - 1]["title"]));
            } else {
                return (array("skill_title" => $cnode["title"],
                    "ord" => $avg, "avg_title" => $lng->txt("skmg_no_skills")));
            }
        }
        return null;
    }

    public static function determineSteps(int $a_sn_id) : array
    {
        $steps = [];
        if ($a_sn_id > 0) {
            $stree = new ilSkillTree();

            if ($stree->isInTree($a_sn_id)) {
                $cnode = $stree->getNodeData($a_sn_id);
                $childs = $stree->getSubTree($cnode);
                foreach ($childs as $child) {
                    if ($child["type"] == "skll") {
                        $steps[] = $child["child"];
                    }
                }
            }
        }
        return $steps;
    }
}
