<?php

/**
 * Class ilMMItemTranslationStorage
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemTranslationStorage extends ActiveRecord {

	/**
	 * CachingActiveRecord constructor.
	 *
	 * @param int              $primary_key
	 * @param arConnector|NULL $connector
	 */
	public function __construct($primary_key = 0, arConnector $connector = null) {
		$arConnector = $connector;
		if (is_null($arConnector)) {
			$arConnector = new ilGSStorageCache(new arConnectorDB());
		}

		parent::__construct($primary_key, $arConnector);
	}
}
