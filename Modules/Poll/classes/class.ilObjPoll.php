<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
* Class ilObjPoll
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*/
class ilObjPoll extends ilObject2
{
    protected $access_type; // [int]
    protected $access_begin; // [timestamp]
    protected $access_end; // [timestamp]
    protected $access_visibility; // [bool]
    protected $question; // [string]
    protected $image; // [string]
    protected $view_results; // [int]
    protected $period; // [bool]
    protected $period_begin; // [timestamp]
    protected $period_end; // [timestamp]
    
    // 4.5
    protected $max_number_answers = 1; // [int]
    protected $result_sort_by_votes = false; // [bool]
    protected $mode_non_anonymous = false; // [bool]
    protected $show_comments = false; //[bool]
    protected $show_results_as = 1; //[int]
    
    const VIEW_RESULTS_ALWAYS = 1;
    const VIEW_RESULTS_NEVER = 2;
    const VIEW_RESULTS_AFTER_VOTE = 3;
    const VIEW_RESULTS_AFTER_PERIOD = 4;

    const SHOW_RESULTS_AS_BARCHART = 1;
    const SHOW_RESULTS_AS_PIECHART = 2;
    
    public function __construct($a_id = 0, $a_reference = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        // default
        $this->setViewResults(self::VIEW_RESULTS_AFTER_VOTE);
        $this->setAccessType(ilObjectActivation::TIMINGS_DEACTIVATED);
        $this->setVotingPeriod(false);
        
        parent::__construct($a_id, $a_reference);
    }
    
    public function initType()
    {
        $this->type = "poll";
    }
    
    public function setAccessType($a_value)
    {
        $this->access_type = (int) $a_value;
    }
    
    public function getAccessType()
    {
        return $this->access_type;
    }
    
    public function setAccessBegin($a_value)
    {
        $this->access_begin = (int) $a_value;
    }
    
    public function getAccessBegin()
    {
        return $this->access_begin;
    }
    
    public function setAccessEnd($a_value)
    {
        $this->access_end = (int) $a_value;
    }
    
    public function getAccessEnd()
    {
        return $this->access_end;
    }
    
    public function setAccessVisibility($a_value)
    {
        $this->access_visibility = (bool) $a_value;
    }
    
    public function getAccessVisibility()
    {
        return $this->access_visibility;
    }
    
    public function setQuestion($a_value)
    {
        $this->question = (string) $a_value;
    }
    
    public function getQuestion()
    {
        return $this->question;
    }
    
    public function setImage($a_value)
    {
        $this->image = (string) $a_value;
    }
    
    public function getImage()
    {
        return $this->image;
    }
    
    public function setViewResults($a_value)
    {
        $this->view_results = (int) $a_value;
    }
    
    public function getViewResults()
    {
        return $this->view_results;
    }
    
    public function setVotingPeriod($a_value)
    {
        $this->period = (bool) $a_value;
    }
    
    public function getVotingPeriod()
    {
        return $this->period;
    }
    
    public function setVotingPeriodBegin($a_value)
    {
        $this->period_begin = (int) $a_value;
    }
    
    public function getVotingPeriodBegin()
    {
        return $this->period_begin;
    }
    
    public function setVotingPeriodEnd($a_value)
    {
        $this->period_end = (int) $a_value;
    }
    
    public function getVotingPeriodEnd()
    {
        return $this->period_end;
    }
    
    public function setMaxNumberOfAnswers($a_value)
    {
        $this->max_number_answers = (int) $a_value;
    }
    
    public function getMaxNumberOfAnswers()
    {
        return $this->max_number_answers;
    }
    
    public function setSortResultByVotes($a_value)
    {
        $this->result_sort_by_votes = (bool) $a_value;
    }
    
    public function getSortResultByVotes()
    {
        return $this->result_sort_by_votes;
    }
    
    public function setNonAnonymous($a_value)
    {
        $this->mode_non_anonymous = (bool) $a_value;
    }
    
    public function getNonAnonymous()
    {
        return $this->mode_non_anonymous;
    }

