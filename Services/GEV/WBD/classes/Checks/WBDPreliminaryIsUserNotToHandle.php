<?php
require_once ("Services/GEV/WBD/classes/Abstracts/WBDPreliminary.php");

class WBDPreliminaryIsUserNotToHandle extends WBDPreliminary {
	protected $specified_user_ids;
	static $message = "gev_wbd_check_no_handle_user";

	public function __construct(array $specified_user_ids) {

		$this->specified_user_ids = $specified_user_ids;
	}

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return $wbd->hasSpecialUserId($this->specified_user_ids);
	}
}
