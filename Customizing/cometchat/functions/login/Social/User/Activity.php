<?php
/**
 * Class Social_User_Activity
 */
class Social_User_Activity {
	public $id;
	public $date;
	public $text;
	public $user;

	public function __construct() {
		$this->user = new stdClass();
		$this->user->identifier  = null;
		$this->user->displayName = null;
		$this->user->profileURL  = null;
		$this->user->photoURL    = null;
	}
}
