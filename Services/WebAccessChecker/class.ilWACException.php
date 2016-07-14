<?php
require_once('./Services/Exceptions/classes/class.ilException.php');

/**
 * Class ilWACException
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWACException extends ilException {

	const CODE_NO_TYPE = 9001;
	const CODE_NO_PATH = 9002;
	const ACCESS_WITHOUT_CHECK = 9003;
	const NO_CHECKING_INSTANCE = 9004;
	const WRONG_PATH_TYPE = 9005;
	const INITIALISATION_FAILED = 9006;
	const DATA_DIR_NON_WRITEABLE = 9007;
	const ACCESS_DENIED = 9010;
	const ACCESS_DENIED_NO_PUB = 9011;
	const ACCESS_DENIED_NO_LOGIN = 9012;
	/**
	 * @var array
	 */
	protected static $messages = array(
		self::CODE_NO_TYPE           => 'No type for Path-Signing selected',
		self::WRONG_PATH_TYPE        => 'This path-type cannot be signed',
		self::CODE_NO_PATH           => 'No path for checking available',
		self::ACCESS_WITHOUT_CHECK   => 'the requested file cannot be delivered since it is not checked yet',
		self::NO_CHECKING_INSTANCE   => 'This path is not secured by a class',
		self::ACCESS_DENIED          => 'ACCESS DENIED: The requested file cannot be delivered.',
		self::ACCESS_DENIED_NO_PUB   => 'ACCESS DENIED: Public Access is not activated on this installation. Please log in.',
		self::INITIALISATION_FAILED  => 'An error occured during your request. Please reload the page.',
		self::DATA_DIR_NON_WRITEABLE => 'The SALT cannot be written to your /data directory. Please check the write permissions on the webserver.',
	);


	/**
	 * @param string $code
	 * @param string $additional_message
	 */
	public function __construct($code, $additional_message = '') {
		$message = self::$messages[$code];
		if ($additional_message) {
			$message = $message . ': ' . $additional_message;
		}
		ilWACLog::getInstance()->write('Exception in ' . $this->getFile() . ':' . $this->getLine() . ': ' . $message);
		parent::__construct($message, $code);
	}
}

?>
