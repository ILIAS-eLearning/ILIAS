<?php

/**
* Appointment-Handler
* The Appointment-Handler handles all actions with Appointments for the ILIAS3-Calendar.
*
* version 1.0
* @author Christoph Schulz-Sacharov <sch-sa@gmx.de>
* @author MArtin Schumacher <ilias@auchich.de>
* @author Mark Ulbrich <Mark_Ulbrich@web.de>
**/

class ilAppointment
{
	var $access;
	var $appointmentId;
	var $appointmentUnionId;
	var $category;
	var $categoryID;
	var $description;
	var $duration;
	var $priorityId;
	var $priority;
	var $startTimestamp;
	var $term;
	var $ownerId;
	var $userId;
	var $location;
	var $serial;
	var $ser_type;
	var $ser_days;
	var $ser_stop;
	
	function getAccess() {
		return $this->access;
	}
	
	function getAppointmentId() {
		return $this->appointmentId;
	}
	
	function getAppointmentUnionId() {
		return $this->appointmentUnionId;
	}
	
	function getCategory() {
		return $this->category;	
	}
	
	function getCategoryId() {
		return $this->categoryId;	
	}
	
	function getDescription() {
		return $this->description;	
	}
	
	function getDuration() {
		return $this->duration;	
	}
	
	function getPriorityId() {
		return $this->priorityId;	
	}
	
	function getPriority() {
		return $this->priority;	
	}
	
	function getStartTimestamp() {
		return $this->startTimestamp;
	}
	
	function getTerm() {
		return $this->term;	
	}
	
	function getLocation() {
		return $this->location;	
	}
	
	function getSerial() {
		return $this->serial;	
	}
	
	function getSer_type() {
		return $this->ser_type;	
	}
	
	function getSer_days() {
		return $this->ser_days;	
	}

	function getSer_stop() {
		return $this->ser_stop;	
	}
	
	function getOwnerId() {
		return $this->ownerId;
	}
	
	function getUserId() {
		return $this->userId;
	}
	
	function setAccess($thisAccess) {
		$this->access = $thisAccess; 
	}
	
	function setAppointmentId($thisAppointmentId) {
		$this->appointmentId = $thisAppointmentId;	
	}
	
	function setAppointmentUnionId($thisAppointmentUnionId) {
		$this->appointmentUnionId = $thisAppointmentUnionId;	
	}
	
	function setCategory($thisCategory) {
		$this->category = $thisCategory;	
	}
	
	function setCategoryId($thisCategoryId) {
		$this->categoryId = $thisCategoryId;	
	}
	
	function setDescription($thisDescription) {
		$this->description = $thisDescription;		
	}

	function setDuration($thisDuration) {
		$this->duration = $thisDuration;	
	}
	
	function setPriorityId($thisPriorityId) {
		$this->priorityId = $thisPriorityId;		
	}
	
	function setPriority($thisPriority) {
		$this->priority = $thisPriority;		
	}
	
	function setStartTimestamp($thisStartTimestamp) {
		$this->startTimestamp = $thisStartTimestamp;	
	}
	
	function setTerm($thisTerm) {
		$this->term = $thisTerm;	
	}
	
	function setLocation($thisLocation) {
		$this->location = $thisLocation;
	}
	
	function setSerial($thisSerial) {
		$this->serial = $thisSerial;		
	}
	
	function setSer_type($thisSer_type) {
		$this->ser_type = $thisSer_type;		
	}
	
	function setSer_days($thisSer_days) {
		$this->ser_days = $thisSer_days;		
	}
	
	function setSer_stop($thisSer_stop) {
		$this->ser_stop = $thisSer_stop;		
	}
	
	function setOwnerId($thisOwnerId) {
		$this->ownerId = $thisOwnerId;
	}
	
	function setUserId($thisUserId) {
		$this->userId = $thisUserId;
	}
}

?>
