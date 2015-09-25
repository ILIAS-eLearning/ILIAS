<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* valus to import crs from WBD to GOA
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
class gevImportCourseData{

	protected $wbd_booking_id;
	protected $title;
	protected $credit_points;
	protected $begin_date;
	protected $end_date;
	protected $course_type;
	protected $study_content;

	public function __construct($wbd_booking_id, $begin_date, $end_date, $title, $credit_points, $course_type, $study_content) {
		$this->wbd_booking_id = $wbd_booking_id;
		$this->title = $title;
		$this->credit_points = $credit_points;
		$this->begin_date = $begin_date;
		$this->end_date = $end_date;
		$this->course_type = $course_type;
		$this->study_content = $study_content;
	}

	/**
	* gets the creation date
	*
	* @throws LogicException
	* 
	* @return string
	*/
	public function wbdBookingId() {
		if($this->wbd_booking_id === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::wbdBookingId:wbd_booking_id is NULL");
		}

		return $this->wbd_booking_id;
	}

	

	/**
	* gets the Title
	*
	* @throws LogicException
	* 
	* @return string
	*/
	public function title() {
		if($this->title === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::title:title is NULL");
		}

		return $this->title;
	}

	/**
	* gets the credit_points id
	*
	* @throws LogicException
	* 
	* @return integer
	*/
	public function creditPoints() {
		if($this->credit_points === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::creditPoints:credit_points is NULL");
		}

		return $this->credit_points;
	}

	/**
	* gets the internal agent id
	*
	* @throws LogicException
	* 
	* @return ilDate
	*/
	public function beginDate() {
		if($this->begin_date === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::beginDate:begin_date is NULL");
		}

		return $this->begin_date;
	}

	/**
	* gets the end_date
	*
	* @throws LogicException
	* 
	* @return ilDate
	*/
	public function endDate() {
		if($this->end_date === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::endDate:end_date is NULL");
		}

		return $this->end_date;
	}

	/**
	* gets the course_type
	*
	* @throws LogicException
	* 
	* @return sring
	*/
	public function courseType() {
		if($this->course_type === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::courseType:course_type is NULL");
		}

		return $this->course_type;
	}

	/**
	* gets the study_content
	*
	* @throws LogicException
	* 
	* @return string
	*/
	public function studyContent() {
		if($this->study_content === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::studyContent:study_content is NULL");
		}
		
		return $this->study_content;
	}

	/**
	* gets the storno
	*
	* @throws LogicException
	* 
	* @return bool
	*/
	public function storno() {
		if($this->storno === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::storno:storno is NULL");
		}

		return $this->storno;
	}

	/**
	* gets the correct_booking
	*
	* @throws LogicException
	* 
	* @return bool
	*/
	public function correctBooking() {
		if($this->correct_booking === null) {
			throw new LogicException("gevWBDSuccessWPAbfrage::correctBooking:correct_booking is NULL");
		}
		
		return $this->correct_booking;
	}
}
}