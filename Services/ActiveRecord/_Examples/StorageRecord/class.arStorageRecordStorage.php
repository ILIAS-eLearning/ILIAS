<?php

require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Storage/class.arStorage.php');

/**
 * Class arTestRecordStorage
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version 2.0.7
 */
class arStorageRecordStorage extends arStorage {

	/**
	 * @return string
	 * @description Return the Name of your Database Table
	 */
	static function returnDbTableName() {
		return 'ar_demo_storage_record';
	}
}

?>
