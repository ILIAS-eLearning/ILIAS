<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Self evaluation application class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
class ilSkillSelfEvaluation
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * Constructor
     *
     * @param
     */
    public function __construct($a_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        if ($a_id > 0) {
            $this->setId($a_id);
            $this->read();
        }
    }

    /**
     * Set id
     *
     * @param	integer	id
     */
    public function setId($a_val)
    {
        $this->id = $a_val;
    }

    /**
     * Get id
     *
     * @return	integer	id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user id
     *
     * @param	integer	user id
     */
    public function setUserId($a_val)
    {
        $this->user_id = $a_val;
    }
    
    /**
     * Get user id
     *
     * @return	integer	user id
     */
    public function getUserId()
    {
        return $this->user_id;
    }
    
    /**
     * Set top skill id
     *
     * @param	integer	top skill id
     */
    public function setTopSkillId($a_val)
    {
        $this->top_skill_id = $a_val;
    }

    /**
     * Get top skill id
     *
     * @return	integer	top skill id
     */
    public function getTopSkillId()
    {
        return $this->top_skill_id;
    }

    /**
     * Set created at
     *
     * @param	timestamp	created at
     */
    public function setCreated($a_val)
    {
        $this->created = $a_val;
    }

    /**
     * Get created at
     *
     * @return	timestamp	created at
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set last update
     *
     * @param	timestamp	last update
     */
    public function setLastUpdate($a_val)
    {
        $this->last_update = $a_val;
    }

    /**
     * Get last update
     *
     * @return	timestamp	last update
     */
    public function getLastUpdate()
    {
        return $this->last_update;
    }

    /**
     * Set level
     *
     * @param	array	level; index: skill id, value: level id (or 0 for no skills)
     */
    public function setLevels($a_val, $a_keep_existing = false)
    {
        if (!$a_keep_existing) {
            $this->levels = $a_val;
        } else {
            if (is_array($a_val)) {
                foreach ($a_val as $k => $v) {
                    $this->levels[$k] = $v;
                }
            }
        }
    }

    /**
     * Get level
     *
     * @return	array	level
     */
    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * Read
     *
     * @param
     * @return
     */
    public function read()
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
        $levels = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $levels[$rec["skill_id"]] = $rec["level_id"];
        }
        $this->setLevels($levels);
    }

    /**
     * Create self evaluation
     */
    public function create()
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

    /**
     * Update self evaluation
     */
    public function update()
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

    /**
     * Delete self evaluation
     */
    public function delete()
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

    /**
     * Get all self evaluations
     */
    public static function getAllSelfEvaluationsOfUser($a_user, $a_one_per_top_skill = false)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM skl_self_eval WHERE user_id = " .
            $ilDB->quote($a_user, "integer") . " " .
            "ORDER BY last_update DESC"
        );

        $self_evaluation = array();

        $top_skills = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (!$a_one_per_top_skill || !in_array($rec["top_skill_id"], $top_skills)) {
                $self_evaluation[] = $rec;
                $top_skills[] = $rec["top_skill_id"];
            }
        }

        return $self_evaluation;
    }

    /**
     * Lookup property
     *
     * @param	id		level id
     * @return	mixed	property value
     */
    protected static function lookupProperty($a_id, $a_prop)
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
    public static function getAverageLevel($a_se_id, $a_user_id, $a_top_skill_id)
    {
        global $DIC;

        $lng = $DIC->language();

        $lng->loadLanguageModule("skmg");

        include_once("./Services/Skill/classes/class.ilSkillTree.php");
        include_once("./Services/Skill/classes/class.ilBasicSkill.php");
        $stree = new ilSkillTree();
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
                    $ord = array();
                    foreach ($ls as $k => $l) {
                        $ord[$l["id"]] = $k + 1;
                    }
                    reset($ls);
                    foreach ($ls as $ld) {
                        if ($ld["id"] == $levels[$child["child"]]) {
                            $sum+= $ord[$ld["id"]];
                        }
                    }
                    $cnt+= 1;
                }
            }
        }
        if ($cnt > 0) {
            $avg = round($sum/$cnt);
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

    /**
     * Determine steps
     *
     * @param
     * @return
     */
    public static function determineSteps($a_sn_id)
    {
        $steps = array();
        if ($a_sn_id > 0) {
            include_once("./Services/Skill/classes/class.ilSkillTree.php");
            include_once("./Services/Skill/classes/class.ilSkillSelfEvalSkillTableGUI.php");
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
