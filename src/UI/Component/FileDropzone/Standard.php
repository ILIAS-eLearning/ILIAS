<?php
/**
 * Interface Standard
 *
 * Describes a standard dropzone which listens on file drop events from the browser.
 * Provides a default message to display.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.1
 *
 * @package ILIAS\UI\Component\FileDropzone
 */

namespace ILIAS\UI\Component\FileDropzone;

interface Standard extends BasicFileDropzone {

	/**
	 * Clones this instance and sets the passed in argument on it.
	 *
	 * @param string $message a message for a dropzone
	 *
	 * @return Standard a copy of this instance
	 */
	public function withMessage($message);


	/**
	 * @return string the message of this dropzone
	 */
	public function getMessage();

}