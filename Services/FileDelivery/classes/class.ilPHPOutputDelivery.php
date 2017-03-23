<?php
require_once('class.ilFileDelivery.php');
require_once('Delivery.php');
use ILIAS\FileDelivery\Delivery as Delivery;

/**
 * Class ilPHPOutputDelivery
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilPHPOutputDelivery {

	/**
	 * @var ILIAS\FileDelivery\Delivery
	 */
	protected $ilFileDelivery;


	/**
	 * @param $download_file_name
	 * @param string $mime_type
	 */
	public function start($download_file_name, $mime_type = ilMimeTypeUtil::APPLICATION__OCTET_STREAM) {
		$this->ilFileDelivery = new Delivery(ilFileDelivery::DIRECT_PHP_OUTPUT);
		$this->ilFileDelivery->setMimeType($mime_type);
		$this->ilFileDelivery->setDownloadFileName($download_file_name);
		$this->ilFileDelivery->setDisposition(ilFileDelivery::DISP_ATTACHMENT);
		$this->ilFileDelivery->setConvertFileNameToAsci(true);
		$this->ilFileDelivery->clearBuffer();
		$this->ilFileDelivery->checkCache();
		$this->ilFileDelivery->setGeneralHeaders();
		$this->ilFileDelivery->setShowLastModified(false);
		$this->ilFileDelivery->setCachingHeaders();
	}


	public function stop() {
		$this->ilFileDelivery->close();
	}
}
