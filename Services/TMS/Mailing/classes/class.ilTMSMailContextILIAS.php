<?php
use ILIAS\TMS\Mailing;

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

/**
 * ilias-related placeholder-values
 */
class ilTMSMailContextILIAS implements Mailing\MailContext {

	/**
	 * @var ilLanguage
	 */
	protected $g_lang;

	public function __construct() {
		global $DIC;
		$this->g_lang = $DIC->language();
		$this->g_lang->loadLanguageModule("tms");
	}

	private static $PLACEHOLDER = array(
		'ILIAS_URL' => 'placeholder_desc_ilias_ilias_url',
		'CLIENT_NAME' => 'placeholder_desc_ilias_client_name'
	);

	/**
	 * @inheritdoc
	 */
	public function valueFor($placeholder_id, $contexts = array()) {
		switch ($placeholder_id) {
			case 'ILIAS_URL':
				return ILIAS_HTTP_PATH . '/login.php?client_id=' . CLIENT_ID;
			case 'CLIENT_NAME':
				return CLIENT_NAME;
			default:
				return null;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function placeholderIds() {
		return array_keys(self::$PLACEHOLDER);
	}

	/**
	 * @inheritdoc
	 */
	public function placeholderDescriptionForId($placeholder_id) {
		return $this->g_lang->txt(self::$PLACEHOLDER[$placeholder_id]);
	}
}
