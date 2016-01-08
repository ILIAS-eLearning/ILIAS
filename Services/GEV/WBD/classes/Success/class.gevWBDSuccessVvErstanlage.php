<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Trait for gevWBDSuccessVvErtstanlage
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Services/GEV/WBD/classes/Success/trait.gevWBDSuccess.php");
require_once("Services/GEV/WBD/classes/class.gevWBD.php");

class gevWBDSuccessVvErstanlage extends WBDSuccessVvErstanlage{
	use gevWBDSuccess;

	protected $row_id;
	protected $wbd_type;

	public function __construct($response, $row_id, $next_wbd_action) {
		parent::__construct($response);

		$this->row_id = $row_id;

		$create_date = self::nodeValue($response,self::CREATE_DATE);
		$this->create_date = $this->createDate($create_date);

		$begin_of_certification_period = self::nodeValue($response,self::BEGIN_OF_CERTIFICATION_PERIOD);
		$this->begin_of_certification_period = $this->createDate($begin_of_certification_period);

		switch ($next_wbd_action) {
			case gevWBD::USR_WBD_NEXT_ACTION_NEW_TP_SERVICE:
				$this->wbd_type = gevWBD::WBD_TP_SERVICE;
				break;
			case gevWBD::USR_WBD_NEXT_ACTION_NEW_TP_BASIS:
				$this->wbd_type = gevWBD::WBD_TP_BASIS;
				break;
			default:
				throw new LogicException ("gevWBDSuccessVvErstanlage::__construct:no next_wbd_action");
		}
	}
	
	/**
	* gets the row id
	*
	* @throws LogicException
	* 
	*@return integer
	*/
	public function rowId() {
		return $this->row_id;
	}

	/**
	* gets the wb tyoe
	*
	* @return string
	*/
	public function wbdType() {
		return $this->wbd_type;
	}
}