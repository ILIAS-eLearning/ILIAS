<?php
require_once('./Services/Exceptions/classes/class.ilException.php');
/**
 * Class ilWACException
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWACException extends ilException {

	const BASE_CODE = 9000;
	const CODE_NO_TYPE = 1;
	const CODE_NO_PATH = 2;
	const ACCESS_WITHOUT_CHECK = 3;
	const ACCESS_DENIED= 10;
	/**
	 * @var array
	 */
	protected static $messages = array(
		self::CODE_NO_TYPE => 'No type for Path-Signing selected',
		self::CODE_NO_PATH => 'No path for checking available',
		self::ACCESS_WITHOUT_CHECK => 'the requested file cannot be delivered since it is not checked yet',
		self::ACCESS_DENIED => 'the requested file cannot be delivered',
	);


	/**
	 * @param int $code
	 */
	public function __construct($code) {
		parent::__construct(self::$messages[$code], self::BASE_CODE + $code);
	}
}

?>
