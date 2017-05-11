<?php
namespace ILIAS\FileDelivery\FileDeliveryTypes;

use ILIAS\FileDelivery\ilFileDeliveryType;
use ILIAS\HTTP\GlobalHttpState;

require_once('./Services/FileDelivery/interfaces/int.ilFileDeliveryType.php');

/**
 * Class XSendfile
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 */
final class XSendfile implements ilFileDeliveryType {

	/**
	 * @var GlobalHttpState $httpService
	 */
	private $httpService;


	/**
	 * PHP constructor.
	 *
	 * @param GlobalHttpState $httpState
	 *
	 */
	public function __construct(GlobalHttpState $httpState) {
		$this->httpService = $httpState;
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

		$this->httpService->sendResponse();

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
