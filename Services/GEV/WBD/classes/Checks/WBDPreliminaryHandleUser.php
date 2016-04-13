<?php
require_once ("Services/GEV/WBD/classes/Abstracts/WBDPreliminary.php");

/**
* Check if user ist to handle
*
* if user is in $specified_user_ids do not handle
*/
class WBDPreliminaryHandleUser extends WBDPreliminary {
	protected $user_not_to_handle;
	static $message = "gev_wbd_check_no_handle_user";

	public function __construct(array $user_not_to_handle) {
		$this->user_not_to_handle = $user_not_to_handle;
	}

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return !$wbd->userIdIn($this->user_not_to_handle);
	}
}
