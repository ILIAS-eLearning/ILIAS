<?php
/**
 * Class Dropzone
 *
 * Basic implementation for dropzones. Provides functionality which are needed
 * for all dropzones.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.3
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Triggerer;

abstract class Dropzone implements \ILIAS\UI\Component\Dropzone\File\Dropzone {

	use Triggerer;
	const DROP_EVENT = "drop";
	protected $darkenedBackground = false;


	/**
	 * @inheritDoc
	 */
	public function withDarkenedBackground($useDarkenedBackground) {
		$clonedFileDropzone = clone $this;
		$clonedFileDropzone->darkenedBackground = $useDarkenedBackground;

		return $clonedFileDropzone;
	}


	/**
	 * @inheritDoc
	 */
	public function isDarkenedBackground() {
		return $this->darkenedBackground;
	}


	/**
	 * @inheritDoc
	 */
	public function withOnDrop(Signal $signal) {
		return $this->addTriggeredSignal($signal, self::DROP_EVENT);
	}


	/**
	 * @inheritDoc
	 */
	public function appendOnDrop(Signal $signal) {
		return $this->appendTriggeredSignal($signal, self::DROP_EVENT);
	}
}