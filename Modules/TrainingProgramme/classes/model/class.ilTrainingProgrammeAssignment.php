<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> */

require_once(dirname(__FILE__)."/../../../../Services/ActiveRecord/class.ActiveRecord.php");

/**
 * Class ilTrainingProgrammeAssignment.
 *
 * Represents one assignment of the user to a program tree.
 *
 * One user can have multiple assignments to the same tree. This makes it possible
 * to represent programs that need to be accomplished periodically as well.
 * 
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 * @version: 0.1.0
 */

class ilTrainingProgrammeAssignment extends ActiveRecord {
    /**
     * @return string
     */
    static function returnDbTableName() {
        return "prg_usr_assignments";
    }

    /**
     * Id of this assignment.
     *
     * @var int
     * 
     * @con_is_primary  true
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
     * a larger program. 
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
 
}

?>
