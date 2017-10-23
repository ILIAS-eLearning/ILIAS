<?php
use ILIAS\TMS\Mailing;

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

require_once('./Services/User/classes/class.ilObjUser.php');

/**
 * recipients for mails
 */
class ilTMSMailRecipient implements Mailing\Recipient {

	/**
	 * @var int | null
	 */
	protected $usr_id;

	/**
	 * @var string | null
	 */
	protected $mail;

	/**
	 * @var string | null
	 */
	protected $name;

	public function __construct($usr_id = null) {
		assert('is_int($usr_id) || $usr_id===null');
		$this->usr_id = $usr_id;
	}

	/**
	 * @inheritdoc
	 */
	public function getMailAddress() {
		if($this->usr_id) {
			return \ilObjUser::_lookupEmail($this->usr_id);
		}
		if($this->mail) {
			return $this->mail;
		} else {
			return null;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getUserId() {
		return $this->usr_id;
	}

	/**
	 * @inheritdoc
	 */
	public function getUserLogin() {
		if($this->usr_id) {
			return \ilObjUser::_lookupLogin($this->usr_id);
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function isInactiveUser() {
		if(is_null($this->getUserId())) {
			return false;
		}
		$user = new \ilObjUser($this->getUserId());
		return (bool)$user->getActive() === false;
	}

	/**
	 * @inheritdoc
	 */
	public function getUserName() {
		if($this->usr_id) {
			$nam = \ilObjUser::_lookupName($this->usr_id);
			return trim(sprintf('%s %s %s',
				$nam['title'],
				$nam['firstname'],
				$nam['lastname']
			));
		}
		return $this->name;
	}

	/**
	 * @inheritdoc
	 */
	public function withName($name) {
		assert('is_string($name)');
		if(! is_null($this->usr_id)) {
			throw new \Exception('You cannot manually set the name for a recipient based on usr_id');
		}
		$clone = clone $this;
		$clone->name = $name;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withMail($mail) {
		assert('is_string($mail)');
		if(! is_null($this->usr_id)) {
			throw new \Exception('You cannot manually set the mail for a recipient based on usr_id');
		}
		$clone = clone $this;
		$clone->mail = $mail;
		return $clone;
	}

}
