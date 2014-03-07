<?php

require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Storage/class.arStorage.php');

/**
 * Class arTestRecordStorage
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class arTestRecordStorage extends arStorage {

	/**
	 * @return string
	 * @description Return the Name of your Database Table
	 */
	static function returnDbTableName() {
		return 'test_ar_storage';
	}
}

?>
