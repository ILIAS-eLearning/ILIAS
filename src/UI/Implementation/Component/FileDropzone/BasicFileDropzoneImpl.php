<?php
/**
 * Class BasicFileDropzone
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.1
 *
 * @package ILIAS\UI\Implementation\Component\FileDropzone
 */

namespace ILIAS\UI\Implementation\Component\FileDropzone;

use ILIAS\UI\Component\FileDropzone\BasicFileDropzone;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Triggerer;

abstract class BasicFileDropzoneImpl implements BasicFileDropzone {
	use Triggerer;

	private $darkendBackground = true;

	/**
	 * @inheritDoc
	 */
	function withDarkendBackground(bool $useDarkendBackground) {
		// TODO: Implement withDarkendBackground() method.
	}


	/**
	 * @inheritDoc
	 */
	function isDarkendBackground() {
		// TODO: Implement isDarkendBackground() method.
	}


	/**
	 * @inheritDoc
	 */
	function withOnDrop(Signal $signal) {
		// TODO: Implement withOnDrop() method.
	}


	/**
	 * @inheritDoc
	 */
	function appendOnDrop(Signal $signal) {
		// TODO: Implement appendOnDrop() method.
	}
}