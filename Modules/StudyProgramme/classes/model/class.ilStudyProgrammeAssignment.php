<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(dirname(__FILE__) . "/../../../../Services/ActiveRecord/class.ActiveRecord.php");

/**
 * Class ilStudyProgrammeAssignment.
 *
 * Represents one assignment of the user to a program tree.
 *
 * One user can have multiple assignments to the same tree. This makes it possible
 * to represent programs that need to be accomplished periodically as well.
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 * @version: 0.1.0
 */

class ilStudyProgrammeAssignment extends ActiveRecord
{
    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return "prg_usr_assignments";
    }

    /**
     * Id of this assignment.
     *
     * @var int
     *
     * @con_is_primary  true
     * @con_sequence    true
     * @con_is_unique   true
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $id;
 
    /**
     * The id of the user that is assigned.
     *
     * @var int
     *
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     * @con_is_notnull  true
     */
    protected $usr_id;

    /**
     * Root node of the program tree, the user was assigned to. Could be a subtree of
     * a larger program. This is the object id of the program.
     *
     * @var int
     *
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     * @con_is_notnull  true
     */
    protected $root_prg_id;


    /**
     * Timestamp of the moment of the assignment to or last update of the program.
     *
     * @var int
     *
     * @con_has_field   true
     * @con_fieldtype   timestamp
     * @con_is_notnull  true
     */
    protected $last_change;

    /**
     * Id of user who did the assignment to or last update of the program.
     *
     * @var int
     *
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     * @con_is_notnull  true
     */
    protected $last_change_by;
    
    
    /**
     * Create new assignment object for study program and user.
     *
     * Throws when $a_usr_id does not point to a user.
     *
     * @throws ilException
     * @param  int $a_usr_id
     * @param  int $a_assigning_usr_id
     * @return ilStudyProgrammeAssignment
     */
    public static function createFor(ilStudyProgramme $a_prg, $a_usr_id, $a_assigning_usr_id)
    {
        if (ilObject::_lookupType($a_usr_id) != "usr") {
            throw new ilException("ilStudyProgrammeAssignment::createFor: '$a_usr_id' "
                                 . "is no id of a user.");
        }
        
        $ass = new ilStudyProgrammeAssignment();
        $ass->setRootId($a_prg->getObjId())
            ->setUserId($a_usr_id)
            ->setLastChangeBy($a_assigning_usr_id)
            ->updateLastChange()
            ->create();
        return $ass;
    }
    
    /**
     * Get the id of the assignment.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get the object id of the program the user was assigned to.
     *
     * @return int
     */
    public function getRootId()
    {
        return $this->root_prg_id;
    }
    
    protected function setRootId($a_id)
    {
        $this->root_prg_id = $a_id;
        return $this;
    }
    
    /**
     * Get the id of the user who is assigned.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->usr_id;
    }
    
    protected function setUserId($a_usr_id)
    {
        $this->usr_id = $a_usr_id;
        return $this;
    }
    
    /**
     * Get the id of the user who did the last change on this assignment.
     *
     * @return int
     */
    public function getLastChangeBy()
    {
        return $this->last_change_by;
    }
    
    /**
     * Set the id of the user who did the last change on this assignment.
     *
     * Throws when $a_usr_id is not the id of a user.
     *
     * @throws ilException
     * @return $this
     */
    public function setLastChangeBy($a_usr_id)
    {
        if (ilObject::_lookupType($a_usr_id) != "usr") {
            throw new ilException("ilStudyProgrammeAssignment::setLastChangeBy: '$a_usr_id' "
                                 . "is no id of a user.");
        }
        $this->last_change_by = $a_usr_id;
        return $this;
    }
    
    /**
     * Get the timestamp of the last change on this program or a sub program.
     *
     * @return ilDateTime
     */
    public function getLastChange()
    {
        return new ilDateTime($this->last_change, IL_CAL_DATETIME);
    }

    /**
     * Update the last change timestamp to the current time.
     *
     * @return $this
     */
    public function updateLastChange()
    {
        $this->setLastChange(new ilDateTime(ilUtil::now(), IL_CAL_DATETIME));
        return $this;
    }

    /**
     * Set the last change timestamp to the given time.
     *
     * Throws when given time is smaller then current timestamp
     * since that is logically impossible.
     *
     * @throws ilException
     * @return $this
     */
    public function setLastChange(ilDateTime $a_timestamp)
    {
        if (ilDateTime::_before($a_timestamp, $this->getLastChange())) {
            throw new ilException("ilStudyProgrammeAssignment::setLastChange: Given "
                                 . "timestamp is before current timestamp. That "
                                 . "is logically impossible.");
        }
        
        $this->last_change = $a_timestamp->get(IL_CAL_DATETIME);
        return $this;
    }
}
