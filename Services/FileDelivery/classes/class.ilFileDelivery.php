<?php
require_once('./Services/FileDelivery/classes/FileDeliveryTypes/Factory.php');
require_once('./Services/FileDelivery/classes/Delivery.php');
require_once('./Services/FileDelivery/interfaces/int.ilFileDeliveryService.php');

use ILIAS\FileDelivery\FileDeliveryTypes\Factory as F;
use ILIAS\FileDelivery\Delivery as Delivery;

/**
 * Class ilFileDelivery
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilFileDelivery implements ilFileDeliveryService {

	const DIRECT_PHP_OUTPUT = Delivery::DIRECT_PHP_OUTPUT;
	const DELIVERY_METHOD_XSENDFILE = F::DELIVERY_METHOD_XSENDFILE;
	const DELIVERY_METHOD_XACCEL = F::DELIVERY_METHOD_XACCEL;
	const DELIVERY_METHOD_PHP = F::DELIVERY_METHOD_PHP;
	const DELIVERY_METHOD_PHP_CHUNKED = F::DELIVERY_METHOD_PHP_CHUNKED;
	const DISP_ATTACHMENT = Delivery::DISP_ATTACHMENT;
	const DISP_INLINE = Delivery::DISP_INLINE;


	/**
	 * @inheritdoc
	 */
	public static function deliverFileAttached($path_to_file, $download_file_name = null, $mime_type = null, $delete_file = false) {
		Delivery::deliverFileAttached($path_to_file, $download_file_name, $mime_type, $delete_file);
	}


	/**
	 * @inheritdoc
	 */
	public static function streamVideoInline($path_to_file, $download_file_name = null) {
		Delivery::streamVideoInline($path_to_file, $download_file_name);
	}


	/**
	 * @inheritdoc
	 */
	public static function deliverFileInline($path_to_file, $download_file_name = null) {
		Delivery::deliverFileInline($path_to_file, $download_file_name);
	}


	/**
	 * @inheritdoc
	 */
	public static function returnASCIIFileName($original_filename) {
		return Delivery::returnASCIIFileName($original_filename);
	}
}
