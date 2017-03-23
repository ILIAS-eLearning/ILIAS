<?php
namespace ILIAS\FileDelivery\FileDeliveryTypes;

use ILIAS\DI\HTTPServices;

require_once('./Services/FileDelivery/interfaces/int.ilFileDeliveryType.php');

/**
 * Class XSendfile
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class XSendfile implements \ilFileDeliveryType {

	/**
	 * @var HTTPServices $httpService
	 */
	private $httpService;


	public function __construct() {
		$this->httpService = $GLOBALS["DIC"]->http();
	}


	/**
	 * @inheritdoc
	 */
	public function prepare($path_to_file) {
		//		$this->clearHeaders();
		//		$this->setDispositionHeaders();
		return true;
	}


	/**
	 * @inheritdoc
	 */
	public function deliver($path_to_file) {
		$response = $this->httpService->response()->withHeader('X-Sendfile', realpath($path_to_file));

		$this->httpService->saveResponse($response);

		$this->httpService->renderResponse();

		return true;
	}


	/**
	 * @inheritdoc
	 */
	public function supportsInlineDelivery() {
		return true;
	}


	/**
	 * @inheritdoc
	 */
	public function supportsAttachmentDelivery() {
		return true;
	}


	/**
	 * @inheritdoc
	 */
	public function supportsStreaming() {
		return true;
	}
}
