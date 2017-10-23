<?php
use ILIAS\TMS\Mailing;

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

/**
 * User-related placeholder-values
 */
class ilTMSMailContextUser implements Mailing\MailContext {

	protected static $PLACEHOLDER = array(
		'MAIL_SALUTATION' => 'salutation',
		'FIRST_NAME' => 'firstName',
		'LAST_NAME' => 'lastName',
		'LOGIN' => 'login'
	);

	/**
	 * @var int
	 */
	protected $usr_id;

	/**
	 * @var ilObjUser
	 */
	protected $usr;

	public function __construct($usr_id) {
		assert('is_int($usr_id)');
		$this->usr_id = $usr_id;
	}

	/**
	 * @inheritdoc
	 */
	public function valueFor($placeholder_id, $contexts = array()) {
		if(array_key_exists($placeholder_id, $this::$PLACEHOLDER)){
			$func = $this::$PLACEHOLDER[$placeholder_id];
			return $this->$func();
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function placeholderIds() {
		return array_keys(static::$PLACEHOLDER);
	}

	/**
	 * @return int
	 */
	public function getUsrId() {
		return $this->usr_id;
	}

	/**
	 * @return ilObjUser
	 */
	protected function getUser(){
		if(! $this->usr) {
			$this->usr = new \ilObjUser($this->usr_id);
		}
		return $this->usr;
	}

	/**
	 * @return string
	 */
	protected function salutation() {
		global $DIC;
		$salutation = 'salutation';
		$gender = $this->getUser()->getGender();
		if($gender === 'm') {
			$salutation = 'salutation_m';
		}
		if($gender === 'f') {
			$salutation = 'salutation_f';
		}
		return $DIC->language()->txt($salutation);

	}
	/**
	 * @return string
	 */
	protected function firstName() {
		return $this->getUser()->getFirstname();
	}
	/**
	 * @return string
	 */
	protected function lastName() {
		return $this->getUser()->getLastname();
	}

	/**
	 * @return string
	 */
	protected function login() {
		return $this->getUser()->getLogin();
	}
}
