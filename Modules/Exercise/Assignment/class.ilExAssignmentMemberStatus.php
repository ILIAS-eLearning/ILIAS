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
 * Exercise assignment member status
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExAssignmentMemberStatus
{
    protected ilDBInterface $db;

    protected int $ass_id = 0;
    protected int $user_id = 0;
    protected string $notice = "";
    protected bool $returned = false;
    protected bool $solved = false;
    protected bool $sent = false;
    protected string $sent_time = "";
    protected bool $feedback = false;
    protected string $feedback_time = "";
    protected string $status = "notgraded";
    protected string $status_time = "";
    protected string $mark = "";
    protected string $comment = "";
    protected bool $db_exists = false;
    protected bool $returned_update = false;
    protected bool $status_update = false;
    
    public function __construct(int $a_ass_id, int $a_user_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->ass_id = $a_ass_id;
        $this->user_id = $a_user_id;
        
        $this->read();
    }
    
    public function setNotice(string $a_value) : void
    {
        $this->notice = $a_value;
    }
    
    public function getNotice() : string
    {
        return $this->notice;
    }
    
    public function setReturned(bool $a_value) : void
    {
        if ($a_value &&
            !$this->returned) {
            $this->returned_update = true;
        }
        $this->returned = $a_value;
    }

    public function getReturned() : bool
    {
        return $this->returned;
    }

    /**
     * @deprecated
     */
    public function setSolved(bool $a_value) : void
    {
        $this->solved = $a_value;
    }

    /**
     * @deprecated
     */
    public function getSolved() : bool
    {
        return $this->solved;
    }

    // Y-m-d H:i:s
    protected function setStatusTime(string $a_value) : void
    {
        $this->status_time = $a_value;
    }
    
    public function getStatusTime() : string
    {
        return $this->status_time;
    }
    
    public function setSent(bool $a_value) : void
    {
        if ($a_value && $a_value != $this->sent) {
            $this->setSentTime(ilUtil::now());
        }
        $this->sent = $a_value;
    }
    
    public function getSent() : bool
    {
        return $this->sent;
    }

    // Y-m-d H:i:s
    protected function setSentTime(string $a_value) : void
    {
        $this->sent_time = $a_value;
    }
    
    public function getSentTime() : string
    {
        return $this->sent_time;
    }

    public function setFeedback(bool $a_value) : void
    {
        if ($a_value != $this->sent) {
            $this->setFeedbackTime(ilUtil::now());
        }
        $this->feedback = $a_value;
    }
    
    public function getFeedback() : bool
    {
        return $this->feedback;
    }

    // Y-m-d H:i:s
    protected function setFeedbackTime(string $a_value) : void
    {
        $this->feedback_time = $a_value;
    }
    
    public function getFeedbackTime() : string
    {
        return $this->feedback_time;
    }
    
    public function setStatus(string $a_value) : void
    {
        if ($a_value != $this->status) {
            $this->setStatusTime(ilUtil::now());
            $this->status = $a_value;
            $this->status_update = true;
        }
    }
    
    public function getStatus() : string
    {
        return $this->status;
    }
    
    public function setMark(string $a_value) : void
    {
        if ($a_value != $this->mark) {
            $this->setStatusTime(ilUtil::now());
        }
        $this->mark = $a_value;
    }
    
    public function getMark() : string
    {
        return $this->mark;
    }
    
    public function setComment(string $a_value) : void
    {
        $this->comment = $a_value;
    }
    
    public function getComment() : string
    {
        return $this->comment;
    }
    
    protected function read() : void
    {
        $ilDB = $this->db;
        
        $set = $ilDB->query("SELECT * FROM exc_mem_ass_status" .
            " WHERE ass_id = " . $ilDB->quote($this->ass_id, "integer") .
            " AND usr_id = " . $ilDB->quote($this->user_id, "integer"));
        if ($ilDB->numRows($set)) {
            $row = $ilDB->fetchAssoc($set);
            
            // not using setters to circumvent any datetime-logic/-magic
            $this->notice = (string) $row["notice"];
            $this->returned = (bool) $row["returned"];
            $this->solved = (bool) $row["solved"];
            $this->status_time = (string) $row["status_time"];
            $this->sent = (bool) $row["sent"];
            $this->sent_time = (string) $row["sent_time"];
            $this->feedback_time = (string) $row["feedback_time"];
            $this->feedback = (bool) $row["feedback"];
            $this->status = (string) $row["status"];
            $this->mark = (string) $row["mark"];
            $this->comment = (string) $row["u_comment"];
            $this->db_exists = true;
        }
    }
    
    protected function getFields() : array
    {
        return array(
            "notice" => array("text", $this->getNotice())
            ,"returned" => array("integer", $this->getReturned())
            ,"solved" => array("integer", $this->getSolved())
            ,"status_time" => array("timestamp", $this->getStatusTime())
            ,"sent" => array("integer", $this->getSent())
            ,"sent_time" => array("timestamp", $this->getSentTime())
            ,"feedback_time" => array("timestamp", $this->getFeedbackTime())
            ,"feedback" => array("integer", (int) $this->getFeedback())
            ,"status" => array("text", $this->getStatus())
            ,"mark" => array("text", $this->getMark())
            ,"u_comment" => array("text", $this->getComment())
        );
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function update() : void
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

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    protected function postUpdateReturned() : void
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
            ilExAssignment::sendFeedbackNotifications($this->ass_id, $this->user_id);
        }
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    protected function postUpdateStatus() : void
    {
        $ass = new ilExAssignment($this->ass_id);
        $exc = new ilObjExercise($ass->getExerciseId(), false);
        $exc->updateUserStatus($this->user_id);
    }
    
    // Check whether exercise has been sent to any student per mail.
    public static function lookupAnyExerciseSent(int $a_ass_id) : bool
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
