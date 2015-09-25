<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* implementation of GEV WBD Request for Service WPAbfrage
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/GEV/WBD/classes/Dictionary/class.gevWBDDictionary.php");
require_once("Services/GEV/WBD/classes/Requests/class.gevWBDRequest.php");
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessWPAbfrage.php");
require_once("Services/GEV/WBD/classes/Data/class.gevWBDData.php");
class gevWBDRequestWPAbfrage extends gevWBDRequest {
	
	protected $certification_period;
	protected $bwv_id;

	protected $xml_tmpl_file_name;

	static $request_type = "CP_REQUEST";
	static $check_szenarios = array('bwv_id'					=> array('mandatory' => 1)
									,'certification_period'		=> array('mandatory' => 1)
								);

	protected function __construct($data) {
		parent::__construct();

		$this->bwv_id 					= new gevWBDData("VermittlerId",$data["bwv_id"]);
		$this->certification_period 	= new gevWBDData("ZertifizierungsPeriode",$this->dictionary->getWBDName($data["certification_period"],gevWBDDictionary::SEARCH_IN_CERTIFICATION_PERIOD));
		
		$this->xml_tmpl_file_name = "WpAbfrage.xml";
		$this->wbd_service_name = "WpAbfrageService";

		$this->user_id = $data["user_id"];
		$this->row_id = $data["row_id"];
	}

	public static function getInstance(array $data) {
		$errors = self::checkData($data);
		if(!count($errors))  {
			return new gevWBDRequestWPAbfrage($data);
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
		$this->wbd_success = new gevWBDSuccessWPAbfrage($response,$this->user_id);
	}
}