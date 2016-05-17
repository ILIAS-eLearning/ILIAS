<?php

require_once("./Services/Exceptions/classes/class.ilException.php");

/**
 * Class ilDatabaseException
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDatabaseException extends ilException {

	/**
	 * ilDatabaseException constructor.
	 *
	 * @param string $a_message
	 * @param int $a_code
	 */
	public function __construct($a_message, $a_code = 0) {
		parent::__construct($a_message, $a_code);
	}
}