<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */#

/**
* Settings for a decentral training.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

class gevDecentralTrainingSettings {
	// @var ilDateTime		Datetime when the training starts.
	protected $start_datetime;
	
	// @var ilDateTime		Datetime when training ends
	protected $end_datetime;
	
	// @var int|null		Id of the Venue, where the training takes place.
	protected $venue_obj_id; 
	
	// @var string|null		A free form Venue.
	protected $venue_text;
	
	// @var int|null		Reference Id of the org unit the training is made for.
	protected $orgu_ref_id;
	
	// @var string			Description to be used for the training.
	protected $description;
	
	// @var string			Some info about orga to be send in an invitation.
	protected $orga_info;
	
	// @var string|null		Link to a VC
	protected $webinar_link;
	
	// @var string|null		Password to a VC
	protected $webinar_password;
	
	public function __construct( ilDateTime $a_start_datetime
							   , ilDateTime $a_end_datetime
							   , $a_venue_obj_id
							   , $a_venue_text
							   , $a_orgu_ref_id
							   , $a_description
							   , $a_orga_info
							   , $a_webinar_link
							   , $a_webinar_password
							   , $a_title
							   , $a_vc_type
							   , $a_training_category
							   , $a_target_group
							   , $a_gdv_topic
							   ) {
		assert($a_start_datetime->get(IL_CAL_DATE) == $a_end_datetime->get(IL_CAL_DATE));
		
		assert($a_venue_obj_id === null || is_int($a_venue_obj_id));
		assert($a_venue_text === null || is_string($a_venue_text));
		assert($a_venue_obj_id === null || $a_venue_text === null);
		
		assert($a_orgu_ref_id === null || is_int($a_orgu_ref_id));
		assert($a_orgu_ref_id === null || ilObject::_lookupType($a_orgu_ref_id, true) == "orgu");
		
		assert(is_string($a_description));
		assert(is_string($a_orga_info));
		assert($a_webinar_link === null || is_string($a_webinar_link));
		assert($a_webinar_password === null || is_string($a_webinar_password));

		assert($a_title === null || is_string($a_title));
		assert($a_vc_type === null || is_string($a_vc_type));
		assert($a_training_category === null || is_array($a_training_category));
		assert($a_target_group === null || is_array($a_target_group));
		assert($a_gdv_topic === null || is_string($a_gdv_topic));

		$this->start_datetime = $a_start_datetime;
		$this->end_datetime = $a_end_datetime;
		$this->venue_obj_id = $a_venue_obj_id; 
		$this->venue_text = $a_venue_text;
		$this->trainer_ids = $a_trainer_ids;
		$this->orgu_ref_id = $a_orgu_ref_id;
		$this->description = $a_description;
		$this->orga_info = $a_orga_info;
		$this->webinar_link = $a_webinar_link;
		$this->webinar_password = $a_webinar_password;

		$this->title = $a_title;
		$this->vc_type = $a_vc_type;
		$this->training_category = $a_training_category;
		$this->target_group = $a_target_group;
		$this->gdv_topic = $a_gdv_topic;
	}
	
	protected function getCourseUtils($a_obj_id) {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		return gevCourseUtils::getInstance($a_obj_id);
	}
	
	protected function throwException($msg) {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingException.php");
		throw new gevDecentralTrainingException($msg);
	}
	
	public function start() {
		return $this->start_datetime;
	}
	
	public function end() {
		return $this->end_datetime;
	}

	public function setStart(ilDateTime $a_start_datetime) {
		$this->start_datetime = $a_start_datetime;
	}

	public function setEnd(ilDateTime $a_end_datetime) {
		$this->end_datetime = $a_end_datetime;
	}

	public function venueObjId() {
		return $this->venue_obj_id;
	}
	
	public function venueText() {
		return $this->venue_text;
	}
	
	public function orguRefId() {
		return $this->orgu_ref_id;
	}
	
	public function description() {
		return $this->description;
	}
	
	public function orgaInfo() {
		return $this->orga_info;
	}
	
	public function webinarLink() {
		return $this->webinar_link;
	}
	
	public function webinarPassword() {
		return $this->webinar_password;
	}

	public function title() {
		return $this->title;
	}

	public function vcType() {
		return $this->vc_type;
	}

	public function trainingCategory() {
		return $this->training_category;
	}
	
	public function targetGroup() {
		return $this->target_group;
	}

	public function gdvTopic() {
		return $this->gdv_topic;
	}

	public function applyTo($a_obj_id) {
		assert(is_int($a_obj_id));
		assert(ilObject::_lookupType($a_obj_id) == "crs");
		
		require_once("Services/Calendar/classes/class.ilDate.php");
		$crs_utils = $this->getCourseUtils($a_obj_id);
		$crs = $crs_utils->getCourse();
		
		if ($crs_utils->isFinalized()) {
			$this->throwException("Training already finalized.");
		}
		
		$crs_utils->setTEPOrguId($this->orguRefId());
		$crs->setDescription($this->description());
		
		$start = explode(" ", $this->start()->get(IL_CAL_DATETIME));
		$end = explode(" ", $this->end()->get(IL_CAL_DATETIME));
		$crs_utils->setStartDate(new ilDate($start[0], IL_CAL_DATE));
		$crs_utils->setEndDate(new ilDate($end[0], IL_CAL_DATE));

		$start = explode(":", $start[1]);
		$end = explode(":", $end[1]);
		$crs_utils->setSchedule(array($start[0].":".$start[1]."-".$end[0].":".$end[1]));

		$crs_utils->setVenueId($this->venueObjId());
		$crs_utils->setVenueFreeText($this->venueText());
		$crs_utils->setVirtualClassLink($this->webinarLink());
		$crs_utils->setVirtualClassPassword($this->webinarPassword());
		$crs_utils->setOrgaInfo($this->orgaInfo());

		if ($this->title() !== null) {
			$crs->setTitle($this->title());
		}
		if ($this->vcType() !== null) {
			$crs_utils->setVirtualClassType($this->vcType());
		}
		if ($this->trainingCategory() !== null) {
			$crs_utils->setTrainingCategory($this->trainingCategory());
		}
		if ($this->targetGroup() !== null) {
			$crs_utils->setTargetGroup($this->targetGroup());
		}
		if ($this->gdvTopic() !== null) {
			$crs_utils->setGDVTopic($this->gdvTopic());
		}
		
		$crs->update();
	}
}
