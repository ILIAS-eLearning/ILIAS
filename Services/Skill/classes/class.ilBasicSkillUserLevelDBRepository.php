<?php

class ilBasicSkillUserLevelDBRepository implements ilBasicSkillUserLevelRepository
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
    public function deleteUserLevelsOfSkill(int $skill_id)
    {
        $ilDB = $this->db;

        $ilDB->manipulate("DELETE FROM skl_user_has_level WHERE "
            . " skill_id = " . $ilDB->quote($skill_id, "integer")
        );
    }

    /**
     * @inheritDoc
     */
    public function resetUserSkillLevelStatus(
        bool $update,
        int $trigger_obj_id,
        $status_date,
        int $a_user_id,
        int $a_skill_id,
        int $a_tref_id = 0,
        int $a_trigger_ref_id = 0,
        bool $a_self_eval = false
    ) {
        $ilDB = $this->db;

        if ($update) {
            // this will only be set in self eval case, means this will always have a $rec
            $now = ilUtil::now();
            $ilDB->manipulate("UPDATE skl_user_skill_level SET " .
                " level_id = " . $ilDB->quote(0, "integer") . "," .
                " next_level_fulfilment = " . $ilDB->quote(0.0, "float") . "," .
                " status_date = " . $ilDB->quote($now, "timestamp") .
                " WHERE user_id = " . $ilDB->quote($a_user_id, "integer") .
                " AND status_date = " . $ilDB->quote($status_date, "timestamp") .
                " AND skill_id = " . $ilDB->quote($a_skill_id, "integer") .
                " AND status = " . $ilDB->quote(ilBasicSkill::ACHIEVED, "integer") .
                " AND trigger_obj_id = " . $ilDB->quote($trigger_obj_id, "integer") .
                " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
                " AND self_eval = " . $ilDB->quote($a_self_eval, "integer")
            );
        } else {
            $now = ilUtil::now();
            $ilDB->manipulate("INSERT INTO skl_user_skill_level " .
                "(level_id, user_id, tref_id, status_date, skill_id, status, valid, trigger_ref_id," .
                "trigger_obj_id, trigger_obj_type, trigger_title, self_eval, unique_identifier," .
                "next_level_fulfilment) VALUES (" .
                $ilDB->quote(0, "integer") . "," .
                $ilDB->quote($a_user_id, "integer") . "," .
                $ilDB->quote((int) $a_tref_id, "integer") . "," .
                $ilDB->quote($now, "timestamp") . "," .
                $ilDB->quote($a_skill_id, "integer") . "," .
                $ilDB->quote(ilBasicSkill::ACHIEVED, "integer") . "," .
                $ilDB->quote(1, "integer") . "," .
                $ilDB->quote($a_trigger_ref_id, "integer") . "," .
                $ilDB->quote($trigger_obj_id, "integer") . "," .
                $ilDB->quote("", "text") . "," .
                $ilDB->quote("", "text") . "," .
                $ilDB->quote($a_self_eval, "integer") . "," .
                $ilDB->quote("", "text") . "," .
                $ilDB->quote(0.0, "float") .
                ")");
        }

        $ilDB->manipulate("DELETE FROM skl_user_has_level WHERE "
            . " user_id = " . $ilDB->quote($a_user_id, "integer")
            . " AND skill_id = " . $ilDB->quote($a_skill_id, "integer")
            . " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer")
            . " AND trigger_obj_id = " . $ilDB->quote($trigger_obj_id, "integer")
            . " AND self_eval = " . $ilDB->quote($a_self_eval, "integer")
        );
    }

    /**
     * @inheritDoc
     */
    public function hasRecentSelfEvaluation(
        int $trigger_obj_id,
        int $a_user_id,
        int $a_skill_id,
        int $a_tref_id = 0,
        int $a_trigger_ref_id = 0
    ) {
        $ilDB = $this->db;

        $recent = "";

        $ilDB->setLimit(1);
        $set = $ilDB->query("SELECT * FROM skl_user_skill_level WHERE " .
            "skill_id = " . $ilDB->quote($a_skill_id, "integer") . " AND " .
            "user_id = " . $ilDB->quote($a_user_id, "integer") . " AND " .
            "tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") . " AND " .
            "trigger_obj_id = " . $ilDB->quote($trigger_obj_id, "integer") . " AND " .
            "self_eval = " . $ilDB->quote(1, "integer") .
            " ORDER BY status_date DESC"
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            $status_day = substr($rec["status_date"], 0, 10);
            $today = substr(ilUtil::now(), 0, 10);
            if ($rec["valid"] && $rec["status"] == ilBasicSkill::ACHIEVED && $status_day == $today) {
                $recent = $rec["status_date"];
            }
        }

        return $recent;
    }

    /**
     * @inheritDoc
     */
    public function getNewAchievementsPerUser(
        string $a_timestamp,
        string $a_timestamp_to = null,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : array {
        $ilDB = $this->db;

        $to = (!is_null($a_timestamp_to))
            ? " AND status_date <= " . $ilDB->quote($a_timestamp_to, "timestamp")
            : "";

        $user = ($a_user_id > 0)
            ? " AND user_id = " . $ilDB->quote($a_user_id, "integer")
            : "";

        $set = $ilDB->query("SELECT * FROM skl_user_skill_level " .
            " WHERE status_date >= " . $ilDB->quote($a_timestamp, "timestamp") .
            " AND valid = " . $ilDB->quote(1, "integer") .
            " AND status = " . $ilDB->quote(ilBasicSkill::ACHIEVED, "integer") .
            " AND self_eval = " . $ilDB->quote($a_self_eval, "integer") .
            $to .
            $user .
            " ORDER BY user_id, status_date ASC ");
        $achievments = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $achievments[$rec["user_id"]][] = $rec;
        }

        return $achievments;
    }

    /**
     * @inheritDoc
     */
    public function writeUserSkillLevelStatus(
        int $skill_id,
        int $trigger_ref_id,
        int $trigger_obj_id,
        ?string $trigger_title,
        ?string $trigger_type,
        bool $update,
        $status_date,
        int $a_level_id,
        int $a_user_id,
        int $a_tref_id = 0,
        bool $a_self_eval = false,
        string $a_unique_identifier = "",
        float $a_next_level_fulfilment = 0.0
    ) {
        $ilDB = $this->db;
        $a_status = ilBasicSkill::ACHIEVED;

        if ($update) {
            // this will only be set in self eval case, means this will always have a $rec
            $now = ilUtil::now();
            $ilDB->manipulate("UPDATE skl_user_skill_level SET " .
                " level_id = " . $ilDB->quote($a_level_id, "integer") . "," .
                " status_date = " . $ilDB->quote($now, "timestamp") . "," .
                " next_level_fulfilment = " . $ilDB->quote((float) $a_next_level_fulfilment, "float") .
                " WHERE user_id = " . $ilDB->quote($a_user_id, "integer") .
                " AND status_date = " . $ilDB->quote($status_date, "timestamp") .
                " AND skill_id = " . $ilDB->quote($skill_id, "integer") .
                " AND status = " . $ilDB->quote($a_status, "integer") .
                " AND trigger_obj_id = " . $ilDB->quote($trigger_obj_id, "integer") .
                " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
                " AND self_eval = " . $ilDB->quote($a_self_eval, "integer")
            );
        } else {
            if ($a_unique_identifier != "") {
                $ilDB->manipulate("DELETE FROM skl_user_skill_level WHERE " .
                    " user_id = " . $ilDB->quote($a_user_id, "integer") .
                    " AND tref_id = " . $ilDB->quote($a_tref_id, "integer") .
                    " AND skill_id = " . $ilDB->quote($skill_id, "integer") .
                    " AND trigger_ref_id = " . $ilDB->quote($trigger_ref_id, "integer") .
                    " AND trigger_obj_id = " . $ilDB->quote($trigger_obj_id, "integer") .
                    " AND self_eval = " . $ilDB->quote($a_self_eval, "integer") .
                    " AND unique_identifier = " . $ilDB->quote($a_unique_identifier, "text")
                );
            }

            $now = ilUtil::now();
            $ilDB->manipulate("INSERT INTO skl_user_skill_level " .
                "(level_id, user_id, tref_id, status_date, skill_id, status, valid, trigger_ref_id," .
                "trigger_obj_id, trigger_obj_type, trigger_title, self_eval, unique_identifier," .
                "next_level_fulfilment) VALUES (" .
                $ilDB->quote($a_level_id, "integer") . "," .
                $ilDB->quote($a_user_id, "integer") . "," .
                $ilDB->quote((int) $a_tref_id, "integer") . "," .
                $ilDB->quote($now, "timestamp") . "," .
                $ilDB->quote($skill_id, "integer") . "," .
                $ilDB->quote($a_status, "integer") . "," .
                $ilDB->quote(1, "integer") . "," .
                $ilDB->quote($trigger_ref_id, "integer") . "," .
                $ilDB->quote($trigger_obj_id, "integer") . "," .
                $ilDB->quote($trigger_type, "text") . "," .
                $ilDB->quote($trigger_title, "text") . "," .
                $ilDB->quote($a_self_eval, "integer") . "," .
                $ilDB->quote($a_unique_identifier, "text") . "," .
                $ilDB->quote((float) $a_next_level_fulfilment, "float") .
                ")");
        }

        // fix (removed level_id and added skill id, since table should hold only
        // one entry per skill)
        $ilDB->manipulate("DELETE FROM skl_user_has_level WHERE "
            . " user_id = " . $ilDB->quote($a_user_id, "integer")
            . " AND skill_id = " . $ilDB->quote($skill_id, "integer")
            . " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer")
            . " AND trigger_obj_id = " . $ilDB->quote($trigger_obj_id, "integer")
            . " AND self_eval = " . $ilDB->quote($a_self_eval, "integer")
        );

        if ($a_status == ilBasicSkill::ACHIEVED) {
            $ilDB->manipulate("INSERT INTO skl_user_has_level " .
                "(level_id, user_id, tref_id, status_date, skill_id, trigger_ref_id, trigger_obj_id, trigger_obj_type," .
                "trigger_title, self_eval, next_level_fulfilment) VALUES (" .
                $ilDB->quote($a_level_id, "integer") . "," .
                $ilDB->quote($a_user_id, "integer") . "," .
                $ilDB->quote($a_tref_id, "integer") . "," .
                $ilDB->quote($now, "timestamp") . "," .
                $ilDB->quote($skill_id, "integer") . "," .
                $ilDB->quote($trigger_ref_id, "integer") . "," .
                $ilDB->quote($trigger_obj_id, "integer") . "," .
                $ilDB->quote($trigger_type, "text") . "," .
                $ilDB->quote($trigger_title, "text") . "," .
                $ilDB->quote($a_self_eval, "integer") . "," .
                $ilDB->quote((float) $a_next_level_fulfilment, "float") .
                ")");
        }
    }

    /**
     * @inheritDoc
     */
    public function removeAllUserSkillLevelStatusOfObject(
        int $a_user_id,
        int $a_trigger_obj_id,
        bool $a_self_eval = false,
        string $a_unique_identifier = ""
    ) : bool {
        $ilDB = $this->db;

        $changed = false;

        $aff_rows = $ilDB->manipulate("DELETE FROM skl_user_skill_level WHERE "
            . " user_id = " . $ilDB->quote($a_user_id, "integer")
            . " AND trigger_obj_id = " . $ilDB->quote($a_trigger_obj_id, "integer")
            . " AND self_eval = " . $ilDB->quote($a_self_eval, "integer")
            . " AND unique_identifier = " . $ilDB->quote($a_unique_identifier, "text")
        );
        if ($aff_rows > 0) {
            $changed = true;
        }

        $aff_rows = $ilDB->manipulate("DELETE FROM skl_user_has_level WHERE "
            . " user_id = " . $ilDB->quote($a_user_id, "integer")
            . " AND trigger_obj_id = " . $ilDB->quote($a_trigger_obj_id, "integer")
            . " AND self_eval = " . $ilDB->quote($a_self_eval, "integer")
        );
        if ($aff_rows > 0) {
            $changed = true;
        }
        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function removeAllUserData(int $a_user_id)
    {
        $ilDB = $this->db;

        $ilDB->manipulate("DELETE FROM skl_user_skill_level WHERE "
            . " user_id = " . $ilDB->quote($a_user_id, "integer")
        );
        $ilDB->manipulate("DELETE FROM skl_user_has_level WHERE "
            . " user_id = " . $ilDB->quote($a_user_id, "integer")
        );
    }

    /**
     * @inheritDoc
     */
    public function getMaxLevelPerType(
        int $skill_id,
        array $levels,
        int $a_tref_id,
        string $a_type,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : int {
        $ilDB = $this->db;

        $set = $ilDB->query($q = "SELECT level_id FROM skl_user_has_level " .
            " WHERE trigger_obj_type = " . $ilDB->quote($a_type, "text") .
            " AND skill_id = " . $ilDB->quote($skill_id, "integer") .
            " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND self_eval = " . $ilDB->quote($a_self_eval, "integer")
        );

        $has_level = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $has_level[$rec["level_id"]] = true;
        }
        $max_level = 0;
        foreach ($levels as $l) {
            if (isset($has_level[$l["id"]])) {
                $max_level = $l["id"];
            }
        }
        return $max_level;
    }

    /**
     * @inheritDoc
     */
    public function getAllLevelEntriesOfUser(
        int $skill_id,
        int $a_tref_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : array {
        $ilDB = $this->db;

        $set = $ilDB->query($q = "SELECT * FROM skl_user_has_level " .
            " WHERE skill_id = " . $ilDB->quote($skill_id, "integer") .
            " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND self_eval = " . $ilDB->quote($a_self_eval, "integer") .
            " ORDER BY status_date DESC"
        );

        $levels = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $levels[] = $rec;
        }
        return $levels;
    }

    /**
     * @inheritDoc
     */
    public function getAllHistoricLevelEntriesOfUser(
        int $skill_id,
        int $a_tref_id,
        int $a_user_id = 0,
        int $a_eval_by = 0
    ) : array {
        $ilDB = $this->db;

        $by = ($a_eval_by != ilBasicSkill::EVAL_BY_ALL)
            ? " AND self_eval = " . $ilDB->quote($a_self_eval, "integer")
            : "";

        $set = $ilDB->query($q = "SELECT * FROM skl_user_skill_level " .
            " WHERE skill_id = " . $ilDB->quote($skill_id, "integer") .
            " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            $by .
            " ORDER BY status_date DESC"
        );
        $levels = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $levels[] = $rec;
        }
        return $levels;
    }

    /**
     * @inheritDoc
     */
    public function getMaxLevelPerObject(
        int $skill_id,
        array $levels,
        int $a_tref_id,
        int $a_object_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : int {
        $ilDB = $this->db;

        $set = $ilDB->query($q = "SELECT level_id FROM skl_user_has_level " .
            " WHERE trigger_obj_id = " . $ilDB->quote($a_object_id, "integer") .
            " AND skill_id = " . $ilDB->quote($skill_id, "integer") .
            " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND self_eval = " . $ilDB->quote($a_self_eval, "integer")
        );

        $has_level = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $has_level[$rec["level_id"]] = true;
        }
        $max_level = 0;
        foreach ($levels as $l) {
            if (isset($has_level[$l["id"]])) {
                $max_level = $l["id"];
            }
        }
        return $max_level;
    }

    /**
     * @inheritDoc
     */
    public function getMaxLevel(
        int $skill_id,
        array $levels,
        int $a_tref_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : int {
        $ilDB = $this->db;

        $set = $ilDB->query($q = "SELECT level_id FROM skl_user_has_level " .
            " WHERE skill_id = " . $ilDB->quote($skill_id, "integer") .
            " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND self_eval = " . $ilDB->quote($a_self_eval, "integer")
        );

        $has_level = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $has_level[$rec["level_id"]] = true;
        }
        $max_level = 0;
        foreach ($levels as $l) {
            if (isset($has_level[$l["id"]])) {
                $max_level = $l["id"];
            }
        }
        return $max_level;
    }

    /**
     * @inheritDoc
     */
    public function hasSelfEvaluated(int $a_user_id, int $a_skill_id, int $a_tref_id) : bool
    {
        $ilDB = $this->db;

        $set = $ilDB->query($q = "SELECT level_id FROM skl_user_has_level " .
            " WHERE skill_id = " . $ilDB->quote((int) $a_skill_id, "integer") .
            " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND self_eval = " . $ilDB->quote(1, "integer")
        );

        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getLastLevelPerObject(
        int $skill_id,
        int $a_tref_id,
        int $a_object_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : ?int {
        $ilDB = $this->db;

        $ilDB->setLimit(1);
        $set = $ilDB->query($q = "SELECT level_id FROM skl_user_has_level " .
            " WHERE trigger_obj_id = " . $ilDB->quote($a_object_id, "integer") .
            " AND skill_id = " . $ilDB->quote($skill_id, "integer") .
            " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND self_eval = " . $ilDB->quote($a_self_eval, "integer") .
            " ORDER BY status_date DESC"
        );

        $rec = $ilDB->fetchAssoc($set);

        return $rec["level_id"];
    }

    /**
     * @inheritDoc
     */
    public function getLastUpdatePerObject(
        int $skill_id,
        int $a_tref_id,
        int $a_object_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : ?string {
        $ilDB = $this->db;

        $ilDB->setLimit(1);
        $set = $ilDB->query($q = "SELECT status_date FROM skl_user_has_level " .
            " WHERE trigger_obj_id = " . $ilDB->quote($a_object_id, "integer") .
            " AND skill_id = " . $ilDB->quote($skill_id, "integer") .
            " AND tref_id = " . $ilDB->quote((int) $a_tref_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND self_eval = " . $ilDB->quote($a_self_eval, "integer") .
            " ORDER BY status_date DESC"
        );

        $rec = $ilDB->fetchAssoc($set);

        return $rec["status_date"];
    }
}