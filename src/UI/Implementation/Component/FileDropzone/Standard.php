<?php
/**
 * Class Standard
 *
 * Implementation of a file dropzone which provides a default message.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.2
 *
 * @package ILIAS\UI\Implementation\Component\FileDropzone
 */

namespace ILIAS\UI\Implementation\Component\FileDropzone;

class Standard extends BasicFileDropzoneImpl implements \ILIAS\UI\Component\FileDropzone\Standard {

	private $message = "";

	/**
	 * @inheritDoc
	 */
	function withDefaultMessage($message) {
		$clonedFileDropzone = clone $this;
		$clonedFileDropzone->message = $message;
		return $clonedFileDropzone;
	}


	/**
	 * @inheritDoc
	 */
	function getDefaultMessage() {
		return $this->message;
	}
}