    public function setShowComments($a_value)
    {
        $this->show_comments = (bool) $a_value;
    }

    public function getShowComments()
    {
        return $this->show_comments;
    }

    public function setShowResultsAs($a_value)
    {
        $this->show_results_as = (int) $a_value;
    }

    public function getShowResultsAs()
    {
        return $this->show_results_as;
    }

    protected function doRead()
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT * FROM il_poll" .
                " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
        $row = $ilDB->fetchAssoc($set);
        $this->setQuestion((string) ($row["question"] ?? ''));
        $this->setImage((string) ($row["image"] ?? ''));
        $this->setViewResults((int) ($row["view_results"] ?? self::VIEW_RESULTS_AFTER_VOTE));
        $this->setVotingPeriod((int) ($row["period"] ?? 0));
        $this->setVotingPeriodBegin((int) ($row["period_begin"] ?? 0));
        $this->setVotingPeriodEnd((int) ($row["period_end"] ?? 0));
        $this->setMaxNumberOfAnswers((int) ($row["max_answers"] ?? 0));
        $this->setSortResultByVotes((int) ($row["result_sort"] ?? 0));
        $this->setNonAnonymous((int) ($row["non_anon"] ?? 0));
        $this->setShowResultsAs((int) ($row["show_results_as"] ?? self::SHOW_RESULTS_AS_BARCHART));
        
        // #14661
        $this->setShowComments(ilNote::commentsActivated($this->getId(), 0, $this->getType()));
        
