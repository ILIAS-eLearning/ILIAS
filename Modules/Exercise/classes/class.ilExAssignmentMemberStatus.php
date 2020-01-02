<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Exercise assignment member status
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExAssignmentMemberStatus
{
    /**
     * @var ilDB
     */
    protected $db;

    protected $ass_id; // [int]
    protected $user_id;  // [int]
    protected $notice; // [string]
    protected $returned;  // [int]
    protected $solved;  // [int] - obsolete?!
    protected $sent; // [int]
    protected $sent_time; // [datetime]
    protected $feedback; // [int]
    protected $feedback_time; // [datetime]
    protected $status = "notgraded";  // [string]
    protected $status_time; // [datetime]
    protected $mark; // [string]
    protected $comment; // [string]
    protected $db_exists; // [bool]
    protected $returned_update; // [bool]
    protected $status_update; // [bool]
    
    public function __construct($a_ass_id, $a_user_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->ass_id = $a_ass_id;
        $this->user_id = $a_user_id;
        
        $this->read();
    }
    
    public function setNotice($a_value)
    {
        $this->notice = $a_value;
    }
    
    public function getNotice()
    {
        return $this->notice;
    }
    
    public function setReturned($a_value)
    {
        if ($a_value &&
            !$this->returned) {
            $this->returned_update = true;
        }
        $this->returned = $a_value;
    }
    
    public function getReturned()
    {
        return $this->returned;
    }
    
    public function setSolved($a_value)
    {
        $this->solved = $a_value;
    }
    
    public function getSolved()
    {
        return $this->solved;
    }
    
    protected function setStatusTime($a_value)
    {
        $this->status_time = $a_value;
    }
    
    public function getStatusTime()
    {
        return $this->status_time;
    }
    
    public function setSent($a_value)
    {
        if ($a_value && $a_value != $this->sent) {
            $this->setSentTime(ilUtil::now());
        }
        $this->sent = $a_value;
    }
    
    public function getSent()
    {
        return $this->sent;
    }
    
    protected function setSentTime($a_value)
    {
        $this->sent_time = $a_value;
    }
    
    public function getSentTime()
    {
        return $this->sent_time;
    }
    
    public function setFeedback($a_value)
    {
        if ($a_value != $this->sent) {
            $this->setFeedbackTime(ilUtil::now());
        }
        $this->feedback = $a_value;
    }
    
    public function getFeedback()
    {
        return $this->feedback;
    }
    
    protected function setFeedbackTime($a_value)
    {
        $this->feedback_time = $a_value;
    }
    
    public function getFeedbackTime()
    {
        return $this->feedback_time;
    }
    
    public function setStatus($a_value)
    {
        if ($a_value != $this->status) {
            $this->setStatusTime(ilUtil::now());
            $this->status = $a_value;
            $this->status_update = true;
        }
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function setMark($a_value)
    {
        if ($a_value != $this->mark) {
            $this->setStatusTime(ilUtil::now());
        }
        $this->mark = $a_value;
    }
    
    public function getMark()
    {
        return $this->mark;
    }
    
    public function setComment($a_value)
    {
        $this->comment = $a_value;
    }
    
    public function getComment()
    {
        return $this->comment;
    }
    
    protected function read()
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query("SELECT * FROM exc_mem_ass_status" .
            " WHERE ass_id = " . $ilDB->quote($this->ass_id, "integer") .
            " AND usr_id = " . $ilDB->quote($this->user_id, "integer"));
        if ($ilDB->numRows($set)) {
            $row  = $ilDB->fetchAssoc($set);
            
            // not using setters to circumvent any datetime-logic/-magic
            $this->notice = $row["notice"];
            $this->returned = $row["returned"];
            $this->solved = $row["solved"];
            $this->status_time = $row["status_time"];
            $this->sent = $row["sent"];
            $this->sent_time = $row["sent_time"];
            $this->feedback_time = $row["feedback_time"];
            $this->feedback = $row["feedback"];
            $this->status = $row["status"];
            $this->mark = $row["mark"];
            $this->comment = $row["u_comment"];
            $this->db_exists = true;
        }
    }
    
    protected function getFields()
    {
        return array(
            "notice" => array("text", $this->getNotice())
            ,"returned" => array("integer", $this->getReturned())
            ,"solved" => array("integer", $this->getSolved())
            ,"status_time" => array("timestamp", $this->getStatusTime())
            ,"sent" => array("integer", $this->getSent())
            ,"sent_time" => array("timestamp", $this->getSentTime())
            ,"feedback_time" => array("timestamp", $this->getFeedbackTime())
            ,"feedback" => array("integer", $this->getFeedback())
            ,"status" => array("text", $this->getStatus())
            ,"mark" => array("text", $this->getMark())
            ,"u_comment" => array("text", $this->getComment())
        );
    }
    
    public function update()
    {
        $ilDB = $this->db;
        
        $keys = array(
            "ass_id" => array("integer", $this->ass_id)
            ,"usr_id" => array("integer", $this->user_id)
        );
        $fields = $this->getFields();
        if (!$this->db_exists) {
            $fields = array_merge($keys, $fields);
            $ilDB->insert("exc_mem_ass_status", $fields);
        } else {
            $ilDB->update("exc_mem_ass_status", $fields, $keys);
        }
        
        if ($this->returned_update) {
            $this->postUpdateReturned();
        }
        if ($this->status_update) {
            $this->postUpdateStatus();
        }
    }
    
    protected function postUpdateReturned()
    {
        $ilDB = $this->db;
        
        // first upload => notification on submission?
        $set = $ilDB->query("SELECT fb_cron, fb_date, fb_file" .
            " FROM exc_assignment" .
            " WHERE id = " . $ilDB->quote($this->ass_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        if ($row["fb_cron"] &&
            $row["fb_file"] &&
            $row["fb_date"] == ilExAssignment::FEEDBACK_DATE_SUBMISSION) { // #16200
            include_once "Modules/Exercise/classes/class.ilExAssignment.php";
            ilExAssignment::sendFeedbackNotifications($this->ass_id, $this->user_id);
        }
    }
        
    protected function postUpdateStatus()
    {
        include_once "Modules/Exercise/classes/class.ilExAssignment.php";
        $ass = new ilExAssignment($this->ass_id);
        $exc = new ilObjExercise($ass->getExerciseId(), false);
        $exc->updateUserStatus($this->user_id);
    }
    
    public function getStatusIcon()
    {
        switch ($this->getStatus()) {
            case "passed":
                return "scorm/passed.svg";
            
            case "failed":
                return "scorm/failed.svg";
                
            default:
                return "scorm/not_attempted.svg";
        }
    }
    
    /**
     * Check whether exercise has been sent to any student per mail.
     */
    public static function lookupAnyExerciseSent($a_ass_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT count(*) AS cnt" .
            " FROM exc_mem_ass_status" .
            " WHERE NOT sent_time IS NULL" .
            " AND ass_id = " . $ilDB->quote($a_ass_id, "integer");
        $set = $ilDB->query($q);
        $rec = $ilDB->fetchAssoc($set);
        return ($rec["cnt"] > 0);
    }
}
