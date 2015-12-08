<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* implementation of GEV WBD Request for Service WPMeldung
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");
require_once("Services/GEV/WBD/classes/Requests/trait.gevWBDRequest.php");
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessWPMeldung.php");
class gevWBDRequestWPMeldung extends WBDRequestWPMeldung {
	use gevWBDRequest;

	protected function __construct($data) {
		parent::__construct();

		$this->title 				= new WBDData("Weiterbildung",$data["title"]);
		$this->begin_date 			= new WBDData("SeminarDatumVon",$data["begin_date"]);
		$this->end_date 			= new WBDData("SeminarDatumBis",$data["end_date"]);
		$this->credit_points 		= new WBDData("WeiterbildungsPunkte",$data["credit_points"]);
		$this->type 				= new WBDData("LernArt",$this->dictionary->getWBDName($data["type"],gevWBDDictionary::SERACH_IN_COURSE_TYPE));
		$this->wbd_topic 			= new WBDData("LernInhalt",$this->dictionary->getWBDName($data["wbd_topic"],gevWBDDictionary::SEARCH_IN_STUDY_CONTENT));
		$this->internal_booking_id	= new WBDData("InterneBuchungsId",$data["row_id"]);
		$this->agent_id 			= new WBDData("VermittlerId",$data["bwv_id"]);
		

		$errors = $this->checkData($data);

		if(!empty($errors)) {
			throw new myLogicException("gevWBDRequestWPMeldung::__construct:checkData failed",0,null, $errors);
		}

		$this->user_id = $data["user_id"];
		$this->row_id = $data["row_id"];
		$this->crs_id = $data["crs_id"];
		$this->begin_of_certification = $data["begin_of_certification"];
	}

	public static function getInstance(array $data) {
		$errors = self::checkData($data);
		
		try {
			return new gevWBDRequestWPMeldung($data);
		}catch(myLogicException $e) {
			return $e->options();
		} catch(LogicException $e) {
			$errors = array();
			$errors[] =  self::createWBDError($e->getMessage(), static::$request_type, $data["user_id"], $data["row_id"],0);
			return $errors;
		}
	}

	/**
	* checked all given data
	*
	* @throws LogicException
	* 
	* @return string
	*/
	protected function checkData($data) {
		return $this->checkSzenarios($data);
	}

	/**
	* creates the success object VvErstanlage
	*
	* @throws LogicException
	*/
	public function createWBDSuccess($response) {
		$this->wbd_success = new gevWBDSuccessWPMeldung($response, $this->begin_of_certification, $this->user_id);
	}

	/**
	* gets the row id
	*
	* @return integer
	*/
	public function rowId() {
		return $this->row_id;
	}

	/**
	* gets the agent_id
	*
	* @return integer
	*/
	public function agentId() {
		return $this->agent_id;
	}

	/**
	* gets the user_id
	*
	* @return integer
	*/
	public function userId() {
		return $this->user_id;
	}
}