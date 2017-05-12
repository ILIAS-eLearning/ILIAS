<?php
/**
 * Class Wrapper
 *
 * Implementation of a wrapper dropzone which can hold other ILIAS UI components.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.2
 *
 * @package ILIAS\UI\Implementation\Component\FileDropzone
 */

namespace ILIAS\UI\Implementation\Component\FileDropzone;

use ILIAS\UI\Component\Component;

class Wrapper extends BasicFileDropzoneImpl implements \ILIAS\UI\Component\FileDropzone\Wrapper {

	/**
	 * @var Component[]
	 */
	private $componentList;

	/**
	 * Wrapper constructor.
	 * An array of ILIAS UI components. At least, the array must contain one or more elements.
	 *
	 * @param Component[] $componentList an array of ILIAS UI components
	 */
	public function __construct(array $componentList) {
		$this->checkEmptyArray($componentList);
		$this->componentList = $componentList;
		$this->darkendBackground = true;
	}


	/**
	 * @inheritDoc
	 */
	public function withContent(array $componentList) {
		$this->checkEmptyArray($componentList);
		$clonedFileDropzone = clone $this;
		$clonedFileDropzone->componentList = $componentList;
		return $clonedFileDropzone;
	}


	/**
	 * @inheritDoc
	 */
	public function getContent() {
		return $this->componentList;
	}


	/**
	 * Checks the size of the passed in argument to 0.
	 *
	 * @param array $array the array to check
	 * @throws \LogicException if the passed in argument counts 0
	 */
	private function checkEmptyArray(array $array) {
		if (count($array) === 0) {
			throw new \LogicException("At least, one ILIAS UI component is required, otherwise this element is not visible.");
		}
	}

}