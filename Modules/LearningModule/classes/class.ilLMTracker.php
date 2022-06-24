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
 *********************************************************************/

/**
 * Track access to ILIAS learning modules
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMTracker
{
    public const NOT_ATTEMPTED = 0;
    public const IN_PROGRESS = 1;
    public const COMPLETED = 2;
    public const FAILED = 3;
    public const CURRENT = 99;
    protected int $user_id;

    protected ilDBInterface $db;
    protected ilLanguage $lng;
    protected ilPluginAdmin $plugin_admin;
    protected ilObjUser $user;
    protected int $lm_ref_id;
    protected int $lm_obj_id;
    protected ilLMTree $lm_tree;
    protected array $lm_obj_ids = array();
    protected array $tree_arr = array();		// tree array
    protected array $re_arr = array();		// read event data array
    protected bool $loaded_for_node = false;	// current node for that the tracking data has been loaded
    protected bool $dirty = false;
    protected array $page_questions = array();
    protected array $all_questions = array();
    protected array $answer_status = array();
    protected bool $has_incorrect_answers = false;
    protected int $current_page_id = 0;

    public static array $instances = array();
    public static array $instancesbyobj = array();

    private function __construct(
        int $a_id,
        bool $a_by_obj_id = false,
        int $a_user_id = 0
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $this->user = $DIC->user();
        $this->user_id = $a_user_id;

        if ($a_by_obj_id) {
            $this->lm_ref_id = 0;
            $this->lm_obj_id = $a_id;
        } else {
            $this->lm_ref_id = $a_id;
            $this->lm_obj_id = ilObject::_lookupObjId($a_id);
        }

        $this->lm_tree = ilLMTree::getInstance($this->lm_obj_id);
    }

    public static function getInstance(
        int $a_ref_id,
        int $a_user_id = 0
    ) : self {
        global $DIC;

        $ilUser = $DIC->user();

        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }

        if (!isset(self::$instances[$a_ref_id][$a_user_id])) {
            self::$instances[$a_ref_id][$a_user_id] = new ilLMTracker($a_ref_id, false, $a_user_id);
        }
        return self::$instances[$a_ref_id][$a_user_id];
    }

    public static function getInstanceByObjId(
        int $a_obj_id,
        int $a_user_id = 0
    ) : self {
        global $DIC;

        $ilUser = $DIC->user();

        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }

        if (!isset(self::$instancesbyobj[$a_obj_id][$a_user_id])) {
            self::$instancesbyobj[$a_obj_id][$a_user_id] = new ilLMTracker($a_obj_id, true, $a_user_id);
        }
        return self::$instancesbyobj[$a_obj_id][$a_user_id];
    }

    ////
    //// Tracking
    ////

    /**
     * Track access to lm page
     */
    public function trackAccess(
        int $a_page_id,
        int $user_id
    ) : void {
        if ($user_id == ANONYMOUS_USER_ID) {
            ilChangeEvent::_recordReadEvent("lm", $this->lm_ref_id, $this->lm_obj_id, $user_id);
            return;
        }

        if ($this->lm_ref_id == 0) {
            throw new ilLMPresentationException("ilLMTracker: No Ref Id given.");
        }

        // track page and chapter access
        $this->trackPageAndChapterAccess($a_page_id);

        // track last page access (must be done after calling trackPageAndChapterAccess())
        $this->trackLastPageAccess($this->user_id, $this->lm_ref_id, $a_page_id);

        // #9483
        // general learning module lp tracking
        ilLearningProgress::_tracProgress(
            $this->user_id,
            $this->lm_obj_id,
            $this->lm_ref_id,
            "lm"
        );

        // obsolete?
        ilLPStatusWrapper::_updateStatus($this->lm_obj_id, $this->user_id);

        // mark currently loaded data as dirty to force reload if necessary
        $this->dirty = true;
    }

    /**
     * Track last accessed page for a learning module
     * @param int $usr_id user id
     * @param int $lm_id learning module id
     * @param int $obj_id page id
     */
    public function trackLastPageAccess(
        int $usr_id,
        int $lm_id,
        int $obj_id
    ) : void {
        $title = "";
        $db = $this->db;
        $db->replace(
            "lo_access",
            [
            "usr_id" => ["integer", $usr_id],
            "lm_id" => ["integer", $lm_id]
        ],
            [
                "timestamp" => ["timestamp", ilUtil::now()],
                "obj_id" => ["integer", $obj_id],
                "lm_title" => ["text", $title]
            ]
        );
    }

    protected function trackPageAndChapterAccess(
        int $a_page_id
    ) : void {
        $ilDB = $this->db;

        $now = time();

        //
        // 1. Page access: current page
        //
        $set = $ilDB->query("SELECT obj_id FROM lm_read_event" .
            " WHERE obj_id = " . $ilDB->quote($a_page_id, "integer") .
            " AND usr_id = " . $ilDB->quote($this->user_id, "integer"));
        if (!$ilDB->fetchAssoc($set)) {
            $fields = array(
                "obj_id" => array("integer", $a_page_id),
                "usr_id" => array("integer", $this->user_id)
            );
            // $ilDB->insert("lm_read_event", $fields);
            $ilDB->replace("lm_read_event", $fields, array()); // #15144
        }

        // update all parent chapters
        $ilDB->manipulate("UPDATE lm_read_event SET" .
            " read_count = read_count + 1 " .
            " , last_access = " . $ilDB->quote($now, "integer") .
            " WHERE obj_id = " . $ilDB->quote($a_page_id, "integer") .
            " AND usr_id = " . $ilDB->quote($this->user_id, "integer"));


        //
        // 2. Chapter access: based on last page accessed
        //

        // get last accessed page
        $set = $ilDB->query("SELECT * FROM lo_access WHERE " .
            "usr_id = " . $ilDB->quote($this->user_id, "integer") . " AND " .
            "lm_id = " . $ilDB->quote($this->lm_ref_id, "integer"));
        $res = $ilDB->fetchAssoc($set);
        if (isset($res["obj_id"])) {
            $valid_timespan = ilObjUserTracking::_getValidTimeSpan();

            $pg_ts = new ilDateTime($res["timestamp"], IL_CAL_DATETIME);
            $pg_ts = $pg_ts->get(IL_CAL_UNIX);
            $pg_id = $res["obj_id"];
            if (!$this->lm_tree->isInTree($pg_id)) {
                return;
            }
            
            $time_diff = $read_diff = 0;

            // spent_seconds or read_count ?
            if (($now - $pg_ts) <= $valid_timespan) {
                $time_diff = $now - $pg_ts;
            } else {
                $read_diff = 1;
            }

            // find parent chapter(s) for that page
            $parent_st_ids = array();
            foreach ($this->lm_tree->getPathFull($pg_id) as $item) {
                if ($item["type"] == "st") {
                    $parent_st_ids[] = $item["obj_id"];
                }
            }

            if ($parent_st_ids && ($time_diff || $read_diff)) {
                // get existing chapter entries
                $ex_st = array();
                $set = $ilDB->query("SELECT obj_id FROM lm_read_event" .
                    " WHERE " . $ilDB->in("obj_id", $parent_st_ids, "", "integer") .
                    " AND usr_id = " . $ilDB->quote($this->user_id, "integer"));
                while ($row = $ilDB->fetchAssoc($set)) {
                    $ex_st[] = $row["obj_id"];
                }

                // add missing chapter entries
                $missing_st = array_diff($parent_st_ids, $ex_st);
                if (sizeof($missing_st)) {
                    foreach ($missing_st as $st_id) {
                        $fields = array(
                            "obj_id" => array("integer", $st_id),
                            "usr_id" => array("integer", $this->user_id)
                        );
                        // $ilDB->insert("lm_read_event", $fields);
                        $ilDB->replace("lm_read_event", $fields, array()); // #15144
                    }
                }

                // update all parent chapters
                $ilDB->manipulate("UPDATE lm_read_event SET" .
                    " read_count = read_count + " . $ilDB->quote($read_diff, "integer") .
                    " , spent_seconds = spent_seconds + " . $ilDB->quote($time_diff, "integer") .
                    " , last_access = " . $ilDB->quote($now, "integer") .
                    " WHERE " . $ilDB->in("obj_id", $parent_st_ids, "", "integer") .
                    " AND usr_id = " . $ilDB->quote($this->user_id, "integer"));
            }
        }
    }


    ////
    //// Tracking
    ////

    public function setCurrentPage(
        int $a_val
    ) : void {
        $this->current_page_id = $a_val;
    }

    public function getCurrentPage() : int
    {
        return $this->current_page_id;
    }

    /**
     * Load LM tracking data. Loaded when needed.
     */
    protected function loadLMTrackingData() : void
    {
        $ilDB = $this->db;

        // we must prevent loading tracking data multiple times during a request where possible
        // please note that the dirty flag works only to a certain limit
        // e.g. if questions are answered the flag is not set (yet)
        // or if pages/chapter are added/deleted the flag is not set
        if ((int) $this->loaded_for_node === $this->getCurrentPage() && !$this->dirty) {
            return;
        }

        $this->loaded_for_node = $this->getCurrentPage();
        $this->dirty = false;

        // load lm tree in array
        $this->tree_arr = array();
        $nodes = $this->lm_tree->getCompleteTree();
        foreach ($nodes as $node) {
            $this->tree_arr["childs"][$node["parent"]][] = $node;
            $this->tree_arr["parent"][$node["child"]] = $node["parent"];
            $this->tree_arr["nodes"][$node["child"]] = $node;
        }

        // load all lm obj ids of learning module
        $this->lm_obj_ids = ilLMObject::_getAllLMObjectsOfLM($this->lm_obj_id);

        // load read event data
        $this->re_arr = array();
        $set = $ilDB->query("SELECT * FROM lm_read_event " .
            " WHERE " . $ilDB->in("obj_id", $this->lm_obj_ids, false, "integer") .
            " AND usr_id = " . $ilDB->quote($this->user_id, "integer"));
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->re_arr[$rec["obj_id"]] = $rec;
        }

        // load question/pages information
        $this->page_questions = array();
        $this->all_questions = array();
        $q = ilLMPageObject::queryQuestionsOfLearningModule($this->lm_obj_id, "", "", 0, 0);
        foreach ($q["set"] as $quest) {
            $this->page_questions[$quest["page_id"]][] = $quest["question_id"];
            $this->all_questions[] = $quest["question_id"];
        }

        // load question answer information
        $this->answer_status = ilPageQuestionProcessor::getAnswerStatus($this->all_questions, $this->user_id);

        $this->has_incorrect_answers = false;

        $has_pred_incorrect_answers = false;
        $has_pred_incorrect_not_unlocked_answers = false;
        $this->determineProgressStatus($this->lm_tree->readRootId(), $has_pred_incorrect_answers, $has_pred_incorrect_not_unlocked_answers);

        $this->has_incorrect_answers = $has_pred_incorrect_answers;
    }

    /**
     * Have all questions been answered correctly (and questions exist)?
     * @return bool true, if learning module contains any question and all questions (in the chapter structure) have been answered correctly
     */
    public function getAllQuestionsCorrect() : bool
    {
        $this->loadLMTrackingData();
        if (count($this->all_questions) > 0 && !$this->has_incorrect_answers) {
            return true;
        }
        return false;
    }


    /**
     * Determine progress status of nodes
     * @param int $a_obj_id lm object id
     */
    protected function determineProgressStatus(
        int $a_obj_id,
        bool &$a_has_pred_incorrect_answers,
        bool &$a_has_pred_incorrect_not_unlocked_answers
    ) : int {
        $status = ilLMTracker::NOT_ATTEMPTED;

        if (isset($this->tree_arr["nodes"][$a_obj_id])) {
            $this->tree_arr["nodes"][$a_obj_id]["has_pred_incorrect_answers"] = $a_has_pred_incorrect_answers;
            $this->tree_arr["nodes"][$a_obj_id]["has_pred_incorrect_not_unlocked_answers"] = $a_has_pred_incorrect_not_unlocked_answers;

            if (isset($this->tree_arr["childs"][$a_obj_id])) {
                // sort childs in correct order
                $this->tree_arr["childs"][$a_obj_id] = ilArrayUtil::sortArray(
                    $this->tree_arr["childs"][$a_obj_id],
                    "lft",
                    "asc",
                    true
                );

                $cnt_completed = 0;
                foreach ($this->tree_arr["childs"][$a_obj_id] as $c) {
                    // if child is not activated/displayed count child as implicitly completed
                    // rationale: everything that is visible for the learner determines the status
                    // see also bug #14642
                    if (!self::_isNodeVisible($c)) {
                        $cnt_completed++;
                        continue;
                    }
                    $c_stat = $this->determineProgressStatus(
                        $c["child"],
                        $a_has_pred_incorrect_answers,
                        $a_has_pred_incorrect_not_unlocked_answers
                    );
                    if ($status != ilLMTracker::FAILED) {
                        if ($c_stat == ilLMTracker::FAILED) {
                            $status = ilLMTracker::IN_PROGRESS;
                        } elseif ($c_stat == ilLMTracker::IN_PROGRESS) {
                            $status = ilLMTracker::IN_PROGRESS;
                        } elseif ($c_stat == ilLMTracker::COMPLETED || $c_stat == ilLMTracker::CURRENT) {
                            $status = ilLMTracker::IN_PROGRESS;
                            $cnt_completed++;
                        }
                    }
                    // if an item is failed or in progress or (not attempted and contains questions)
                    // the next item has predecessing incorrect answers
                    if ($this->tree_arr["nodes"][$c["child"]]["type"] == "pg") {
                        if ($c_stat == ilLMTracker::FAILED || $c_stat == ilLMTracker::IN_PROGRESS ||
                            ($c_stat == ilLMTracker::NOT_ATTEMPTED && isset($this->page_questions[$c["child"]]) && count($this->page_questions[$c["child"]]) > 0)) {
                            $a_has_pred_incorrect_answers = true;
                            if (!$this->tree_arr["nodes"][$c["child"]]["unlocked"]) {
                                $a_has_pred_incorrect_not_unlocked_answers = true;
                            }
                        }
                    }
                }
                if ($cnt_completed == count($this->tree_arr["childs"][$a_obj_id])) {
                    $status = ilLMTracker::COMPLETED;
                }
            } elseif ($this->tree_arr["nodes"][$a_obj_id]["type"] == "pg") {
                // check read event data
                if (isset($this->re_arr[$a_obj_id]) && $this->re_arr[$a_obj_id]["read_count"] > 0) {
                    $status = ilLMTracker::COMPLETED;
                } elseif ($a_obj_id == $this->getCurrentPage()) {
                    $status = ilLMTracker::CURRENT;
                }

                $unlocked = false;
                if (isset($this->page_questions[$a_obj_id])) {
                    // check questions, if one is failed -> failed
                    $unlocked = true;
                    foreach ($this->page_questions[$a_obj_id] as $q_id) {
                        if (isset($this->answer_status[$q_id])
                            && $this->answer_status[$q_id]["try"] > 0
                            && !$this->answer_status[$q_id]["passed"]) {
                            $status = ilLMTracker::FAILED;
                            if (!$this->answer_status[$q_id]["unlocked"]) {
                                $unlocked = false;
                            }
                        }
                    }

                    // check questions, if one is not answered -> in progress
                    if ($status != ilLMTracker::FAILED) {
                        foreach ($this->page_questions[$a_obj_id] as $q_id) {
                            if (!isset($this->answer_status[$q_id])
                                || $this->answer_status[$q_id]["try"] == 0) {
                                if ($status != ilLMTracker::NOT_ATTEMPTED) {
                                    $status = ilLMTracker::IN_PROGRESS;
                                }
                            }
                        }
                        $unlocked = false;
                    }
                }
                $this->tree_arr["nodes"][$a_obj_id]["unlocked"] = $unlocked;
                $this->tree_arr["nodes"][$a_obj_id]["has_pred_incorrect_answers"] = $a_has_pred_incorrect_answers;
                $this->tree_arr["nodes"][$a_obj_id]["has_pred_incorrect_not_unlocked_answers"] = $a_has_pred_incorrect_not_unlocked_answers;
            }
        } /*else {	// free pages (currently not called, since only walking through tree structure)
        }*/
        $this->tree_arr["nodes"][$a_obj_id]["status"] = $status;

        return $status;
    }

    public function getIconForLMObject(
        array $a_node,
        int $a_highlighted_node = 0
    ) : string {
        $this->loadLMTrackingData();
        if ($a_node["child"] == $a_highlighted_node) {
            return ilUtil::getImagePath('scorm/running.svg');
        }
        if (isset($this->tree_arr["nodes"][$a_node["child"]])) {
            switch ($this->tree_arr["nodes"][$a_node["child"]]["status"]) {
                case ilLMTracker::IN_PROGRESS:
                    return ilUtil::getImagePath('scorm/incomplete.svg');

                case ilLMTracker::FAILED:
                    return ilUtil::getImagePath('scorm/failed.svg');

                case ilLMTracker::COMPLETED:
                    return ilUtil::getImagePath('scorm/completed.svg');
            }
        }
        return ilUtil::getImagePath('scorm/not_attempted.svg');
    }

    /**
     * Has predecessing incorrect answers
     * @return bool true if incorrect/unsanswered questions exist in predecessing pages
     */
    public function hasPredIncorrectAnswers(
        int $a_obj_id,
        bool $a_ignore_unlock = false
    ) {
        $this->loadLMTrackingData();
        $ret = false;
        if (isset($this->tree_arr["nodes"][$a_obj_id])) {
            if ($a_ignore_unlock) {
                $ret = $this->tree_arr["nodes"][$a_obj_id]["has_pred_incorrect_answers"];
            } else {
                $ret = $this->tree_arr["nodes"][$a_obj_id]["has_pred_incorrect_not_unlocked_answers"];
            }
        }
        return $ret;
    }

    ////
    //// Blocked Users
    ////

    public function getBlockedUsersInformation() : array
    {
        $ilDB = $this->db;
        $lng = $this->lng;
        $ilPluginAdmin = $this->plugin_admin;

        $blocked_users = array();

        // load question/pages information
        $this->page_questions = array();
        $this->all_questions = array();
        $page_for_question = array();
        $q = ilLMPageObject::queryQuestionsOfLearningModule($this->lm_obj_id, "", "", 0, 0);
        foreach ($q["set"] as $quest) {
            $this->page_questions[$quest["page_id"]][] = $quest["question_id"];
            $this->all_questions[] = $quest["question_id"];
            $page_for_question[$quest["question_id"]] = $quest["page_id"];
        }
        // get question information
        $qlist = new ilAssQuestionList($ilDB, $lng, $ilPluginAdmin);
        $qlist->setParentObjId(0);
        $qlist->setJoinObjectData(false);
        $qlist->addFieldFilter("question_id", $this->all_questions);
        $qlist->load();
        $qdata = $qlist->getQuestionDataArray();

        // load question answer information
        $this->answer_status = ilPageQuestionProcessor::getAnswerStatus($this->all_questions);
        foreach ($this->answer_status as $as) {
            if ($as["try"] >= $qdata[$as["qst_id"]]["nr_of_tries"] && $qdata[$as["qst_id"]]["nr_of_tries"] > 0 && !$as["passed"]) {
                //var_dump($qdata[$as["qst_id"]]);
                $name = ilObjUser::_lookupName($as["user_id"]);
                $as["user_name"] = $name["lastname"] . ", " . $name["firstname"] . " [" . $name["login"] . "]";
                $as["question_text"] = $qdata[$as["qst_id"]]["question_text"];
                $as["page_id"] = $page_for_question[$as["qst_id"]];
                $as["page_title"] = ilLMPageObject::_lookupTitle($as["page_id"]);
                $blocked_users[] = $as;
            }
        }

        return $blocked_users;
    }

    /**
     * Is node visible for the learner
     */
    public static function _isNodeVisible(
        array $a_node
    ) : bool {
        if ($a_node["type"] != "pg") {
            return true;
        }

        $lm_set = new ilSetting("lm");
        $active = ilPageObject::_lookupActive(
            $a_node["child"],
            "lm",
            $lm_set->get("time_scheduled_page_activation")
        );

        if (!$active) {
            $act_data = ilPageObject::_lookupActivationData((int) $a_node["child"], "lm");
            if ($act_data["show_activation_info"] &&
                (ilUtil::now() < $act_data["activation_start"])) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}
