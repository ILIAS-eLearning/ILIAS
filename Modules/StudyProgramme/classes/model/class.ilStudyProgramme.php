<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(dirname(__FILE__) . "/../../../../Services/ActiveRecord/class.ActiveRecord.php");

/**
 * Class ilStudyProgramme
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 * @version: 0.1.0
 */

class ilStudyProgramme extends ActiveRecord
{
    
    // There are two different modes the programs calculation of the learning
    // progress can run in. It is also possible, that the mode is not defined
    // yet.
    const MODE_UNDEFINED = 0;
    
    // User is successful if he collected enough points in the subnodes of
    // this node.
    const MODE_POINTS = 1;
    // User is successful if he has the "completed" learning progress in any
    // subobject.
    const MODE_LP_COMPLETED = 2;

    public static $MODES = array( ilStudyProgramme::MODE_UNDEFINED
                         , ilStudyProgramme::MODE_POINTS
                         , ilStudyProgramme::MODE_LP_COMPLETED
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

    public static $STATUS = array( ilStudyProgramme::STATUS_DRAFT
                          , ilStudyProgramme::STATUS_ACTIVE
                          , ilStudyProgramme::STATUS_OUTDATED
                          );

    
    // Defaults
    const DEFAULT_POINTS = 100;
    const DEFAULT_SUBTYPE = 0; // TODO: What should that be?
    
    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return "prg_settings";
    }

    /**
     * Id of this study program and the corresponding ILIAS-object as well.
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
     * Amount of points a user needs to achieve to be successful on this program node
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
    
    
    /**
     * Create new study program settings for an object.
     *
     * Throws when object is no program object.
     *
     * @throws ilException
     * @return ilStudyProgramme
     */
    public static function createForObject(ilObject $a_object)
    {
        if ($a_object->getType() != "prg") {
            throw new ilException("ilStudyProgramme::createSettingsForObject: "
                                 . "Object is no prg object.");
        }
        if (!$a_object->getId()) {
            throw new ilException("ilStudyProgramme::createSettingsForObject: "
                                 . "Object has no id.");
        }

        $prg = new ilStudyProgramme();
        $prg->subtype_id = self::DEFAULT_SUBTYPE;
        $prg->setObjId($a_object->getId())
            ->setStatus(self::STATUS_DRAFT)
            ->setLPMode(self::MODE_UNDEFINED)
            ->setPoints(self::DEFAULT_POINTS)
            ->create();
        return $prg;
    }

    
    protected function setObjId($a_id)
    {
        $this->obj_id = $a_id;
        return $this;
    }

    /**
     * Get the id of the study program.
     *
     * @return integer
     */
    public function getObjId()
    {
        return (int) $this->obj_id;
    }

    /**
     * Return the meta-data subtype id
     *
     * @return int
     */
    public function getSubtypeId()
    {
        return $this->subtype_id;
    }


    /**
     * Sets the meta-data type id
     *
     * @param int $subtype_id
     */
    public function setSubtypeId($subtype_id)
    {
        $this->subtype_id = $subtype_id;
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
     * @return $this
     */
    public function setLastChange(ilDateTime $a_timestamp)
    {
        if (ilDateTime::_before($a_timestamp, $this->getLastChange())) {
            throw new ilException("ilStudyProgramme::setLastChange: Given "
                                 . "timestamp is before current timestamp. That "
                                 . "is logically impossible.");
        }
        
        $this->last_change = $a_timestamp->get(IL_CAL_DATETIME);
        return $this;
    }



    /**
     * Set the amount of points.
     *
     * @param integer   $a_points   - larger than zero
     * @throws ilException
     * @return $this
     */
    public function setPoints($a_points)
    {
        $a_points = (int) $a_points;
        if ($a_points < 0) {
            throw new ilException("ilStudyProgramme::setPoints: Points cannot "
                                 . "be smaller than zero.");
        }

        $this->points = $a_points;
        $this->updateLastChange();
        return $this;
    }

    /**
     * Get the amount of points
     *
     * @return integer  - larger than zero
     */
    public function getPoints()
    {
        return (int) $this->points;
    }

    /**
     * Set the lp mode.
     *
     * Throws when program is not in draft status.
     *
     * @param integer $a_mode       - one of self::$MODES
     * @return $this
     */
    public function setLPMode($a_mode)
    {
        $a_mode = (int) $a_mode;
        if (!in_array($a_mode, self::$MODES)) {
            throw new ilException("ilStudyProgramme::setLPMode: No lp mode: "
                                 . "'$a_mode'");
        }
        $this->lp_mode = $a_mode;
        $this->updateLastChange();
        return $this;
    }

    /**
     * Get the lp mode.
     *
     * @return integer  - one of self::$MODES
     */
    public function getLPMode()
    {
        return (int) $this->lp_mode;
    }

    /**
     * Set the status of the node.
     *
     * TODO: Should this throw, when one wants to go back in lifecycle? Maybe getting
     * back to draft needs to be forbidden only?
     *
     * @param integer $a_status     - one of self::$STATUS
     * @return $this
     */
    public function setStatus($a_status)
    {
        $a_status = (int) $a_status;
        if (!in_array($a_status, self::$STATUS)) {
            throw new ilException("ilStudyProgramme::setStatus: No lp mode: "
                                 . "'$a_status'");
        }
        $this->status = $a_status;
        $this->updateLastChange();
        return $this;
    }

    /**
     * Get the status.
     *
     * @return integer  - one of self::$STATUS
     */
    public function getStatus()
    {
        return (int) $this->status;
    }
}
