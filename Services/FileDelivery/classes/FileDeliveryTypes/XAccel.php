<?php
namespace ILIAS\FileDelivery\FileDeliveryTypes;

use ILIAS\DI\HTTPServices;
use ILIAS\HTTP\Response\ResponseHeader;

require_once('./Services/FileDelivery/interfaces/int.ilFileDeliveryType.php');

/**
 * Class XAccel
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class XAccel implements \ilFileDeliveryType {

	const DATA = 'data';
	const SECURED_DATA = 'secured-data';
	/**
	 * @var HTTPServices $httpService
	 */
	private $httpService;


	/**
	 * XAccel constructor.
	 *
	 */
	public function __construct() {
		$this->httpService = $GLOBALS["DIC"]->http();
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

		$this->httpService->renderResponse();
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
