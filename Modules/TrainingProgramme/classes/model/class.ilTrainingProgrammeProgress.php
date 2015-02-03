<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(dirname(__FILE__)."/../../../../Services/ActiveRecord/class.ActiveRecord.php");

/**
 * Class ilTrainingProgrammeProgress.
 *
 * Represents the progress of a user for one program assignment on one node of the
 * program. 
 *
 * The user has one progress per assignment and program node in the subtree of the
 * assigned program.
 * 
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 * @version: 0.1.0
 */

class ilTrainingProgrammeProgress extends ActiveRecord {
    
    // The progress of a user on a program node can have different status that 
    // determine how the node is taken into account for calculation of the learning
    // progress.
    
    // User needs to be successfull in the node, but currently isn't.
    const STATUS_PROGRESS = 1;
    // User has completed the node successfully according to the program nodes
    // mode.
    const STATUS_COMPLETED = 2;
    // User was marked as successfull in the node without actually having
    // successfully completed the program node according to his mode.
    const STATUS_ACCREDITED = 3;
    // The user does not need to be successfull in this node.
    const STATUS_NOT_RELEVANT = 4;

    static $STATUS = array( STATUS_PROGRESS
                          , STATUS_COMPLETED
                          , STATUS_ACCREDITED
                          , STATUS_NOT_RELEVANT
                          );  

    /**
     * @return string
     */
    static function returnDbTableName() {
        return "prg_usr_progress";
    }

    /**
     * The id of the assignment this progress belongs to.
     *
     * @var int 
     * 
     * @con_is_primary  true
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $assignment_id;

    /**
     * The id of the program node this progress belongs to.
     *
     * @var int 
     * 
     * @con_is_primary  true
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $prg_id;

    /**
     * The id of the user this progress belongs to.
     * 
     * @var int 
     * 
     * @con_is_primary  true
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */

    protected $usr_id;
    /**
     * Amount of points the user needs to achieve in the subnodes to be successfull
     * on this node. Also the amount of points a user gets by being successfull on this
     * node.
     *
     * @var int 
     * 
     * @con_has_field   true
     * @con_fieldtype   integer 
     * @con_length      4
     * @con_is_notnull  true 
     */
    protected $points;

    /**
     * Amount of points the user currently has in the subnodes of this node.
     *
     * @var int 
     * 
     * @con_has_field   true
     * @con_fieldtype   integer 
     * @con_length      4
     * @con_is_notnull  true 
     */
    protected $points_cur;
 
    /**
     * The status this progress is in.
     *
     * @var int 
     * 
     * @con_has_field   true
     * @con_fieldtype   integer 
     * @con_length      1
     * @con_is_notnull  true 
     */
    protected $status;

    /**
     * The id of the object, that lead to the successfull completion of this node.
     * This is either a user when status is accreditted, a course object if the mode
     * of the program node is lp_completed and the node is completed. Its null 
     * otherwise.
     *
     * @var int
     *
     * @con_has_field   true
     * @con_fieldtype   integer 
     * @con_length      4
     * @con_is_notnull  false 
     */
    protected $completion_by;
    

    /**
     * The timestamp of the moment this progress was created or updated the
     * last time.
     *
     * @var int
     * 
     * @con_has_field   true
     * @con_fieldtype   timestamp 
     * @con_is_notnull  true
     */
    protected $last_change;

    /**
     * Id of user who did the assignment that lead to creation of this progress
     * or the update of the progress the last time.
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
