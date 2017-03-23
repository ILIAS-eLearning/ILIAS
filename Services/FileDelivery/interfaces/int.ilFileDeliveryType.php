<?php
use ILIAS\HTTP\Headers\HeadersInterface as Headers;

/**
 * Interface ilFileDeliveryType
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilFileDeliveryType {

	/**
	 * @param $path_to_file
	 * @return bool
	 */
	public function prepare($path_to_file);


	/**
	 * @param string $path_to_file absolute path to file
	 * @return bool
	 */
	public function deliver($path_to_file);


	/**
	 * @return bool
	 */
	public function supportsInlineDelivery();


	/**
	 * @return bool
	 */
	public function supportsAttachmentDelivery();


	/**
	 * @return bool
	 */
	public function supportsStreaming();
}
