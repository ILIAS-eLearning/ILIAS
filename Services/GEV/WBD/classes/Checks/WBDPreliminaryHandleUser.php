<?php
require_once ("Services/GEV/WBD/classes/Abstracts/WBDPreliminary.php");

/**
* Check if user ist to handle
*
* if user is in $specified_user_ids do not handle
*/
class WBDPreliminaryHandleUser extends WBDPreliminary {
	protected $no_handle_user_ids;
	static $message = "gev_wbd_check_no_handle_user";

	public function __construct(array $no_handle_user_ids) {
		$this->no_handle_user_ids = $no_handle_user_ids;
	}

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return !$wbd->userIdIn($this->no_handle_user_ids);
	}
}
