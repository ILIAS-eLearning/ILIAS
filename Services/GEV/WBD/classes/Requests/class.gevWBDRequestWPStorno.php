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
require_once("Services/GEV/WBD/classes/Success/class.gevWBDSuccessWPStorno.php");
require_once("Services/GEV/WBD/classes/Data/class.gevWBDData.php");
class gevWBDRequestWPStorno extends gevWBDRequest {
	
	protected $wbd_booking_id;
	protected $bwv_id;

	protected $xml_tmpl_file_name;

	static $request_type = "CP_STORNO";
	static $check_szenarios = array('wbd_booking_id'	=> array('mandatory' => 1)
									,'bwv_id'	 		=> array('mandatory' => 1)
								);

	protected function __construct($data) {
		parent::__construct();

		$this->wbd_booking_id 	= new gevWBDData("WeiterbildungsPunkteBuchungsId",$data["wbd_booking_id"]);
		$this->bwv_id 			= new gevWBDData("VermittlerId",$data["bwv_id"]);
		

		$this->xml_tmpl_file_name = "WpStorno.xml";
		$this->wbd_service_name = "WpStornoService";

		$this->user_id = $data["user_id"];
		$this->row_id = $data["row_id"];
	}

	public static function getInstance(array $data) {
		$errors = self::checkData($data);
		if(!count($errors))  {
			return new gevWBDRequestWPStorno($data);
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
		$this->wbd_success = new gevWBDSuccessWPStorno($response,$this->row_id);
	}
}