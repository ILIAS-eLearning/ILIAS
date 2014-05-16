<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class {CLASS_NAME}
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class {CLASS_NAME} extends ActiveRecord {

	/**
	 * @return string
	 */
	static function returnDbTableName() {
		return '{TABLE_NAME}';
	}

	<!-- BEGIN member -->
	/**
     * @var {DECLARATION}
     *
     <!-- BEGIN attribute -->* @con_{NAME} {VALUE}
     <!-- END attribute -->
     */
    protected ${FIELD_NAME};
	<!-- END member -->
}

?>
