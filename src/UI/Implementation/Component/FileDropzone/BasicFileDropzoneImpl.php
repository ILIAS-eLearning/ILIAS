<?php
/**
 * Class BasicFileDropzone
 *
 * Basic implementation for file dropzones. Provides functionality which are needed for all dropzones.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.2
 *
 * @package ILIAS\UI\Implementation\Component\FileDropzone
 */

namespace ILIAS\UI\Implementation\Component\FileDropzone;

use ILIAS\UI\Component\FileDropzone\BasicFileDropzone;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Triggerer;

abstract class BasicFileDropzoneImpl implements BasicFileDropzone {
	use Triggerer;

	const DROP_EVENT = "drop";

	private $darkendBackground = true;

	/**
	 * @inheritDoc
	 */
	function withDarkendBackground($useDarkendBackground) {
		$clonedFileDropzone = clone $this;
		$clonedFileDropzone->darkendBackground = $useDarkendBackground;
		return $clonedFileDropzone;
	}


	/**
	 * @inheritDoc
	 */
	function isDarkendBackground() {
		return $this->darkendBackground;
	}


	/**
	 * @inheritDoc
	 */
	function withOnDrop(Signal $signal) {
		$this->addTriggeredSignal($signal, self::DROP_EVENT);
	}


	/**
	 * @inheritDoc
	 */
	function appendOnDrop(Signal $signal) {
		$this->appendTriggeredSignal($signal, self::DROP_EVENT);
	}
}