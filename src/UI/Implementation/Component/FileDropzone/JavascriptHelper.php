<?php
/**
 * Class JavascriptHelper
 *
 * Helper class to create often used javascript commands a dropzone will need.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    09.05.17
 * @version 0.0.1
 *
 * @package ILIAS\UI\Implementation\Component\FileDropzone
 */

namespace ILIAS\UI\Implementation\Component\FileDropzone;

class JavascriptHelper {

	/**
	 * @var SimpleDropzone $simpleDropzone
	 */
	private $simpleDropzone;


	/**
	 * JavascriptHelper constructor.
	 *
	 * @param SimpleDropzone $simpleDropzone
	 */
	public function __construct(SimpleDropzone $simpleDropzone) {
		$this->simpleDropzone = $simpleDropzone;
	}

	public function enableDropDesign() {

	}

	public function disableDropDesign() {

	}

	public function triggerSignals(array $signalList) {

	}
}