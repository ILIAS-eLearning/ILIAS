<?php
namespace ILIAS\FileDelivery\FileDeliveryTypes;

/**
 * Class Factory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Factory {

	const DELIVERY_METHOD_PHP = 'php';
	const DELIVERY_METHOD_PHP_CHUNKED = 'php_chunked';
	const DELIVERY_METHOD_XACCEL = 'x-accel-redirect';
	const DELIVERY_METHOD_XSENDFILE = 'mod_xsendfile';
	/**
	 * @var array
	 */
	protected static $instances = array();


	/**
	 * @param $type
	 * @return \ilFileDeliveryType
	 */
	public static function getInstance($type) {
		if (isset(self::$instances[$type])) {
			return self::$instances[$type];
		}
		switch ($type) {
			case self::DELIVERY_METHOD_PHP:
			default:
				require_once('PHP.php');
				self::$instances[$type] = new PHP();
				break;
			case self::DELIVERY_METHOD_XSENDFILE:
				require_once('XSendfile.php');
				self::$instances[$type] = new XSendfile();
				break;
			case self::DELIVERY_METHOD_XACCEL:
				require_once('XAccel.php');
				self::$instances[$type] = new XAccel();
				break;
			case self::DELIVERY_METHOD_PHP_CHUNKED:
				require_once('PHPChunked.php');
				self::$instances[$type] = new PHPChunked();
				break;
		}

		return self::$instances[$type];
	}
}
