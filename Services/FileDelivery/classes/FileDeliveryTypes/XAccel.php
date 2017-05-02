<?php
namespace ILIAS\FileDelivery\FileDeliveryTypes;

use ILIAS\FileDelivery\ilFileDeliveryType;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Response\ResponseHeader;

require_once('./Services/FileDelivery/interfaces/int.ilFileDeliveryType.php');

/**
 * Class XAccel
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 */
class XAccel implements ilFileDeliveryType {

	const DATA = 'data';
	const SECURED_DATA = 'secured-data';
	/**
	 * @var GlobalHttpState $httpService
	 */
	private $httpService;


	/**
	 * PHP constructor.
	 *
	 * @param GlobalHttpState $httpState
	 */
	public function __construct(GlobalHttpState $httpState) {
		$this->httpService = $httpState;
	}


	/**
	 * @inheritdoc
	 */
	public function prepare($path_to_file) {
		$response = $this->httpService->response()->withHeader(ResponseHeader::CONTENT_TYPE, '');

		$this->httpService->saveResponse($response);

		return true;
	}


	/**
	 * @inheritdoc
	 */
	public function deliver($path_to_file) {
		if (strpos($path_to_file, './' . self::DATA . '/') === 0) {
			$path_to_file = str_replace('./' . self::DATA . '/', '/' . self::SECURED_DATA . '/', $path_to_file);
		}

		$response = $this->httpService->response()->withHeader('X-Accel-Redirect', $path_to_file);

		$this->httpService->saveResponse($response);

		$this->httpService->sendResponse();
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
