<?php
use ILIAS\TMS\Mailing;

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

/**
 * User-related placeholder-values, this time for the currently logged in user.
 */
class ilTMSMailContextCurrentUser extends ilTMSMailContextUser {

	protected static $PLACEHOLDER = array(
		'CURRENT_USER_MAIL_SALUTATION' => 'placeholder_desc_current_user_salutation',
		'CURRENT_USER_FIRST_NAME' => 'placeholder_desc_current_user_firstName',
		'CURRENT_USER_LAST_NAME' => 'placeholder_desc_current_user_lastName',
		'CURRENT_USER_LOGIN' => 'placeholder_desc_current_user_login',
		'CURRENT_USER_EMAIL' => 'placeholder_desc_current_user_email'
	);

	/**
	 * @var int
	 */
	protected $usr_id;

	/**
	 * @var ilObjUser
	 */
	protected $usr;

	/**
	 * @var ilLanguage
	 */
	protected $g_lang;


	public function __construct() {
		global $DIC;
		$this->usr = $DIC->user();
		$this->usr_id = $this->usr->getId();
		$this->g_lang = $DIC->language();
		$this->g_lang->loadLanguageModule("tms");
	}

	/**
	 * @inheritdoc
	 */
	public function valueFor($placeholder_id, $contexts = array()) {
		switch ($placeholder_id) {
			case 'CURRENT_USER_MAIL_SALUTATION':
				return $this->salutation();
			case 'CURRENT_USER_FIRST_NAME':
				return $this->firstName();
			case 'CURRENT_USER_LAST_NAME':
				return $this->lastName();
			case 'CURRENT_USER_LOGIN':
				return $this->login();
			case 'CURRENT_USER_EMAIL':
				return $this->email();

			default:
				return null;
		}
	}

	/**
	 * @return string
	 */
	private function email() {
		return $this->getUser()->getEmail();
	}
}
