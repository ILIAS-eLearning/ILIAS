<?php
require_once('./Services/FileDelivery/classes/FileDeliveryTypes/FileDeliveryTypeFactory.php');
require_once './Services/FileDelivery/classes/FileDeliveryTypes/DeliveryMethod.php';
require_once('./Services/FileDelivery/classes/Delivery.php');
require_once('./Services/FileDelivery/interfaces/int.ilFileDeliveryService.php');
require_once './Services/FileDelivery/classes/HttpServiceAware.php';

use ILIAS\FileDelivery\FileDeliveryTypes\DeliveryMethod;
use ILIAS\FileDelivery\Delivery;
use ILIAS\FileDelivery\HttpServiceAware;
use ILIAS\FileDelivery\ilFileDeliveryService;

/**
 * Class ilFileDelivery
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 *
 * @public
 */
final class ilFileDelivery implements ilFileDeliveryService {

	use HttpServiceAware;

	const DIRECT_PHP_OUTPUT = Delivery::DIRECT_PHP_OUTPUT;
	const DELIVERY_METHOD_XSENDFILE = DeliveryMethod::XSENDFILE;
	const DELIVERY_METHOD_XACCEL = DeliveryMethod::XACCEL;
	const DELIVERY_METHOD_PHP = DeliveryMethod::PHP;
	const DELIVERY_METHOD_PHP_CHUNKED = DeliveryMethod::PHP_CHUNKED;
	const DISP_ATTACHMENT = Delivery::DISP_ATTACHMENT;
	const DISP_INLINE = Delivery::DISP_INLINE;
	/**
	 * @var Delivery $delivery
	 */
	private $delivery;


	/**
	 * ilFileDelivery constructor.
	 *
	 * @param string $filePath
	 */
	public function __construct(string $filePath) {
		$this->delivery = new Delivery($filePath, self::http());
	}


	/**
	 * @inheritdoc
	 */
	public static function deliverFileAttached(string $path_to_file, string $download_file_name = '', string $mime_type = '', bool $delete_file = false) {
		$obj = new Delivery($path_to_file, self::http());

		if (self::isNonEmptyString($download_file_name)) {
			$obj->setDownloadFileName($download_file_name);
		}
		if (self::isNonEmptyString($mime_type)) {
			$obj->setMimeType($mime_type);
		}
		$obj->setDisposition(self::DISP_ATTACHMENT);
		$obj->setDeleteFile($delete_file);
		$obj->deliver();
	}


	/**
	 * @inheritdoc
	 */
	public static function streamVideoInline(string $path_to_file, string $download_file_name = '') {
		$obj = new Delivery($path_to_file, self::http());
		if (self::isNonEmptyString($download_file_name)) {
			$obj->setDownloadFileName($download_file_name);
		}
		$obj->setDisposition(self::DISP_INLINE);
		$obj->stream();
	}


	/**
	 * @inheritdoc
	 */
	public static function deliverFileInline(string $path_to_file, string $download_file_name = '') {
		$obj = new Delivery($path_to_file, self::http());

		if (self::isNonEmptyString($download_file_name)) {
			$obj->setDownloadFileName($download_file_name);
		}
		$obj->setDisposition(self::DISP_INLINE);
		$obj->deliver();
	}


	/**
	 * @inheritdoc
	 */
	public static function returnASCIIFileName(string $original_filename) : string {
		return Delivery::returnASCIIFileName($original_filename);
	}


	/**
	 * Workaround because legacy components try to call methods which are moved to the Deliver class.
	 *
	 * @param string $name          The function name which was not found on the current object.
	 * @param array  $arguments     The function arguments passed to the function which was not existent on the current object.
	 */
	public function __call(string $name, array $arguments)
	{
		//forward call to Deliver class
		call_user_func_array([$this->delivery, $name], $arguments);
	}


	/**
	 * Checks if the string is not empty.
	 *
	 * @param string $text  The text which should be checked.
	 *
	 * @return bool True if the text is not empty otherwise false.
	 */
	private static function isNonEmptyString(string $text) : bool
	{
		return strcmp($text, '') !== 0;
	}
}
