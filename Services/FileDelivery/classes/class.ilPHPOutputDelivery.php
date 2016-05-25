<?php
require_once('class.ilFileDelivery.php');

/**
 * Class ilPHPOutputDelivery
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilPHPOutputDelivery {

	/**
	 * @var ilFileDelivery
	 */
	protected $ilFileDelivery;


	/**
	 * @param $download_file_name
	 * @param string $mime_type
	 */
	public function start($download_file_name, $mime_type = ilMimeTypeUtil::APPLICATION__OCTET_STREAM) {
		$this->ilFileDelivery = new ilFileDelivery(ilFileDelivery::DIRECT_PHP_OUTPUT);
		$this->ilFileDelivery->setMimeType($mime_type);
		$this->ilFileDelivery->setDownloadFileName($download_file_name);
		$this->ilFileDelivery->setDisposition(ilFileDelivery::DISP_ATTACHMENT);
		$this->ilFileDelivery->cleanDownloadFileName();
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

?>