        if ($this->ref_id) {
            $activation = ilObjectActivation::getItem($this->ref_id);
            $this->setAccessType((int) ($activation["timing_type"] ?? ilObjectActivation::TIMINGS_DEACTIVATED ));
            if ($this->getAccessType() == ilObjectActivation::TIMINGS_ACTIVATION) {
                // default entry values should not be loaded if not activated
                $this->setAccessBegin((int) ($activation["timing_start"] ?? time()));
                $this->setAccessEnd((int) ($activation["timing_end"] ?? time()));
                $this->setAccessVisibility((bool) ($activation["visible"] ?? false));
            }
        }
    }
    
    protected function propertiesToDB()
    {
        $fields = array(
            "question" => array("text", $this->getQuestion()),
            "image" => array("text", $this->getImage()),
            "view_results" => array("integer", $this->getViewResults()),
            "period" => array("integer", $this->getVotingPeriod()),
            "period_begin" => array("integer", $this->getVotingPeriodBegin()),
            "period_end" => array("integer", $this->getVotingPeriodEnd()),
            "max_answers" => array("integer", $this->getMaxNumberOfAnswers()),
            "result_sort" => array("integer", $this->getSortResultByVotes()),
            "non_anon" => array("integer", $this->getNonAnonymous()),
            "show_results_as" => array("integer", $this->getShowResultsAs()),
        );
                        
        return $fields;
    }

    protected function doCreate()
    {
        $ilDB = $this->db;
        
        if ($this->getId()) {
            $fields = $this->propertiesToDB();
            $fields["id"] = array("integer", $this->getId());

            $ilDB->insert("il_poll", $fields);
            
            
            // object activation default entry will be created on demand
            
            
            // block handling
            $block = new ilPollBlock();
            $block->setType("poll");
            $block->setContextObjId($this->getId());
            $block->setContextObjType("poll");
            $block->create();
        }
    }
        
    protected function doUpdate()
    {
        $ilDB = $this->db;
    
        if ($this->getId()) {
            $fields = $this->propertiesToDB();
            
            $ilDB->update(
                "il_poll",
                $fields,
                array("id" => array("integer", $this->getId()))
            );
            
            // #14661
            ilNote::activateComments($this->getId(), 0, $this->getType(), $this->getShowComments());
            
            if ($this->ref_id) {
                $activation = new ilObjectActivation();
                $activation->setTimingType($this->getAccessType());
                $activation->setTimingStart($this->getAccessBegin());
                $activation->setTimingEnd($this->getAccessEnd());
                $activation->toggleVisible($this->getAccessVisibility());
                $activation->update($this->ref_id);
            }
        }
    }
    
    protected function doDelete()
    {
        $ilDB = $this->db;
        
        if ($this->getId()) {
            $this->deleteImage();
            $this->deleteAllAnswers();
            
            if ($this->ref_id) {
                ilObjectActivation::deleteAllEntries($this->ref_id);
            }
            
            $ilDB->manipulate("DELETE FROM il_poll" .
                " WHERE id = " . $ilDB->quote($this->id, "integer"));
        }
    }
    
    /**
     * Clone poll
     *
     * @param ilObjPoll new object
     * @param int target ref_id
     * @param int copy id
     * @return ilObjPoll
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = 0)
    {
        assert($new_obj instanceof ilObjPoll);
        
        // question/image
        $new_obj->setQuestion($this->getQuestion());
        $image = $this->getImageFullPath();
        if ($image) {
            $image = array("tmp_name" => $image,
                "name" => $this->getImage());
            $new_obj->uploadImage($image, true);
        }

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

        if ($cp_options->isRootNode($this->getRefId())) {
            $new_obj->setOfflineStatus(true);
        }

        $new_obj->setViewResults($this->getViewResults());
        $new_obj->setShowComments($this->getShowComments());
        $new_obj->setShowResultsAs($this->getShowResultsAs());
        $new_obj->update();
        
        // answers
        $answers = $this->getAnswers();
        if ($answers) {
            foreach ($answers as $item) {
                $new_obj->saveAnswer($item["answer"]);
            }
        }
        
        return $new_obj;
    }
        
    
    //
    // image
    //
    
    public function getImageFullPath(bool $a_as_thumb = false) : ?string
    {
        $img = $this->getImage();
        if ($img) {
            $path = $this->initStorage($this->id);
            if (!$a_as_thumb) {
                return $path . $img;
            } else {
                return $path . "thb_" . $img;
            }
        }

        return null;
    }
    
    /**
     * remove existing file
     */
    public function deleteImage()
    {
        if ($this->id) {
            $storage = new ilFSStoragePoll($this->id);
            $storage->delete();
            
            $this->setImage(null);
        }
    }

    /**
     * Init file system storage
     *
     * @return string
     */
    public static function initStorage(int $a_id, string $a_subdir = null)
    {
        $storage = new ilFSStoragePoll($a_id);
        $storage->create();
        
        $path = $storage->getAbsolutePath() . "/";
        
        if ($a_subdir) {
            $path .= $a_subdir . "/";
            
            if (!is_dir($path)) {
                mkdir($path);
            }
        }
                
        return $path;
    }
    
    /**
     * Upload new image file
     *
     * @return bool
     */
    public function uploadImage(array $a_upload, bool $a_clone = false)
    {
        if (!$this->id) {
            return false;
        }
        
        $this->deleteImage();
        
        // #10074
        $name = (string) ($a_upload['name'] ?? '');
        $tmp_name = (string) ($a_upload['tmp_name'] ?? '');
        $clean_name = preg_replace("/[^a-zA-Z0-9\_\.\-]/", "", $name);
    
        $path = $this->initStorage($this->id);
        $original = "org_" . $this->id . "_" . $clean_name;
        $thumb = "thb_" . $this->id . "_" . $clean_name;
        $processed = $this->id . "_" . $clean_name;
        
        $success = false;
        if (!$a_clone) {
            $success = ilUtil::moveUploadedFile($tmp_name, $original, $path . $original);
        } else {
            $success = copy($tmp_name, $path . $original);
        }
        
        if ($success) {
            chmod($path . $original, 0770);

            // take quality 100 to avoid jpeg artefacts when uploading jpeg files
            // taking only frame [0] to avoid problems with animated gifs
            $original_file = ilUtil::escapeShellArg($path . $original);
            $thumb_file = ilUtil::escapeShellArg($path . $thumb);
            $processed_file = ilUtil::escapeShellArg($path . $processed);
            ilUtil::execConvert($original_file . "[0] -geometry \"100x100>\" -quality 100 PNG:" . $thumb_file);
            ilUtil::execConvert($original_file . "[0] -geometry \"" . self::getImageSize() . ">\" -quality 100 PNG:" . $processed_file);
            
            $this->setImage($processed);
            return true;
        }
        return false;
    }
    
    public static function getImageSize()
    {
        // :TODO:
        return "300x300";
    }
    
    
    //
    // Answer
    //
    
    public function getAnswers()
    {
        $ilDB = $this->db;
        
        $res = array();
        
        $sql = "SELECT * FROM il_poll_answer" .
            " WHERE poll_id = " . $ilDB->quote($this->getId(), "integer") .
            " ORDER BY pos ASC";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row;
        }
        return $res;
    }
    
    public function getAnswer($a_id)
    {
        $ilDB = $this->db;
        
        $sql = "SELECT * FROM il_poll_answer" .
            " WHERE id = " . $ilDB->quote($a_id, "integer");
        $set = $ilDB->query($sql);
        return (array) $ilDB->fetchAssoc($set);
    }

    public function saveAnswer($a_text, $a_pos = null) : ?int
    {
        $ilDB = $this->db;
        
        if (!trim($a_text)) {
            return null;
        }
        
        $id = $ilDB->nextId("il_poll_answer");
        
        if (!$a_pos) {
            // append
            $sql = "SELECT max(pos) pos" .
                " FROM il_poll_answer" .
                " WHERE poll_id = " . $ilDB->quote($this->getId(), "integer");
            $set = $ilDB->query($sql);
            $a_pos = $ilDB->fetchAssoc($set);
            $a_pos = (int) ($a_pos["pos"] ?? 0) + 10;
        }
        
        $fields = array(
            "id" => array("integer", $id),
            "poll_id" => array("integer", $this->getId()),
            "answer" => array("text", trim($a_text)),
            "pos" => array("integer", $a_pos)
        );
        $ilDB->insert("il_poll_answer", $fields);
        
        return $id;
    }
    
    public function updateAnswer($a_id, $a_text)
    {
        $ilDB = $this->db;
                    
        $ilDB->update(
            "il_poll_answer",
            array("answer" => array("text", $a_text)),
            array("id" => array("integer", $a_id))
        );
    }
    
    public function rebuildAnswerPositions()
    {
        $answers = $this->getAnswers();
        
        $pos = array();
        foreach ($answers as $item) {
            $id = (int) ($item['id'] ?? 0);
            $pos[$id] = (int) ($item["pos"] ?? 10);
        }
        
        $this->updateAnswerPositions($pos);
    }
    
    public function updateAnswerPositions(array $a_pos)
    {
        $ilDB = $this->db;
        
        asort($a_pos);
        
        $pos = 0;
        foreach (array_keys($a_pos) as $id) {
            $pos += 10;
            
            $ilDB->update(
                "il_poll_answer",
                array("pos" => array("integer", $pos)),
                array("id" => array("integer", $id))
            );
        }
    }
    
    public function deleteAnswer($a_id)
    {
        $ilDB = $this->db;
        
        if ($a_id) {
            $ilDB->manipulate("DELETE FROM il_poll_vote" .
                " WHERE answer_id = " . $ilDB->quote($this->getId(), "integer"));
            
            $ilDB->manipulate("DELETE FROM il_poll_answer" .
                " WHERE id = " . $ilDB->quote($a_id, "integer"));
        }
    }
    
    protected function deleteAllAnswers()
    {
        $ilDB = $this->db;
        
        if ($this->getId()) {
            $this->deleteAllVotes();
            
            $ilDB->manipulate("DELETE FROM il_poll_answer" .
                " WHERE poll_id = " . $ilDB->quote($this->getId(), "integer"));
        }
    }
    
    public function deleteAllVotes()
    {
        $ilDB = $this->db;
        
        if ($this->getId()) {
            $ilDB->manipulate("DELETE FROM il_poll_vote" .
                " WHERE poll_id = " . $ilDB->quote($this->getId(), "integer"));
        }
    }
        
    public function saveAnswers(array $a_answers)
    {
        $existing = $this->getAnswers();
                        
        $ids = array();
        $pos = 0;
        $id = null;
        foreach ($a_answers as $answer) {
            if (trim($answer)) {
                // existing answer?
                $found = false;
                foreach ($existing as $idx => $item) {
                    if (trim($answer) == (string) ($item["answer"] ?? '')) {
                        $found = true;
                        unset($existing[$idx]);

                        $id = (int) ($item["id"] ?? 0);
                    }
                }

                // create new answer
                if (!$found) {
                    $id = $this->saveAnswer($answer);
                }

                // add existing answer id to order
                if (isset($id) && is_int($id)) {
                    $ids[$id] = ++$pos;
                }
            }
        }
        
        // remove obsolete answers
        if (sizeof($existing)) {
            foreach ($existing as $item) {
                if(isset($item["id"]) && is_int($item["id"])) {
                    $this->deleteAnswer($item["id"]);
                }
            }
        }
        
        // save current order
        if (sizeof($ids)) {
            $this->updateAnswerPositions($ids);
        }
        
        return sizeof($ids);
    }
    
    
    //
    // votes
    //
    
    public function saveVote($a_user_id, $a_answers)
    {
        if ($this->hasUserVoted($a_user_id)) {
            return;
        }
        
        if (!is_array($a_answers)) {
            $a_answers = array($a_answers);
        }
        
        foreach ($a_answers as $answer_id) {
            $fields = array("user_id" => array("integer", $a_user_id),
                "poll_id" => array("integer", $this->getId()),
                "answer_id" => array("integer", $answer_id));
            $this->db->insert("il_poll_vote", $fields);
        }
    }
    
    public function hasUserVoted($a_user_id)
    {
        $sql = "SELECT user_id" .
            " FROM il_poll_vote" .
            " WHERE poll_id = " . $this->db->quote($this->getId(), "integer") .
            " AND user_id = " . $this->db->quote($a_user_id, "integer");
        $this->db->setLimit(1);
        $set = $this->db->query($sql);
        return (bool) $this->db->numRows($set);
    }
    
    public function countVotes()
    {
        $sql = "SELECT COUNT(DISTINCT(user_id)) cnt" .
            " FROM il_poll_vote" .
            " WHERE poll_id = " . $this->db->quote($this->getId(), "integer");
        $set = $this->db->query($sql);
        $row = $this->db->fetchAssoc($set);
        return (int) $row["cnt"];
    }
    
    public function getVotePercentages()
    {
        $res = array();
        $cnt = 0;
        
        $sql = "SELECT answer_id, count(*) cnt" .
            " FROM il_poll_vote" .
            " WHERE poll_id = " . $this->db->quote($this->getId(), "integer") .
            " GROUP BY answer_id";
        $set = $this->db->query($sql);
        while ($row = $this->db->fetchAssoc($set)) {
            $cnt += $row["cnt"];
            $res[$row["answer_id"]] = array("abs" => $row["cnt"], "perc" => 0);
        }

        foreach ($res as $id => $item) {
            $abs = (int) ($item['abs'] ?? 0);
            $id = (int) ($id ?? 0);
            $res[$id]["perc"] = $abs / $cnt * 100;
        }
        
        return array("perc" => $res, "total" => $this->countVotes());
    }
    
    public function getVotesByUsers()
    {
        $ilDB = $this->db;
        
        $res = array();
        
        $sql = "SELECT answer_id, user_id, firstname, lastname, login" .
            " FROM il_poll_vote" .
            " JOIN usr_data ON (usr_data.usr_id = il_poll_vote.user_id)" .
            " WHERE poll_id = " . $ilDB->quote($this->getId(), "integer");
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $user_id = (int) ($row["user_id"] ?? 0);
            if (!isset($res[$user_id])) {
                $res[$user_id] = $row;
            }
            $res[$user_id]["answers"][] = (int) ($row["answer_id"] ?? 0);
        }
    
        return $res;
    }
}
