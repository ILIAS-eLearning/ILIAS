<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(dirname(__FILE__)."/../../../../Services/ActiveRecord/class.ActiveRecord.php");

/**
 * Class ilTrainingProgramme
 * 
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 * @version: 0.1.0
 */

class ilTrainingProgramme extends ActiveRecord {
    
    // There are two different modes the programs calculation of the learning
    // progress can run in.
    
    // User is successfull if he collected enough points in the subnodes of
    // this node. 
    const MODE_POINTS = 1;
    // User is successfull if he has the "completed" learning progress in any
    // subobject.
    const MODE_LP_COMPLETED = 2;

    static $MODES = array( ilTrainingProgramme::MODE_POINTS
                         , ilTrainingProgramme::MODE_LP_COMPLETED
                         );


    // A program tree has a lifecycle during which it has three status.

    // The program is a draft, that is users won't be assigned to the program
    // already.
    const STATUS_DRAFT = 10;
    // The program is active, that is used can be assigned to it. 
    const STATUS_ACTIVE = 20;
    // The program is outdated, that is user won't be assigned to it but can
    // still complete the program.
    const STATUS_OUTDATED = 30;

    static $STATUS = array( ilTrainingProgramme::STATUS_DRAFT
                          , ilTrainingProgramme::STATUS_ACTIVE
                          , ilTrainingProgramme::STATUS_OUTDATED
                          );

    
    /**
     * @return string
     */
    static function returnDbTableName() {
        return "prg_settings";
    }

    /**
     * Id of this training program and the corresponding ILIAS-object as well.
     *
     * @var int
     * 
     * @con_is_primary  true
     * @con_is_unique   true
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $obj_id;
    
    /**
     * Timestamp of the moment the last change was made on this object or any
     * object in the subtree of the program.
     * 
     * @var string 
     * 
     * @con_has_field   true
     * @con_fieldtype   timestamp 
     * @con_is_notnull  true
     */
    protected $last_change;

    /**
     * Id of the subtype of the program object.
     *
     * Subtype concepts is also used in Org-Units. 
     * 
     * @var int 
     * 
     * @con_has_field   true
     * @con_fieldtype   integer 
     * @con_length      4
     * @con_is_notnull  true 
     */
    protected $subtype_id;

    /**
     * Amount of points a user needs to achieve to be successfull on this program node
     * and amount of points for the completion of the parent node in the program tree
     * as well.
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
     * Mode the calculation of the learning progress on this node is run in.    
     *
     * @var int 
     * 
     * @con_has_field   true
     * @con_fieldtype   integer 
     * @con_length      1
     * @con_is_notnull  true 
     */
    protected $lp_mode;

    /**
     * Lifecycle status the program is in.
     *
     * @var int 
     * 
     * @con_has_field   true
     * @con_fieldtype   integer 
     * @con_length      1
     * @con_is_notnull  true 
     */
    protected $status;
     
}

?>
