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
require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequest.php");
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessWPMeldung.php");
require_once("Services/GEV/WBD/classes/Data/class.gevWBDData.php");
class gevWBDRequestWPMeldung extends gevWBDRequest {
	
	protected $title;
	protected $begin_date;
	protected $end_date;
	protected $credit_points;
	protected $type;
	protected $wbd_topic;
	protected $row_id;
	protected $bwv_id;

	protected $xml_tmpl_file_name;

	static $request_type = "CP_REPORT";
	static $check_szenarios = array('title' 			=> array('mandatory' => 1, 'maxlen' => 100)
									,'begin_date' 		=> array('mandatory' => 1)
									,'end_date' 		=> array('mandatory' => 1)
									,'credit_points' 	=> array('mandatory' => 1, 'min_int_value' => 1)
									,'type' 			=> array('mandatory' => 1)
									,'wbd_topic' 		=> array('mandatory' => 1)
									,'row_id' 			=> array('mandatory' => 1, 'maxlen' => 50)
									,'bwv_id'	 		=> array('mandatory' => 1)
								);

	protected function __construct($data) {
		parent::__construct();

		$this->title 			= new gevWBDData("Weiterbildung",$data["title"]);
		$this->begin_date 		= new gevWBDData("SeminarDatumVon",$data["begin_date"]);
		$this->end_date 		= new gevWBDData("SeminarDatumBis",$data["end_date"]);
		$this->credit_points 	= new gevWBDData("WeiterbildungsPunkte",$data["credit_points"]);
		$this->type 			= new gevWBDData("LernArt",$this->dictionary->getWBDName($data["type"],gevWBDDictionary::SERACH_IN_COURSE_TYPE));
		$this->wbd_topic 		= new gevWBDData("LernInhalt",$this->dictionary->getWBDName($data["wbd_topic"],gevWBDDictionary::SEARCH_IN_STUDY_CONTENT));
		$this->row_id 			= new gevWBDData("InterneBuchungsId",$data["row_id"]);
		$this->bwv_id 			= new gevWBDData("VermittlerId",$data["bwv_id"]);
		

		$this->xml_tmpl_file_name = "WpMeldung.xml";
		$this->wbd_service_name = "WpMeldungService";

		$this->user_id = $data["user_id"];
		$this->row_id = $data["row_id"];
	}

	public static function getInstance(array $data) {
		$errors = self::checkData($data);
		if(!count($errors))  {
			return new gevWBDRequestWPMeldung($data);
		} else {
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
	private static function checkData($data) {
		return self::checkSzenarios($data);
	}

	/**
	* creates the success object VvErstanlage
	*
	* @throws LogicException
	*/
	public function createWBDSuccess($response) {
		$this->wbd_success = new gevWBDSuccessWPMeldung($response);
	}
}