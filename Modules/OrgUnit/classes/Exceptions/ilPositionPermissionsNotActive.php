<?php

require_once("./Modules/OrgUnit/classes/Exceptions/class.ilOrguException.php");

/**
 * Class ilPositionPermissionsNotActive
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilPositionPermissionsNotActive extends ilOrguException {
	/** @var string  */
	protected $object_type = "";

	/**
	 * ilPositionPermissionsNotActive constructor.
	 *
	 * @param string $a_message
	 * @param string $type
	 * @param int    $a_code
	 */
	public function __construct($a_message, $type, $a_code = 0) {
		parent::__construct($a_message, $a_code);

		$this->object_type = $type;
	}


	/**
	 * @return string
	 */
	public function getObjectType(): string {
		return $this->object_type;
	}
}