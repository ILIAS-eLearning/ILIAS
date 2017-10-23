<?php
use ILIAS\TMS\Mailing;

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

/**
 * ilias-related placeholder-values
 */
class ilTMSMailContextILIAS implements Mailing\MailContext {

	private static $PLACEHOLDER = array(
		'ILIAS_URL',
		'CLIENT_NAME'
	);

	/**
	 * @inheritdoc
	 */
	public function valueFor($placeholder_id, $contexts = array()) {
		switch ($placeholder_id) {
			case 'ILIAS_URL':
				return ILIAS_HTTP_PATH . '/login.php?client_id=' . CLIENT_ID;
				break;
			case 'CLIENT_NAME':
				return CLIENT_NAME;
				break;
			default:
				return null;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function placeholderIds() {
		return $this::$PLACEHOLDER;
	}

}
