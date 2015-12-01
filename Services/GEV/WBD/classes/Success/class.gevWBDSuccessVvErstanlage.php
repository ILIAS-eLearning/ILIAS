<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Trait for gevWBDSuccessVvErtstanlage
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
require_once("Service/GEV/WBD/classes/Success/trait.gevWBDSuccess.php");
require_once ("Services/GEV/WBD/classes/Utils/class.gevSettings.php");
require_once ("Services/GEV/WBD/classes/Utils/class.gevUserUtils.php");
class gevWBDSuccessVvErstanlage extends WBDSuccessVvErstanlage{
	use gevWBDSuccess;

	protected $row_id;
	protected $wbd_type;

	public function __construct($response, $row_id, $next_wbd_action) {
		parent::__construct($response);

		$this->row_id = $row_id;
		
		switch ($next_action) {
			case gevSettings::USR_WBD_NEXT_ACTION_NEW_TP_SERVICE:
				$this->wbd_type = gevUserUtils::WBD_TP_SERVICE;
				break;
			case gevSettings::USR_WBD_NEXT_ACTION_NEW_TP_BASIS:
				$this->wbd_type = gevUserUtils::WBD_TP_BASIS;
				break;
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