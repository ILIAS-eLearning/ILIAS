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

	
	// Defaults
	const DEFAULT_POINTS = 100;
	const DEFAULT_SUBTYPE = 0; // TODO: What should that be?
	
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
	
	
	/**
	 * Create new training program settings for an object.
	 *
	 * Throws when object is no program object.
	 *
	 * @throws ilException
	 */
	static public function createForObject(ilObject $a_object) {
		if ($a_object->getType() != "prg") {
			throw new ilException("ilTrainingProgramme::createSettingsForObject: "
								 ."Object is no prg object.");
		}
		if(!$a_object->getId()) {
			throw new ilException("ilTrainingProgramme::createSettingsForObject: "
								 ."Object has no id."); 
		}

		$prg = new ilTrainingProgramme();
		$prg->setObjId($a_object->getId());
		$prg->setStatus(self::STATUS_DRAFT);
		$prg->setLPMode(self::MODE_POINTS);
		$prg->setPoints(self::DEFAULT_POINTS);
		$prg->subtype_id = self::DEFAULT_SUBTYPE;
		$prg->create();
		return $prg;
	} 

	
	protected function setObjId($a_id) {
		$this->obj_id = $a_id;
	}

	/**
	 * Get the id of the training program.
	 *
	 * @return integer
	 */
	public function getObjId() {
		return $this->obj_id;
	} 

	/**
	 * Get the timestamp of the last change on this program or a sub program.
	 *
	 * @return ilDateTime
	 */
	public function getLastChange() {
		return new ilDateTime($this->last_change, IL_CAL_DATETIME);
	}

	/**
	 * Update the last change timestamp to the current time.
	 */
	public function updateLastChange() {
		$this->setLastChange(new ilDateTime(ilUtil::now(), IL_CAL_DATETIME)); 
	} 

	/**
	 * Set the last change timestamp to the given time.
	 * 
	 * Throws when given time is smaller then current timestamp
	 * since that is logically impossible.
	 */
	public function setLastChange(ilDateTime $a_timestamp) {
		if (ilDateTime::_before($a_timestamp, $this->getLastChange())) {
			throw new ilException("ilTrainingProgramme::setLastChange: Given "
								 ."timestamp is before current timestamp. That "
								 ."is logically impossible.");
		}
		
		$this->last_change = $a_timestamp->get(IL_CAL_DATETIME);
	}

	// TODO: setters and getters for subtype

	/**
	 * Set the amount of points.
	 * 
	 * @param integer   $a_points   - larger than zero 
	 * @throws ilException 
	 */
	public function setPoints($a_points) {
		$a_points = (int)$a_points;
		if ($a_points <= 0) {
			throw new ilException("ilTrainingProgramme::setPoints: Points need to "
								 ."be larger than zero.");
		}

		$this->points = $a_points;
		$this->updateLastChange();
	} 

	/**
	 * Get the amount of points
	 *
	 * @return integer  - larger than zero
	 */
	public function getPoints() {
		return $this->points;
	}

	/**
	 * Set the lp mode.
	 *
	 * Throws when program is not in draft status.
	 *
	 * @param integer $a_mode       - one of self::$MODES
	 */
	public function setLPMode($a_mode) {
		$a_mode = (int)$a_mode;
		if ($this->getStatus() !== self::STATUS_DRAFT) {
			throw new ilException("ilTrainingProgramme::setLPMode: Can't set "
								 ." lp mode when not in draft status.");
		}
		if (!in_array($a_mode, self::$MODES)) {
			throw new ilException("ilTrainingProgramme::setLPMode: No lp mode: "
								 ."'$a_mode'");
		}
		$this->lp_mode = $a_mode;
		$this->updateLastChange();
	}

	/**
	 * Get the lp mode.
	 *
	 * @return integer  - one of self::$MODES
	 */
	public function getLPMode() {
		return $this->lp_mode;
	}

	/**
	 * Set the status of the node.
	 *
	 * TODO: Should this throw, when one wants to go back in lifecycle? Maybe getting
	 * back to draft needs to be forbidden only?
	 *
	 * @param integer $a_status     - one of self::$STATUS
	 */
	public function setStatus($a_status) {
		$a_status = (int)$a_status;
		if (!in_array($a_status, self::$STATUS)) {
			throw new ilException("ilTrainingProgramme::setStatus: No lp mode: "
								 ."'$a_status'");
		}
		$this->status = $a_status;
		$this->updateLastChange();
	}

	/**
	 * Get the status.
	 *
	 * @return integer  - one of self::$STATUS
	 */
	public function getStatus() {
		return $this->status;
	}
}

?>
