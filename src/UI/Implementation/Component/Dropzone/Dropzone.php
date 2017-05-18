<?php
/**
 * Class Dropzone
 *
 * Basic implementation for dropzones. Provides functionality which are needed for all dropzones.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.3
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone
 */

namespace ILIAS\UI\Implementation\Component\Dropzone;

use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Triggerer;

abstract class Dropzone implements \ILIAS\UI\Component\Dropzone\Dropzone {
	use Triggerer;

	const DROP_EVENT = "drop";

	protected $darkendBackground = false;

	/**
	 * @inheritDoc
	 */
	public function withDarkendBackground($useDarkendBackground) {
		$clonedFileDropzone = clone $this;
		$clonedFileDropzone->darkendBackground = $useDarkendBackground;
		return $clonedFileDropzone;
	}


	/**
	 * @inheritDoc
	 */
	public function isDarkendBackground() {
		return $this->darkendBackground;
	}


	/**
	 * @inheritDoc
	 */
	public function withOnDrop(Signal $signal) {
		$this->addTriggeredSignal($signal, self::DROP_EVENT);
	}


	/**
	 * @inheritDoc
	 */
	public function appendOnDrop(Signal $signal) {
		$this->appendTriggeredSignal($signal, self::DROP_EVENT);
	}
}