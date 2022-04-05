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
 * Class ilExerciseMembers
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseMembers
{
    protected ilDBInterface $db;
    public int $ref_id;
    public int $obj_id;
    public array $members;
    public string $status;
    protected ilRecommendedContentManager $recommended_content_manager;
    protected ilObjExercise $exc;

    public function __construct(ilObjExercise $a_exc)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->exc = $a_exc;
        $this->obj_id = $a_exc->getId();
        $this->ref_id = $a_exc->getRefId();
        $this->read();
        $this->recommended_content_manager = new ilRecommendedContentManager();
    }

    // Get exercise ref id
    public function getRefId() : int
    {
        return $this->ref_id;
    }

    // Get exercise obj id
    public function getObjId() : int
    {
        return $this->obj_id;
    }
    
    public function setObjId(int $a_obj_id) : void
    {
        $this->obj_id = $a_obj_id;
    }

    public function getMembers() : array
    {
        return $this->members ?: array();
    }
    
    public function setMembers(array $a_members) : void
    {
        $this->members = $a_members;
    }

    /**
    * Assign a user to the exercise
    */
    public function assignMember(int $a_usr_id) : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate("DELETE FROM exc_members " .
            "WHERE obj_id = " . $ilDB->quote($this->getObjId(), "integer") . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, "integer") . " ");

        // @todo: some of this fields may not be needed anymore
        $ilDB->manipulateF(
            "INSERT INTO exc_members (obj_id, usr_id, status, sent, feedback) " .
            " VALUES (%s,%s,%s,%s,%s)",
            array("integer", "integer", "text", "integer", "integer"),
            array($this->getObjId(), $a_usr_id, 'notgraded', 0, 0)
        );

        ilExAssignment::createNewUserRecords($a_usr_id, $this->getObjId());
        
        $this->read();
        
        ilLPStatusWrapper::_updateStatus($this->getObjId(), $a_usr_id);
    }
    
    // Is user assigned to exercise?
    public function isAssigned(int $a_id) : bool
    {
        return in_array($a_id, $this->getMembers());
    }

    /**
     * Assign members to exercise
     * @param int[] $a_members
     * @return bool true, if all passed users could be assigned, false otherwise
     */
    public function assignMembers(array $a_members) : bool
    {
        $assigned = 0;
        if (is_array($a_members)) {
            foreach ($a_members as $member) {
                if (!$this->isAssigned($member)) {
                    $this->assignMember($member);
                } else {
                    ++$assigned;
                }
            }
        }
        if ($assigned == count($a_members)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Detaches a user from an exercise
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function deassignMember(int $a_usr_id) : void
    {
        $ilDB = $this->db;

        $this->recommended_content_manager->removeObjectRecommendation($a_usr_id, $this->getRefId());

        $query = "DELETE FROM exc_members " .
            "WHERE obj_id = " . $ilDB->quote($this->getObjId(), "integer") . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, "integer") . " ";

        $ilDB->manipulate($query);

        $this->read();

        ilLPStatusWrapper::_updateStatus($this->getObjId(), $a_usr_id);

        // delete all delivered files of the member
        ilExSubmission::deleteUser($this->exc->getId(), $a_usr_id);

        // @todo: delete all assignment associations (and their files)
    }

    public function read() : void
    {
        $ilDB = $this->db;

        $tmp_arr_members = array();

        $query = "SELECT * FROM exc_members " .
            "WHERE obj_id = " . $ilDB->quote($this->getObjId(), "integer");

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            if (ilObject::_lookupType($row->usr_id) == "usr") {
                $tmp_arr_members[] = $row->usr_id;
            }
        }
        $this->setMembers($tmp_arr_members);
    }

    // @todo: clone also assignments
    public function ilClone(int $a_new_id) : void
    {
        $ilDB = $this->db;

        $data = array();

        $query = "SELECT * FROM exc_members " .
            "WHERE obj_id = " . $ilDB->quote($this->getObjId(), "integer");

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            $data[] = array("usr_id" => $row->usr_id,
                            "notice" => $row->notice,
                            "returned" => $row->returned,
                            "status" => $row->status,
                            "sent" => $row->sent,
                            "feedback" => $row->feedback
                            );
        }
        foreach ($data as $row) {
            $ilDB->manipulateF(
                "INSERT INTO exc_members " .
                " (obj_id, usr_id, notice, returned, status, feedback, sent) VALUES " .
                " (%s,%s,%s,%s,%s,%s,%s)",
                array("integer", "integer", "text", "integer", "text", "integer", "integer"),
                array($a_new_id, $row["usr_id"], $row["notice"], (int) $row["returned"],
                    $row["status"], (int) $row["feedback"], (int) $row["sent"])
            );
            
            ilLPStatusWrapper::_updateStatus($a_new_id, $row["usr_id"]);
        }
    }

    // @todo: delete also assignments
    public function delete() : void
    {
        $ilDB = $this->db;

        $query = "DELETE FROM exc_members WHERE obj_id = " .
            $ilDB->quote($this->getObjId(), "integer");
        $ilDB->manipulate($query);
        
        ilLPStatusWrapper::_refreshStatus($this->getObjId());
    }

    public static function _getMembers(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        // #14963 - see ilExAssignment::getMemberListData()
        $query = "SELECT DISTINCT(excm.usr_id) ud" .
            " FROM exc_members excm" .
            " JOIN object_data od ON (od.obj_id = excm.usr_id)" .
            " WHERE excm.obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND od.type = " . $ilDB->quote("usr", "text");

        $res = $ilDB->query($query);
        $usr_ids = [];
        while ($row = $ilDB->fetchObject($res)) {
            $usr_ids[] = $row->ud;
        }

        return $usr_ids;
    }

    /**
     * Lookup current status (notgraded|passed|failed)
     *
     * This information is determined by the assignment status and saved
     * redundantly in this table for performance reasons.
     *
     * @param	int		$a_obj_id	exercise id
     * @param	int		$a_user_id	member id
     * @return	?string null if user is no member,  or notgraded|passed|failed
     */
    public static function _lookupStatus(int $a_obj_id, int $a_user_id) : ?string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT status FROM exc_members " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND usr_id = " . $ilDB->quote($a_user_id, "integer");

        $res = $ilDB->query($query);
        if ($row = $ilDB->fetchAssoc($res)) {
            return $row["status"];
        }

        return null;
    }

    /**
     * Write user status
     * This information is determined by the assignment status and saved
     * redundantly in this table for performance reasons.
     * See ilObjExercise->updateUserStatus().
     */
    public static function _writeStatus(
        int $a_obj_id,
        int $a_user_id,
        string $a_status
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->manipulate(
            "UPDATE exc_members SET " .
            " status = " . $ilDB->quote($a_status, "text") .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND usr_id = " . $ilDB->quote($a_user_id, "integer")
        );
        
        ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id);
    }
    
    /**
     * Write returned status
     *
     * The returned status is initially 0. If the first file is returned
     * by a user for any assignment of the exercise, the returned status
     * is set to 1 and it will stay that way, even if this file is deleted again.
     * -> learning progress uses this to determine "in progress" status
     */
    public static function _writeReturned(
        int $a_obj_id,
        int $a_user_id,
        int $a_status
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        
        $ilDB->manipulate(
            "UPDATE exc_members SET " .
            " returned = " . $ilDB->quote($a_status, "integer") .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND usr_id = " . $ilDB->quote($a_user_id, "integer")
        );
        
        ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id);
    }
    
    
    //
    // LP
    //
    
    /**
     * Get returned status for all members (if they have anything returned for
     * any assignment)
     */
    public static function _getReturned(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT DISTINCT(usr_id) as ud FROM exc_members " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") . " " .
            "AND returned = 1";

        $res = $ilDB->query($query);
        $usr_ids = [];
        while ($row = $ilDB->fetchObject($res)) {
            $usr_ids[] = $row->ud;
        }

        return $usr_ids;
    }

    /**
     * Has user returned anything in any assignment?
     */
    public static function _hasReturned(int $a_obj_id, int $a_user_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
    
        $set = $ilDB->query(
            "SELECT DISTINCT(usr_id) FROM exc_members WHERE " .
            " obj_id = " . $ilDB->quote($a_obj_id, "integer") . " AND " .
            " returned = " . $ilDB->quote(1, "integer") . " AND " .
            " usr_id = " . $ilDB->quote($a_user_id, "integer")
        );
        return (bool) $ilDB->fetchAssoc($set);
    }

    /**
     * Get all users that passed the exercise
     */
    public static function _getPassedUsers(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT DISTINCT(usr_id) FROM exc_members " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") . " " .
            "AND status = " . $ilDB->quote("passed", "text");
        $res = $ilDB->query($query);
        $usr_ids = [];
        while ($row = $ilDB->fetchObject($res)) {
            $usr_ids[] = $row->usr_id;
        }
        return $usr_ids;
    }

    /**
     * Get all users that failed the exercise
     */
    public static function _getFailedUsers(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT DISTINCT(usr_id) FROM exc_members " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") . " " .
            "AND status = " . $ilDB->quote("failed", "text");
        $res = $ilDB->query($query);
        $usr_ids = [];
        while ($row = $ilDB->fetchObject($res)) {
            $usr_ids[] = $row->usr_id;
        }
        return $usr_ids;
    }
}
